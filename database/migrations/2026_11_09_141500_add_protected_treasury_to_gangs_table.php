<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('gangs', 'protected_treasury')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->unsignedBigInteger('protected_treasury')->default(0)->after('treasury');
            });
        }

        // Protect the 100% kiosk restitution amounts from theft in gang raids
        // Gangs can spend this money freely on properties and garages, but rival attackers cannot loot it!
        $restitutionGangs = [
            3 => 104184200000, // LA FAMILIA
            5 => 1634600000,   // BadIntentions
            6 => 47166400000,  // Tokkie kartel
            10 => 413300000,   // test bende
        ];

        foreach ($restitutionGangs as $gangId => $restitutionAmount) {
            $gang = DB::table('gangs')->where('id', $gangId)->first();
            if ($gang) {
                // Protected amount is capped at current treasury (in case they already spent part of it)
                $protectedAmount = min((int) $gang->treasury, (int) $restitutionAmount);
                DB::table('gangs')->where('id', $gangId)->update([
                    'protected_treasury' => $protectedAmount,
                    // Give BadIntentions extra shield protection so they can organize in peace
                    'shield_until' => $gangId === 5 ? now()->addHours(48) : $gang->shield_until,
                ]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('gangs', 'protected_treasury')) {
            Schema::table('gangs', function (Blueprint $table) {
                $table->dropColumn('protected_treasury');
            });
        }
    }
};
