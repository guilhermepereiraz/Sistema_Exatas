<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { userApi } from '../services/api'
import HeaderLogout from './Header_Logout.vue'

const router = useRouter()

const userEmail = ref('')
const userName = ref('')
const userType = ref('')
const userId = ref(null)
const userPhotoUrl = ref(null)

const isEditing = ref(false)
const fileInput = ref(null)
const selectedFile = ref(null)
const isLoading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const isChangingPassword = ref(false)
const oldPassword = ref('')
const newPassword = ref('')

const placeholderAvatar = '/Imagens/Carregar_foto_perfil.png'

const userData = ref({
  nome: '',
  criadoEm: '',
  divisao: 'Santana - Mirante',
  email: '',
  avatar: placeholderAvatar,
})

const originalUserData = ref(null)

// Helper para construir URL completa da foto
function getPhotoUrl(photoUrl) {
  if (!photoUrl) return placeholderAvatar
  
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

// Carrega dados do usuário da API
async function loadUserData() {
  try {
    isLoading.value = true
    errorMessage.value = ''
    
    const user = authService.getCurrentUser()
    
    if (!user || !user.id) {
      router.push('/')
      return
    }
    
    userId.value = user.id
    
    // Busca dados completos do usuário da API
    const response = await userApi.getUser(user.id)
    const userDataFromApi = response.data.data || response.data
    
    // Preenche dados do usuário
    userName.value = userDataFromApi.name || user.name || 'Usuário'
    userEmail.value = userDataFromApi.email || user.email || 'N/A'
    userType.value = userDataFromApi.nivel_acesso || user.nivel_acesso || 'Usuário'
    
    // Configura foto do usuário
    if (userDataFromApi.photo_url) {
      userPhotoUrl.value = userDataFromApi.photo_url
      userData.value.avatar = getPhotoUrl(userDataFromApi.photo_url)
    } else {
      userPhotoUrl.value = null
      userData.value.avatar = placeholderAvatar
    }
    
    // Formata data de criação
    if (userDataFromApi.created_at) {
      const date = new Date(userDataFromApi.created_at)
      userData.value.criadoEm = date.toLocaleDateString('pt-BR', {
        day: 'numeric',
        month: 'long',
        year: 'numeric'
      })
    }
    
    userData.value.nome = userName.value
    userData.value.email = userEmail.value
    
  } catch (error) {
    console.error('Erro ao carregar dados do usuário:', error)
    errorMessage.value = 'Erro ao carregar dados do usuário. Tente novamente.'
    
    // Fallback para dados do localStorage
    const user = authService.getCurrentUser()
    if (user) {
      userName.value = user.name || 'Usuário'
      userEmail.value = user.email || 'N/A'
      userData.value.nome = userName.value
      userData.value.email = userEmail.value
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  loadUserData()
})

function editarPerfil() {
  originalUserData.value = JSON.parse(JSON.stringify(userData.value))
  selectedFile.value = null
  isEditing.value = true
  errorMessage.value = ''
  successMessage.value = ''
}

async function salvarAlteracoes() {
  try {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''
    
    // Se há um arquivo selecionado, faz upload
    if (selectedFile.value) {
      const response = await userApi.uploadPhoto(userId.value, selectedFile.value)
      
      // Atualiza a URL da foto com a resposta da API
      const photoUrl = response.data.data?.photo_url || response.data.photo_url
      if (photoUrl) {
        userPhotoUrl.value = photoUrl
        userData.value.avatar = getPhotoUrl(photoUrl)
      }
      
      // Limpa o arquivo selecionado
      selectedFile.value = null
      successMessage.value = 'Foto atualizada com sucesso!'
    }
    
    // Atualiza nome e email se foram alterados
    if (userName.value !== originalUserData.value.nome || 
        userEmail.value !== originalUserData.value.email) {
      await userApi.updateUser(userId.value, {
        name: userName.value,
        email: userEmail.value
      })
      successMessage.value = successMessage.value || 'Dados atualizados com sucesso!'
    }
    
    // Atualiza dados no localStorage
    const user = authService.getCurrentUser()
    if (user) {
      user.name = userName.value
      user.email = userEmail.value
      if (userPhotoUrl.value) {
        user.photo_url = userPhotoUrl.value
      }
      localStorage.setItem('user_data', JSON.stringify(user))
      
      // Dispara evento para atualizar headers na mesma aba
      window.dispatchEvent(new Event('userPhotoUpdated'))
    }
    
    isEditing.value = false
    
    // Limpa mensagem de sucesso após 3 segundos
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (error) {
    console.error('Erro ao salvar alterações:', error)
    errorMessage.value = error.response?.data?.message || 
                        error.response?.data?.error || 
                        'Erro ao salvar alterações. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

function cancelarAlteracao() {
  userData.value = JSON.parse(JSON.stringify(originalUserData.value))
  userName.value = originalUserData.value.nome
  userEmail.value = originalUserData.value.email
  selectedFile.value = null
  isEditing.value = false
  errorMessage.value = ''
  successMessage.value = ''
  
  // Limpa o input de arquivo
  if (fileInput.value) {
    fileInput.value.value = ''
  }
}

function triggerFileInput() {
  fileInput.value?.click()
}

function onFileChange(event) {
  const file = event.target.files[0]
  if (file) {
    // Validação do arquivo
    const maxSize = 2 * 1024 * 1024 // 2MB
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif']
    
    if (!allowedTypes.includes(file.type)) {
      errorMessage.value = 'Formato de arquivo não permitido. Use JPG, PNG ou GIF.'
      return
    }
    
    if (file.size > maxSize) {
      errorMessage.value = 'Arquivo muito grande. Tamanho máximo: 2MB.'
      return
    }
    
    selectedFile.value = file
    userData.value.avatar = URL.createObjectURL(file)
    errorMessage.value = ''
  }
}

async function removePhoto() {
  if (!confirm('Tem certeza que deseja remover sua foto de perfil?')) {
    return
  }
  
  try {
    isLoading.value = true
    errorMessage.value = ''
    
    await userApi.removePhoto(userId.value)
    
    userPhotoUrl.value = null
    userData.value.avatar = placeholderAvatar
    selectedFile.value = null
    
    // Atualiza dados no localStorage
    const user = authService.getCurrentUser()
    if (user) {
      user.photo_url = null
      localStorage.setItem('user_data', JSON.stringify(user))
      
      // Dispara evento para atualizar headers na mesma aba
      window.dispatchEvent(new Event('userPhotoUpdated'))
    }
    
    successMessage.value = 'Foto removida com sucesso!'
    
    // Limpa o input de arquivo
    if (fileInput.value) {
      fileInput.value.value = ''
    }
    
    setTimeout(() => {
      successMessage.value = ''
    }, 3000)
    
  } catch (error) {
    console.error('Erro ao remover foto:', error)
    errorMessage.value = error.response?.data?.message || 
                        error.response?.data?.error || 
                        'Erro ao remover foto. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

function showPasswordFields() {
  isChangingPassword.value = true
}

function saveNewPassword() {
  console.log('Senha Antiga:', oldPassword.value)
  console.log('Senha Nova:', newPassword.value)
  hidePasswordFields()
}

function hidePasswordFields() {
  isChangingPassword.value = false
  oldPassword.value = ''
  newPassword.value = ''
}
</script>

<template>
  <HeaderLogout />

  <div class="info-container">
    <input
      type="file"
      ref="fileInput"
      @change="onFileChange"
      style="display: none"
      accept="image/*"
    />

    <div class="info-header">
      <h1 class="info_userh1">Informações do Usuário</h1>
      <button v-if="!isEditing" class="info_button" @click="editarPerfil">
        Editar Informações
      </button>

      <div v-if="isEditing" class="action-buttons">
        <button 
          class="btn-salvar" 
          @click="salvarAlteracoes"
          :disabled="isLoading"
        >
          {{ isLoading ? 'Salvando...' : 'Salvar' }}
        </button>
        <button 
          class="btn-cancelar" 
          @click="cancelarAlteracao"
          :disabled="isLoading"
        >
          Cancelar
        </button>
      </div>
    </div>

    <hr class="hr_cima" />

    <!-- Mensagens de feedback -->
    <div v-if="errorMessage" class="message error-message">
      {{ errorMessage }}
    </div>
    <div v-if="successMessage" class="message success-message">
      {{ successMessage }}
    </div>

    <!-- Loading overlay -->
    <div v-if="isLoading" class="loading-overlay">
      <div class="loading-spinner"></div>
      <p>Carregando...</p>
    </div>

    <div class="user-details">
      <div class="detail-item item-nome">
        <h1>Nome Completo:</h1>
        <h2 v-if="!isEditing">{{ userName }}</h2>
        <input v-else type="text" v-model="userName" class="edit-input" />
      </div>

      <div class="detail-item item-criado">
        <h1>Usuário Criado em:</h1>
        <h2>{{ userData.criadoEm || 'Carregando...' }}</h2>
      </div>

      <div class="detail-item item-divisao">
        <h1>Divisão:</h1>
        <h2>Santana - Mirante</h2>
      </div>

      <div class="detail-item item-avatar">
        <div class="avatar-container">
          <img
            :src="userData.avatar"
            alt="Avatar do usuário"
            class="avatar"
            :class="{ 'editable-avatar': isEditing }"
            @click="isEditing ? triggerFileInput() : null"
          />
          <div v-if="isEditing" class="avatar-actions">
            <button 
              class="btn-remove-photo" 
              @click="removePhoto"
              :disabled="!userPhotoUrl && !selectedFile"
              title="Remover foto"
            >
              Remover Foto
            </button>
          </div>
        </div>
      </div>

      <div class="detail-item item-email">
        <h1>E-mail:</h1>
        <h2 v-if="!isEditing">{{ userEmail }}</h2>
        <input v-else type="text" v-model="userEmail" class="edit-input" />
      </div>

      <div class="detail-item item-senha">
        <h1>Senha:</h1>

        <button v-if="!isChangingPassword" @click="showPasswordFields" class="alt_bnt">
          Alterar Senha
        </button>

        <div v-if="isChangingPassword" class="password-change-form">
          <input
            type="password"
            v-model="oldPassword"
            class="edit-password"
            placeholder="Informe a senha antiga"
          />
          <input
            type="password"
            v-model="newPassword"
            class="edit-password"
            placeholder="Informe a nova senha"
          />
          <button class="bntsalvar" @click="saveNewPassword">Salvar</button>
        </div>
      </div>
    </div>
  </div>
</template>

<style setup>
.home_green {
  background-color: rgba(19, 44, 13, 1);
  width: 100%;
  height: 13vh;
}

.dados_principais {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5%;
}

.icone-logout {
  width: 40px; /* Ajuste o tamanho do ícone de perfil */
  height: 40px;
  color: white;
  margin-right: 10px;
  cursor: pointer;
}

img {
  width: 250px;
}

.info-container {
  width: 75%;
  min-width: 620px;
  margin: 5% auto;
  height: auto;
  border-radius: 20px;
  border: 1px solid rgba(180, 180, 180, 0.8);
  box-shadow: -3px 3px 2px 2px rgba(197, 197, 197, 0.726);
  background-color: #fafcff;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 55px 10px 55px;
}

.info_userh1 {
  font-size: 25px;
  color: rgba(0, 0, 0, 0.8);
}

.hr_cima {
  width: 92%;
  margin: auto;
  margin-top: 5px;
}

.info_button {
  border: 1px solid black;
  padding: 10px;
  font-size: 20px;
  font-weight: bolder;
  color: white;
  background-color: rgba(19, 44, 13, 0.8);
  border-radius: 10px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.info_button:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.user-details {
  padding: 60px 55px;
  display: grid;
  grid-template-columns: minmax(300px, 2fr) minmax(180px, 1fr);
  grid-template-areas:
    'nome    criado'
    'divisao avatar'
    'email   avatar'
    'senha   avatar';
  gap: 2.5rem 30rem;
}

.item-nome {
  grid-area: nome;
}
.item-criado {
  grid-area: criado;
}
.item-divisao {
  grid-area: divisao;
}
.item-email {
  grid-area: email;
}
.item-senha {
  grid-area: senha;
}
.item-avatar {
  grid-area: avatar;
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail-item h1 {
  font-size: 20px;
  color: rgba(0, 0, 0, 0.6);
  font-weight: bold;
}

.detail-item h2 {
  font-size: 21px;
  color: #000000;
  word-break: break;
}

.password-btn {
  border: 1px solid #666;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: bold;
  color: #333;
  background-color: transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.password-btn:hover {
  background-color: #f0f0f0;
  border-color: #333;
}

.avatar {
  width: 100%;
  max-width: 160px;
  height: auto;
  border-radius: 12px;
  object-fit: cover;
}

.user_passowrd {
  padding: 0px 65px;
}

.user_passowrd h1 {
  font-size: 20px;
  color: rgba(0, 0, 0, 0.6);
  font-weight: bold;
}

.user_passowrd h2 {
  font-size: 21px;
  color: #000000;
}

.alt_bnt {
  border: 1px solid black;
  padding: 10px;
  margin-top: 10px;
  font-size: 20px;
  font-weight: bolder;
  color: white;
  background-color: rgba(19, 44, 13, 0.8);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.alt_bnt:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.action-buttons {
  display: flex;
  gap: 15px; /* Espaçamento entre os botões */
}

/* Estilo para o botão SALVAR */
.btn-salvar {
  border: 2px solid #353535;
  padding: 10px 50px;
  font-size: 18px;
  font-weight: bolder;
  color: white;
  background-color: rgba(19, 44, 13, 0.8); /* Um verde para salvar */
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-salvar:hover {
  background-color: #1e501e;
  transform: translateY(-2px);
}

/* Estilo para o botão CANCELAR */
.btn-cancelar {
  border: 2px solid #353535;
  padding: 10px 40px;
  font-size: 18px;
  font-weight: bolder;
  color: white;
  background-color: rgba(139, 14, 14, 0.8); /* Um cinza claro para cancelar */
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-cancelar:hover {
  background-color: rgba(163, 8, 8, 0.8);
  transform: translateY(-2px);
}

/* Classe para a imagem quando estiver editável */
.editable-avatar {
  cursor: pointer;
  opacity: 0.8;
  border: 2px dashed #333; /* Adiciona uma borda para indicar que é clicável */
}

.editable-avatar:hover {
  opacity: 1;
  border-color: #2a6e2a;
}

.edit-input {
  width: 55%;
  font-size: 15px;
  padding: 12px 12px 12px 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  color: #333;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.edit-input:focus {
  outline: none;
  border-color: rgba(19, 44, 13, 0.8);
  box-shadow: 0 0 5px rgba(19, 44, 13, 0.5);
}

.password-change-form {
  display: flex;
  flex-wrap: wrap; 
  align-items: center;
  gap: 5px; /* <-- ADICIONE ESTA LINHA */
}

.edit-password {
  width: 40%;
  font-size: 15px;
  padding: 12px 12px 12px 20px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  color: #333;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}


.bntsalvar {
  padding: 8px 25px;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  border: 1px solid black;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.3s ease;
  margin-left: 10px;
}

.bntsalvar:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

/* Mensagens de feedback */
.message {
  margin: 15px 55px;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 500;
  animation: slideIn 0.3s ease-out;
}

.error-message {
  background-color: #fee;
  color: #c33;
  border: 1px solid #fcc;
}

.success-message {
  background-color: #efe;
  color: #3c3;
  border: 1px solid #cfc;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

/* Loading overlay */
.loading-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  z-index: 9999;
}

.loading-spinner {
  width: 50px;
  height: 50px;
  border: 5px solid #f3f3f3;
  border-top: 5px solid rgba(19, 44, 13, 0.8);
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

.loading-overlay p {
  color: white;
  margin-top: 20px;
  font-size: 18px;
}

@keyframes spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

/* Avatar container */
.avatar-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 15px;
}

.avatar-actions {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.btn-remove-photo {
  padding: 8px 16px;
  font-size: 14px;
  font-weight: bold;
  color: white;
  background-color: rgba(139, 14, 14, 0.8);
  border: 1px solid #8b0e0e;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.btn-remove-photo:hover:not(:disabled) {
  background-color: rgba(163, 8, 8, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 14, 14, 0.3);
}

.btn-remove-photo:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

/* Botões desabilitados */
.btn-salvar:disabled,
.btn-cancelar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

@media (max-width: 992px) {
  .info-container {
    width: 90%;
  }
  .user-details {
    gap: 2rem 2.5rem;
    padding: 40px 30px;
  }
  .avatar {
    max-width: 120px;
  }
  .info-header {
    padding: 20px 30px 10px 30px;
  }
  .info_userh1,
  .detail-item h1,
  .detail-item h2 {
    font-size: 95%;
  }
}

@media (max-width: 768px) {
  .info-container {
    width: 95%;
    /* REMOVEMOS A LARGURA MÍNIMA PARA O LAYOUT DE CELULAR FUNCIONAR */
    min-width: 0;
    margin: 2rem auto;
    padding-bottom: 2rem;
  }
  .info-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    padding: 20px;
    text-align: center;
  }
  .user-details {
    grid-template-columns: 1fr;
    grid-template-areas:
      'avatar'
      'nome'
      'email'
      'criado'
      'divisao'
      'senha';
    gap: 1.5rem;
    padding: 20px;
    text-align: center;
  }
  .avatar {
    max-width: 140px;
  }
  .item-avatar {
    justify-self: center;
    margin-bottom: 1rem;
  }
  .detail-item h1 {
    font-size: 16px;
  }
  .detail-item h2 {
    font-size: 18px;
  }
  .alt_bnt,
  .info_button {
    width: 100%;
    font-size: 18px;
  }
}
</style>
