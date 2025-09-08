<template>
  <div class="relatorios">
    <v-container>
      <v-card>
        <v-card-title class="d-flex flex-column flex-md-row justify-space-between align-start align-md-center ga-2">
          <span class="text-h6">Relatórios do Sistema</span>
        </v-card-title>
        
        <v-card-text>
          <!-- Query A: Usuários com mais produtos -->
          <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon color="primary" class="me-2">mdi-account-multiple</v-icon>
              Query A: Usuários com mais produtos
            </v-card-title>
            <v-card-text>
              <v-btn 
                color="primary" 
                @click="executarQueryA"
                :loading="loadingQueryA"
                class="mb-4"
              >
                <v-icon start>mdi-play</v-icon>
                Executar Query A
              </v-btn>
              
              <v-data-table
                v-if="resultadoQueryA.length > 0"
                :headers="headersQueryA"
                :items="resultadoQueryA"
                :loading="loadingQueryA"
                class="elevation-1"
              >
                <template v-slot:item.total_produtos="{ item }">
                  <v-chip color="success" variant="outlined">
                    {{ item.total_produtos }}
                  </v-chip>
                </template>
              </v-data-table>
              
              <v-alert v-if="!loadingQueryA && resultadoQueryA.length === 0 && executouQueryA" type="info">
                Nenhum resultado encontrado para esta query.
              </v-alert>
            </v-card-text>
          </v-card>

          <!-- Query B: Produto mais caro por usuário -->
          <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon color="success" class="me-2">mdi-currency-usd</v-icon>
              Query B: Produto mais caro por usuário
            </v-card-title>
            <v-card-text>
              <v-btn 
                color="success" 
                @click="executarQueryB"
                :loading="loadingQueryB"
                class="mb-4"
              >
                <v-icon start>mdi-play</v-icon>
                Executar Query B
              </v-btn>
              
              <v-data-table
                v-if="resultadoQueryB.length > 0"
                :headers="headersQueryB"
                :items="resultadoQueryB"
                :loading="loadingQueryB"
                class="elevation-1"
              >
                <template v-slot:item.preco="{ item }">
                  <v-chip color="success" variant="outlined">
                    R$ {{ Number(item.preco).toFixed(2) }}
                  </v-chip>
                </template>
              </v-data-table>
              
              <v-alert v-if="!loadingQueryB && resultadoQueryB.length === 0 && executouQueryB" type="info">
                Nenhum resultado encontrado para esta query.
              </v-alert>
            </v-card-text>
          </v-card>

          <!-- Query C: Produtos por faixa de preço -->
          <v-card class="mb-6" elevation="2">
            <v-card-title class="d-flex align-center">
              <v-icon color="warning" class="me-2">mdi-chart-bar</v-icon>
              Query C: Produtos por faixa de preço
            </v-card-title>
            <v-card-text>
              <v-btn 
                color="warning" 
                @click="executarQueryC"
                :loading="loadingQueryC"
                class="mb-4"
              >
                <v-icon start>mdi-play</v-icon>
                Executar Query C
              </v-btn>
              
              <v-data-table
                v-if="resultadoQueryC.length > 0"
                :headers="headersQueryC"
                :items="resultadoQueryC"
                :loading="loadingQueryC"
                class="elevation-1"
              >
                <template v-slot:item.quantidade="{ item }">
                  <v-chip color="warning" variant="outlined">
                    {{ item.quantidade }}
                  </v-chip>
                </template>
              </v-data-table>
              
              <v-alert v-if="!loadingQueryC && resultadoQueryC.length === 0 && executouQueryC" type="info">
                Nenhum resultado encontrado para esta query.
              </v-alert>
            </v-card-text>
          </v-card>
        </v-card-text>
      </v-card>
    </v-container>
  </div>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { relatorioService } from '@/services/api'
import { useNotifications } from '@/composables'

const { showApiError } = useNotifications()

// Estados de loading para cada query
const loadingQueryA = ref(false)
const loadingQueryB = ref(false)
const loadingQueryC = ref(false)

// Estados para controlar se a query foi executada
const executouQueryA = ref(false)
const executouQueryB = ref(false)
const executouQueryC = ref(false)

// Resultados das queries
const resultadoQueryA = ref<any[]>([])
const resultadoQueryB = ref<any[]>([])
const resultadoQueryC = ref<any[]>([])

// Headers das tabelas
const headersQueryA = [
  { title: 'ID', key: 'id', sortable: true },
  { title: 'Nome', key: 'nome', sortable: true },
  { title: 'Email', key: 'email', sortable: true },
  { title: 'Total de Produtos', key: 'total_produtos', sortable: true }
]

const headersQueryB = [
  { title: 'ID Usuário', key: 'usuario_id', sortable: true },
  { title: 'Nome do Usuário', key: 'usuario_nome', sortable: true },
  { title: 'ID Produto', key: 'produto_id', sortable: true },
  { title: 'Nome do Produto', key: 'produto_nome', sortable: true },
  { title: 'Preço', key: 'preco', sortable: true }
]

const headersQueryC = [
  { title: 'Faixa de Preço', key: 'faixa', sortable: true },
  { title: 'Quantidade', key: 'quantidade', sortable: true }
]

// Query A: Usuários com mais produtos
const executarQueryA = async () => {
  loadingQueryA.value = true
  executouQueryA.value = true
  
  try {
    const response = await relatorioService.usuariosComMaisProdutos()
    resultadoQueryA.value = response.data.data
    
  } catch (error) {
    showApiError(error)
    resultadoQueryA.value = []
  } finally {
    loadingQueryA.value = false
  }
}

// Query B: Produto mais caro por usuário
const executarQueryB = async () => {
  loadingQueryB.value = true
  executouQueryB.value = true
  
  try {
    const response = await relatorioService.produtoMaisCaroPorUsuario()
    resultadoQueryB.value = response.data.data
    
  } catch (error) {
    showApiError(error)
    resultadoQueryB.value = []
  } finally {
    loadingQueryB.value = false
  }
}

// Query C: Produtos por faixa de preço
const executarQueryC = async () => {
  loadingQueryC.value = true
  executouQueryC.value = true
  
  try {
    const response = await relatorioService.produtosPorFaixaPreco()
    resultadoQueryC.value = response.data.data
    
  } catch (error) {
    showApiError(error)
    resultadoQueryC.value = []
  } finally {
    loadingQueryC.value = false
  }
}
</script>

<style scoped>
.relatorios {
  padding: 16px;
}

@media (min-width: 600px) {
  .relatorios {
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
