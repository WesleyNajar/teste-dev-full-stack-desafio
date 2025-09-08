<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use App\Models\Produto;
use Illuminate\Support\Facades\DB;
use Faker\Factory as Faker;

class UsuarioProdutoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');
        
        $usuarios = Usuario::all();
        $produtos = Produto::all();

        if ($usuarios->isEmpty() || $produtos->isEmpty()) {
            $this->command->info('Usuários ou produtos não encontrados. Execute UsuarioSeeder e ProdutoSeeder primeiro.');
            return;
        }

        $relacionamentos = [];
        
        foreach ($usuarios as $usuario) {
            $numProdutos = $faker->numberBetween(1, 5);
            $produtosAleatorios = $produtos->random($numProdutos);
            
            foreach ($produtosAleatorios as $produto) {
                $relacionamentos[] = [
                    'usuario_id' => $usuario->id,
                    'produto_id' => $produto->id,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        $relacionamentos = collect($relacionamentos)->unique(function ($item) {
            return $item['usuario_id'] . '-' . $item['produto_id'];
        })->values()->all();

        DB::table('usuario_produto')->insert($relacionamentos);

        $this->command->info(count($relacionamentos) . ' relacionamentos usuário-produto criados com sucesso!');
    }
}
