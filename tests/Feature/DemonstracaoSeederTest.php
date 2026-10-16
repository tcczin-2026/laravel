<?php

namespace Tests\Feature;

use App\Models\Console;
use App\Models\ConsoleHistorico;
use App\Models\User;
use Database\Seeders\AuxiliaresSeeder;
use Database\Seeders\DemonstracaoSeeder;
use Database\Seeders\RemoverDemonstracaoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class DemonstracaoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    public function test_cria_os_dados_da_demonstracao(): void
    {
        $this->seed(DemonstracaoSeeder::class);

        $this->assertSame(12, DB::table('console')->count());
        $this->assertSame(8, DB::table('controle')->count());
        $this->assertSame(14, DB::table('jogo')->count());
        $this->assertSame(8, DB::table('acessorio')->count());
        $this->assertDatabaseHas('users', ['email' => 'ana.demo@colecao.com', 'perfil' => 'gerente']);
        Storage::assertExists(DemonstracaoSeeder::ARQUIVO);
    }

    public function test_usuarios_da_demonstracao_conseguem_entrar(): void
    {
        $this->seed(DemonstracaoSeeder::class);

        $this->post('/login', ['email' => 'bruno.demo@colecao.com', 'password' => 'demo1234']);

        $this->assertAuthenticated();
    }

    public function test_listagens_mostram_paginacao_e_as_tres_cores_de_estoque(): void
    {
        $this->seed(DemonstracaoSeeder::class);
        $html = $this->actingAs(User::where('email', 'ana.demo@colecao.com')->first())->get('/jogo')->getContent();

        $this->assertStringContainsString('pagination', $html);
        $this->assertStringContainsString('stock-pill--ok', $html);
        $this->assertStringContainsString('stock-pill--low', $html);
        $this->assertStringContainsString('stock-pill--out', $html);
    }

    public function test_historico_tem_o_caso_15_17_19_feito_por_pessoas_diferentes(): void
    {
        $this->seed(DemonstracaoSeeder::class);
        $ps2 = Console::where('nome', 'PlayStation 2 Slim')->first();

        $linhas = ConsoleHistorico::where('console_id', $ps2->id)->where('campo', 'quantidade')->orderBy('id')->get();

        $this->assertSame(['15', '17'], $linhas->pluck('valor_anterior')->map(fn ($v) => (string) $v)->all());
        $this->assertSame(['Bruno Souza', 'Carla Lima'], $linhas->pluck('usuario')->all());
        $this->assertSame('14:35', $linhas[1]->created_at->format('H:i'));
        $this->assertSame(19, (int) $ps2->quantidade);

        // E o "Desfazer" da alteração errada volta para 17
        $this->actingAs(User::where('email', 'ana.demo@colecao.com')->first())
            ->post(route('historico.desfazer', ['console', $linhas[1]->id]));
        $this->assertSame(17, (int) $ps2->refresh()->quantidade);
    }

    public function test_nao_cria_duas_vezes(): void
    {
        $this->seed(DemonstracaoSeeder::class);
        $this->seed(DemonstracaoSeeder::class);

        $this->assertSame(12, DB::table('console')->count());
        $this->assertSame(1, DB::table('users')->where('email', 'ana.demo@colecao.com')->count());
    }

    public function test_remover_apaga_so_a_demonstracao(): void
    {
        // Dados "reais" que já existiam antes
        $this->seed(AuxiliaresSeeder::class);
        $real = Console::create([
            'nome' => 'Meu console real', 'quantidade' => 3, 'plataformaConsole' => 1, 'estado' => 1,
            'leitor' => 1, 'cores' => 1, 'vintage' => 1, 'aberto' => 1, 'colecionador' => 1,
        ]);
        $usuarioReal = $this->funcionario();
        $usuariosAntes = DB::table('users')->count();

        $this->seed(DemonstracaoSeeder::class);
        $this->seed(RemoverDemonstracaoSeeder::class);

        $this->assertSame(1, DB::table('console')->count());
        $this->assertDatabaseHas('console', ['id' => $real->id]);
        $this->assertSame(0, DB::table('controle')->count() + DB::table('jogo')->count() + DB::table('acessorio')->count());
        $this->assertSame($usuariosAntes, DB::table('users')->count());
        $this->assertDatabaseHas('users', ['id' => $usuarioReal->id]);

        // só sobra o histórico do console real
        $this->assertSame(0, ConsoleHistorico::where('console_id', '!=', $real->id)->count());
        $this->assertTrue(ConsoleHistorico::where('console_id', $real->id)->exists());
        Storage::assertMissing(DemonstracaoSeeder::ARQUIVO);
    }

    public function test_da_pra_criar_de_novo_depois_de_remover(): void
    {
        $this->seed(DemonstracaoSeeder::class);
        $this->seed(RemoverDemonstracaoSeeder::class);
        $this->seed(DemonstracaoSeeder::class);

        $this->assertSame(12, DB::table('console')->count());
    }
}
