<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Usuario;
use Faker\Factory as Faker;

class UsuarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $faker = Faker::create('pt_BR');

        $usuariosFixos = [
            [
                'nome' => 'João Silva',
                'cpf' => '123.456.789-01',
                'email' => 'joao@example.com',
                'senha' => '123456'
            ],
            [
                'nome' => 'Maria Santos',
                'cpf' => '987.654.321-00',
                'email' => 'maria@example.com',
                'senha' => '123456'
            ],
            [
                'nome' => 'Pedro Oliveira',
                'cpf' => '111.222.333-44',
                'email' => 'pedro@example.com',
                'senha' => '123456'
            ]
        ];

        foreach ($usuariosFixos as $usuario) {
            Usuario::create($usuario);
        }

        for ($i = 0; $i < 30; $i++) {
            Usuario::create([
                'nome' => $faker->name(),
                'cpf' => $faker->numerify('###.###.###-##'),
                'email' => $faker->unique()->safeEmail(),
                'senha' => '123456'
            ]);
        }

        $this->command->info('33 usuários criados com sucesso!');
    }
}
