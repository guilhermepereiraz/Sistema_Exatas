<script setup>
import IconUser from '@/components/icons/IconUser.vue'
import IconSeta from '@/components/icons/IconSeta.vue'
import { useRouter } from 'vue-router'
import { ref, onMounted, watch } from 'vue'
import { authService } from '../services/auth'
import { userApi } from '../services/api'

const menuAberto = ref(false)
const router = useRouter()

const userEmail = ref('')
const userName = ref('')
const userType = ref('')
const userPhotoUrl = ref(null)
const placeholderAvatar = '/Imagens/Carregar_foto_perfil.png'

// Helper para construir URL completa da foto
function getPhotoUrl(photoUrl) {
  if (!photoUrl) return null
  
  // Se já é uma URL completa (http:// ou https://), retorna como está
  if (photoUrl.startsWith('http://') || photoUrl.startsWith('https://')) {
    return photoUrl
  }
  
  // Se começa com /, adiciona baseURL da API
  if (photoUrl.startsWith('/')) {
    const baseURL = import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1'
    // Remove /api/v1 do final se existir, pois a foto está em storage público
    const apiBase = baseURL.replace('/api/v1', '')
    return `${apiBase}${photoUrl}`
  }
  
  return photoUrl
}

// Carrega foto do usuário da API
async function loadUserPhoto() {
  try {
    const user = authService.getCurrentUser()
    if (!user || !user.id) return
    
    const response = await userApi.getUser(user.id)
    const userData = response.data.data || response.data
    
    if (userData.photo_url) {
      userPhotoUrl.value = getPhotoUrl(userData.photo_url)
      // Atualiza também no localStorage
      if (user) {
        user.photo_url = userData.photo_url
        localStorage.setItem('user_data', JSON.stringify(user))
      }
    } else {
      userPhotoUrl.value = null
    }
  } catch (error) {
    console.error('Erro ao carregar foto do usuário:', error)
    // Fallback para foto do localStorage se existir
    const user = authService.getCurrentUser()
    if (user?.photo_url) {
      userPhotoUrl.value = getPhotoUrl(user.photo_url)
    }
  }
}

function irParaPerfil() {
    router.push ({ name: 'perfil'})
}

function IrParaInicio() {
  router.push({name: 'home'})
}

// Observa mudanças no localStorage para atualizar foto quando alterada
watch(() => {
  const user = authService.getCurrentUser()
  return user?.photo_url
}, (newPhotoUrl) => {
  if (newPhotoUrl) {
    userPhotoUrl.value = getPhotoUrl(newPhotoUrl)
  } else {
    userPhotoUrl.value = null
  }
})

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
  
  // Carrega foto do usuário
  if (user.photo_url) {
    userPhotoUrl.value = getPhotoUrl(user.photo_url)
  } else {
    loadUserPhoto()
  }
  
  // Listener para atualizar foto quando localStorage mudar (de outras abas/componentes)
  window.addEventListener('storage', (e) => {
    if (e.key === 'user_data') {
      const user = authService.getCurrentUser()
      if (user?.photo_url) {
        userPhotoUrl.value = getPhotoUrl(user.photo_url)
      } else {
        userPhotoUrl.value = null
      }
    }
  })
  
  // Também escuta eventos customizados para atualização na mesma aba
  window.addEventListener('userPhotoUpdated', () => {
    const user = authService.getCurrentUser()
    if (user?.photo_url) {
      userPhotoUrl.value = getPhotoUrl(user.photo_url)
    } else {
      userPhotoUrl.value = null
    }
  })
})

const logout = () => {
  authService.logout()
  router.push('/')
}



</script>

<template>
  <div class="home_green">
    <div class="dados_principais">
      <img src="/Imagens/IconeExata.png" alt="Icone Exatas" v-on:click="IrParaInicio"/>
      <div class="dados_usuarios">
        <!-- Mostra foto do usuário se existir, senão mostra o ícone padrão -->
        <img 
          v-if="userPhotoUrl" 
          :src="userPhotoUrl" 
          alt="Foto do usuário" 
          class="icone-perfil foto-perfil"
        />
        <IconUser v-else class="icone-perfil" />

        <div class="info-texto">
          <h1>{{ userName }}</h1>
          <h2>{{ userEmail }}</h2>
        </div>

        <IconSeta class="icone-seta" :class="{ 'virado-para-baixo': menuAberto}" @click="menuAberto = !menuAberto"/>
        <div v-if="menuAberto" class="div_info_inv">
          <h1 @click="irParaPerfil">Meu Perfil</h1>
          <h1 @click="logout()">Logout</h1>
        </div>
      </div>
    </div>

  </div>

</template>

<style scoped>

.home_green {
       max-height: 12vh;
}

.dados_principais {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2% 0px 0.5%; /* Usando porcentagem para o padding lateral */
}

img:hover {
  cursor: pointer;
}

.dados_principais img {
  width: 180px; /* Tamanho inicial do logo */
  transition: width 0.3s ease; /* Transição suave para a mudança de tamanho */
}

.icone-perfil {
  width: 45px;
  height: 45px;
  min-width: 45px;
  min-height: 45px;
  max-width: 45px;
  max-height: 45px;
  color: white;
  margin-right: 15px;
  border: 3px solid white;
  border-radius: 50%;
  flex-shrink: 0;
  aspect-ratio: 1 / 1; /* Garante proporção quadrada */
  box-sizing: border-box; /* Inclui a borda no cálculo do tamanho */
}

.foto-perfil {
  object-fit: cover;
  object-position: center;
  display: block;
  width: 100%;
  height: 100%;
  border-radius: 50%;
}

.icone-seta {
  width: 25px;
  height: 25px;
  cursor: pointer;
  margin-left: 10px;
  transition: transform 0.3s ease;
  flex-shrink: 0; /* Impede que a seta seja espremida */
}

.icone-seta.virado-para-baixo {
  transform: rotate(180deg);
}

.hr_cima {
  width: 96%;
  margin: 10px auto 0 auto;
  border: 0;
  border-top: 2px solid rgb(255, 255, 255);
}

.dados_usuarios {
  position: relative;
  display: flex;
  align-items: center;
  color: white;
}

.info-texto {
  text-align: left;
  line-height: 1.2; /* Melhora o espaçamento entre as linhas */
}

.dados_usuarios h1 {
  font-size: 18px;
  margin: 0; /* Removido o padding para melhor controle */
}

.dados_usuarios h2 {
  font-size: 14px;
  font-weight: 300; /* Lighter é mais fino que normal */
  margin: 0;
}

.div_info_inv {
  background-color: white;
  position: absolute; /* Posiciona em relação ao pai (.dados_usuarios) */
  top: 100%; /* Começa exatamente onde o pai termina (embaixo) */
  right: 0; /* Alinha à direita do pai */
  margin-top: 8px; /* Cria um pequeno espaço entre o header e o menu */

  width: 250px; /* Defina uma largura fixa para o menu */
  color: #333; /* Cor escura para o texto */
  font-size: 16px;
  text-align: center;
  padding: 10px;
  border-radius: 0px 0px 20px 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Sombra suave */
  z-index: 10;
}

.div_info_inv h1 {
  padding: 4px;
  font-weight: lighter;
  transition: all 0.3s ease;
}

.div_info_inv h1:hover {
  background-color: rgba(19, 44, 13, 0.1);
  cursor: pointer;
}


/* =============================================== */
/* PARTE RESPONSIVA - ADICIONE ISSO NO SEU CÓDIGO  */
/* =============================================== */

/* Para telas com largura máxima de 768px (tablets e celulares) */
@media (max-width: 768px) {
  .dados_principais img {
    width: 150px; /* Diminui um pouco o logo */
  }

  .dados_usuarios h1 {
    font-size: 16px; /* Diminui a fonte do nome */
  }

  .dados_usuarios h2 {
    font-size: 12px; /* Diminui a fonte do cargo */
  }

  .icone-perfil {
    width: 40px;
    height: 40px;
    min-width: 40px;
    min-height: 40px;
    max-width: 40px;
    max-height: 40px;
    margin-right: 10px;
  }
}

/* Para telas com largura máxima de 480px (celulares pequenos) */
@media (max-width: 480px) {
  .dados_principais img {
    width: 120px; /* Diminui ainda mais o logo */
  }

  /* Para economizar espaço, vamos esconder o cargo do usuário */
  .dados_usuarios h2 {
    display: none; 
  }

  .dados_usuarios h1 {
    font-size: 14px; /* Fonte ainda menor para o nome */
  }

  .icone-perfil {
    width: 35px;
    height: 35px;
    min-width: 35px;
    min-height: 35px;
    max-width: 35px;
    max-height: 35px;
    border-width: 2px;
  }

  /* Ajusta o menu dropdown para não ficar tão largo na tela pequena */
  .div_info_inv {
      width: 160px;
  }
}
</style>