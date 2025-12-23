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

// Feedback específico para alteração de senha
const showPasswordFeedback = ref(false)
const passwordFeedbackMessage = ref('')
const passwordFeedbackType = ref('success') // 'success' | 'error'

const isChangingPassword = ref(false)
const oldPassword = ref('')
const newPassword = ref('')
const confirmPassword = ref('')

const placeholderAvatar = '/Imagens/3d_avatar_21.png'

const userData = ref({
  nome: '',
  criadoEm: '',
  divisaoCodigo: '',
  divisaoNome: '',
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

    // Preenche dados básicos do usuário
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
        year: 'numeric',
      })
    }

    userData.value.nome = userName.value
    userData.value.email = userEmail.value

    // Define divisão (código e nome) com base nos possíveis formatos de resposta
    let divisaoCodigo = ''
    let divisaoNome = ''

    // 1) Se vier relação "divisao" no usuário
    if (userDataFromApi.divisao) {
      divisaoCodigo = userDataFromApi.divisao.id || userDataFromApi.divisao.codigo || ''
      divisaoNome = userDataFromApi.divisao.nome || userDataFromApi.divisao.descricao || ''
    }

    // 2) Campos diretos no usuário (API)
    if (!divisaoCodigo && (userDataFromApi.divisao_id || userDataFromApi.divisaoCodigo)) {
      divisaoCodigo = userDataFromApi.divisao_id || userDataFromApi.divisaoCodigo
    }
    if (!divisaoNome && (userDataFromApi.divisao_nome || userDataFromApi.divisaoNome)) {
      divisaoNome = userDataFromApi.divisao_nome || userDataFromApi.divisaoNome
    }

    // 3) Fallback: dados salvos no localStorage
    if (!divisaoCodigo && (user.divisao_id || user.divisaoCodigo)) {
      divisaoCodigo = user.divisao_id || user.divisaoCodigo
    }
    if (!divisaoNome && (user.divisao_nome || user.divisaoNome)) {
      divisaoNome = user.divisao_nome || user.divisaoNome
    }

    userData.value.divisaoCodigo = divisaoCodigo || ''
    userData.value.divisaoNome = divisaoNome || ''
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

      // Tenta também preencher divisão a partir do localStorage em caso de erro na API
      userData.value.divisaoCodigo = user.divisao_id || user.divisaoCodigo || ''
      userData.value.divisaoNome = user.divisao_nome || user.divisaoNome || ''
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
  // Nota: Nome e email não podem ser editados, apenas a foto
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

    // Nome e email não podem ser editados pelo próprio usuário
    // Apenas a foto pode ser alterada

    // Atualiza dados no localStorage
    const user = authService.getCurrentUser()
    if (user) {
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
    errorMessage.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'Erro ao salvar alterações. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

function cancelarAlteracao() {
  userData.value = JSON.parse(JSON.stringify(originalUserData.value))
  selectedFile.value = null
  isEditing.value = false
  errorMessage.value = ''
  successMessage.value = ''

  // Limpa o input de arquivo
  if (fileInput.value) {
    fileInput.value.value = ''
  }

  // Restaura a foto original se havia uma selecionada
  if (userPhotoUrl.value) {
    userData.value.avatar = getPhotoUrl(userPhotoUrl.value)
  } else {
    userData.value.avatar = placeholderAvatar
  }
}

// Função para obter label do nível de acesso
function getAccessLevelLabel(nivel) {
  const labels = {
    admin: 'Administrador',
    exata: 'Exata',
    sabesp: 'SABESP',
    usuario: 'Usuário',
  }
  return labels[nivel?.toLowerCase()] || nivel || 'Usuário'
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
    errorMessage.value =
      error.response?.data?.message ||
      error.response?.data?.error ||
      'Erro ao remover foto. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

function showPasswordFields() {
  isChangingPassword.value = true
  oldPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
  errorMessage.value = ''
  successMessage.value = ''
}

function showPasswordFeedbackPopup(type, message) {
  passwordFeedbackType.value = type
  passwordFeedbackMessage.value = message
  showPasswordFeedback.value = true
}

function closePasswordFeedbackPopup() {
  showPasswordFeedback.value = false
}

function setPasswordError(message) {
  errorMessage.value = message
  showPasswordFeedbackPopup('error', message)
}

function setPasswordSuccess(message) {
  successMessage.value = message
  showPasswordFeedbackPopup('success', message)
}

async function saveNewPassword() {
  try {
    // Validações
    if (!oldPassword.value || !oldPassword.value.trim()) {
      setPasswordError('Por favor, informe sua senha atual.')
      return
    }

    if (!newPassword.value || newPassword.value.length < 8) {
      setPasswordError('A nova senha deve ter no mínimo 8 caracteres.')
      return
    }

    if (newPassword.value !== confirmPassword.value) {
      setPasswordError('As senhas não coincidem.')
      return
    }

    if (oldPassword.value === newPassword.value) {
      setPasswordError('A nova senha deve ser diferente da senha atual.')
      return
    }

    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    console.log('Alterando senha...')

    // Chama o endpoint de alteração de senha
    const response = await userApi.changePassword({
      current_password: oldPassword.value,
      password: newPassword.value,
      password_confirmation: confirmPassword.value,
    })

    console.log('Resposta da API:', response.data)

    // Verifica diferentes formatos de resposta
    const isSuccess =
      response.data?.success === true ||
      response.status === 200 ||
      response.status === 201 ||
      (response.data?.message && !response.data?.errors)

    if (isSuccess) {
      const msg = response.data?.message || 'Senha alterada com sucesso!'
      setPasswordSuccess(msg)
      hidePasswordFields()

      // Limpa mensagem após 5 segundos
      setTimeout(() => {
        successMessage.value = ''
      }, 5000)
    } else {
      setPasswordError(response.data?.message || 'Erro ao alterar senha.')
    }
  } catch (error) {
    console.error('Erro completo ao alterar senha:', error)
    console.error('Response data:', error.response?.data)
    console.error('Response status:', error.response?.status)

    // Tratamento específico para diferentes tipos de erro
    if (error.response?.status === 422) {
      // Erro de validação
      const errors = error.response.data?.errors
      if (errors?.current_password) {
        setPasswordError(
          Array.isArray(errors.current_password)
            ? errors.current_password[0]
            : errors.current_password,
        )
      } else if (errors?.password) {
        setPasswordError(Array.isArray(errors.password) ? errors.password[0] : errors.password)
      } else if (errors?.password_confirmation) {
        setPasswordError(
          Array.isArray(errors.password_confirmation)
            ? errors.password_confirmation[0]
            : errors.password_confirmation,
        )
      } else {
        setPasswordError(
          error.response.data?.message || 'Dados inválidos. Verifique as informações fornecidas.',
        )
      }
    } else if (error.response?.status === 401) {
      setPasswordError('Senha atual incorreta. Verifique e tente novamente.')
    } else if (error.response?.status === 400) {
      setPasswordError(
        error.response.data?.message || 'Erro ao alterar senha. Verifique os dados informados.',
      )
    } else if (error.response?.status === 500) {
      setPasswordError('Erro interno do servidor. Tente novamente mais tarde.')
    } else {
      setPasswordError(
        error.response?.data?.message ||
          error.response?.data?.error ||
          'Erro ao alterar senha. Verifique sua conexão e tente novamente.',
      )
    }
  } finally {
    isLoading.value = false
  }
}

function hidePasswordFields() {
  isChangingPassword.value = false
  oldPassword.value = ''
  newPassword.value = ''
  confirmPassword.value = ''
  errorMessage.value = ''
  successMessage.value = ''
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
        <button class="btn-salvar" @click="salvarAlteracoes" :disabled="isLoading">
          {{ isLoading ? 'Salvando...' : 'Salvar' }}
        </button>
        <button class="btn-cancelar" @click="cancelarAlteracao" :disabled="isLoading">
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
        <h2>{{ userName }}</h2>
      </div>

      <div class="detail-item item-criado">
        <h1>Usuário Criado em:</h1>
        <h2>{{ userData.criadoEm || 'Carregando...' }}</h2>
      </div>

      <div class="detail-item item-nivel-acesso">
        <h1>Nível de Acesso:</h1>
        <h2>
          <span class="access-level-badge" :class="userType.toLowerCase()">
            {{ getAccessLevelLabel(userType) }}
          </span>
        </h2>
      </div>

      <div class="detail-item item-divisao">
        <h1>Divisão:</h1>
        <h2 v-if="userData.divisaoCodigo || userData.divisaoNome">
          <span v-if="userData.divisaoCodigo">Código: {{ userData.divisaoCodigo }}</span>
          <span v-if="userData.divisaoCodigo && userData.divisaoNome"> - </span>
          <span v-if="userData.divisaoNome">{{ userData.divisaoNome }}</span>
        </h2>
        <h2 v-else>Não informado</h2>
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
        <h2>{{ userEmail }}</h2>
      </div>

      <div class="detail-item item-senha">
        <h1>Senha:</h1>
        <h2>••••••••</h2>
        <button
          v-if="!isChangingPassword"
          @click="showPasswordFields"
          class="alt_bnt"
          :disabled="isLoading"
        >
          Alterar Senha
        </button>
      </div>
    </div>
  </div>

  <!-- Modal de alteração de senha -->
  <div v-if="isChangingPassword" class="password-modal-overlay" @click.self="hidePasswordFields">
    <div class="password-modal">
      <div class="password-modal-header">
        <h2>Alterar Senha</h2>
        <button class="password-modal-close" @click="hidePasswordFields" :disabled="isLoading">
          &times;
        </button>
      </div>

      <div class="password-modal-body">
        <p class="password-modal-description">
          Informe sua senha atual e defina uma nova senha para acessar o sistema.
        </p>

        <div class="password-change-form">
          <input
            type="password"
            v-model="oldPassword"
            class="edit-password"
            placeholder="Senha atual"
            :disabled="isLoading"
            autocomplete="current-password"
          />
          <input
            type="password"
            v-model="newPassword"
            class="edit-password"
            placeholder="Nova senha (mín. 8 caracteres)"
            :disabled="isLoading"
            autocomplete="new-password"
          />
          <input
            type="password"
            v-model="confirmPassword"
            class="edit-password"
            placeholder="Confirmar nova senha"
            :disabled="isLoading"
            autocomplete="new-password"
          />
        </div>

        <div class="password-modal-actions">
          <button class="btn-cancelar-senha" @click="hidePasswordFields" :disabled="isLoading">
            Cancelar
          </button>
          <button class="bntsalvar" @click="saveNewPassword" :disabled="isLoading">
            <span v-if="isLoading">Salvando...</span>
            <span v-else>Salvar</span>
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Pop-up de feedback da alteração de senha -->
  <div
    v-if="showPasswordFeedback"
    class="feedback-popup-overlay"
    @click.self="closePasswordFeedbackPopup"
  >
    <div class="feedback-popup-content" :class="passwordFeedbackType" @click.stop>
      <div class="feedback-popup-icon">
        <span v-if="passwordFeedbackType === 'success'">✅</span>
        <span v-else>❌</span>
      </div>
      <div class="feedback-popup-message">
        {{ passwordFeedbackMessage }}
      </div>
      <button class="feedback-popup-close" @click="closePasswordFeedbackPopup">OK</button>
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
  width: 65%;
  min-width: 620px;
  margin: 3% auto;
  height: auto;
  border-radius: 20px;
  border: 1px solid rgba(180, 180, 180, 0.8);
  box-shadow: -3px 3px 2px 2px rgba(197, 197, 197, 0.726);
  background-color: #fafcff;
  overflow: hidden;
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
  padding: 30px 55px;
  display: grid;
  grid-template-columns: minmax(300px, 2fr) minmax(200px, 1fr);
  grid-template-areas:
    'nome         criado'
    'nivel-acesso avatar'
    'divisao      avatar'
    'email        avatar'
    'senha        avatar';
  gap: 2.5rem 3rem;
  align-items: start;
}



.item-nome {
  grid-area: nome;
}
.item-criado {
  grid-area: criado;
}
.item-nivel-acesso {
  grid-area: nivel-acesso;
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

/* Badge de Nível de Acesso */
.access-level-badge {
  display: inline-block;
  padding: 0.4rem 1rem;
  border-radius: 12px;
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  justify-content: center;    
  align-items: center;       
  width: 140px;             
  height: 30px;               
  border-radius: 20px;       
  font-size: 0.75rem;        
  font-weight: 700;          
  text-transform: uppercase;
  letter-spacing: 0.5px;    
  white-space: nowrap;
}

.access-level-badge.admin {
  background-color: #ffebee;
  color: #c62828;
}

.access-level-badge.exata {
  background-color: #fff3e0;
  color: #ef6c00;
}

.access-level-badge.sabesp {
  background-color: #e3f2fd;
  color: #1565c0;
}

.access-level-badge.usuario {
  background-color: #e8f5e8;
  color: #2e7d32;
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
  flex-direction: column;
  gap: 12px;
}

.edit-password {
  width: 100%;
  font-size: 15px;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 4px;
  background-color: #fff;
  color: #333;
  box-shadow: inset 0 1px 2px rgba(0, 0, 0, 0.1);
}

.edit-password:focus {
  outline: none;
  border-color: rgba(19, 44, 13, 0.8);
  box-shadow: 0 0 5px rgba(19, 44, 13, 0.5);
}

.password-actions {
  display: flex;
  gap: 10px;
  margin-top: 10px;
}

/* Modal de alteração de senha */
.password-modal-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1000;
  padding: 20px;
  box-sizing: border-box;
}

.password-modal {
  background: white;
  border-radius: 16px;
  width: 100%;
  max-width: 480px;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  animation: slideIn 0.25s ease-out;
}

.password-modal-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 16px 20px;
  border-bottom: 1px solid #e0e0e0;
}

.password-modal-header h2 {
  margin: 0;
  font-size: 1.4rem;
  color: #132c0d;
}

.password-modal-close {
  background: none;
  border: none;
  font-size: 24px;
  cursor: pointer;
  color: #666;
  width: 32px;
  height: 32px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.password-modal-close:hover:not(:disabled) {
  background-color: #f0f0f0;
  color: #333;
}

.password-modal-body {
  padding: 20px;
}

.password-modal-description {
  font-size: 14px;
  color: #555;
  margin-bottom: 16px;
}

.password-modal-actions {
  display: flex;
  justify-content: flex-end;
  gap: 10px;
  margin-top: 18px;
}

.btn-cancelar-senha {
  padding: 8px 25px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
  border: 1px solid black;
  border-radius: 7px;
  cursor: pointer;
  transition: all 0.3s ease;
  font-size: 14px;
  font-weight: 600;
}

.btn-cancelar-senha:hover:not(:disabled) {
  background-color: rgba(163, 8, 8, 0.8);
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(139, 14, 14, 0.3);
}

.btn-cancelar-senha:disabled {
  opacity: 0.6;
  cursor: not-allowed;
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

/* Pop-up de feedback da alteração de senha */
.feedback-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 1100;
  padding: 20px;
  box-sizing: border-box;
}

.feedback-popup-content {
  background: #fff;
  border-radius: 14px;
  padding: 20px 24px;
  max-width: 380px;
  width: 100%;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 12px;
  text-align: center;
}

.feedback-popup-content.success {
  border-left: 6px solid #2e7d32;
}

.feedback-popup-content.error {
  border-left: 6px solid #c62828;
}

.feedback-popup-icon {
  font-size: 32px;
}

.feedback-popup-message {
  font-size: 15px;
  color: #333;
}

.feedback-popup-close {
  margin-top: 8px;
  padding: 8px 24px;
  border-radius: 8px;
  border: none;
  background-color: rgba(19, 44, 13, 0.9);
  color: #fff;
  cursor: pointer;
  font-weight: 600;
  transition: all 0.2s ease;
}

.feedback-popup-close:hover {
  background-color: #0f2410;
  transform: translateY(-1px);
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
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
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

/* Breakpoint Tablet - Grid System Melhorado */
@media (min-width: 769px) and (max-width: 992px) {
  .info-container {
    width: 90%;
  }
  .user-details {
    grid-template-columns: 1fr 1fr;
    grid-template-areas:
      'nome         criado'
      'nivel-acesso avatar'
      'divisao      avatar'
      'email        email'
      'senha        senha';
    gap: 2rem 2rem;
    padding: 40px 35px;
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
      'nivel-acesso'
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
