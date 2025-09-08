import { ref, readonly } from 'vue'

interface Notification {
  id: string
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
  validationErrors?: Record<string, string[]>
  timeout?: number
  details?: string
}

const notifications = ref<Notification[]>([])
const currentNotification = ref<Notification | null>(null)

export function useNotifications() {
  const showNotification = (
    type: 'success' | 'error' | 'warning' | 'info',
    message: string,
    validationErrors?: Record<string, string[]>,
    timeout: number = 6000,
    details?: string
  ) => {
    // Limpar notificação anterior se existir
    if (currentNotification.value) {
      currentNotification.value = null
    }

    const notification: Notification = {
      id: Date.now().toString(),
      type,
      message,
      validationErrors,
      timeout,
      details
    }

    notifications.value.push(notification)
    
    // Definir como notificação atual
    currentNotification.value = notification

    // Remover automaticamente após o timeout
    if (timeout > 0) {
      setTimeout(() => {
        removeNotification(notification.id)
      }, timeout)
    }
  }

  const showSuccess = (message: string, timeout?: number, details?: string) => {
    showNotification('success', message, undefined, timeout, details)
  }

  const showError = (message: string, validationErrors?: Record<string, string[]>, timeout?: number, details?: string) => {
    showNotification('error', message, validationErrors, timeout, details)
  }

  const showWarning = (message: string, timeout?: number, details?: string) => {
    showNotification('warning', message, undefined, timeout, details)
  }

  const showInfo = (message: string, timeout?: number, details?: string) => {
    showNotification('info', message, undefined, timeout, details)
  }

  const removeNotification = (id: string) => {
    const index = notifications.value.findIndex(n => n.id === id)
    if (index > -1) {
      notifications.value.splice(index, 1)
    }
    
    // Se for a notificação atual, limpar
    if (currentNotification.value?.id === id) {
      currentNotification.value = null
    }
  }

  const clearCurrentNotification = () => {
    currentNotification.value = null
  }

  const clearAllNotifications = () => {
    notifications.value = []
    currentNotification.value = null
  }

  // Funções específicas para operações CRUD
  const showCreateSuccess = (entity: string) => {
    showSuccess(`${entity} criado(a) com sucesso!`)
  }

  const showUpdateSuccess = (entity: string) => {
    showSuccess(`${entity} atualizado(a) com sucesso!`)
  }

  const showDeleteSuccess = (entity: string) => {
    showSuccess(`${entity} removido(a) com sucesso!`)
  }

  const showValidationError = (message: string, validationErrors: Record<string, string[]>) => {
    showError(message, validationErrors, 8000)
  }

  const showApiError = (error: any) => {
    let message = 'Erro interno do servidor'
    let validationErrors: Record<string, string[]> = {}
    let details = ''
    let timeout = 6000

    if (error.response) {
      const { status, data } = error.response
      
      switch (status) {
        case 400:
          message = 'Requisição inválida'
          details = data.message || 'Os dados enviados não estão no formato correto'
          break
        case 401:
          message = 'Não autorizado'
          details = 'Você não tem permissão para realizar esta ação'
          break
        case 403:
          message = 'Acesso negado'
          details = 'Você não tem permissão para acessar este recurso'
          break
        case 404:
          message = 'Recurso não encontrado'
          details = data.message || 'O recurso solicitado não existe'
          break
        case 409:
          message = 'Conflito'
          details = data.message || 'Existe um conflito com os dados fornecidos'
          break
        case 422:
          message = 'Erro de validação'
          validationErrors = data.errors || {}
          details = 'Verifique os campos destacados e corrija os erros'
          timeout = 8000
          break
        case 429:
          message = 'Muitas requisições'
          details = 'Você excedeu o limite de requisições. Tente novamente em alguns instantes'
          break
        case 500:
          message = 'Erro interno do servidor'
          details = data.message || 'Ocorreu um erro inesperado no servidor'
          break
        case 502:
          message = 'Servidor indisponível'
          details = 'O servidor está temporariamente indisponível'
          break
        case 503:
          message = 'Serviço indisponível'
          details = 'O serviço está temporariamente indisponível'
          break
        default:
          message = data.message || `Erro ${status}`
          details = data.details || 'Ocorreu um erro inesperado'
      }
    } else if (error.request) {
      message = 'Erro de conexão'
      details = 'Não foi possível conectar com o servidor. Verifique sua conexão com a internet'
      timeout = 8000
    } else if (error.code === 'ECONNABORTED') {
      message = 'Timeout da requisição'
      details = 'A requisição demorou muito para responder. Tente novamente'
      timeout = 8000
    } else {
      message = error.message || 'Erro desconhecido'
      details = 'Ocorreu um erro inesperado'
    }

    showError(message, validationErrors, timeout, details)
  }

  const showFieldValidationError = (field: string, message: string) => {
    const validationErrors = { [field]: [message] }
    showValidationError('Erro de validação', validationErrors)
  }

  const showRequiredFieldError = (field: string) => {
    const fieldNames: Record<string, string> = {
      nome: 'Nome',
      cpf: 'CPF',
      email: 'E-mail',
      senha: 'Senha',
      preco: 'Preço',
      descricao: 'Descrição',
      usuario_id: 'Usuário'
    }
    
    const fieldName = fieldNames[field] || field.charAt(0).toUpperCase() + field.slice(1)
    showFieldValidationError(field, `${fieldName} é obrigatório`)
  }

  return {
    notifications: readonly(notifications),
    currentNotification,
    showNotification,
    showSuccess,
    showError,
    showWarning,
    showInfo,
    removeNotification,
    clearCurrentNotification,
    clearAllNotifications,
    showCreateSuccess,
    showUpdateSuccess,
    showDeleteSuccess,
    showValidationError,
    showApiError,
    showFieldValidationError,
    showRequiredFieldError
  }
}
