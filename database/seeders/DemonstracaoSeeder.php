<?php

namespace Database\Seeders;

use App\Models\Acessorio;
use App\Models\Console;
use App\Models\Controle;
use App\Models\Jogo;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

/**
 * Dados TEMPORÁRIOS para apresentação: consoles, controles, jogos,
 * acessórios, 3 usuários e um histórico de alterações feito por
 * pessoas diferentes (inclui o caso 15 -> 17 -> 19 pra mostrar o "Desfazer").
 *
 * Os ids criados ficam anotados em storage/app/demonstracao.json, e o
 * RemoverDemonstracaoSeeder apaga exatamente esses registros, sem tocar
 * nos dados reais.
 *
 *     php artisan db:seed --class=DemonstracaoSeeder         (cria)
 *     php artisan db:seed --class=RemoverDemonstracaoSeeder  (apaga)
 *
 * Usuários de demonstração (senha: demo1234):
 *     ana.demo@colecao.com    - gerente
 *     bruno.demo@colecao.com  - funcionário
 *     carla.demo@colecao.com  - funcionário
 */
class DemonstracaoSeeder extends Seeder
{
    public const ARQUIVO = 'demonstracao.json';

    private array $criados = ['console' => [], 'controle' => [], 'jogo' => [], 'acessorio' => [], 'users' => []];

    private array $idsAux = [];

    public function run(): void
    {
        if (Storage::exists(self::ARQUIVO)) {
            $this->command?->error('A demonstração já está no banco. Rode o RemoverDemonstracaoSeeder antes de criar de novo.');

            return;
        }

        $this->call(AuxiliaresSeeder::class); // garante as opções dos selects

        DB::transaction(function () {
            [$ana, $bruno, $carla] = $this->usuarios();

            // Cadastros espalhados pelos últimos dias, por pessoas diferentes
            $consoles   = $this->consoles($bruno, $carla);
            $this->controles($bruno, $carla, $consoles);
            $jogos      = $this->jogos($bruno, $carla);
            $acessorios = $this->acessorios($bruno, $carla);

            $this->alteracoes($ana, $bruno, $carla, $consoles, $jogos, $acessorios);
        });

        Auth::forgetGuards();
        Storage::put(self::ARQUIVO, json_encode($this->criados, JSON_PRETTY_PRINT));

        $this->command?->info(sprintf(
            'Demonstração criada: %d consoles, %d controles, %d jogos, %d acessórios e %d usuários (senha demo1234).',
            count($this->criados['console']), count($this->criados['controle']),
            count($this->criados['jogo']), count($this->criados['acessorio']), count($this->criados['users'])
        ));
    }

    // ------------------------------------------------------------ usuários

    private function usuarios(): array
    {
        $dados = [
            ['Ana Ribeiro',  'ana.demo@colecao.com',   'gerente'],
            ['Bruno Souza',  'bruno.demo@colecao.com', 'funcionario'],
            ['Carla Lima',   'carla.demo@colecao.com', 'funcionario'],
        ];

        return array_map(function ($u) {
            $usuario = User::create(['name' => $u[0], 'email' => $u[1], 'perfil' => $u[2], 'password' => 'demo1234']);
            $this->criados['users'][] = $usuario->id;

            return $usuario;
        }, $dados);
    }

    // ------------------------------------------------------------ cadastros

    private function consoles(User $bruno, User $carla): array
    {
        // nome, plataforma, qtd, estado, leitor, cor, retro/atual, desbloqueado, edição
        $lista = [
            ['PlayStation 2 Slim',            'playstation', 15, 'semi-novo', 'fisico',  'preto',    'retro', 'bloqueado',    'padrao'],
            ['PlayStation 3 Super Slim',      'playstation',  6, 'semi-novo', 'fisico',  'branco',   'retro', 'bloqueado',    'padrao'],
            ['PlayStation 5 Digital',         'playstation', 22, 'novo',      'digital', 'branco',   'atual', 'bloqueado',    'padrao'],
            ['PS5 Edição 30 Anos',            'playstation',  2, 'novo',      'fisico',  'cinza',    'atual', 'bloqueado',    'especial'],
            ['Xbox Clássico',                 'xbox',         0, 'semi-novo', 'fisico',  'preto',    'retro', 'desbloqueado', 'padrao'],
            ['Xbox One S',                    'xbox',        12, 'semi-novo', 'fisico',  'branco',   'atual', 'bloqueado',    'padrao'],
            ['Xbox Series X Halo Edition',    'xbox',         3, 'novo',      'fisico',  'cinza',    'atual', 'bloqueado',    'especial'],
            ['Super Nintendo',                'nintendo',     4, 'semi-novo', 'fisico',  'cinza',    'retro', 'bloqueado',    'padrao'],
            ['Nintendo 64',                   'nintendo',     7, 'semi-novo', 'fisico',  'preto',    'retro', 'bloqueado',    'padrao'],
            ['GameCube',                      'nintendo',     0, 'semi-novo', 'fisico',  'roxo',     'retro', 'desbloqueado', 'padrao'],
            ['Nintendo Switch OLED',          'nintendo',    18, 'novo',      'fisico',  'branco',   'atual', 'bloqueado',    'padrao'],
            ['Switch OLED Zelda Edition',     'nintendo',     1, 'novo',      'fisico',  'verde',    'atual', 'bloqueado',    'especial'],
        ];

        $criados = [];
        foreach ($lista as $i => [$nome, $plat, $qtd, $estado, $leitor, $cor, $retro, $aberto, $edicao]) {
            $criados[$nome] = $this->como($i % 2 ? $carla : $bruno, $this->diasAtras(9 - intdiv($i, 2), 9 + $i), fn () => Console::create([
                'nome' => $nome, 'quantidade' => $qtd,
                'plataformaConsole' => $this->aux('plataforma', $plat), 'estado' => $this->aux('usado', $estado),
                'leitor' => $this->aux('digital', $leitor), 'cores' => $this->aux('cor', $cor),
                'vintage' => $this->aux('retro', $retro), 'aberto' => $this->aux('desbloqueado', $aberto),
                'colecionador' => $this->aux('edicao_especial', $edicao),
            ]));
            $this->criados['console'][] = $criados[$nome]->id;
        }

        return $criados;
    }

    private function controles(User $bruno, User $carla, array $consoles): void
    {
        // nome, plataforma, qtd, estado, cor, retro/atual, edição, console onde é usado
        $lista = [
            ['DualShock 2',                 'playstation',  9, 'semi-novo', 'preto',    'retro', 'padrao',   'PlayStation 2 Slim'],
            ['DualSense',                   'playstation', 25, 'novo',      'branco',   'atual', 'padrao',   'PlayStation 5 Digital'],
            ['DualSense Edge',              'playstation',  4, 'novo',      'branco',   'atual', 'especial', 'PlayStation 5 Digital'],
            ['Controle Xbox Duke',          'xbox',         0, 'semi-novo', 'preto',    'retro', 'padrao',   'Xbox Clássico'],
            ['Controle Xbox Wireless',      'xbox',        14, 'novo',      'azul',     'atual', 'padrao',   'Xbox One S'],
            ['Controle N64',                'nintendo',     5, 'semi-novo', 'cinza',    'retro', 'padrao',   'Nintendo 64'],
            ['Joy-Con Neon',                'nintendo',    11, 'novo',      'vermelho', 'atual', 'padrao',   'Nintendo Switch OLED'],
            ['Pro Controller Switch',       'nintendo',     3, 'novo',      'preto',    'atual', 'padrao',   'Nintendo Switch OLED'],
        ];

        foreach ($lista as $i => [$nome, $plat, $qtd, $estado, $cor, $retro, $edicao, $console]) {
            $controle = $this->como($i % 2 ? $bruno : $carla, $this->diasAtras(6 - intdiv($i, 3), 10 + $i), fn () => Controle::create([
                'nome' => $nome, 'quantidade' => $qtd, 'dispositivo' => $consoles[$console]->id,
                'plataformaControle' => $this->aux('plataforma', $plat), 'estado' => $this->aux('usado', $estado),
                'cores' => $this->aux('cor', $cor), 'vintage' => $this->aux('retro', $retro),
                'colecionador' => $this->aux('edicao_especial', $edicao),
            ]));
            $this->criados['controle'][] = $controle->id;
        }
    }

    private function jogos(User $bruno, User $carla): array
    {
        // nome, plataforma, qtd, estado, retro/atual, edição
        $lista = [
            ['God of War Ragnarök',                 'playstation', 16, 'novo',      'atual', 'padrao'],
            ['The Last of Us Part II',              'playstation',  8, 'semi-novo', 'atual', 'padrao'],
            ['Gran Turismo 7',                      'playstation',  0, 'novo',      'atual', 'padrao'],
            ['Shadow of the Colossus',              'playstation',  3, 'semi-novo', 'retro', 'padrao'],
            ['Crash Bandicoot',                     'playstation',  5, 'semi-novo', 'retro', 'padrao'],
            ['Halo Infinite Steelbook',             'xbox',         2, 'novo',      'atual', 'especial'],
            ['Forza Horizon 5',                     'xbox',        19, 'novo',      'atual', 'padrao'],
            ['Gears 5',                             'xbox',         7, 'semi-novo', 'atual', 'padrao'],
            ['The Legend of Zelda: Tears of the Kingdom', 'nintendo', 13, 'novo',   'atual', 'padrao'],
            ['Mario Kart 8 Deluxe',                 'nintendo',    24, 'novo',      'atual', 'padrao'],
            ['Super Mario Odyssey',                 'nintendo',     9, 'semi-novo', 'atual', 'padrao'],
            ['Pokémon Scarlet',                     'nintendo',     0, 'novo',      'atual', 'padrao'],
            ['Super Mario 64',                      'nintendo',     4, 'semi-novo', 'retro', 'padrao'],
            ['GoldenEye 007',                       'nintendo',     1, 'semi-novo', 'retro', 'especial'],
        ];

        $criados = [];
        foreach ($lista as $i => [$nome, $plat, $qtd, $estado, $retro, $edicao]) {
            $criados[$nome] = $this->como($i % 3 ? $bruno : $carla, $this->diasAtras(8 - intdiv($i, 2), 11 + $i % 7), fn () => Jogo::create([
                'nome' => $nome, 'quantidade' => $qtd,
                'Plataforma' => $this->aux('plataforma', $plat), 'estado' => $this->aux('usado', $estado),
                'vintage' => $this->aux('retro', $retro), 'colecionador' => $this->aux('edicao_especial', $edicao),
            ]));
            $this->criados['jogo'][] = $criados[$nome]->id;
        }

        return $criados;
    }

    private function acessorios(User $bruno, User $carla): array
    {
        // nome, plataforma, qtd, estado, cor, retro/atual, edição
        $lista = [
            ['Headset Pulse 3D',               'playstation', 10, 'novo',      'branco',  'atual', 'padrao'],
            ['Estação de carga DualSense',     'playstation', 15, 'novo',      'branco',  'atual', 'padrao'],
            ['Volante Logitech G29',           'playstation',  2, 'semi-novo', 'preto',   'atual', 'padrao'],
            ['Memory Card PS2 8MB',            'playstation',  0, 'semi-novo', 'preto',   'retro', 'padrao'],
            ['Headset Xbox Wireless',          'xbox',        12, 'novo',      'preto',   'atual', 'padrao'],
            ['Bateria recarregável Xbox',      'xbox',         6, 'novo',      'preto',   'atual', 'padrao'],
            ['Case de viagem Switch',          'nintendo',    20, 'novo',      'vermelho', 'atual', 'padrao'],
            ['Expansion Pak N64',              'nintendo',     1, 'semi-novo', 'vermelho', 'retro', 'especial'],
        ];

        $criados = [];
        foreach ($lista as $i => [$nome, $plat, $qtd, $estado, $cor, $retro, $edicao]) {
            $criados[$nome] = $this->como($i % 2 ? $carla : $bruno, $this->diasAtras(5 - intdiv($i, 3), 13 + $i), fn () => Acessorio::create([
                'nome' => $nome, 'quantidade' => $qtd,
                'Plataforma' => $this->aux('plataforma', $plat), 'estado' => $this->aux('usado', $estado),
                'cores' => $this->aux('cor', $cor), 'vintage' => $this->aux('retro', $retro),
                'colecionador' => $this->aux('edicao_especial', $edicao),
            ]));
            $this->criados['acessorio'][] = $criados[$nome]->id;
        }

        return $criados;
    }

    // ------------------------------------------------------------ histórico

    private function alteracoes(User $ana, User $bruno, User $carla, array $consoles, array $jogos, array $acessorios): void
    {
        $ps2 = $consoles['PlayStation 2 Slim'];

        // O caso para demonstrar o "Desfazer": ontem 14:30 Bruno 15 -> 17,
        // 14:35 Carla 17 -> 19 (errado, o certo era 17)
        $this->como($bruno, Carbon::yesterday()->setTime(14, 30), fn () => $ps2->update(['quantidade' => 17]));
        $this->como($carla, Carbon::yesterday()->setTime(14, 35), fn () => $ps2->update(['quantidade' => 19]));

        // Venda e reposição de estoque
        $mario = $jogos['Mario Kart 8 Deluxe'];
        $this->como($bruno, $this->diasAtras(2, 10), fn () => $mario->update(['quantidade' => 21]));
        $this->como($carla, $this->diasAtras(1, 16), fn () => $mario->update(['quantidade' => 24]));

        // Correção de nome e estado
        $this->como($ana, $this->diasAtras(3, 9), fn () => $jogos['Gears 5']->update([
            'estado' => $this->aux('usado', 'semi-novo'), 'quantidade' => 7,
        ]));
        $this->como($carla, $this->diasAtras(1, 11), fn () => $consoles['Xbox One S']->update(['nome' => 'Xbox One S 1TB']));

        // Esgotou hoje cedo
        $this->como($bruno, Carbon::today()->setTime(9, 15), fn () => $acessorios['Memory Card PS2 8MB']->update(['quantidade' => 0]));
        $this->como($ana, Carbon::today()->setTime(10, 5), fn () => $consoles['GameCube']->update(['cores' => $this->aux('cor', 'roxo')]));
    }

    // ------------------------------------------------------------ ajudantes

    /**
     * Executa a ação como se o usuário estivesse logado (o Observer grava o
     * nome dele no histórico) e ajusta a data das linhas de histórico geradas.
     */
    private function como(User $usuario, Carbon $quando, callable $acao)
    {
        $tabelas = ['console_historico', 'controle_historico', 'jogo_historico', 'acessorio_historico'];
        $antes   = array_map(fn ($t) => (int) DB::table($t)->max('id'), array_combine($tabelas, $tabelas));

        Auth::setUser($usuario);
        $resultado = $acao();

        foreach ($antes as $tabela => $ultimoId) {
            DB::table($tabela)->where('id', '>', $ultimoId)->update(['created_at' => $quando]);
        }

        return $resultado;
    }

    private function diasAtras(int $dias, int $hora): Carbon
    {
        return Carbon::today()->subDays($dias)->setTime($hora, ($dias * 7 + $hora * 13) % 60);
    }

    /** id de um valor da tabela auxiliar pelo nome (ex: aux('cor', 'preto')) */
    private function aux(string $tabela, string $nome): int
    {
        return $this->idsAux[$tabela][$nome] ??= (int) DB::table($tabela)->where('nome', $nome)->value('id')
            ?: throw new \RuntimeException("Valor \"{$nome}\" não existe na tabela {$tabela}. Rode o AuxiliaresSeeder.");
    }
}
