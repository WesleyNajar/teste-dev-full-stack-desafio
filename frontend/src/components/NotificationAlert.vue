<template>
  <v-snackbar
    v-model="show"
    :color="getNotificationColor"
    :timeout="timeout"
    location="top"
    class="notification-alert"
  >
    <div class="d-flex align-center">
      <v-icon
        :icon="getNotificationIcon"
        class="me-3"
      />
      <div class="flex-grow-1">
        <div class="text-h6 font-weight-bold mb-1">
          {{ getNotificationTitle }}
        </div>
        <div class="text-body-1 mb-2">{{ message }}</div>
        
        <!-- Detalhes adicionais -->
        <div v-if="details" class="text-body-2 mb-3 text-medium-emphasis">
          {{ details }}
        </div>
        
        <!-- Detalhes de validação -->
        <div v-if="validationErrors && Object.keys(validationErrors).length > 0" class="mt-3">
          <div class="text-subtitle-2 font-weight-bold mb-2">Detalhes dos erros:</div>
          <v-list density="compact" class="pa-0 bg-transparent">
            <v-list-item
              v-for="(errors, field) in validationErrors"
              :key="field"
              class="pa-0 mb-1"
            >
              <template #prepend>
                <v-icon icon="mdi-alert" color="error" size="small" class="me-2" />
              </template>
              <v-list-item-title class="text-body-2">
                <strong>{{ formatFieldName(field) }}:</strong> {{ errors[0] }}
              </v-list-item-title>
            </v-list-item>
          </v-list>
        </div>
      </div>
    </div>

    <template #actions>
      <v-btn
        variant="text"
        @click="close"
        color="white"
      >
        Fechar
      </v-btn>
    </template>
  </v-snackbar>
</template>

<script setup lang="ts">
import { ref, computed, onMounted } from 'vue'

interface Props {
  type: 'success' | 'error' | 'warning' | 'info'
  message: string
  validationErrors?: Record<string, string[]>
  timeout?: number
  details?: string
}

interface Emits {
  (e: 'close'): void
}

const props = withDefaults(defineProps<Props>(), {
  timeout: 6000,
  validationErrors: () => ({}),
  details: ''
})

const emit = defineEmits<Emits>()

const show = ref(true)

// Fechar automaticamente após o timeout
onMounted(() => {
  if (props.timeout > 0) {
    setTimeout(() => {
      close()
    }, props.timeout)
  }
})

const close = () => {
  show.value = false
  emit('close')
}

const getNotificationColor = computed(() => {
  switch (props.type) {
    case 'success':
      return 'success'
    case 'error':
      return 'error'
    case 'warning':
      return 'warning'
    case 'info':
      return 'info'
    default:
      return 'success'
  }
})

const getNotificationIcon = computed(() => {
  switch (props.type) {
    case 'success':
      return 'mdi-check-circle'
    case 'error':
      return 'mdi-alert-circle'
    case 'warning':
      return 'mdi-alert'
    case 'info':
      return 'mdi-information'
    default:
      return 'mdi-check-circle'
  }
})

const getNotificationTitle = computed(() => {
  switch (props.type) {
    case 'success':
      return 'Sucesso!'
    case 'error':
      return 'Erro!'
    case 'warning':
      return 'Atenção!'
    case 'info':
      return 'Informação'
    default:
      return 'Notificação'
  }
})

const formatFieldName = (field: string): string => {
  const fieldNames: Record<string, string> = {
    nome: 'Nome',
    cpf: 'CPF',
    email: 'E-mail',
    senha: 'Senha',
    confirmacao_senha: 'Confirmação de Senha',
    preco: 'Preço',
    descricao: 'Descrição',
    usuario_id: 'Usuário'
  }
  
  return fieldNames[field] || field.charAt(0).toUpperCase() + field.slice(1)
}
</script>

<style scoped>
.notification-alert {
  z-index: 9999;
}

.v-list-item {
  min-height: auto;
}

.text-medium-emphasis {
  opacity: 0.8;
}
</style>

