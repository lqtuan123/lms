<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TMotionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('t_motions')->insert([
            [
                'title' => 'Like',
                'icon' => '👍',
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Love',
                'icon' => '❤️', // Biểu tượng trái tim
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Haha',
                'icon' => '😁', // Biểu tượng mặt cười
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Wow',
                'icon' => '😮', // Biểu tượng ngạc nhiên
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Sad',
                'icon' => '😢', // Biểu tượng buồn
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Angry',
                'icon' => '😡', // Biểu tượng tức giận
                'status' => 1, // 1: Active
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
