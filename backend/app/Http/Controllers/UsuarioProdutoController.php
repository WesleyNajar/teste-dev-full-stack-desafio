<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Produto;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class UsuarioProdutoController extends Controller
{
    /**
     * Listar todos os relacionamentos usuário-produto
     */
    public function index(): JsonResponse
    {
        $relacionamentos = DB::table('usuario_produto')
            ->join('usuarios', 'usuario_produto.usuario_id', '=', 'usuarios.id')
            ->join('produtos', 'usuario_produto.produto_id', '=', 'produtos.id')
            ->select(
                'usuario_produto.id',
                'usuarios.nome as usuario_nome',
                'usuarios.email as usuario_email',
                'produtos.nome as produto_nome',
                'produtos.preco as produto_preco',
                'usuario_produto.created_at'
            )
            ->get();

        return response()->json([
            'success' => true,
            'data' => $relacionamentos
        ]);
    }

    /**
     * Vincular um usuário a um produto
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'usuario_id' => 'required|exists:usuarios,id',
                'produto_id' => 'required|exists:produtos,id'
            ], [
                'usuario_id.required' => 'O campo usuário é obrigatório.',
                'usuario_id.exists' => 'O usuário selecionado não existe no sistema.',
                'produto_id.required' => 'O campo produto é obrigatório.',
                'produto_id.exists' => 'O produto selecionado não existe no sistema.'
            ]);

            $existe = DB::table('usuario_produto')
                ->where('usuario_id', $validated['usuario_id'])
                ->where('produto_id', $validated['produto_id'])
                ->exists();

            if ($existe) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relacionamento já existe',
                    'details' => 'Este usuário já está vinculado a este produto.'
                ], 409);
            }

            $relacionamentoId = DB::table('usuario_produto')->insertGetId([
                'usuario_id' => $validated['usuario_id'],
                'produto_id' => $validated['produto_id'],
                'created_at' => now(),
                'updated_at' => now()
            ]);

            $relacionamento = DB::table('usuario_produto')
                ->join('usuarios', 'usuario_produto.usuario_id', '=', 'usuarios.id')
                ->join('produtos', 'usuario_produto.produto_id', '=', 'produtos.id')
                ->select(
                    'usuario_produto.id',
                    'usuarios.nome as usuario_nome',
                    'produtos.nome as produto_nome'
                )
                ->where('usuario_produto.id', $relacionamentoId)
                ->first();

            return response()->json([
                'success' => true,
                'message' => 'Usuário vinculado ao produto com sucesso',
                'data' => $relacionamento,
                'details' => 'O relacionamento foi criado no sistema'
            ], 201);

        } catch (\Exception $e) {
            \Log::error('Erro ao vincular usuário ao produto: ' . $e->getMessage(), [
                'data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao vincular usuário ao produto',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Desvincular um usuário de um produto
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $relacionamento = DB::table('usuario_produto')->find($id);

            if (!$relacionamento) {
                return response()->json([
                    'success' => false,
                    'message' => 'Relacionamento não encontrado',
                    'details' => 'O relacionamento com ID ' . $id . ' não foi encontrado no sistema'
                ], 404);
            }

            DB::table('usuario_produto')->where('id', $id)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Relacionamento removido com sucesso',
                'details' => 'O usuário foi desvinculado do produto'
            ]);

        } catch (\Exception $e) {
            \Log::error('Erro ao remover relacionamento: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover relacionamento',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Listar produtos de um usuário específico
     */
    public function produtosPorUsuario(int $usuarioId): JsonResponse
    {
        $usuario = Usuario::find($usuarioId);

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado',
                'details' => 'O usuário com ID ' . $usuarioId . ' não foi encontrado no sistema'
            ], 404);
        }

        $produtos = DB::table('usuario_produto')
            ->join('produtos', 'usuario_produto.produto_id', '=', 'produtos.id')
            ->select('produtos.*')
            ->where('usuario_produto.usuario_id', $usuarioId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'usuario' => $usuario,
                'produtos' => $produtos
            ]
        ]);
    }

    /**
     * Listar usuários de um produto específico
     */
    public function usuariosPorProduto(int $produtoId): JsonResponse
    {
        $produto = Produto::find($produtoId);

        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado',
                'details' => 'O produto com ID ' . $produtoId . ' não foi encontrado no sistema'
            ], 404);
        }

        $usuarios = DB::table('usuario_produto')
            ->join('usuarios', 'usuario_produto.usuario_id', '=', 'usuarios.id')
            ->select('usuarios.*')
            ->where('usuario_produto.produto_id', $produtoId)
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'produto' => $produto,
                'usuarios' => $usuarios
            ]
        ]);
    }
}
