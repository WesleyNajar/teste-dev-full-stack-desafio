<template>
  <div class="relacionamentos">
    <v-card>
      <v-card-title class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-2">
        <span class="text-h6">Gerenciar Relacionamentos</span>
        <v-btn 
          color="primary" 
          @click="dialog = true"
          class="align-self-stretch align-self-md-auto"
          size="large"
        >
          <v-icon start>mdi-link</v-icon>
          <span class="d-none d-sm-inline">Vincular Usuário-Produto</span>
          <span class="d-sm-none">Vincular</span>
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
          :items="filteredRelacionamentos"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.produto_preco="{ item }">
            R$ {{ Number(item.produto_preco || 0).toFixed(2) }}
          </template>
          
          <template v-slot:item.created_at="{ item }">
            {{ formatarData(item.created_at) }}
          </template>
          
          <template v-slot:item.actions="{ item }">
            <v-btn icon small @click="desvincular(item)">
              <v-icon>mdi-link-off</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Dialog para vincular usuário-produto -->
    <v-dialog v-model="dialog" max-width="500px" fullscreen-mobile>
      <v-card>
        <v-card-title>
          Vincular Usuário a Produto
        </v-card-title>
        
        <v-card-text>
          <v-form ref="form">
            <v-select
              v-model="editedItem.usuario_id"
              :items="usuarios"
              item-title="nome"
              item-value="id"
              label="Usuário"
              required
              :rules="[v => !!v || 'Usuário é obrigatório']"
            />
            
            <v-select
              v-model="editedItem.produto_id"
              :items="produtos"
              item-title="nome"
              item-value="id"
              label="Produto"
              required
              :rules="[v => !!v || 'Produto é obrigatório']"
            />
          </v-form>
        </v-card-text>
        
        <v-card-actions>
          <v-spacer></v-spacer>
          <v-btn color="blue-darken-1" variant="text" @click="close">
            Cancelar
          </v-btn>
          <v-btn color="blue-darken-1" variant="text" @click="save" :loading="saving">
            Vincular
          </v-btn>
        </v-card-actions>
      </v-card>
    </v-dialog>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted, computed } from 'vue'
import { 
  relacionamentoService, 
  usuarioService, 
  produtoService, 
  type RelacionamentoUsuarioProduto,
  type Usuario,
  type Produto
} from '@/services/api'
import { useNotifications } from '@/composables'
import SearchFilter, { type FilterOption } from '@/components/SearchFilter.vue'

const { showApiError, showCreateSuccess, showDeleteSuccess } = useNotifications()

const relacionamentos = ref<RelacionamentoUsuarioProduto[]>([])
const usuarios = ref<Usuario[]>([])
const produtos = ref<Produto[]>([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const form = ref()
const editedItem = ref({
  usuario_id: null as number | null,
  produto_id: null as number | null
})

// Variáveis de busca
const searchTerm = ref('')
const selectedFilter = ref('usuario_nome')

const filterOptions: FilterOption[] = [
  { value: 'usuario_nome', label: 'Usuário', placeholder: 'Digite o nome do usuário...' },
  { value: 'usuario_email', label: 'Email', placeholder: 'Digite o email do usuário...' },
  { value: 'produto_nome', label: 'Produto', placeholder: 'Digite o nome do produto...' },
  { value: 'produto_preco', label: 'Preço', placeholder: 'Digite o preço do produto...' },
  { value: 'created_at', label: 'Data', placeholder: 'Digite a data (DD/MM/AAAA)...' }
]

const headers = [
  { title: 'Usuário', key: 'usuario_nome' },
  { title: 'Email', key: 'usuario_email' },
  { title: 'Produto', key: 'produto_nome' },
  { title: 'Preço', key: 'produto_preco' },
  { title: 'Data', key: 'created_at' },
  { title: 'Ações', key: 'actions', sortable: false }
]

// Computed para filtrar relacionamentos
const filteredRelacionamentos = computed(() => {
  if (!searchTerm.value) {
    return relacionamentos.value
  }
  
  return relacionamentos.value.filter(relacionamento => {
    const term = searchTerm.value.toLowerCase()
    
    switch (selectedFilter.value) {
      case 'usuario_nome':
        return (relacionamento.usuario_nome || '').toLowerCase().includes(term)
      case 'usuario_email':
        return (relacionamento.usuario_email || '').toLowerCase().includes(term)
      case 'produto_nome':
        return (relacionamento.produto_nome || '').toLowerCase().includes(term)
      case 'produto_preco':
        return (relacionamento.produto_preco || '').toString().includes(term)
      case 'created_at':
        return formatarData(relacionamento.created_at).includes(term)
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
  selectedFilter.value = 'usuario_nome'
}

const carregarRelacionamentos = async () => {
  loading.value = true
  try {
    const response = await relacionamentoService.listar()
    relacionamentos.value = response.data.data
  } catch (error) {
    showApiError(error)
  } finally {
    loading.value = false
  }
}

const carregarUsuarios = async () => {
  try {
    const response = await usuarioService.listar()
    usuarios.value = response.data.data
  } catch (error) {
    showApiError(error)
  }
}

const carregarProdutos = async () => {
  try {
    const response = await produtoService.listar()
    produtos.value = response.data.data
  } catch (error) {
    showApiError(error)
  }
}

const desvincular = async (item: RelacionamentoUsuarioProduto) => {
  if (confirm('Tem certeza que deseja desvincular este usuário do produto?')) {
    try {
      await relacionamentoService.desvincular(item.id)
      showDeleteSuccess('Relacionamento')
      await carregarRelacionamentos()
    } catch (error) {
      showApiError(error)
    }
  }
}

const close = () => {
  dialog.value = false
  editedItem.value = {
    usuario_id: null,
    produto_id: null
  }
  form.value?.resetValidation()
}

const save = async () => {
  if (!form.value) return

  const isValid = await form.value.validate()
  if (!isValid) {
    return
  }

  saving.value = true
  try {
    const data = {
      usuario_id: editedItem.value.usuario_id!,
      produto_id: editedItem.value.produto_id!
    }

    await relacionamentoService.vincular(data)
    showCreateSuccess('Relacionamento')
    close()
    await carregarRelacionamentos()
  } catch (error) {
    showApiError(error)
  } finally {
    saving.value = false
  }
}

const formatarData = (data: string) => {
  return new Date(data).toLocaleDateString('pt-BR')
}

onMounted(() => {
  carregarRelacionamentos()
  carregarUsuarios()
  carregarProdutos()
})
</script>

<style scoped>
.relacionamentos {
  padding: 16px;
}

@media (min-width: 600px) {
  .relacionamentos {
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
