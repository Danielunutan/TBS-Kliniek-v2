<?php

use App\Models\Character;
use App\Models\User;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            if (! Schema::hasColumn('users', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('email');
            }
            if (! Schema::hasColumn('users', 'is_onboarded')) {
                $table->boolean('is_onboarded')->default(false)->after('birthdate');
            }
        });

        Schema::table('characters', function (Blueprint $table): void {
            if (! Schema::hasColumn('characters', 'username')) {
                $table->string('username', 30)->nullable()->unique()->after('name');
            }
            if (! Schema::hasColumn('characters', 'display_name')) {
                $table->string('display_name', 50)->nullable()->after('username');
            }
            if (! Schema::hasColumn('characters', 'birthdate')) {
                $table->date('birthdate')->nullable()->after('gender');
            }
            if (! Schema::hasColumn('characters', 'profile_color')) {
                $table->string('profile_color', 20)->default('purple')->after('birthdate');
            }
            if (! Schema::hasColumn('characters', 'is_onboarded')) {
                $table->boolean('is_onboarded')->default(false)->after('profile_color');
            }
            if (! Schema::hasColumn('characters', 'avatar_custom')) {
                $table->string('avatar_custom')->nullable()->after('avatar');
            }
        });

        // Backfill existing characters so existing players aren't locked out
        User::query()->update(['is_onboarded' => true]);

        $usedUsernames = [];
        foreach (Character::all() as $char) {
            $char->is_onboarded = true;
            $char->display_name = $char->name ?: 'Patiënt';

            // Clean username: lowercase letters, digits, underscore only
            $clean = strtolower((string) preg_replace('/[^a-zA-Z0-9_-]/', '', (string) $char->name));
            if (empty($clean) || strlen($clean) < 3) {
                if (str_contains(strtolower((string) $char->name), 'daniel')) {
                    $clean = 'daniel';
                } else {
                    $clean = 'inmate_'.strtolower(Str::random(5));
                }
            }

            // Ensure unique
            $candidate = $clean;
            $counter = 1;
            while (isset($usedUsernames[$candidate]) || Character::where('username', $candidate)->where('id', '!=', $char->id)->exists()) {
                $candidate = substr($clean, 0, 15).'_'.$counter;
                $counter++;
            }

            $usedUsernames[$candidate] = true;
            $char->username = $candidate;
            $char->profile_color = $char->profile_color ?: 'purple';
            $char->save();
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn(['birthdate', 'is_onboarded']);
        });

        Schema::table('characters', function (Blueprint $table): void {
            $table->dropColumn([
                'username',
                'display_name',
                'birthdate',
                'profile_color',
                'is_onboarded',
                'avatar_custom',
            ]);
        });
    }
};
