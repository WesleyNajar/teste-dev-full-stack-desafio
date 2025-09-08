<template>
  <v-app>
    <v-app-bar>
      <v-app-bar-title>Teste Técnico Full Stack</v-app-bar-title>
      <v-spacer />
      
      <!-- Menu desktop -->
      <div class="d-none d-md-flex">
        <v-btn to="/" variant="text">Home</v-btn>
        <v-btn to="/usuarios" variant="text">Usuários</v-btn>
        <v-btn to="/produtos" variant="text">Produtos</v-btn>
        <v-btn to="/relacionamentos" variant="text">Relacionamentos</v-btn>
        <v-btn to="/relatorios" variant="text">Relatórios</v-btn>
      </div>
      
      <!-- Toggle de tema -->
      <v-btn
        icon
        @click="toggleTheme"
        class="me-2"
      >
        <v-icon>{{ isDark ? 'mdi-weather-sunny' : 'mdi-weather-night' }}</v-icon>
      </v-btn>
      
      <!-- Menu hambúrguer mobile -->
      <v-btn
        icon
        class="d-md-none"
        @click="drawer = !drawer"
      >
        <v-icon>mdi-menu</v-icon>
      </v-btn>
    </v-app-bar>

    <!-- Navigation drawer para mobile -->
    <v-navigation-drawer
      v-model="drawer"
      temporary
      location="right"
      class="d-md-none"
    >
      <v-list>
        <v-list-item>
          <v-list-item-title class="text-h6">Menu</v-list-item-title>
        </v-list-item>
        <v-divider></v-divider>
        
        <v-list-item
          prepend-icon="mdi-home"
          title="Home"
          to="/"
          @click="drawer = false"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-account-group"
          title="Usuários"
          to="/usuarios"
          @click="drawer = false"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-package-variant"
          title="Produtos"
          to="/produtos"
          @click="drawer = false"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-link"
          title="Relacionamentos"
          to="/relacionamentos"
          @click="drawer = false"
        ></v-list-item>
        
        <v-list-item
          prepend-icon="mdi-chart-line"
          title="Relatórios"
          to="/relatorios"
          @click="drawer = false"
        ></v-list-item>
      </v-list>
    </v-navigation-drawer>

    <v-main>
      <v-container fluid>
        <router-view />
      </v-container>
    </v-main>

    <!-- Sistema de Notificações -->
    <NotificationAlert
      v-if="currentNotification"
      :type="currentNotification.type"
      :message="currentNotification.message"
      :validation-errors="currentNotification.validationErrors || {}"
      :timeout="currentNotification.timeout || 6000"
      :details="currentNotification.details || ''"
      @close="clearCurrentNotification"
    />
  </v-app>
</template>

<script setup lang="ts">
import { ref } from 'vue'
import { useNotifications, useTheme } from './composables'
import NotificationAlert from './components/NotificationAlert.vue'

const { currentNotification, clearCurrentNotification } = useNotifications()
const { isDark, toggleTheme } = useTheme()
const drawer = ref(false)
</script>

<style>
#app {
  font-family: 'Roboto', sans-serif;
}

/* Transições suaves para mudança de tema */
* {
  transition: background-color 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

/* Melhorias para modo dark */
.v-application {
  transition: background-color 0.3s ease;
}

.v-app-bar {
  transition: background-color 0.3s ease;
}

.v-navigation-drawer {
  transition: background-color 0.3s ease;
}

.v-main {
  transition: background-color 0.3s ease;
}
</style>
