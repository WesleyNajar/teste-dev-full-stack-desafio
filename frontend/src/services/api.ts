import axios from 'axios'

// Configuração base do axios
const api = axios.create({
  baseURL: 'http://localhost/api',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json'
  },
  timeout: 10000 // 10 segundos de timeout
})

// Interceptor para requisições
api.interceptors.request.use(
  (config) => {
    // Removido o token Bearer - não precisamos mais de autenticação
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptor para respostas
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    
    if (error.response) {
      // O servidor respondeu com um status de erro
      error.apiError = {
        status: error.response.status,
        statusText: error.response.statusText,
        data: error.response.data,
        headers: error.response.headers
      }
    } else if (error.request) {
      // A requisição foi feita mas não houve resposta
      error.apiError = {
        status: 'NO_RESPONSE',
        statusText: 'Sem resposta do servidor',
        data: { message: 'O servidor não respondeu' }
      }
    } else {
      // Algo aconteceu na configuração da requisição
      error.apiError = {
        status: 'REQUEST_ERROR',
        statusText: 'Erro na requisição',
        data: { message: error.message || 'Erro desconhecido na requisição' }
      }
    }

    return Promise.reject(error)
  }
)

export interface Usuario {
  id: number
  nome: string
  cpf: string
  email: string
  produtos?: Produto[]
  created_at: string
  updated_at: string
}

export interface Produto {
  id: number
  nome: string
  preco: number
  descricao?: string
  created_at: string
  updated_at: string
}

export interface RelacionamentoUsuarioProduto {
  id: number
  usuario_id: number
  produto_id: number
  usuario_nome?: string
  usuario_email?: string
  produto_nome?: string
  produto_preco?: string | number
  created_at: string
  updated_at: string
}

// Serviços de Usuários
export const usuarioService = {
  listar: () => api.get<{ success: boolean; data: Usuario[] }>('/usuarios'),
  buscar: (id: number) => api.get<{ success: boolean; data: Usuario }>(`/usuarios/${id}`),
  criar: (data: Omit<Usuario, 'id' | 'created_at' | 'updated_at'>) => 
    api.post<{ success: boolean; data: Usuario; message: string }>('/usuarios', data),
  atualizar: (id: number, data: Partial<Usuario>) => 
    api.put<{ success: boolean; data: Usuario; message: string }>(`/usuarios/${id}`, data),
  excluir: (id: number) => 
    api.delete<{ success: boolean; message: string }>(`/usuarios/${id}`)
}

// Serviços de Produtos
export const produtoService = {
  listar: () => api.get<{ success: boolean; data: Produto[] }>('/produtos'),
  buscar: (id: number) => api.get<{ success: boolean; data: Produto }>(`/produtos/${id}`),
  criar: (data: Omit<Produto, 'id' | 'created_at' | 'updated_at'>) => 
    api.post<{ success: boolean; data: Produto; message: string }>('/produtos', data),
  atualizar: (id: number, data: Partial<Produto>) => 
    api.put<{ success: boolean; data: Produto; message: string }>(`/produtos/${id}`, data),
  excluir: (id: number) => 
    api.delete<{ success: boolean; message: string }>(`/produtos/${id}`)
}

// Serviços de Relacionamento Usuário-Produto
export const relacionamentoService = {
  listar: () => api.get<{ success: boolean; data: RelacionamentoUsuarioProduto[] }>('/relacionamentos'),
  vincular: (data: { usuario_id: number; produto_id: number }) => 
    api.post<{ success: boolean; data: RelacionamentoUsuarioProduto; message: string }>('/relacionamentos', data),
  desvincular: (id: number) => 
    api.delete<{ success: boolean; message: string }>(`/relacionamentos/${id}`),
  produtosPorUsuario: (usuarioId: number) => 
    api.get<{ success: boolean; data: { usuario: Usuario; produtos: Produto[] } }>(`/relacionamentos/usuario/${usuarioId}/produtos`),
  usuariosPorProduto: (produtoId: number) => 
    api.get<{ success: boolean; data: { produto: Produto; usuarios: Usuario[] } }>(`/relacionamentos/produto/${produtoId}/usuarios`)
}

// Serviços de Relatórios
export const relatorioService = {
  usuariosComMaisProdutos: () => 
    api.get<{ success: boolean; data: any[] }>('/relatorios/usuarios-mais-produtos'),
  produtoMaisCaroPorUsuario: () => 
    api.get<{ success: boolean; data: any[] }>('/relatorios/produto-mais-caro-por-usuario'),
  produtosPorFaixaPreco: () => 
    api.get<{ success: boolean; data: any[] }>('/relatorios/produtos-por-faixa-preco')
}

// Serviços de Autenticação removidos - não há mais autenticação

export default api
