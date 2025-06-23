<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
// use Illuminate\Support\Facades\DB;
use App\Models\Customer;
use Illuminate\Support\Facades\Hash;

class TableUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Customer::create([
            'table_number' => '101',
            'password' => Hash::make('table101pass')
        ]);
        
        Customer::create([
            'table_number' => '102',
            'password' => Hash::make('table102pass')
        ]);

        Customer::create([
            'table_number' => '103',
            'password' => Hash::make('table103pass')
        ]);

        Customer::create([
            'table_number'=>'guest',
            'password' => Hash::make('guestpass')
        ]);

        
    }
}
