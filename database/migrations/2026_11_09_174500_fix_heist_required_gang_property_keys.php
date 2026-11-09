<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('heists')) {
            return;
        }

        $targetColumns = ['required_gang_property', 'required_property_keys'];

        foreach ($targetColumns as $col) {
            if (! Schema::hasColumn('heists', $col)) {
                continue;
            }

            $rows = DB::table('heists')->whereNotNull($col)->get(['id', $col]);

            foreach ($rows as $row) {
                $raw = $row->$col;
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

                if (is_array($decoded)) {
                    $modified = false;
                    foreach ($decoded as $idx => $key) {
                        if ($key === 'cartel_villa') {
                            $decoded[$idx] = 'gang_villa';
                            $modified = true;
                        } elseif ($key === 'sky_penthouse') {
                            $decoded[$idx] = 'luxe_penthouse';
                            $modified = true;
                        }
                    }

                    if ($modified) {
                        DB::table('heists')->where('id', $row->id)->update([
                            $col => json_encode(array_values($decoded)),
                        ]);
                    }
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('heists')) {
            return;
        }

        $targetColumns = ['required_gang_property', 'required_property_keys'];

        foreach ($targetColumns as $col) {
            if (! Schema::hasColumn('heists', $col)) {
                continue;
            }

            $rows = DB::table('heists')->whereNotNull($col)->get(['id', $col]);

            foreach ($rows as $row) {
                $raw = $row->$col;
                $decoded = is_string($raw) ? json_decode($raw, true) : $raw;

                if (is_array($decoded)) {
                    $modified = false;
                    foreach ($decoded as $idx => $key) {
                        if ($key === 'gang_villa') {
                            $decoded[$idx] = 'cartel_villa';
                            $modified = true;
                        } elseif ($key === 'luxe_penthouse') {
                            $decoded[$idx] = 'sky_penthouse';
                            $modified = true;
                        }
                    }

                    if ($modified) {
                        DB::table('heists')->where('id', $row->id)->update([
                            $col => json_encode(array_values($decoded)),
                        ]);
                    }
                }
            }
        }
    }
};
