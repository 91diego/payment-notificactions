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
                    $notification_cases = [
                        'caso_1' => '<7',
                        'caso_2' => '>15',
                        'caso_3' => '>30',
                        'caso_4' => '>60',
                        'caso_5' => '>=90',
                    ];
                    break;
                case 1:
                    $name = 'ANUVA';
                    $notification_cases = [
                        'caso_1' => '<7',
                        'caso_2' => '>15',
                        'caso_3' => '>30',
                        'caso_4' => '>60',
                        'caso_5' => '>=90',
                    ];
                    break;
                case 2:
                    $name = 'ALADRA';
                    $notification_cases = [
                        'caso_1' => '<7',
                        'caso_2' => '>15',
                        'caso_3' => '>30',
                        'caso_4' => '>60',
                        'caso_5' => '>=90',
                    ];
                    break;
            }
            DB::table('connections')->insert([
                'name' => $name,
                'database' => 'sqlsrv',
                'notification_cases' => json_encode($notification_cases),
            ]);
        }
    }
}
