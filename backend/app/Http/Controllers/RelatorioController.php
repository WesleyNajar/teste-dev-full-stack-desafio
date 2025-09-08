<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RelatorioController extends Controller
{
    /**
     * Query A: Listar os usuários com mais produtos
     */
    public function usuariosComMaisProdutos()
    {
        try {
            $resultado = DB::select("
                SELECT 
                    u.id,
                    u.nome,
                    u.email,
                    COUNT(up.produto_id) as total_produtos
                FROM usuarios u
                LEFT JOIN usuario_produto up ON u.id = up.usuario_id
                GROUP BY u.id, u.nome, u.email
                ORDER BY total_produtos DESC
            ");

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar query: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query B: Buscar o produto mais caro por usuário
     */
    public function produtoMaisCaroPorUsuario()
    {
        try {
            $resultado = DB::select("
                SELECT 
                    u.id as usuario_id,
                    u.nome as usuario_nome,
                    p.id as produto_id,
                    p.nome as produto_nome,
                    p.preco
                FROM usuarios u
                INNER JOIN usuario_produto up ON u.id = up.usuario_id
                INNER JOIN produtos p ON up.produto_id = p.id
                INNER JOIN (
                    SELECT 
                        up2.usuario_id,
                        MAX(p2.preco) as max_preco
                    FROM usuario_produto up2
                    INNER JOIN produtos p2 ON up2.produto_id = p2.id
                    GROUP BY up2.usuario_id
                ) max_precos ON u.id = max_precos.usuario_id AND p.preco = max_precos.max_preco
                ORDER BY p.preco DESC
            ");

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar query: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Query C: Exibir a quantidade de produtos por faixa de preço
     */
    public function produtosPorFaixaPreco()
    {
        try {
            $resultado = DB::select("
                SELECT 
                    faixa,
                    COUNT(*) as quantidade
                FROM (
                    SELECT 
                        CASE 
                            WHEN preco >= 0 AND preco < 50 THEN 'R$ 0,00 - R$ 50,00'
                            WHEN preco >= 50 AND preco < 100 THEN 'R$ 50,00 - R$ 100,00'
                            WHEN preco >= 100 AND preco < 200 THEN 'R$ 100,00 - R$ 200,00'
                            WHEN preco >= 200 AND preco < 500 THEN 'R$ 200,00 - R$ 500,00'
                            WHEN preco >= 500 THEN 'R$ 500,00+'
                        END as faixa,
                        CASE 
                            WHEN preco >= 0 AND preco < 50 THEN 1
                            WHEN preco >= 50 AND preco < 100 THEN 2
                            WHEN preco >= 100 AND preco < 200 THEN 3
                            WHEN preco >= 200 AND preco < 500 THEN 4
                            WHEN preco >= 500 THEN 5
                        END as ordem
                    FROM produtos
                ) as faixas
                GROUP BY faixa, ordem
                ORDER BY ordem
            ");

            return response()->json([
                'success' => true,
                'data' => $resultado
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao executar query: ' . $e->getMessage()
            ], 500);
        }
    }
}
