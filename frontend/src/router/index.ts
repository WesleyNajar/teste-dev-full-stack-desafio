import { createRouter, createWebHistory } from 'vue-router'
import HomeView from '../views/HomeView.vue'

const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'home',
      component: HomeView
    },
    {
      path: '/usuarios',
      name: 'usuarios',
      component: () => import('../views/UsuariosView.vue')
    },
    {
      path: '/produtos',
      name: 'produtos',
      component: () => import('../views/ProdutosView.vue')
    },
    {
      path: '/relacionamentos',
      name: 'relacionamentos',
      component: () => import('../views/RelacionamentosView.vue')
    },
    {
      path: '/relatorios',
      name: 'relatorios',
      component: () => import('../views/RelatoriosView.vue')
    }
  ]
})

export default router
