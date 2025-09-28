<template>
  <div class="dashboard">
    <header class="dashboard-header">
      <div class="header-left">
        <div class="logo-section">
          <img src="/Imagens/E__3_-removebg-preview (1).png" alt="Logo Exata" class="header-logo" />
        </div>
      </div>
      <div class="user-section">
        <div class="user-info-header">
          <span class="user-name">{{ userName }}</span>
          <span class="user-type">{{ userType }}</span>
        </div>
        <button @click="logout" class="logout-btn">Sair</button>
      </div>
    </header>
    
    <main class="dashboard-content">
      <div class="welcome-card">
        <h2>Bem-vindo ao Sistema Exatas!</h2>
        <p>Você está logado com sucesso.</p>
        <div class="user-info">
          <p><strong>E-mail:</strong> {{ userEmail }}</p>
        </div>
      </div>
      
      <!-- Botões de navegação -->
      <div class="navigation-buttons">
        <button @click="goToImobilizacao" class="nav-button">
          Imobilização
        </button>
        
        <button @click="goToAdminPanel" class="nav-button">
          Painel do Administrador
        </button>
      </div>
    </main>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'

const router = useRouter()

// Dados do usuário para exibição no header
const userEmail = ref('')
const userName = ref('')
const userType = ref('')

// Carrega dados do usuário quando o componente é montado
onMounted(() => {
  const user = authService.getCurrentUser()
  
  // Se não há usuário logado, redireciona para login
  if (!user) {
    router.push('/')
    return
  }
  
  // Preenche dados do usuário para exibição
  userEmail.value = user.email || 'N/A'
  userName.value = user.name || 'Usuário'
  userType.value = user.nivel_acesso || 'Usuário'
})

// Função de logout - limpa dados e redireciona para login
const logout = () => {
  authService.logout()
  router.push('/')
}

// Navega para a página de imobilização
const goToImobilizacao = () => {
  // TODO: Implementar página de imobilização
  console.log('Navegando para Imobilização')
  alert('Funcionalidade de Imobilização será implementada em breve!')
}

// Navega para o painel do administrador
const goToAdminPanel = () => {
  router.push('/admin')
}
</script>

<style scoped>
.dashboard {
  min-height: 100vh;
  background-color: #f5f5f5;
}

.dashboard-header {
  background-color: #132c0d;
  color: white;
  padding: 1rem 2rem;
  display: flex;
  justify-content: space-between;
  align-items: center;
  box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}

.header-left {
  display: flex;
  align-items: center;
}

.user-section {
  display: flex;
  align-items: center;
  gap: 1rem;
}

.user-info-header {
  display: flex;
  flex-direction: column;
  align-items: flex-end;
  text-align: right;
}

.user-name {
  font-size: 1rem;
  font-weight: 600;
  color: white;
  margin-bottom: 0.25rem;
}

.user-type {
  font-size: 0.875rem;
  color: #e0e0e0;
  background-color: rgba(255, 255, 255, 0.1);
  padding: 0.25rem 0.5rem;
  border-radius: 12px;
  text-transform: uppercase;
  font-weight: 500;
}

.logo-section {
  display: flex;
  align-items: center;
  justify-content: flex-start;
}

.header-logo {
  height: 50px;
  width: auto;
  max-width: 120px;
  object-fit: contain;
  filter: brightness(0) invert(1);
  transition: opacity 0.3s ease;
}

.header-logo:hover {
  opacity: 0.8;
}

.logout-btn {
  background-color: #4caf50;
  color: white;
  border: none;
  padding: 0.5rem 1rem;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-size: 0.875rem;
}

.logout-btn:hover {
  background-color: #45a049;
}

.dashboard-content {
  padding: 2rem;
  max-width: 1200px;
  margin: 0 auto;
}

.welcome-card {
  background: white;
  padding: 2rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0,0,0,0.1);
  text-align: center;
}

.welcome-card h2 {
  color: #132c0d;
  margin-bottom: 1rem;
}

.user-info {
  margin-top: 1rem;
  padding: 1rem;
  background-color: #f8f9fa;
  border-radius: 4px;
  text-align: left;
}

/* Estilos dos botões de navegação */
.navigation-buttons {
  display: flex;
  justify-content: space-between;
  margin-top: 3rem;
  padding: 0 2rem;
  gap: 2rem;
}

.nav-button {
  width: 300px; /* Mesma largura do botão de login */
  padding: 15px;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  transition: all 0.3s ease;
  background-color: #132c0d67; /* Mesma cor do botão de login */
  color: white;
  font-size: 1.1em;
  font-weight: 600;
}

.nav-button:hover {
  background-color: #4caf50; /* Cor verde de sucesso no hover */
  transform: translateY(-4px);
  width: 290px;
}

/* Responsividade para telas menores */
@media (max-width: 768px) {
  .dashboard-header {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }
  
  .header-left {
    align-items: center;
    justify-content: center;
  }
  
  .user-section {
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
  }
  
  .user-info-header {
    align-items: center;
    text-align: center;
  }
  
  .header-logo {
    height: 40px;
    max-width: 100px;
  }
  
  .logout-btn {
    width: 100%;
    max-width: 200px;
  }
  
  .navigation-buttons {
    flex-direction: column;
    align-items: center;
    gap: 1rem;
    padding: 0 1rem;
  }
  
  .nav-button {
    width: 90%;
    max-width: 350px; /* Largura relativa em mobile */
    padding: 15px;
  }
}

/* Responsividade para telas médias */
@media (max-width: 1024px) {
  .header-logo {
    height: 45px;
    max-width: 110px;
  }
}
</style>