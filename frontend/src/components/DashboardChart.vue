<template>
  <v-card class="chart-card" elevation="4">
    <v-card-title class="chart-title">
      <v-icon :color="iconColor" class="me-2">{{ icon }}</v-icon>
      {{ title }}
    </v-card-title>
    <v-card-text class="pa-4">
      <div class="chart-container">
        <!-- Gráfico de Linha -->
        <div v-if="type === 'line'" class="line-chart">
          <div class="chart-grid">
            <div v-for="(_, index) in data.datasets[0].data" :key="index" class="grid-line">
              <div class="grid-label">{{ Math.max(...data.datasets[0].data) - Math.floor((Math.max(...data.datasets[0].data) - Math.min(...data.datasets[0].data)) * index / 4) }}</div>
            </div>
          </div>
          <div class="line-container">
            <svg class="line-svg" viewBox="0 0 400 200">
              <polyline
                :points="linePoints"
                fill="none"
                :stroke="data.datasets[0].borderColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
              <polyline
                :points="linePoints2"
                fill="none"
                :stroke="data.datasets[1].borderColor"
                stroke-width="3"
                stroke-linecap="round"
                stroke-linejoin="round"
              />
            </svg>
            <div class="x-labels">
              <span v-for="label in data.labels" :key="label" class="x-label">{{ label }}</span>
            </div>
          </div>
          <div class="legend">
            <div class="legend-item">
              <div class="legend-color" :style="{ backgroundColor: data.datasets[0].borderColor }"></div>
              <span>{{ data.datasets[0].label }}</span>
            </div>
            <div class="legend-item">
              <div class="legend-color" :style="{ backgroundColor: data.datasets[1].borderColor }"></div>
              <span>{{ data.datasets[1].label }}</span>
            </div>
          </div>
        </div>

        <!-- Gráfico de Barras -->
        <div v-else-if="type === 'bar'" class="bar-chart">
          <div class="bars-container">
            <div v-for="(value, index) in data.datasets[0].data" :key="index" class="bar-item">
              <div class="bar" :style="{ 
                height: `${(value / Math.max(...data.datasets[0].data)) * 100}%`,
                backgroundColor: data.datasets[0].backgroundColor[index]
              }"></div>
              <div class="bar-label">{{ data.labels[index] }}</div>
              <div class="bar-value">{{ value }}</div>
            </div>
          </div>
        </div>

        <!-- Gráfico de Rosquinha -->
        <div v-else-if="type === 'doughnut'" class="doughnut-chart">
          <div class="doughnut-container">
            <div class="doughnut-svg">
              <svg viewBox="0 0 200 200" class="doughnut-svg-inner">
                <circle
                  cx="100"
                  cy="100"
                  r="80"
                  fill="none"
                  stroke="rgba(var(--v-theme-outline), 0.1)"
                  stroke-width="20"
                />
                <circle
                  v-for="(_, index) in data.datasets[0].data"
                  :key="index"
                  cx="100"
                  cy="100"
                  r="80"
                  fill="none"
                  :stroke="data.datasets[0].backgroundColor[index]"
                  stroke-width="20"
                  :stroke-dasharray="circumference"
                  :stroke-dashoffset="getDoughnutOffset(index)"
                  stroke-linecap="round"
                  transform="rotate(-90 100 100)"
                />
              </svg>
              <div class="doughnut-center">
                <div class="center-value">{{ getTotalValue() }}</div>
                <div class="center-label">Total</div>
              </div>
            </div>
            <div class="doughnut-legend">
              <div v-for="(label, index) in data.labels" :key="index" class="legend-item">
                <div class="legend-color" :style="{ backgroundColor: data.datasets[0].backgroundColor[index] }"></div>
                <span>{{ label }} ({{ data.datasets[0].data[index] }}%)</span>
              </div>
            </div>
          </div>
        </div>
      </div>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { computed } from 'vue'

interface Props {
  title: string
  icon: string
  iconColor: string
  type: 'bar' | 'doughnut' | 'line'
  data: any
  options?: any
}

const props = defineProps<Props>()

// Computed para pontos da linha
const linePoints = computed(() => {
  if (props.type !== 'line' || !props.data.datasets[0]) return ''
  
  const maxValue = Math.max(...props.data.datasets[0].data)
  const minValue = Math.min(...props.data.datasets[0].data)
  const range = maxValue - minValue
  
  return props.data.datasets[0].data.map((value: number, index: number) => {
    const x = (index / (props.data.datasets[0].data.length - 1)) * 400
    const y = 200 - ((value - minValue) / range) * 180 - 10
    return `${x},${y}`
  }).join(' ')
})

const linePoints2 = computed(() => {
  if (props.type !== 'line' || !props.data.datasets[1]) return ''
  
  const maxValue = Math.max(...props.data.datasets[1].data)
  const minValue = Math.min(...props.data.datasets[1].data)
  const range = maxValue - minValue
  
  return props.data.datasets[1].data.map((value: number, index: number) => {
    const x = (index / (props.data.datasets[1].data.length - 1)) * 400
    const y = 200 - ((value - minValue) / range) * 180 - 10
    return `${x},${y}`
  }).join(' ')
})

// Computed para gráfico de rosquinha
const circumference = computed(() => 2 * Math.PI * 80)

const getDoughnutOffset = (index: number) => {
  if (props.type !== 'doughnut') return 0
  
  let offset = 0
  for (let i = 0; i < index; i++) {
    offset += (props.data.datasets[0].data[i] / 100) * circumference.value
  }
  return circumference.value - offset
}

const getTotalValue = () => {
  if (props.type !== 'doughnut') return 0
  return props.data.datasets[0].data.reduce((sum: number, value: number) => sum + value, 0)
}
</script>

<style scoped>
.chart-card {
  height: 100%;
  border-radius: 16px;
  transition: all 0.3s ease;
}

.chart-card:hover {
  transform: translateY(-4px);
  box-shadow: 0 12px 24px rgba(0, 0, 0, 0.15);
}

.chart-title {
  font-size: 1.25rem;
  font-weight: 600;
  color: rgb(var(--v-theme-on-surface));
  padding-bottom: 8px;
}

.chart-container {
  position: relative;
  height: 300px;
  width: 100%;
}

/* Gráfico de Linha */
.line-chart {
  height: 100%;
  display: flex;
  flex-direction: column;
}

.chart-grid {
  position: absolute;
  top: 0;
  left: 0;
  right: 0;
  height: 200px;
  display: flex;
  flex-direction: column;
  justify-content: space-between;
  padding: 10px 0;
  z-index: 1;
}

.grid-line {
  display: flex;
  align-items: center;
  height: 1px;
  background: rgba(var(--v-theme-outline), 0.1);
  position: relative;
}

.grid-label {
  position: absolute;
  left: -40px;
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.6;
}

.line-container {
  position: relative;
  flex: 1;
  margin-top: 20px;
}

.line-svg {
  width: 100%;
  height: 200px;
}

.x-labels {
  display: flex;
  justify-content: space-between;
  margin-top: 10px;
  padding: 0 20px;
}

.x-label {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.6;
}

.legend {
  display: flex;
  justify-content: center;
  gap: 20px;
  margin-top: 20px;
}

.legend-item {
  display: flex;
  align-items: center;
  gap: 8px;
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface));
}

.legend-color {
  width: 12px;
  height: 12px;
  border-radius: 2px;
}

/* Gráfico de Barras */
.bar-chart {
  height: 100%;
  display: flex;
  align-items: end;
}

.bars-container {
  display: flex;
  align-items: end;
  justify-content: space-between;
  width: 100%;
  height: 200px;
  gap: 8px;
}

.bar-item {
  display: flex;
  flex-direction: column;
  align-items: center;
  flex: 1;
  height: 100%;
}

.bar {
  width: 100%;
  min-height: 4px;
  border-radius: 4px 4px 0 0;
  transition: all 0.3s ease;
  margin-bottom: 8px;
}

.bar:hover {
  opacity: 0.8;
  transform: scaleY(1.05);
}

.bar-label {
  font-size: 0.75rem;
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.7;
  text-align: center;
  margin-bottom: 4px;
  word-break: break-word;
}

.bar-value {
  font-size: 0.875rem;
  font-weight: 600;
  color: rgb(var(--v-theme-primary));
}

/* Gráfico de Rosquinha */
.doughnut-chart {
  height: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
}

.doughnut-container {
  display: flex;
  align-items: center;
  gap: 30px;
  width: 100%;
}

.doughnut-svg {
  position: relative;
  flex-shrink: 0;
}

.doughnut-svg-inner {
  width: 160px;
  height: 160px;
}

.doughnut-center {
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  text-align: center;
}

.center-value {
  font-size: 1.5rem;
  font-weight: 700;
  color: rgb(var(--v-theme-primary));
}

.center-label {
  font-size: 0.875rem;
  color: rgb(var(--v-theme-on-surface));
  opacity: 0.7;
}

.doughnut-legend {
  flex: 1;
  display: flex;
  flex-direction: column;
  gap: 12px;
}

/* Responsive */
@media (max-width: 768px) {
  .chart-container {
    height: 250px;
  }
  
  .doughnut-container {
    flex-direction: column;
    gap: 20px;
  }
  
  .doughnut-legend {
    flex-direction: row;
    flex-wrap: wrap;
    justify-content: center;
  }
  
  .bars-container {
    gap: 4px;
  }
  
  .bar-label {
    font-size: 0.7rem;
  }
  
  .legend {
    flex-direction: column;
    gap: 8px;
  }
}

@media (max-width: 480px) {
  .chart-container {
    height: 200px;
  }
  
  .doughnut-svg-inner {
    width: 120px;
    height: 120px;
  }
  
  .center-value {
    font-size: 1.25rem;
  }
}
</style>
