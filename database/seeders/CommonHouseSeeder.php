<?php

namespace Database\Seeders;

use App\Models\CommonHouse;
use Illuminate\Database\Seeder;

class CommonHouseSeeder extends Seeder
{
    public function run(): void
    {
        $houses = [
            [
                'name' => 'Jesuit Curia',
                'code' => 'CUR',
                'assistancy_id' => 1,
                'address' => 'Borgo Santo Spirito 4, 00193 Rome, Italy',
                'contact_details' => [
                    'phone' => '+39 06 698 681',
                    'email' => 'info@sjcuria.org',
                    'website' => 'www.sjcuria.global'
                ],
                'description' => 'General Curia of the Society of Jesus',
                'is_active' => true
            ],
            [
                'name' => 'Satya Nilayam',
                'code' => 'SNM',
                'assistancy_id' => 1,
                'address' => 'Satya Nilayam, Chennai, Tamil Nadu',
                'contact_details' => [
                    'phone' => '+91 44 2462 3583',
                    'email' => 'info@satyanilayam.org'
                ],
                'description' => 'Jesuit Philosophy Centre',
                'is_active' => true
            ]
        ];

        foreach ($houses as $house) {
            CommonHouse::firstOrCreate(
                ['code' => $house['code']],
                $house
            );
        }
    }
} 