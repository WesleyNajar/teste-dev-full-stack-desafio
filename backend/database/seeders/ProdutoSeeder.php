<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Produto;
use Faker\Factory as Faker;

class ProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $produtosFixos = [
            [
                'nome' => 'Notebook Dell Inspiron',
                'preco' => 2999.99,
                'descricao' => 'Notebook com processador Intel i5, 8GB RAM, 256GB SSD'
            ],
            [
                'nome' => 'Mouse Wireless Logitech',
                'preco' => 89.90,
                'descricao' => 'Mouse sem fio com sensor óptico de alta precisão'
            ],
            [
                'nome' => 'Teclado Mecânico RGB',
                'preco' => 299.99,
                'descricao' => 'Teclado mecânico com switches Cherry MX Blue'
            ],
            [
                'nome' => 'Monitor LG 24"',
                'preco' => 599.99,
                'descricao' => 'Monitor Full HD com painel IPS'
            ],
            [
                'nome' => 'Webcam HD 1080p',
                'preco' => 199.99,
                'descricao' => 'Webcam com resolução Full HD e microfone integrado'
            ]
        ];

        foreach ($produtosFixos as $produto) {
            Produto::create($produto);
        }

        $categorias = ['Eletrônicos', 'Informática', 'Gaming', 'Escritório', 'Casa', 'Tecnologia'];
        
        for ($i = 0; $i < 25; $i++) {
            $categoria = $faker->randomElement($categorias);
            
            Produto::create([
                'nome' => $faker->words(3, true) . ' ' . $categoria,
                'preco' => $faker->randomFloat(2, 10, 5000),
                'descricao' => $faker->paragraph(2)
            ]);
        }

        $this->command->info('30 produtos criados com sucesso!');
    }
}
