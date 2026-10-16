<?php

namespace Tests\Feature;

use Database\Seeders\AuxiliaresSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AuxiliaresSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_preenche_as_tabelas_auxiliares_com_os_ids_certos(): void
    {
        $this->seed(AuxiliaresSeeder::class);

        $this->assertSame(3, DB::table('plataforma')->count());
        $this->assertSame(11, DB::table('cor')->count());
        $this->assertSame(2, DB::table('retro')->count());
        $this->assertSame(2, DB::table('usado')->count());
        $this->assertSame(2, DB::table('digital')->count());
        $this->assertSame(2, DB::table('desbloqueado')->count());
        $this->assertSame(2, DB::table('edicao_especial')->count());

        $this->assertDatabaseHas('cor', ['id' => 7, 'nome' => 'preto']);
        $this->assertDatabaseHas('plataforma', ['id' => 3, 'nome' => 'nintendo']);
    }

    public function test_rodar_duas_vezes_nao_duplica(): void
    {
        $this->seed(AuxiliaresSeeder::class);
        $this->seed(AuxiliaresSeeder::class);

        $this->assertSame(11, DB::table('cor')->count());
    }

    public function test_corrige_nome_alterado_sem_mudar_o_id(): void
    {
        DB::table('cor')->insert(['id' => 7, 'nome' => 'pretoo']);

        $this->seed(AuxiliaresSeeder::class);

        $this->assertDatabaseHas('cor', ['id' => 7, 'nome' => 'preto']);
    }

    public function test_formularios_ficam_com_as_opcoes(): void
    {
        $this->seed(AuxiliaresSeeder::class);

        $this->actingAs($this->funcionario())->get('/console/create')
            ->assertSee('playstation')->assertSee('vermelho')->assertSee('desbloqueado');
    }
}
