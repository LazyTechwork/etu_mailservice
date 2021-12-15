<?php

namespace Database\Seeders;

use App\Models\Recipient;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $vk_ids = [242521347];
        $db_query = array_map(static function ($i) {
            return ['vk' => $i];
        }, $vk_ids);
        Recipient::create($db_query);
    }
}
