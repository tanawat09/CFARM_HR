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
        // Merge duplicates: for each name, keep the row with the smallest id,
        // reassign any employees referencing the duplicates to that keeper, then delete duplicates.
        $duplicates = DB::table('positions')
            ->select('name', DB::raw('MIN(id) as keeper_id'))
            ->groupBy('name')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplicates as $dup) {
            $dupIds = DB::table('positions')
                ->where('name', $dup->name)
                ->where('id', '!=', $dup->keeper_id)
                ->pluck('id');

            if ($dupIds->isNotEmpty()) {
                DB::table('employees')
                    ->whereIn('position_id', $dupIds)
                    ->update(['position_id' => $dup->keeper_id]);

                DB::table('positions')->whereIn('id', $dupIds)->delete();
            }
        }

        Schema::table('positions', function (Blueprint $table) {
            $table->unique('name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('positions', function (Blueprint $table) {
            $table->dropUnique(['name']);
        });
    }
};
