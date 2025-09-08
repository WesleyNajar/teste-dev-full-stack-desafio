import { ref, watch } from 'vue'

let globalVuetify: any = null

export function setVuetifyInstance(vuetify: any) {
  globalVuetify = vuetify
}

export function useTheme() {
  const getInitialTheme = () => {
    const savedTheme = localStorage.getItem('theme')
    if (savedTheme) {
      return savedTheme as 'light' | 'dark'
    }
    
    if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
      return 'dark'
    }
    
    return 'light'
  }
  
  const isDark = ref(getInitialTheme() === 'dark')
  
  const applyTheme = (dark: boolean) => {
    localStorage.setItem('theme', dark ? 'dark' : 'light')
    
    if (globalVuetify && globalVuetify.theme) {
      globalVuetify.theme.change(dark ? 'dark' : 'light')
    } else {
      const html = document.documentElement
      if (dark) {
        html.classList.add('v-theme--dark')
        html.classList.remove('v-theme--light')
      } else {
        html.classList.add('v-theme--light')
        html.classList.remove('v-theme--dark')
      }
    }
  }
  
  watch(isDark, (newValue) => {
    applyTheme(newValue)
  }, { immediate: true })
  
  const toggleTheme = () => {
    isDark.value = !isDark.value
  }
  
  const setTheme = (theme: 'light' | 'dark') => {
    isDark.value = theme === 'dark'
  }
  
  return {
    isDark,
    toggleTheme,
    setTheme
  }
}
