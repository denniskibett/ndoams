<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run()
    {
        $categories = [
            // Marriage Types
            ['name' => 'Christian', 'type' => 'marriage_type', 'icon' => 'church', 'color' => 'blue'],
            ['name' => 'Muslim', 'type' => 'marriage_type', 'icon' => 'mosque', 'color' => 'green'],
            ['name' => 'Hindu', 'type' => 'marriage_type', 'icon' => 'temple', 'color' => 'orange'],
            ['name' => 'Customary', 'type' => 'marriage_type', 'icon' => 'handshake', 'color' => 'purple'],
            ['name' => 'Civil', 'type' => 'marriage_type', 'icon' => 'balance-scale', 'color' => 'gray'],
            ['name' => 'Special Church', 'type' => 'marriage_type', 'icon' => 'church', 'color' => 'indigo'],
            ['name' => 'Special Civil', 'type' => 'marriage_type', 'icon' => 'balance-scale', 'color' => 'pink'],

            // Marriage Status
            ['name' => 'Pending', 'type' => 'marriage_status', 'icon' => 'hourglass', 'color' => 'yellow'],
            ['name' => 'Completed', 'type' => 'marriage_status', 'icon' => 'check', 'color' => 'green'],
            ['name' => 'Cancelled', 'type' => 'marriage_status', 'icon' => 'x-circle', 'color' => 'red'],
            ['name' => 'Annulled', 'type' => 'marriage_status', 'icon' => 'ban', 'color' => 'gray'],

            // Verification Status
            ['name' => 'Pending', 'type' => 'verification_status', 'icon' => 'hourglass', 'color' => 'yellow'],
            ['name' => 'Verified', 'type' => 'verification_status', 'icon' => 'check', 'color' => 'green'],
            ['name' => 'Rejected', 'type' => 'verification_status', 'icon' => 'x-circle', 'color' => 'red'],

            // ID Types
            ['name' => 'National ID', 'type' => 'id_type', 'icon' => 'id-card', 'color' => 'blue'],
            ['name' => 'Passport', 'type' => 'id_type', 'icon' => 'passport', 'color' => 'green'],
            ['name' => 'Birth Certificate', 'type' => 'id_type', 'icon' => 'file', 'color' => 'orange'],

            // Parent Types
            ['name' => 'Father', 'type' => 'parent_type', 'icon' => 'male', 'color' => 'blue'],
            ['name' => 'Mother', 'type' => 'parent_type', 'icon' => 'female', 'color' => 'pink'],

            // Witness Types
            ['name' => 'Husband Side', 'type' => 'witness_type', 'icon' => 'male', 'color' => 'blue'],
            ['name' => 'Wife Side', 'type' => 'witness_type', 'icon' => 'female', 'color' => 'pink'],
        ];

        foreach ($categories as $category) {
            Category::updateOrCreate(
                ['name' => $category['name'], 'type' => $category['type']],
                $category
            );
        }
    }
}
