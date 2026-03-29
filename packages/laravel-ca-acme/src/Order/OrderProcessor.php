<?php

declare(strict_types=1);

namespace CA\Acme\Order;

use CA\Models\AuthorizationStatus;
use CA\Models\ChallengeStatus;
use CA\Models\OrderStatus;
use CA\Acme\Models\AcmeAuthorization;
use CA\Acme\Models\AcmeOrder;
use Illuminate\Support\Facades\DB;

class OrderProcessor
{
    /**
     * Check if all authorizations for an order are valid and transition order to READY.
     */
    public function checkAndTransition(AcmeOrder $order): AcmeOrder
    {
        if ($order->status !== OrderStatus::PENDING) {
            return $order;
        }

        $order->load('authorizations');

        $allValid = $order->authorizations->every(
            fn(AcmeAuthorization $auth): bool => $auth->status === AuthorizationStatus::VALID,
        );

        $anyInvalid = $order->authorizations->contains(
            fn(AcmeAuthorization $auth): bool => $auth->status === AuthorizationStatus::INVALID,
        );

        if ($anyInvalid) {
            $order->update(['status' => OrderStatus::INVALID]);

            return $order->fresh();
        }

        if ($allValid) {
            $order->update(['status' => OrderStatus::READY]);

            return $order->fresh();
        }

        return $order;
    }

    /**
     * Process a challenge completion and update authorization/order status.
     */
    public function processChallenge(\CA\Acme\Models\AcmeChallenge $challenge): void
    {
        if ($challenge->status !== ChallengeStatus::VALID) {
            return;
        }

        $authorization = $challenge->authorization;

        // If any challenge is valid, mark the authorization as valid
        $authorization->update(['status' => AuthorizationStatus::VALID]);

        // Invalidate other pending challenges for this authorization
        $authorization->challenges()
            ->where('id', '!=', $challenge->id)
            ->where('status', ChallengeStatus::PENDING)
            ->update(['status' => ChallengeStatus::INVALID]);

        // Check if the order should transition
        $order = $authorization->order;

        if ($order !== null) {
            $this->checkAndTransition($order);
        }
    }

    /**
     * Handle expired orders: mark as expired and clean up.
     */
    public function expireOrders(): int
    {
        return DB::transaction(function (): int {
            $expiredOrders = AcmeOrder::where('expires_at', '<', now())
                ->whereIn('status', [OrderStatus::PENDING, OrderStatus::READY])
                ->get();

            foreach ($expiredOrders as $order) {
                $order->update(['status' => OrderStatus::EXPIRED]);

                // Expire associated authorizations
                $order->authorizations()
                    ->whereIn('status', [AuthorizationStatus::PENDING, AuthorizationStatus::VALID])
                    ->update([
                        'status' => AuthorizationStatus::EXPIRED,
                    ]);
            }

            return $expiredOrders->count();
        });
    }

    /**
     * Handle expired authorizations.
     */
    public function expireAuthorizations(): int
    {
        return AcmeAuthorization::where('expires_at', '<', now())
            ->where('status', AuthorizationStatus::PENDING)
            ->update(['status' => AuthorizationStatus::EXPIRED]);
    }

    /**
     * Full cleanup: expire orders, authorizations, and remove old data.
     *
     * @return array<string, int>
     */
    public function cleanup(): array
    {
        return [
            'expired_orders' => $this->expireOrders(),
            'expired_authorizations' => $this->expireAuthorizations(),
        ];
    }
}
