@php
    $badgeClasses = match($status ?? 'unknown') {
        'active' => 'bg-green-100 text-green-800',
        'revoked' => 'bg-red-100 text-red-800',
        'expired' => 'bg-gray-100 text-gray-800',
        'suspended' => 'bg-orange-100 text-orange-800',
        'on_hold' => 'bg-yellow-100 text-yellow-800',
        'pending' => 'bg-yellow-100 text-yellow-800',
        'approved' => 'bg-blue-100 text-blue-800',
        'rejected' => 'bg-red-100 text-red-800',
        'signed' => 'bg-green-100 text-green-800',
        'rotated' => 'bg-purple-100 text-purple-800',
        'compromised' => 'bg-red-100 text-red-800',
        'destroyed' => 'bg-gray-200 text-gray-600',
        default => 'bg-gray-100 text-gray-800',
    };
@endphp

<span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $badgeClasses }}">
    {{ ucfirst(str_replace('_', ' ', $status ?? 'unknown')) }}
</span>
