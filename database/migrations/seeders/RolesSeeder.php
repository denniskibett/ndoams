<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        // Delete existing data safely
        // DB::table('roles')->delete(); // do NOT use truncate

        $now = Carbon::now();

        // Insert fresh roles
        DB::table('roles')->insert([
            ['id' => 1, 'name' => 'admin', 'slug' => 'admin', 'description' => 'Administrator with full access', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 2, 'name' => 'marriage_registrar', 'slug' => 'marriage_registrar', 'description' => 'Registrar responsible for marriage registrations', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 3, 'name' => 'marriage_teller', 'slug' => 'marriage_teller', 'description' => 'Teller responsible for marriage announcements', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 4, 'name' => 'data_clerk', 'slug' => 'data_clerk', 'description' => 'Clerk responsible for data entry and management', 'created_at' => $now, 'updated_at' => $now],
            ['id' => 5, 'name' => 'user', 'slug' => '', 'description' => '', 'created_at'=>$now, 'updated_at'=>$now],
        ]);
    }
}
