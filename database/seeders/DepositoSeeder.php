<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Deposito;

class DepositoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $depositos = [
            ['nombre' => 'DEPOSITO - OPE - AZOTEA', 'activo' => true],
            ['nombre' => 'DEPOSITO - MNTO - AZOTEA', 'activo' => true],
            ['nombre' => 'DEPOSITO 2 - OPE - PRIMER PISO', 'activo' => true],
            ['nombre' => 'DEPOSITO 1 - VU - PRIMER PISO', 'activo' => true],
            ['nombre' => 'CABINA DE CONTROL - OPE - PRIMER PISO', 'activo' => true],
            ['nombre' => 'DEPOSITO CAJA - COM - PRIMER PISO', 'activo' => true],
            ['nombre' => 'DEPOSITO 2 - OPE - SEMISOTANO', 'activo' => true],
            ['nombre' => 'DEPOSITO 3 - LAB - SOTANO 1', 'activo' => true],
            ['nombre' => 'DEPOSITO 4 - COM - SOTANO 1', 'activo' => true],
            ['nombre' => 'DEPOSITO 5 - SSUU - SOTANO 1', 'activo' => true],
        ];

        foreach ($depositos as $deposito) {
            Deposito::create($deposito);
        }
    }
}
