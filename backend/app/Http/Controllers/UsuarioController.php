<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Cache;

class UsuarioController extends Controller
{
    /**
     * Listar todos os usuários
     */
    public function index(): JsonResponse
    {
        // Cache com chave específica e expiração de 60 segundos
        $cacheKey = 'usuarios_lista_completa';
        $cacheExpiration = 10; // 10 segundos
        
        // Tentar buscar do cache primeiro
        $usuarios = Cache::remember($cacheKey, $cacheExpiration, function () {
            // Se não estiver no cache, buscar do banco de dados
            return Usuario::with('produtos')->get();
        });
        
        return response()->json([
            'success' => true,
            'data' => $usuarios,
            'cached' => Cache::has($cacheKey),
            'cache_expires_in' => $cacheExpiration . ' segundos'
        ]);
    }

    /**
     * Mostrar um usuário específico
     */
    public function show(int $id): JsonResponse
    {
        $usuario = Usuario::with('produtos')->find($id);
        
        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuário não encontrado'
            ], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $usuario
        ]);
    }

    /**
     * Criar um novo usuário
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'nome' => 'required|string|max:255',
                'cpf' => 'required|string|max:14|unique:usuarios,cpf',
                'email' => 'required|email|unique:usuarios,email',
                'senha' => 'required|string|min:6'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
                
                'cpf.required' => 'O campo CPF é obrigatório.',
                'cpf.string' => 'O CPF deve ser um texto.',
                'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
                'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
                
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.email' => 'O e-mail deve ter um formato válido.',
                'email.unique' => 'Este e-mail já está cadastrado no sistema.',
                
                'senha.required' => 'O campo senha é obrigatório.',
                'senha.string' => 'A senha deve ser um texto.',
                'senha.min' => 'A senha deve ter pelo menos 6 caracteres.'
            ]);


            $usuario = Usuario::create($validated);
            
            // Limpar cache da lista de usuários após criar novo usuário
            Cache::forget('usuarios_lista_completa');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário criado com sucesso',
                'data' => $usuario,
                'details' => 'O usuário foi criado e está disponível no sistema'
            ], 201);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
                'details' => 'Verifique os campos destacados e corrija os erros'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao criar usuário: ' . $e->getMessage(), [
                'data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao criar usuário',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Atualizar um usuário
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'details' => 'O usuário com ID ' . $id . ' não foi encontrado no sistema'
                ], 404);
            }

            $validated = $request->validate([
                'nome' => 'sometimes|required|string|max:255',
                'cpf' => 'sometimes|required|string|max:14|unique:usuarios,cpf,' . $id,
                'email' => 'sometimes|required|email|unique:usuarios,email,' . $id,
                'senha' => 'sometimes|required|string|min:6'
            ], [
                'nome.required' => 'O campo nome é obrigatório.',
                'nome.string' => 'O nome deve ser um texto.',
                'nome.max' => 'O nome não pode ter mais de 255 caracteres.',
                
                'cpf.required' => 'O campo CPF é obrigatório.',
                'cpf.string' => 'O CPF deve ser um texto.',
                'cpf.max' => 'O CPF não pode ter mais de 14 caracteres.',
                'cpf.unique' => 'Este CPF já está cadastrado no sistema.',
                
                'email.required' => 'O campo e-mail é obrigatório.',
                'email.email' => 'O e-mail deve ter um formato válido.',
                'email.unique' => 'Este e-mail já está cadastrado no sistema.',
                
                'senha.required' => 'O campo senha é obrigatório.',
                'senha.string' => 'A senha deve ser um texto.',
                'senha.min' => 'A senha deve ter pelo menos 6 caracteres.'
            ]);


            $usuario->update($validated);
            
            // Limpar cache da lista de usuários após atualizar usuário
            Cache::forget('usuarios_lista_completa');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário atualizado com sucesso',
                'data' => $usuario,
                'details' => 'As informações do usuário foram atualizadas no sistema'
            ]);
            
        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro de validação',
                'errors' => $e->errors(),
                'details' => 'Verifique os campos destacados e corrija os erros'
            ], 422);
        } catch (\Exception $e) {
            \Log::error('Erro ao atualizar usuário: ' . $e->getMessage(), [
                'id' => $id,
                'data' => $request->all(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao atualizar usuário',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

    /**
     * Excluir um usuário
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $usuario = Usuario::find($id);
            
            if (!$usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'Usuário não encontrado',
                    'details' => 'O usuário com ID ' . $id . ' não foi encontrado no sistema'
                ], 404);
            }

            if ($usuario->produtos()->count() > 0) {
                return response()->json([
                    'success' => false,
                    'message' => 'Não é possível excluir o usuário',
                    'details' => 'Este usuário possui produtos associados. Remova os produtos primeiro.'
                ], 409);
            }

            $usuario->delete();
            
            // Limpar cache da lista de usuários após excluir usuário
            Cache::forget('usuarios_lista_completa');
            
            return response()->json([
                'success' => true,
                'message' => 'Usuário excluído com sucesso',
                'details' => 'O usuário foi removido permanentemente do sistema'
            ]);
            
        } catch (\Exception $e) {
            \Log::error('Erro ao excluir usuário: ' . $e->getMessage(), [
                'id' => $id,
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Erro ao excluir usuário',
                'details' => 'Ocorreu um erro inesperado. Tente novamente.'
            ], 500);
        }
    }

}
