<?php

use App\Services\AuditLogService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        AuditLogService::sanitizeContaminatedIps();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Irreversible data sanitization for contaminated audit log IP addresses.
    }
};
