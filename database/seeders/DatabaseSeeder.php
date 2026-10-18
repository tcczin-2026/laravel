<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(AuxiliaresSeeder::class);
        $this->call(AcervoSeeder::class); // itens reais (consoles, controles, jogos, acessórios)

        // $this->call(FornecedorSeeder::class);
        // $this->call(ContatoSeeder::class);
    }
}
