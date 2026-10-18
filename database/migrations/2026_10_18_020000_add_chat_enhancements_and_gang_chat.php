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
        Schema::table('gangs', function (Blueprint $table) {
            if (! Schema::hasColumn('gangs', 'has_gang_chat')) {
                $table->boolean('has_gang_chat')->default(false)->after('gang_stars');
            }
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('chat_messages', 'reply_to_id')) {
                $table->unsignedBigInteger('reply_to_id')->nullable()->after('message');
                $table->string('reply_to_name')->nullable()->after('reply_to_id');
                $table->string('reply_to_snippet', 255)->nullable()->after('reply_to_name');
            }
            if (! Schema::hasColumn('chat_messages', 'hearts_count')) {
                $table->unsignedInteger('hearts_count')->default(0)->after('reply_to_snippet');
                $table->json('hearts_users')->nullable()->after('hearts_count');
            }
        });

        Schema::table('direct_messages', function (Blueprint $table) {
            if (! Schema::hasColumn('direct_messages', 'reply_to_id')) {
                $table->unsignedBigInteger('reply_to_id')->nullable()->after('message');
                $table->string('reply_to_name')->nullable()->after('reply_to_id');
                $table->string('reply_to_snippet', 255)->nullable()->after('reply_to_name');
            }
            if (! Schema::hasColumn('direct_messages', 'hearts_count')) {
                $table->unsignedInteger('hearts_count')->default(0)->after('reply_to_snippet');
                $table->json('hearts_users')->nullable()->after('hearts_count');
            }
            if (! Schema::hasColumn('direct_messages', 'read_at')) {
                $table->timestamp('read_at')->nullable()->after('is_read');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('direct_messages', function (Blueprint $table) {
            $table->dropColumn(['reply_to_id', 'reply_to_name', 'reply_to_snippet', 'hearts_count', 'hearts_users', 'read_at']);
        });

        Schema::table('chat_messages', function (Blueprint $table) {
            $table->dropColumn(['reply_to_id', 'reply_to_name', 'reply_to_snippet', 'hearts_count', 'hearts_users']);
        });

        Schema::table('gangs', function (Blueprint $table) {
            $table->dropColumn('has_gang_chat');
        });
    }
};
