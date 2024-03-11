<?php

namespace Database\Seeders;

use App\Models\Parameter;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ParameterSeeder2 extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        
        $data = [
            
            [
                'par_code' => 'SFTP_HOST_SANTANDER',
                'par_value' => '200.75.7.235',
                'par_description' => 'Host SFTP SANTANDER',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SFTP_USERNAME_SANTANDER',
                'par_value' => 'flowsa_bsanq',
                'par_description' => 'Username SFTP SANTANDER',
                'par_created_at' => now()
            ],
            [
                'par_code' => 'SFTP_PASSWORD_SANTANDER',
                'par_value' => 'WXv+VC7G',
                'par_description' => 'Password SFTP SANTANDER',
                'par_created_at' => now()
            ],
        ];
        Parameter::insertOrIgnore($data);
    }
}
