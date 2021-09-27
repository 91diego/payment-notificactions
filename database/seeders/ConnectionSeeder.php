<?php

namespace Database\Seeders;

use App\Models\Connection;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ConnectionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Create development connections
        for ($i=0; $i < 3 ; $i++) {
            switch ($i) {
                case 0:
                    $name = 'BRASILIA';
                    break;
                case 1:
                    $name = 'ANUVA';
                    break;
                case 2:
                    $name = 'ALADRA';
                    break;
            }
            DB::table('connections')->insert([
                'name' => $name,
                'database' => 'sqlsrv',
            ]);
        }
    }
}
