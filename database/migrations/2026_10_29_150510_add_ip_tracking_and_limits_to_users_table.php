<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('registration_ip', 45)->nullable()->index()->after('email');
            $table->string('last_login_ip', 45)->nullable()->index()->after('registration_ip');
            $table->boolean('ip_limit_exempt')->default(false)->after('last_login_ip');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['registration_ip']);
            $table->dropIndex(['last_login_ip']);
            $table->dropColumn(['registration_ip', 'last_login_ip', 'ip_limit_exempt']);
        });
    }
};
