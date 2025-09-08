<template>
  <v-card class="mb-4" elevation="2">
    <v-card-text>
      <v-row>
        <v-col cols="12" md="4">
          <v-select
            v-model="selectedFilter"
            :items="filterOptions"
            item-title="label"
            item-value="value"
            label="Filtrar por"
            variant="outlined"
            density="compact"
            clearable
          />
        </v-col>
        
        <v-col cols="12" md="8">
          <v-text-field
            v-model="searchTerm"
            :label="currentFilterLabel"
            :placeholder="currentFilterPlaceholder"
            variant="outlined"
            density="compact"
            clearable
            @input="onSearch"
            @keyup.enter="onSearch"
          >
            <template v-slot:prepend-inner>
              <v-icon>mdi-magnify</v-icon>
            </template>
          </v-text-field>
        </v-col>
      </v-row>
      
      <v-row v-if="searchTerm" class="mt-2">
        <v-col cols="12">
          <v-chip
            color="primary"
            variant="outlined"
            closable
            @click:close="clearSearch"
          >
            <v-icon start>mdi-filter</v-icon>
            {{ currentFilterLabel }}: "{{ searchTerm }}"
          </v-chip>
        </v-col>
      </v-row>
    </v-card-text>
  </v-card>
</template>

<script setup lang="ts">
import { ref, computed, watch } from 'vue'

export interface FilterOption {
  value: string
  label: string
  placeholder: string
}

interface Props {
  filterOptions: FilterOption[]
  modelValue?: string
  selectedFilterValue?: string
}

interface Emits {
  (e: 'update:modelValue', value: string): void
  (e: 'update:selectedFilterValue', value: string): void
  (e: 'search', filter: string, term: string): void
  (e: 'clear'): void
}

const props = withDefaults(defineProps<Props>(), {
  modelValue: '',
  selectedFilterValue: ''
})

const emit = defineEmits<Emits>()

const searchTerm = ref(props.modelValue)
const selectedFilter = ref(props.selectedFilterValue || (props.filterOptions[0]?.value || ''))

const currentFilterLabel = computed(() => {
  const option = props.filterOptions.find(opt => opt.value === selectedFilter.value)
  return option?.label || 'Buscar'
})

const currentFilterPlaceholder = computed(() => {
  const option = props.filterOptions.find(opt => opt.value === selectedFilter.value)
  return option?.placeholder || 'Digite para buscar...'
})

const onSearch = () => {
  if (searchTerm.value && selectedFilter.value) {
    emit('search', selectedFilter.value, searchTerm.value)
  }
}

const clearSearch = () => {
  searchTerm.value = ''
  emit('update:modelValue', '')
  emit('clear')
}

watch(() => props.modelValue, (newValue) => {
  searchTerm.value = newValue
})

watch(() => props.selectedFilterValue, (newValue) => {
  selectedFilter.value = newValue
})

watch(searchTerm, (newValue) => {
  emit('update:modelValue', newValue)
})

watch(selectedFilter, (newValue) => {
  emit('update:selectedFilterValue', newValue)
})
</script>

<style scoped>
.v-chip {
  margin-right: 8px;
  margin-bottom: 4px;
}
</style>
