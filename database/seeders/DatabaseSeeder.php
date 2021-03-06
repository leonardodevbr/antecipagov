<?php

namespace Database\Seeders;

use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
         DB::table('products')->insert([
             'name' => 'AntecipaGOV',
             'created_at' => Carbon::now(),
             'updated_at' => Carbon::now()
         ]);
    }
}
