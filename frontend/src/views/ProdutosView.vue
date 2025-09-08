<template>
  <div class="produtos">
    <v-card>
      <v-card-title class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-2">
        <span class="text-h6">Gerenciar Produtos</span>
        <v-btn 
          color="primary" 
          @click="dialog = true"
          class="align-self-stretch align-self-md-auto"
          size="large"
        >
          <v-icon start>mdi-plus</v-icon>
          <span class="d-none d-sm-inline">Novo Produto</span>
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
          :items="filteredProdutos"
          :loading="loading"
          class="elevation-1"
        >
          <template v-slot:item.actions="{ item }">
            <v-btn icon small @click="editarProduto(item)">
              <v-icon>mdi-pencil</v-icon>
            </v-btn>
            <v-btn icon small @click="excluirProduto(item)">
              <v-icon>mdi-delete</v-icon>
            </v-btn>
          </template>
        </v-data-table>
      </v-card-text>
    </v-card>

    <!-- Dialog para criar/editar produto -->
    <v-dialog v-model="dialog" max-width="500px" fullscreen-mobile>
      <v-card>
        <v-card-title>
          {{ editIndex === -1 ? 'Novo Produto' : 'Editar Produto' }}
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
              v-model="editedItem.preco"
              label="Preço"
              type="number"
              step="0.01"
              required
              :rules="[
                v => !!v || 'Preço é obrigatório',
                v => parseFloat(v) > 0 || 'Preço deve ser maior que zero'
              ]"
            />
            
            <v-textarea
              v-model="editedItem.descricao"
              label="Descrição"
              rows="3"
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
import { produtoService, type Produto } from '@/services/api'
import { useNotifications } from '@/composables'
import SearchFilter, { type FilterOption } from '@/components/SearchFilter.vue'

const { showApiError, showCreateSuccess, showUpdateSuccess, showDeleteSuccess } = useNotifications()

const produtos = ref<Produto[]>([])
const loading = ref(false)
const saving = ref(false)
const dialog = ref(false)
const editIndex = ref(-1)
const form = ref()
const editedItem = ref({
  nome: '',
  preco: '',
  descricao: ''
})

// Variáveis de busca
const searchTerm = ref('')
const selectedFilter = ref('nome')

const filterOptions: FilterOption[] = [
  { value: 'nome', label: 'Nome', placeholder: 'Digite o nome do produto...' },
  { value: 'preco', label: 'Preço', placeholder: 'Digite o preço do produto...' },
  { value: 'descricao', label: 'Descrição', placeholder: 'Digite a descrição do produto...' }
]

const headers = [
  { title: 'Nome', key: 'nome' },
  { title: 'Preço', key: 'preco' },
  { title: 'Descrição', key: 'descricao' },
  { title: 'Ações', key: 'actions', sortable: false }
]

// Computed para filtrar produtos
const filteredProdutos = computed(() => {
  if (!searchTerm.value) {
    return produtos.value
  }
  
  return produtos.value.filter(produto => {
    const term = searchTerm.value.toLowerCase()
    
    switch (selectedFilter.value) {
      case 'nome':
        return produto.nome.toLowerCase().includes(term)
      case 'preco':
        return produto.preco.toString().includes(term)
      case 'descricao':
        return (produto.descricao || '').toLowerCase().includes(term)
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

const carregarProdutos = async () => {
  loading.value = true
  try {
    const response = await produtoService.listar()
    produtos.value = response.data.data
  } catch (error) {
    showApiError(error)
  } finally {
    loading.value = false
  }
}

const editarProduto = (item: Produto) => {
  editIndex.value = produtos.value.indexOf(item)
  editedItem.value = {
    nome: item.nome,
    preco: item.preco.toString(),
    descricao: item.descricao || ''
  }
  dialog.value = true
}

const excluirProduto = async (item: Produto) => {
  if (confirm('Tem certeza que deseja excluir este produto?')) {
    try {
      await produtoService.excluir(item.id)
      showDeleteSuccess('Produto')
      await carregarProdutos()
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
    preco: '',
    descricao: ''
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
      nome: editedItem.value.nome,
      preco: parseFloat(editedItem.value.preco),
      descricao: editedItem.value.descricao
    }

    if (editIndex.value === -1) {
      await produtoService.criar(data)
      showCreateSuccess('Produto')
    } else {
      const produto = produtos.value[editIndex.value]
      await produtoService.atualizar(produto.id, data)
      showUpdateSuccess('Produto')
    }
    close()
    await carregarProdutos()
  } catch (error) {
    showApiError(error)
  } finally {
    saving.value = false
  }
}

onMounted(() => {
  carregarProdutos()
})
</script>

<style scoped>
.produtos {
  padding: 16px;
}

@media (min-width: 600px) {
  .produtos {
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
