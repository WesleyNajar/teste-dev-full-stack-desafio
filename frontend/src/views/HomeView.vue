<template>
  <div class="home">
    <div class="hero-section">
      <v-container>
        <v-row justify="center" align="center" class="hero-content">
          <v-col cols="12" lg="10">
            <div class="text-center mb-8">
              <div class="hero-icon">
                <v-icon size="80" color="primary">mdi-rocket-launch</v-icon>
              </div>
              <h1 class="hero-title">
                Sistema de Gestão Teste
              </h1>
              <p class="hero-subtitle">
                Plataforma completa desenvolvida com <span class="tech-highlight">Vue.js 3</span> e <span class="tech-highlight">Laravel</span>
              </p>
              <div class="hero-badges">
                <v-chip color="primary" variant="outlined" class="ma-1">
                  <v-icon start>mdi-vuejs</v-icon>
                  Vue.js 3
                </v-chip>
                <v-chip color="orange" variant="outlined" class="ma-1">
                  <v-icon start>mdi-laravel</v-icon>
                  Laravel
                </v-chip>
                <v-chip color="blue" variant="outlined" class="ma-1">
                  <v-icon start>mdi-material-design</v-icon>
                  Vuetify
                </v-chip>
              </div>
            </div>
          </v-col>
        </v-row>
      </v-container>
    </div>

    <v-container class="features-section">
      <v-row>
        <v-col cols="12">
          <h2 class="section-title text-center mb-8">
            Módulos do Sistema
          </h2>
        </v-col>
      </v-row>
      
      <v-row>
        <v-col cols="12" md="4" v-for="(module, index) in modules" :key="index">
          <v-card 
            class="module-card" 
            elevation="4" 
            :class="`module-card-${index + 1}`"
            hover
          >
            <v-card-text class="pa-6">
              <div class="module-icon">
                <v-icon :color="module.color" size="48">{{ module.icon }}</v-icon>
              </div>
              
              <h3 class="module-title">{{ module.title }}</h3>
              <p class="module-description">{{ module.description }}</p>
              
              <div class="module-features">
                <v-chip 
                  v-for="feature in module.features" 
                  :key="feature"
                  size="small" 
                  variant="outlined" 
                  class="ma-1"
                >
                  {{ feature }}
                </v-chip>
              </div>
              
              <v-btn 
                :to="module.route" 
                :color="module.color" 
                variant="elevated"
                block
                class="mt-4"
                size="large"
              >
                <v-icon start>{{ module.icon }}</v-icon>
                {{ module.buttonText }}
              </v-btn>
            </v-card-text>
          </v-card>
        </v-col>
      </v-row>
    </v-container>

    <div class="stats-section">
      <v-container>
        <v-row>
          <v-col cols="12">
            <h2 class="section-title text-center mb-8">
              Estatísticas do Sistema
            </h2>
            <div v-if="loading" class="text-center mb-8">
              <v-progress-circular indeterminate color="primary" size="48"></v-progress-circular>
              <p class="mt-4">Carregando estatísticas...</p>
            </div>
          </v-col>
        </v-row>
        
        <v-row v-if="!loading" justify="center">
          <v-col 
            cols="12" 
            sm="6" 
            md="3" 
            v-for="stat in stats" 
            :key="stat.label"
            class="d-flex justify-center"
          >
            <v-card 
              class="stat-card text-center pa-6" 
              elevation="4"
              hover
              :color="`${stat.color}-lighten-5`"
              :class="`stat-card-${stat.color}`"
            >
              <v-card-text class="pa-4">
                <div class="stat-icon mb-4">
                  <v-icon :color="stat.color" size="48">{{ stat.icon }}</v-icon>
                </div>
                <h3 class="stat-number text-h3 font-weight-bold mb-2" :class="`text-${stat.color}`">
                  {{ stat.value }}
                </h3>
                <p class="stat-label text-body-1 text-medium-emphasis">
                  {{ stat.label }}
                </p>
              </v-card-text>
            </v-card>
          </v-col>
        </v-row>
      </v-container>
    </div>

    <!-- Dashboard Section -->
    <div class="dashboard-section">
      <v-container>
        <v-row>
          <v-col cols="12">
            <h2 class="section-title text-center mb-8">
              Dashboard de Estatísticas
            </h2>
            <div v-if="loading" class="text-center mb-8">
              <v-progress-circular indeterminate color="primary" size="64"></v-progress-circular>
              <p class="mt-4">Carregando dados do dashboard...</p>
            </div>
          </v-col>
        </v-row>
        
        <v-row v-if="!loading">
          <v-col cols="12" lg="8">
            <DashboardChart
              title="Crescimento Mensal"
              icon="mdi-chart-line"
              icon-color="primary"
              type="line"
              :data="growthChartData"
            />
          </v-col>
          
          <v-col cols="12" lg="4">
            <DashboardChart
              title="Total de Usuários"
              icon="mdi-chart-donut"
              icon-color="success"
              type="doughnut"
              :data="usersChartData"
            />
          </v-col>
        </v-row>
        
        <v-row v-if="!loading" class="mt-4">
          <v-col cols="12" lg="6">
            <DashboardChart
              title="Total de Produtos"
              icon="mdi-chart-bar"
              icon-color="warning"
              type="bar"
              :data="productsChartData"
            />
          </v-col>
          
          <v-col cols="12" lg="6">
            <DashboardChart
              title="Relacionamentos Ativos"
              icon="mdi-chart-pie"
              icon-color="info"
              type="doughnut"
              :data="relationshipsChartData"
            />
          </v-col>
        </v-row>
      </v-container>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, onMounted } from 'vue'
import DashboardChart from '@/components/DashboardChart.vue'
import { usuarioService, produtoService, relacionamentoService } from '@/services/api'
import { useNotifications } from '@/composables'

// Dados dos módulos do sistema
const modules = ref([
  {
    title: 'Gestão de Usuários',
    description: 'Sistema completo de gerenciamento de usuários com validações avançadas e autenticação.',
    icon: 'mdi-account-group',
    color: 'primary',
    route: '/usuarios',
    buttonText: 'Gerenciar Usuários',
    features: ['CRUD Completo', 'Validações', 'Formulários']
  },
  {
    title: 'Catálogo de Produtos',
    description: 'Controle total do inventário com preços, descrições e categorização de produtos.',
    icon: 'mdi-package-variant',
    color: 'success',
    route: '/produtos',
    buttonText: 'Gerenciar Produtos',
    features: ['Descrição', 'Preço', 'Nome']
  },
  {
    title: 'Relacionamentos',
    description: 'Sistema de vinculação entre usuários e produtos com histórico completo de transações.',
    icon: 'mdi-link-variant',
    color: 'warning',
    route: '/relacionamentos',
    buttonText: 'Ver Relacionamentos',
    features: ['Vinculações']
  }
])

// Estatísticas do sistema (serão carregadas do banco)
const stats = ref([
  {
    icon: 'mdi-account-multiple',
    value: '0',
    label: 'Usuários Cadastrados',
    color: 'primary'
  },
  {
    icon: 'mdi-package',
    value: '0',
    label: 'Produtos Cadastrados',
    color: 'success'
  },
  {
    icon: 'mdi-link',
    value: '0',
    label: 'Relacionamentos Ativos',
    color: 'warning'
  },
  {
    icon: 'mdi-chart-line',
    value: '99.9%',
    label: 'Uptime',
    color: 'info'
  }
])

// Dados dos gráficos (serão carregados do banco)
const growthChartData = ref({
  labels: [] as string[],
  datasets: [
    {
      label: 'Usuários',
      data: [] as number[],
      borderColor: 'rgb(var(--v-theme-primary))',
      backgroundColor: 'rgba(var(--v-theme-primary), 0.1)',
      tension: 0.4,
      fill: true
    },
    {
      label: 'Produtos',
      data: [] as number[],
      borderColor: 'rgb(var(--v-theme-success))',
      backgroundColor: 'rgba(var(--v-theme-success), 0.1)',
      tension: 0.4,
      fill: true
    }
  ]
})

const usersChartData = ref({
  labels: ['Total de Usuários'],
  datasets: [
    {
      data: [0],
      backgroundColor: [
        'rgb(var(--v-theme-primary))'
      ],
      borderWidth: 0,
      hoverOffset: 4
    }
  ]
})

const productsChartData = ref({
  labels: ['Total de Produtos'],
  datasets: [
    {
      label: 'Quantidade',
      data: [0],
      backgroundColor: [
        'rgba(var(--v-theme-success), 0.8)'
      ],
      borderColor: [
        'rgb(var(--v-theme-success))'
      ],
      borderWidth: 2,
      borderRadius: 8,
      borderSkipped: false
    }
  ]
})

const relationshipsChartData = ref({
  labels: ['Relacionamentos Ativos'],
  datasets: [
    {
      data: [0],
      backgroundColor: [
        'rgb(var(--v-theme-warning))'
      ],
      borderWidth: 0,
      hoverOffset: 4
    }
  ]
})

// Estados de carregamento
const loading = ref(false)
const { showApiError } = useNotifications()

// Função para carregar dados reais do dashboard
const carregarDadosDashboard = async () => {
  loading.value = true
  try {
    // Carregar dados em paralelo
    const [usuariosResponse, produtosResponse, relacionamentosResponse] = await Promise.all([
      usuarioService.listar(),
      produtoService.listar(),
      relacionamentoService.listar()
    ])

    const usuarios = usuariosResponse.data.data
    const produtos = produtosResponse.data.data
    const relacionamentos = relacionamentosResponse.data.data

    // Atualizar estatísticas com dados reais
    stats.value[0].value = `${usuarios.length}`
    stats.value[1].value = `${produtos.length}`
    stats.value[2].value = `${relacionamentos.length}`
    // Uptime mantém valor fixo (não existe no banco)
    stats.value[3].value = '99.9%'

    // Atualizar gráfico de usuários
    usersChartData.value = {
      labels: ['Total de Usuários'],
      datasets: [
        {
          data: [usuarios.length],
          backgroundColor: ['rgb(var(--v-theme-primary))'],
          borderWidth: 0,
          hoverOffset: 4
        }
      ]
    }

    // Atualizar gráfico de produtos
    productsChartData.value = {
      labels: ['Total de Produtos'],
      datasets: [
        {
          label: 'Quantidade',
          data: [produtos.length],
          backgroundColor: ['rgba(var(--v-theme-success), 0.8)'],
          borderColor: ['rgb(var(--v-theme-success))'],
          borderWidth: 2,
          borderRadius: 8,
          borderSkipped: false
        }
      ]
    }

    // Atualizar gráfico de relacionamentos
    relationshipsChartData.value = {
      labels: ['Relacionamentos Ativos'],
      datasets: [
        {
          data: [relacionamentos.length],
          backgroundColor: ['rgb(var(--v-theme-warning))'],
          borderWidth: 0,
          hoverOffset: 4
        }
      ]
    }

    // Gerar dados de crescimento baseados nos dados reais
    const meses = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez']
    const crescimentoUsuarios = gerarCrescimentoSimulado(usuarios.length, 12)
    const crescimentoProdutos = gerarCrescimentoSimulado(produtos.length, 12)

    growthChartData.value = {
      labels: meses,
      datasets: [
        {
          label: 'Usuários',
          data: crescimentoUsuarios,
          borderColor: 'rgb(var(--v-theme-primary))',
          backgroundColor: 'rgba(var(--v-theme-primary), 0.1)',
          tension: 0.4,
          fill: true
        },
        {
          label: 'Produtos',
          data: crescimentoProdutos,
          borderColor: 'rgb(var(--v-theme-success))',
          backgroundColor: 'rgba(var(--v-theme-success), 0.1)',
          tension: 0.4,
          fill: true
        }
      ]
    }

  } catch (error) {
    showApiError(error)
  } finally {
    loading.value = false
  }
}

// Função para gerar crescimento simulado baseado no valor atual
const gerarCrescimentoSimulado = (valorAtual: number, meses: number) => {
  const crescimento = []
  const valorInicial = Math.max(1, Math.floor(valorAtual * 0.3))
  
  for (let i = 0; i < meses; i++) {
    const progressao = (i / (meses - 1))
    const valor = Math.floor(valorInicial + (valorAtual - valorInicial) * progressao)
    crescimento.push(valor)
  }
  
  return crescimento
}

// Carregar dados quando o componente for montado
onMounted(() => {
  carregarDadosDashboard()
})
</script>

<style scoped>
.home {
  padding: 0;
  min-height: 100vh;
}

/* Hero Section */
.hero-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.1) 0%, rgba(var(--v-theme-secondary), 0.05) 100%);
  padding: 80px 0;
  position: relative;
  overflow: hidden;
}

.hero-section::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse"><path d="M 10 0 L 0 0 0 10" fill="none" stroke="rgba(var(--v-theme-primary), 0.1)" stroke-width="0.5"/></pattern></defs><rect width="100" height="100" fill="url(%23grid)"/></svg>');
  opacity: 0.3;
}

.hero-content {
  position: relative;
  z-index: 1;
}

.hero-icon {
  margin-bottom: 24px;
  animation: float 3s ease-in-out infinite;
}

@keyframes float {
  0%, 100% { transform: translateY(0px); }
  50% { transform: translateY(-10px); }
}

.hero-title {
  font-size: 3.5rem;
  font-weight: 700;
  background: linear-gradient(135deg, rgb(var(--v-theme-primary)) 0%, rgb(var(--v-theme-secondary)) 100%);
  -webkit-background-clip: text;
  -webkit-text-fill-color: transparent;
  background-clip: text;
  margin-bottom: 16px;
  line-height: 1.2;
}

.hero-subtitle {
  font-size: 1.25rem;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 32px;
  opacity: 0.8;
}

.tech-highlight {
  color: rgb(var(--v-theme-primary));
  font-weight: 600;
}

.hero-badges {
  margin-top: 24px;
}

/* Features Section */
.features-section {
  padding: 80px 0;
}

.section-title {
  font-size: 2.5rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 48px;
}

.module-card {
  height: 100%;
  transition: all 0.3s ease;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
}

.module-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.module-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-secondary)));
}

.module-card-1::before {
  background: linear-gradient(90deg, rgb(var(--v-theme-primary)), #42a5f5);
}

.module-card-2::before {
  background: linear-gradient(90deg, #4caf50, #66bb6a);
}

.module-card-3::before {
  background: linear-gradient(90deg, #ff9800, #ffb74d);
}

.module-icon {
  text-align: center;
  margin-bottom: 24px;
}

.module-title {
  font-size: 1.5rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  margin-bottom: 16px;
  text-align: center;
}

.module-description {
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.7;
  text-align: center;
  margin-bottom: 24px;
  line-height: 1.6;
}

.module-features {
  text-align: center;
  margin-bottom: 24px;
}

/* Stats Section */
.stats-section {
  padding: 80px 0;
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.5) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
  border-top: 1px solid rgba(var(--v-theme-outline), 0.1);
}

.v-theme--dark .stats-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.8) 0%, rgba(var(--v-theme-primary), 0.05) 100%);
}

/* Dashboard Section */
.dashboard-section {
  padding: 80px 0;
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.5) 0%, rgba(var(--v-theme-primary), 0.02) 100%);
}

.stat-card {
  height: 100%;
  transition: all 0.3s ease;
  border-radius: 16px;
  position: relative;
  overflow: hidden;
  min-height: 200px;
  display: flex;
  align-items: center;
  justify-content: center;
}

.stat-card:hover {
  transform: translateY(-8px);
  box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
}

.stat-card::before {
  content: '';
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 4px;
  background: linear-gradient(90deg, rgb(var(--v-theme-primary)), rgb(var(--v-theme-secondary)));
  opacity: 0.8;
}

.stat-icon {
  display: flex;
  justify-content: center;
  align-items: center;
  width: 80px;
  height: 80px;
  border-radius: 50%;
  background: rgba(var(--v-theme-primary), 0.1);
  margin: 0 auto;
}

.stat-number {
  font-size: 2.5rem;
  font-weight: 700;
  margin: 16px 0 8px 0;
  line-height: 1.2;
}

.stat-label {
  font-size: 1rem;
  margin: 0;
  line-height: 1.4;
}

/* Responsividade para estatísticas */
@media (max-width: 600px) {
  .stat-card {
    min-height: 160px;
  }
  
  .stat-number {
    font-size: 2rem;
  }
  
  .stat-icon {
    width: 60px;
    height: 60px;
  }
  
  .stat-icon .v-icon {
    font-size: 36px !important;
  }
}

/* Responsive Design */
@media (max-width: 768px) {
  .hero-title {
    font-size: 2.5rem;
  }
  
  .hero-subtitle {
    font-size: 1.1rem;
  }
  
  .section-title {
    font-size: 2rem;
  }
  
  .hero-section {
    padding: 60px 0;
  }
  
  .features-section {
    padding: 60px 0;
  }
}

@media (max-width: 480px) {
  .hero-title {
    font-size: 2rem;
  }
  
  .stat-number {
    font-size: 2rem;
  }
}

/* Dark mode improvements */
.v-theme--dark .hero-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.15) 0%, rgba(var(--v-theme-secondary), 0.08) 100%);
}

.v-theme--dark .stats-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-primary), 0.08) 0%, rgba(var(--v-theme-secondary), 0.04) 100%);
}

.v-theme--dark .dashboard-section {
  background: linear-gradient(135deg, rgba(var(--v-theme-surface), 0.8) 0%, rgba(var(--v-theme-primary), 0.05) 100%);
}

/* Transitions */
.v-card {
  transition: all 0.3s ease;
}

.v-btn {
  transition: all 0.3s ease;
}

.v-chip {
  transition: all 0.3s ease;
}
</style>
