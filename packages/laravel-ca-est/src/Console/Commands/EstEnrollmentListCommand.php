<?php

declare(strict_types=1);

namespace CA\Est\Console\Commands;

use CA\Est\Models\EstEnrollment;
use Illuminate\Console\Command;

class EstEnrollmentListCommand extends Command
{
    protected $signature = 'ca:est:enrollments
        {--ca= : Filter by Certificate Authority UUID}
        {--type= : Filter by enrollment type (enroll, reenroll, serverkeygen)}
        {--status= : Filter by status (pending, completed, failed, revoked)}
        {--limit=50 : Maximum number of records to display}';

    protected $description = 'List EST enrollment records';

    public function handle(): int
    {
        $query = EstEnrollment::query()
            ->with('certificateAuthority')
            ->orderBy('created_at', 'desc');

        if ($caId = $this->option('ca')) {
            $query->forCa($caId);
        }

        if ($type = $this->option('type')) {
            $query->byType($type);
        }

        if ($status = $this->option('status')) {
            $query->byStatus($status);
        }

        $limit = (int) $this->option('limit');
        $enrollments = $query->limit($limit)->get();

        if ($enrollments->isEmpty()) {
            $this->info('No enrollment records found.');

            return self::SUCCESS;
        }

        $rows = $enrollments->map(fn (EstEnrollment $enrollment) => [
            $enrollment->id,
            $enrollment->ca_id,
            $enrollment->type,
            $enrollment->status,
            $enrollment->client_identity ?? '-',
            $enrollment->ip_address ?? '-',
            $enrollment->certificate_id ?? '-',
            $enrollment->created_at?->format('Y-m-d H:i:s') ?? '-',
        ])->toArray();

        $this->table(
            ['ID', 'CA', 'Type', 'Status', 'Identity', 'IP', 'Certificate', 'Created'],
            $rows,
        );

        $this->info("Showing {$enrollments->count()} of {$query->count()} total records.");

        return self::SUCCESS;
    }
}
