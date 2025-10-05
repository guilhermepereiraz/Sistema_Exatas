import { createRouter, createWebHistory } from 'vue-router'
import LoginView from '../views/LoginView.vue'
import DashboardView from '../views/DashboardView.vue'
import AdminPanelView from '../views/AdminPanelView.vue'
import { authService } from '../services/auth'
import HomePage from '@/views/HomePage.vue'
import UserInfoPainel from '@/views/UserInfoPainel.vue'
import Header_Principal from '@/views/Header_Principal.vue'
import Header_Logout from '@/views/Header_Logout.vue'

// Configuração das rotas da aplicação
const router = createRouter({
  history: createWebHistory(import.meta.env.BASE_URL),
  routes: [
    {
      path: '/',
      name: 'login',
      component: UserInfoPainel,
    },

    {
      path: '/home',
      name: 'home',
      component: HomePage,
    },

    {
      path: '/perfil',
      name: 'perfil',
      component: UserInfoPainel,
    },

    {
      path: '/dashboard',
      name: 'dashboard',
      component: DashboardView,
      meta: { requiresAuth: true } // Rota protegida que requer autenticação
    },
    {
      path: '/admin',
      name: 'admin',
      component: AdminPanelView,
      meta: { requiresAuth: true } // Rota protegida que requer autenticação
    },
  ],
})

// Guard de navegação para verificar autenticação
router.beforeEach((to, from, next) => {
  // Se a rota requer autenticação e o usuário não está logado
  if (to.meta.requiresAuth && !authService.isAuthenticated()) {
    next('/') // Redireciona para login
  }
  // Se está na página de login e já está autenticado
  else if (to.name === 'login' && authService.isAuthenticated()) {
    next('/dashboard') // Redireciona para dashboard
  }
  // Caso contrário, permite navegação normal
  else {
    next()
  }
})

export default router
