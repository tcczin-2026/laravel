<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * O acervo real da loja (consoles, controles, jogos e acessórios), com valor,
 * armazenamento e foto, copiado do banco em 18/10/2026.
 *
 * - Mantém os mesmos ids (o histórico e os filtros dependem deles).
 * - Pode rodar várias vezes: se o id já existe, os dados são atualizados.
 * - Usa DB::table (sem o Observer), então não gera linhas no histórico.
 * - As fotos ficam em storage/app/public; o seeder só guarda o caminho.
 *
 *     php artisan db:seed --class=AcervoSeeder
 */
class AcervoSeeder extends Seeder
{
    private const DADOS = [
        'console' => [
            ['id' => 1, 'nome' => 'playstation 5', 'plataformaConsole' => 1, 'quantidade' => 5, 'valor' => 5200.00, 'armazenamento' => 825, 'estado' => 1, 'leitor' => 1, 'cores' => 3, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/vZIhIVwo0XqsY71IczobU7qNRs1qPRobFAboFtmd.jpg'],
            ['id' => 2, 'nome' => 'playstation 4', 'plataformaConsole' => 1, 'quantidade' => 2, 'valor' => 3100.00, 'armazenamento' => 1000, 'estado' => 2, 'leitor' => 2, 'cores' => 7, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/Ajtq1xaRU2hlJSs5Zs8i8kaQA8NMskIA1uJoIWlT.jpg'],
            ['id' => 3, 'nome' => 'playstation 5', 'plataformaConsole' => 1, 'quantidade' => 4, 'valor' => 5200.00, 'armazenamento' => 1000, 'estado' => 2, 'leitor' => 1, 'cores' => 3, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/dKuL0AvgbgcsxUzUolJMg8meBznrHECt8mqzgW16.jpg'],
            ['id' => 4, 'nome' => 'playstation 5', 'plataformaConsole' => 1, 'quantidade' => 0, 'valor' => 4800.00, 'armazenamento' => 825, 'estado' => 1, 'leitor' => 2, 'cores' => 3, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/JQAvkc7QHHMLsKmsXakuLJd4adM2yVPwMfiCSaK5.webp'],
            ['id' => 6, 'nome' => 'xbox series X', 'plataformaConsole' => 2, 'quantidade' => 5, 'valor' => 4890.00, 'armazenamento' => 1000, 'estado' => 1, 'leitor' => 2, 'cores' => 7, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/aGmoOrLNOIf0NmjhA9IJoqL31PklzrOAFyDWmnb8.jpg'],
            ['id' => 7, 'nome' => 'xbox 360', 'plataformaConsole' => 2, 'quantidade' => 8, 'valor' => 1250.00, 'armazenamento' => 560, 'estado' => 2, 'leitor' => 2, 'cores' => 7, 'vintage' => 1, 'aberto' => 2, 'colecionador' => 1, 'imagem' => 'consoles/DJexNVmjec8us821pcHxpH4YYCSXVKJTWefglBFS.webp'],
            ['id' => 8, 'nome' => 'nintendo switch', 'plataformaConsole' => 3, 'quantidade' => 11, 'valor' => 3450.00, 'armazenamento' => 525, 'estado' => 1, 'leitor' => 2, 'cores' => 11, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/7wQIOjFFroln38sRzFEFC4bQZR19JpSJ9YsOY9q2.jpg'],
            ['id' => 9, 'nome' => 'nintendo switch 2', 'plataformaConsole' => 3, 'quantidade' => 17, 'valor' => 3990.00, 'armazenamento' => 525, 'estado' => 1, 'leitor' => 2, 'cores' => 7, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/3be2wDzaPg5yW47Wd7vL6FHeBd8PqnLKmpCaNwz2.jpg'],
            ['id' => 10, 'nome' => 'nintendo switch', 'plataformaConsole' => 3, 'quantidade' => 11, 'valor' => 3450.00, 'armazenamento' => 525, 'estado' => 1, 'leitor' => 2, 'cores' => 7, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/DCDX9WBPLOmmd7ru6KK6EMFoVp87hbx29XSoo52a.jpg'],
            ['id' => 11, 'nome' => 'nintendo switch 2 pokemon legends z a', 'plataformaConsole' => 3, 'quantidade' => 12, 'valor' => 3850.00, 'armazenamento' => 525, 'estado' => 1, 'leitor' => 2, 'cores' => 7, 'vintage' => 1, 'aberto' => 1, 'colecionador' => 2, 'imagem' => 'consoles/WlhppXivuqElrof2zEHhCXeo29QVSYdgSejQWBTi.jpg'],
            ['id' => 12, 'nome' => 'xbox series S', 'plataformaConsole' => 2, 'quantidade' => 14, 'valor' => 4450.00, 'armazenamento' => 825, 'estado' => 1, 'leitor' => 1, 'cores' => 3, 'vintage' => 2, 'aberto' => 1, 'colecionador' => 1, 'imagem' => 'consoles/XkqhjzVCVLalMGKPGQ365iuVKJ7gHPbEBaZAZ9c4.jpg'],
        ],
        'controle' => [
            ['id' => 1, 'nome' => 'nintendo switch joi con', 'plataformaControle' => 3, 'quantidade' => 15, 'valor' => 390.00, 'dispositivo' => null, 'cores' => 12, 'vintage' => 2, 'estado' => 1, 'colecionador' => 1, 'imagem' => 'controles/Pq4PRO2LVoXhBZmNNqSNP5Ucgq2SGxwhca44Lt4I.webp'],
        ],
        'jogo' => [
            ['id' => 1, 'nome' => 'Marvel\'s spider-man 2', 'Plataforma' => 1, 'quantidade' => 17, 'valor' => 380.00, 'estado' => 1, 'vintage' => 2, 'colecionador' => 1, 'imagem' => 'jogos/iwB8vrAGb32eDTMFywVna977KEvPT7iaPAYKEJuN.jpg'],
        ],
        'acessorio' => [
            ['id' => 1, 'nome' => 'vr ps5', 'Plataforma' => 1, 'quantidade' => 12, 'valor' => 2800.00, 'estado' => 1, 'vintage' => 2, 'cores' => 3, 'colecionador' => 1, 'imagem' => 'acessorios/nn6Jga9q4GgO31yTGDVgZ5wlaUxX5ahiHGANh18M.webp'],
        ],
    ];

    public function run(): void
    {
        $this->call(AuxiliaresSeeder::class); // plataformas, cores etc. que os itens usam

        foreach (self::DADOS as $tabela => $linhas) {
            $colunas = array_keys($linhas[0]);
            DB::table($tabela)->upsert($linhas, ['id'], array_diff($colunas, ['id']));
        }
    }
}
