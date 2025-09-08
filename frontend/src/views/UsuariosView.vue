<template>
  <div class="usuarios">
    <v-card>
      <v-card-title class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-2">
        <span class="text-h6">Gerenciar Usuários</span>
        <v-btn 
          color="primary" 
          @click="dialog = true"
          class="align-self-stretch align-self-md-auto"
          size="large"
        >
          <v-icon start>mdi-plus</v-icon>
          <span class="d-none d-sm-inline">Novo Usuário</span>
          <span class="d-sm-none">Novo</span>
        </v-btn>
      </v-card-title>
      
      <v-card-text>
        <!-- Componente de busca -->
        <SearchFilter
          :filter-options="filterOptions"
          v-model="searchTerm"
          v-model:selected-filter-value="selectedFilter"
          @search="onSearch"
          @clear="clearSearch"
        />
        
        <v-data-table
          :headers="headers"
          :items="filteredUsuarios"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.cpf="{ item }">
            {{ formatarCPF(item.cpf) }}
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-btn icon small @click="editarUsuario(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon small @click="excluirUsuario(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Dialog para criar/editar usuário -->
    <v-dialog v-model="dialog" max-width="500px" fullscreen-mobile>
      <v-card>
        <v-card-title>
          {{ editIndex === -1 ? 'Novo Usuário' : 'Editar Usuário' }}
        </v-card-title>
        
        <v-card-text>
          <v-form ref="form">
            <v-text-field
              v-model="editedItem.nome"
              label="Nome"
              required
              :rules="[v => !!v || 'Nome é obrigatório']"
            />
            
            <v-text-field
              v-model="editedItem.cpf"
              label="CPF"
              required
              :rules="[v => !!v || 'CPF é obrigatório']"
              @input="formatarCPFInput"
              placeholder="000.000.000-00"
            />
            
            <v-text-field
              v-model="editedItem.email"
              label="Email"
              type="email"
              required
              :rules="[
                v => !!v || 'E-mail é obrigatório',
                v => /.+@.+\..+/.test(v) || 'E-mail deve ser válido'
              ]"
            />
            
            <v-text-field
              v-model="editedItem.senha"
              label="Senha"
              type="password"
              :required="editIndex === -1"
              :rules="editIndex === -1 ? [v => !!v || 'Senha é obrigatória'] : []"
            />
          </v-form>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="close">
            Cancelar
          </v-btn>
          <v-btn color="blue-darken-1" variant="text" @click="save" :loading="saving">
            Salvar
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { usuarioService, type Usuario } from '@/services/api'
import { useNotifications } from '@/composables'
import SearchFilter, { type FilterOption } from '@/components/SearchFilter.vue'

const { showApiError, showCreateSuccess, showUpdateSuccess, showDeleteSuccess } = useNotifications()

const usuarios = ref<Usuario[]>([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editIndex = ref(-1)
const form = ref()
const editedItem = ref({
  nome: '',
  cpf: '',
  email: '',
  senha: ''
})

// Variáveis de busca
const searchTerm = ref('')
const selectedFilter = ref('nome')

const filterOptions: FilterOption[] = [
  { value: 'nome', label: 'Nome', placeholder: 'Digite o nome do usuário...' },
  { value: 'cpf', label: 'CPF', placeholder: 'Digite o CPF do usuário...' },
  { value: 'email', label: 'Email', placeholder: 'Digite o email do usuário...' }
]

const headers = [
  { title: 'Nome', key: 'nome' },
  { title: 'CPF', key: 'cpf' },
  { title: 'Email', key: 'email' },
  { title: 'Ações', key: 'actions', sortable: false }
]

// Computed para filtrar usuários
const filteredUsuarios = computed(() => {
  if (!searchTerm.value) {
    return usuarios.value
  }
  
  return usuarios.value.filter(usuario => {
    const term = searchTerm.value.toLowerCase()
    
    switch (selectedFilter.value) {
      case 'nome':
        return usuario.nome.toLowerCase().includes(term)
      case 'cpf':
        return usuario.cpf.toLowerCase().includes(term)
      case 'email':
        return usuario.email.toLowerCase().includes(term)
      default:
        return true
    }
  })
})

// Funções de busca
const onSearch = (filter: string, term: string) => {
  selectedFilter.value = filter
  searchTerm.value = term
}

const clearSearch = () => {
  searchTerm.value = ''
  selectedFilter.value = 'nome'
}

const carregarUsuarios = async () => {
  loading.value = true
  try {
    const response = await usuarioService.listar()
    usuarios.value = response.data.data
  } catch (error) {
    showApiError(error)
  } finally {
    loading.value = false
  }
}

const editarUsuario = (item: Usuario) => {
  editIndex.value = usuarios.value.indexOf(item)
  editedItem.value = {
    nome: item.nome,
    cpf: item.cpf,
    email: item.email,
    senha: ''
  }
  dialog.value = true
}

const excluirUsuario = async (item: Usuario) => {
  if (confirm('Tem certeza que deseja excluir este usuário?')) {
    try {
      await usuarioService.excluir(item.id)
      showDeleteSuccess('Usuário')
      await carregarUsuarios()
    } catch (error) {
      showApiError(error)
    }
  }
}

const close = () => {
  dialog.value = false
  editIndex.value = -1
  editedItem.value = {
    nome: '',
    cpf: '',
    email: '',
    senha: ''
  }
  if (form.value) {
    form.value.resetValidation()
  }
}

const save = async () => {
  if (form.value && !form.value.validate()) {
    return
  }

  saving.value = true
  try {
    if (editIndex.value === -1) {
      await usuarioService.criar(editedItem.value)
      showCreateSuccess('Usuário')
    } else {
      const usuario = usuarios.value[editIndex.value]
      await usuarioService.atualizar(usuario.id, editedItem.value)
      showUpdateSuccess('Usuário')
    }
    close()
    await carregarUsuarios()
  } catch (error) {
    showApiError(error)
  } finally {
    saving.value = false
  }
}

const formatarCPF = (cpf: string): string => {
  // Remove caracteres não numéricos
  const cpfLimpo = cpf.replace(/\D/g, '')
  
  // Aplica a máscara XXX.XXX.XXX-XX
  return cpfLimpo.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
}

const formatarCPFInput = (event: Event) => {
  const input = event.target as HTMLInputElement
  let value = input.value.replace(/\D/g, '')

  if (value.length > 11) {
    value = value.slice(0, 11)
  }

  if (value.length >= 3) {
    if (value.length >= 6) {
      if (value.length >= 9) {
        editedItem.value.cpf = value.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4')
      } else {
        editedItem.value.cpf = value.replace(/(\d{3})(\d{3})(\d{3})/, '$1.$2.$3')
      }
    } else {
      editedItem.value.cpf = value.replace(/(\d{3})(\d{3})/, '$1.$2')
    }
  } else {
    editedItem.value.cpf = value
  }
}

onMounted(() => {
  carregarUsuarios()
})
</script>

<style scoped>
.usuarios {
  padding: 16px;
}

@media (min-width: 600px) {
  .usuarios {
    padding: 20px;
  }
}

/* Melhorias para modo dark */
.v-card {
  transition: background-color 0.3s ease;
}

.v-data-table {
  transition: background-color 0.3s ease;
}
</style>
