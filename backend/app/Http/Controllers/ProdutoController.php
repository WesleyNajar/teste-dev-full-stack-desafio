<?php

namespace App\Http\Controllers;

use App\Models\Produto;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;

class ProdutoController extends Controller
{
    /**
     * Listar todos os produtos
     */
    public function index(): JsonResponse
    {
        $produtos = Produto::all();
        
        return response()->json([
            'success' => true,
            'data' => $produtos
        ]);
    }

    /**
     * Mostrar um produto específico
     */
    public function show(int $id): JsonResponse
    {
        $produto = Produto::find($id);
        
        if (!$produto) {
            return response()->json([
                'success' => false,
                'message' => 'Produto não encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $produto
        ]);
    }

    /**
     * Criar um novo produto
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'preco' => 'required|numeric|min:0',
                'descricao' => 'nullable|string'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
                
                'preco.required' => 'O campo preço é obrigatório.',
                'preco.numeric' => 'O preço deve ser um número.',
                'preco.min' => 'O preço deve ser maior ou igual a zero.',
                
                'descricao.string' => 'A descrição deve ser um texto.'
            ]);

            $produto = Produto::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Produto criado com sucesso',
                'data' => $produto,
                'details' => 'O produto foi criado no sistema'
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        }
    }

    /**
     * Atualizar um produto
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $produto = Produto::find($id);
            
            if (!$produto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                    'details' => 'O produto com ID ' . $id . ' não foi encontrado no sistema'
                ], 404);
            }

            $validated = $request->validate([
                'nome' => 'sometimes|required|string|max:255',
                'preco' => 'sometimes|required|numeric|min:0',
                'descricao' => 'nullable|string'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
                
                'preco.required' => 'O campo preço é obrigatório.',
                'preco.numeric' => 'O preço deve ser um número.',
                'preco.min' => 'O preço deve ser maior ou igual a zero.',
                
                'descricao.string' => 'A descrição deve ser um texto.'
            ]);

            $produto->update($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Produto atualizado com sucesso',
                'data' => $produto,
                'details' => 'As informações do produto foram atualizadas no sistema'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
                'details' => 'Verifique os campos destacados e corrija os erros'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar produto: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar produto',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Excluir um produto
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $produto = Produto::find($id);
            
            if (!$produto) {
                return response()->json([
                    'success' => false,
                    'message' => 'Produto não encontrado',
                    'details' => 'O produto com ID ' . $id . ' não foi encontrado no sistema'
                ], 404);
            }

            $produto->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Produto excluído com sucesso',
                'details' => 'O produto foi removido permanentemente do sistema'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir produto: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir produto',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }
}
