<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CouPonSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table("coupons")->insert([
            [
                'id' => 1,
                'code' => 'DISCOUNT10',
                'discount' => 0.10, // 10% giảm giá
                'expires_at' => '2024-12-31', // Ngày hết hạn
            ],
            [
                'id' => 2,
                'code' => 'DISCOUNT50',
                'discount' => 0.50, // 10% giảm giá
                'expires_at' => '2024-12-31', // Ngày hết hạn
            ],

        ]);
    }
}
