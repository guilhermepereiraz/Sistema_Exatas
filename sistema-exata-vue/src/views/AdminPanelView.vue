<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { userApi, pepsApi, divisaoApi } from '../services/api'
import HeaderNoHR from './Header_no_HR.vue'
import PopupLoadView from './PopupLoadView.vue'
import IconDelete from '@/components/icons/IconDelete.vue'
import IconEdit from '@/components/icons/IconEdit.vue'

const router = useRouter()

// Lista de usuários
const users = ref([])

// Paginação
const currentPage = ref(1)
const totalPages = ref(1)
const totalUsers = ref(0)

// Estados do modal e formulário
const showUserForm = ref(false)
const editingUser = ref(null)

const showDenominacaoPopup = ref(false)
const showCadastroQauntPopup = ref(false)
const showContratosPopup = ref(false)
const showCji3Popup = ref(false)
const showPepsPopup = ref(false)

// Dados do formulário de cadastro
const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  nivel_acesso: '',
  divisao_id: null,
})

// Divisões para dropdown
const divisoes = ref([])
const isLoadingDivisoes = ref(false)
const errorDivisoes = ref('')

// Estados da interface
const isLoading = ref(false)
const isLoadingUsers = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const isDeleteUser = ref(false)
const userToDelete = ref(null)

// Estados do popup de feedback
const showFeedbackPopup = ref(false)
const feedbackMessage = ref('')
const feedbackType = ref('success') // 'success' ou 'error'

// Carrega dados do usuário quando o componente é montado
onMounted(async () => {
  const user = authService.getCurrentUser()

  // Se não há usuário logado, redireciona para login
  if (!user) {
    router.push('/')
    return
  }

  // Carrega lista de usuários
  await loadUsers(1)

  // Carrega divisões para o dropdown
  await loadDivisoes()
})

// Carrega divisões da API
async function loadDivisoes() {
  try {
    isLoadingDivisoes.value = true
    errorDivisoes.value = ''

    const response = await divisaoApi.getDivisoes()

    if (Array.isArray(response.data)) {
      divisoes.value = response.data
    } else if (response.data.data && Array.isArray(response.data.data)) {
      divisoes.value = response.data.data
    } else if (response.data.divisoes && Array.isArray(response.data.divisoes)) {
      divisoes.value = response.data.divisoes
    } else {
      console.warn('Formato de resposta inesperado para divisões:', response.data)
      divisoes.value = []
    }
  } catch (error) {
    console.error('Erro ao carregar divisões:', error)
    errorDivisoes.value = 'Erro ao carregar divisões. Tente novamente.'
    divisoes.value = []
  } finally {
    isLoadingDivisoes.value = false
  }
}

// Carrega lista de usuários
async function loadUsers(page = 1) {
  isLoadingUsers.value = true
  errorMessage.value = ''

  console.log('=== loadUsers INICIADO ===')
  console.log('Página solicitada:', page)

  try {
    const response = await userApi.getUsers(page, 10)

    // Laravel Paginator response structure (UserCollection)
    if (response.data) {
      if (response.data.data && Array.isArray(response.data.data) && response.data.meta) {
        users.value = [...response.data.data]
        const meta = response.data.meta
        currentPage.value = Number(meta.current_page) || Number(page)
        totalPages.value = Number(meta.last_page) || Math.ceil((Number(meta.total) || 0) / 10) || 1
        totalUsers.value = Number(meta.total) || 0

        if (totalPages.value === 1 && totalUsers.value > 10) {
          totalPages.value = Math.ceil(totalUsers.value / 10)
        }
      } else if (Array.isArray(response.data)) {
        users.value = [...response.data]
        totalPages.value = 1
        currentPage.value = 1
        totalUsers.value = response.data.length
      } else if (response.data.users && Array.isArray(response.data.users)) {
        users.value = [...response.data.users]
        currentPage.value = Number(response.data.current_page) || Number(page)
        totalPages.value = Number(response.data.last_page) || 1
        totalUsers.value = Number(response.data.total) || response.data.users.length
      } else {
        users.value = []
        totalPages.value = 1
        currentPage.value = 1
        totalUsers.value = 0
      }
    } else {
      users.value = []
      totalPages.value = 1
      currentPage.value = 1
      totalUsers.value = 0
    }
  } catch (error) {
    console.error('❌ Erro ao carregar usuários:', error)
    errorMessage.value =
      'Erro ao carregar lista de usuários: ' +
      (error.response?.data?.message || error.message || 'Erro desconhecido')
    users.value = []
    totalPages.value = 1
    currentPage.value = 1
    totalUsers.value = 0
  } finally {
    isLoadingUsers.value = false
  }
}

// Navega para uma página específica
async function goToPage(page) {
  if (typeof page === 'string' && page !== '...' && isNaN(Number(page))) return
  if (page === '...') return

  const targetPage = Number(page)
  if (isNaN(targetPage)) return

  if (targetPage < 1 || targetPage > totalPages.value) return
  if (targetPage === currentPage.value) return

  await loadUsers(targetPage)

  setTimeout(() => {
    const tableContainer = document.querySelector('.table-container')
    if (tableContainer) {
      tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' })
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }, 100)
}

// Gera array de números de página
function getPageNumbers() {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value

  if (total <= 7) {
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    pages.push(1)
    if (current <= 3) {
      for (let i = 2; i <= 4; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 2) {
      pages.push('...')
      for (let i = total - 3; i <= total; i++) pages.push(i)
    } else {
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) pages.push(i)
      pages.push('...')
      pages.push(total)
    }
  }
  return pages
}

// Funções dos botões de ação
function atualizarCJI3() {
  showPepsPopup.value = true
}
function atualizarContratos() {
  showContratosPopup.value = true
}
function atualizarCadastro() {
  showCadastroQauntPopup.value = true
}
function atualizarDenominacao() {
  showDenominacaoPopup.value = true
}

// Feedback
function showFeedback(message, type = 'success') {
  feedbackMessage.value = message
  feedbackType.value = type
  showFeedbackPopup.value = true
  setTimeout(() => {
    if (showFeedbackPopup.value) {
      closeFeedbackPopup()
    }
  }, 3000)
}

function closeFeedbackPopup() {
  showFeedbackPopup.value = false
  feedbackMessage.value = ''
  feedbackType.value = 'success'
}

async function saveUser() {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    if (formData.value.password) {
      if (formData.value.password.length < 8) {
        errorMessage.value = 'A nova senha precisa ter no mínimo 8 caracteres.'
        isLoading.value = false
        return
      }

      if (!formData.value.password_confirmation) {
        errorMessage.value = 'Por favor, confirme a nova senha.'
        isLoading.value = false
        return
      }

      if (formData.value.password !== formData.value.password_confirmation) {
        errorMessage.value = 'As senhas não coincidem.'
        isLoading.value = false
        return
      }
    }

    const userData = {
      email: formData.value.email.trim(),
      nivel_acesso: formData.value.nivel_acesso,
    }

    if (formData.value.name) {
      userData.name = formData.value.name.trim()
    }

    if (formData.value.password) {
      userData.password = formData.value.password
      userData.password_confirmation = formData.value.password_confirmation
    }

    if (formData.value.divisao_id !== null && formData.value.divisao_id !== '') {
      userData.divisao_id = parseInt(formData.value.divisao_id)
    } else {
      userData.divisao_id = null
    }

    if (editingUser.value) {
      await userApi.updateUser(editingUser.value.id, userData)
      closeUserForm()
      showFeedback('Usuário atualizado com sucesso!', 'success')
    } else {
      await userApi.createUser(userData)
      closeUserForm()
      showFeedback('Usuário cadastrado com sucesso!', 'success')
    }
    await loadUsers(currentPage.value)
  } catch (error) {
    console.error('Erro ao salvar usuário:', error)
    const errorResponse = error.response?.data
    let msg = 'Erro ao salvar usuário.'

    if (errorResponse?.message) {
      msg = errorResponse.message
    } else if (errorResponse?.errors) {
      msg = Object.values(errorResponse.errors).flat()[0]
    }

    errorMessage.value = msg
    showFeedback(msg, 'error')
  } finally {
    isLoading.value = false
    setTimeout(() => {
      errorMessage.value = ''
    }, 3000)
  }
}

function editUser(user) {
  editingUser.value = user
  formData.value = {
    name: user.name,
    email: user.email,
    password: '',
    password_confirmation: '',
    nivel_acesso: user.nivel_acesso,
    divisao_id: user.divisao_id,
  }
  showUserForm.value = true
}

function promptDeleteUser(user) {
  userToDelete.value = user
  isDeleteUser.value = true
}

function closeDeleteModalUser() {
  isDeleteUser.value = false
  userToDelete.value = null
}

async function confirmDeleteUser() {
  if (!userToDelete.value) return
  try {
    isLoading.value = true
    await userApi.deleteUser(userToDelete.value.id)
    closeDeleteModalUser()
    showFeedback('Usuário excluído com sucesso!', 'success')
    await loadUsers(currentPage.value)
  } catch (error) {
    console.error('Erro ao excluir usuário:', error)
    const errorMsg = error.response?.data?.message || 'Erro ao excluir usuário. Tente novamente.'
    closeDeleteModalUser()
    showFeedback(errorMsg, 'error')
  } finally {
    isLoading.value = false
  }
}

function closeUserForm() {
  showUserForm.value = false
  editingUser.value = null
  formData.value = {
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    nivel_acesso: '',
    divisao_id: null,
  }
  errorMessage.value = ''
  successMessage.value = ''
}

// Upload PEPS
async function handlePepsUpload(file) {
  try {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const response = await pepsApi.uploadPeps(file)
    const statistics = response.data.statistics || {}
    const totalImported = statistics.total_imported || 0
    const totalSkipped = statistics.total_skipped || 0
    const totalFailed = statistics.total_failed || 0
    const failures = response.data.failures || []

    let message = `✅ ${response.data.message || 'PEPS processado com sucesso!'}\n\n`
    message += `📥 Importados: ${totalImported}\n`
    message += `⏭️ Ignorados: ${totalSkipped}\n`
    message += `❌ Falhas: ${totalFailed}`

    if (failures.length > 0) {
      message += `\n\n⚠️ Detalhes das falhas:`
      failures.slice(0, 5).forEach((failure) => {
        message += `\n  Linha ${failure.row}: ${failure.errors?.join(', ') || 'Erro desconhecido'}`
      })
      if (failures.length > 5) {
        message += `\n  ... e mais ${failures.length - 5} falha(s)`
      }
    }

    successMessage.value = message
    showPepsPopup.value = false
    setTimeout(() => {
      successMessage.value = ''
    }, 8000)
  } catch (error) {
    console.error('Erro ao fazer upload de PEPS:', error)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || []
      let errorMsg =
        error.response.data.message || 'Erro ao processar arquivo. Algumas linhas contêm erros.'
      if (errors.length > 0) {
        errorMsg += '\n\nDetalhes:'
        errors.slice(0, 5).forEach((err) => {
          errorMsg += `\n  Linha ${err.row}: ${err.errors?.join(', ') || 'Erro desconhecido'}`
        })
        if (errors.length > 5) errorMsg += `\n  ... e mais ${errors.length - 5} erro(s)`
      }
      errorMessage.value = errorMsg
    } else {
      errorMessage.value =
        error.response?.data?.message ||
        error.response?.data?.error ||
        'Erro ao fazer upload do arquivo PEPS.'
    }
  } finally {
    isLoading.value = false
  }
}

// Testes (mantidos simplificados)
async function testApiConnection() {
  try {
    const response = await fetch('http://127.0.0.1:8000/api/v1/users', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    if (!response.ok) throw new Error(`HTTP ${response.status}`)
    alert('Conexão OK!')
  } catch (error) {
    alert(`Erro de conexão: ${error.message}`)
  }
}

async function testUserRegistration() {
  alert('Função de teste de registro simplificada.')
}

function getAccessLevelLabel(nivel) {
  const labels = {
    admin: 'Administrador',
    exata: 'Exata',
    sabesp: 'SABESP',
    usuario: 'Usuário',
  }
  return labels[nivel] || nivel
}

function getUsersRange() {
  const start = (currentPage.value - 1) * 10 + 1
  const end = Math.min(currentPage.value * 10, totalUsers.value)
  return { start, end }
}
</script>

<template>
  <div class="page-root">
    <div class="admin-panel">
      <HeaderNoHR />

      <Transition name="modal-fade">
        <div class="confirmation-backdrop" v-if="isDeleteUser" @click="closeDeleteModalUser">
          <div class="confirmation-window">
            <div class="confirmation-heading">
              <h1>Tem certeza que deseja<br />excluir o usuário?</h1>
            </div>

            <div>
              <span class="target-user-display"
                >({{ userToDelete ? userToDelete.name : 'usuário' }})</span
              >
            </div>

            <div class="confirmation-text">
              <p>O usuário <strong>SERÁ</strong> excluído do banco de dados.</p>
            </div>

            <div class="confirmation-actions">
              <button class="action-button action-cancel" @click="closeDeleteModalUser">Não</button>
              <button class="action-button action-confirm" @click="confirmDeleteUser">Sim</button>
            </div>
          </div>
        </div>
      </Transition>

      <main class="admin-content">
        <div class="admin-card">
          <div class="admin-buttons">
            <button @click="atualizarContratos" class="admin-btn">
              <span class="btn-icon"></span>
              Atualizar Contratos
            </button>
            <button @click="atualizarCJI3" class="admin-btn">
              <span class="btn-icon"></span>
              Atualizar CJI3
            </button>
            <button @click="atualizarCadastro" class="admin-btn">
              <span class="btn-icon"></span>
              Atualizar Cadastro
            </button>
            <button @click="atualizarDenominacao" class="admin-btn">
              <span class="btn-icon"></span>
              Arquivos Denominação
            </button>
            <button @click="showUserForm = true" class="admin-btn">
              <span class="btn-icon"></span>
              Novo Usuário
            </button>
          </div>

          <div class="users-section">
            <div class="users-header"></div>
            <div class="table-container">
              <div v-if="isLoadingUsers" class="loading-users">
                <div class="loading-spinner"></div>
                <span>Carregando usuários...</span>
              </div>
              <table v-else class="users-table">
                <thead>
                  <tr>
                    <th style="text-align: center">Nome Completo</th>
                    <th style="text-align: center">Email</th>
                    <th style="text-align: center">Nível de Acesso</th>
                    <th style="text-align: center">Divisão</th>
                    <th style="text-align: center">Editar</th>
                    <th style="text-align: center">Excluir</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-for="user in users" :key="user.id">
                    <td>{{ user.name }}</td>
                    <td>{{ user.email }}</td>
                    <td>
                      <span class="access-level" :class="user.nivel_acesso">
                        {{ getAccessLevelLabel(user.nivel_acesso) }}
                      </span>
                    </td>
                    <td>{{ user.divisao_id || '-' }}</td>
                    <td class="actions">
                      <button @click="editUser(user)" class="action-btn edit-btn" title="Editar">
                        <IconEdit />
                      </button>
                    </td>
                    <td>
                      <button
                        @click="promptDeleteUser(user)"
                        class="action-btn delete-btn"
                        title="Excluir"
                      >
                        <IconDelete />
                      </button>
                    </td>
                  </tr>
                </tbody>
              </table>

              <div v-if="!isLoadingUsers && users.length === 0" class="no-users">
                <div class="no-users-content">
                  <h4>Nenhum usuário cadastrado</h4>
                  <p>Verifique se:</p>
                  <ul>
                    <li>O backend Laravel está rodando em <code>http://127.0.0.1:8000</code></li>
                    <li>O endpoint <code>/api/v1/users</code> está funcionando</li>
                    <li>Existem usuários cadastrados no banco de dados</li>
                    <li>Você está autenticado (token válido)</li>
                  </ul>
                  <div class="test-buttons">
                    <button @click="testApiConnection" class="test-api-btn">
                      🔍 Testar Conexão com API
                    </button>
                    <button @click="testUserRegistration" class="test-api-btn">
                      👤 Testar Cadastro de Usuário
                    </button>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div v-if="!isLoadingUsers && users.length > 0" class="pagination-container">
          <div class="pagination-info">
            <span>
              Mostrando {{ getUsersRange().start }} a {{ getUsersRange().end }} de
              {{ totalUsers }} usuário{{ totalUsers !== 1 ? 's' : '' }}
            </span>
          </div>

          <div class="pagination-controls">
            <button
              @click="goToPage(currentPage - 1)"
              :disabled="currentPage === 1 || isLoadingUsers"
              class="pagination-btn"
              title="Página anterior"
            >
              ← Anterior
            </button>

            <div class="pagination-pages">
              <button
                v-for="page in getPageNumbers()"
                :key="page"
                @click="goToPage(page)"
                :disabled="isLoadingUsers"
                :class="[
                  'pagination-page-btn',
                  { active: page === currentPage, ellipsis: page === '...' },
                ]"
              >
                {{ page }}
              </button>
            </div>

            <button
              @click="goToPage(currentPage + 1)"
              :disabled="currentPage >= totalPages || isLoadingUsers"
              class="pagination-btn"
              :title="`Próxima página (${currentPage + 1} de ${totalPages})`"
            >
              Próxima →
            </button>
          </div>
        </div>
      </main>

      <PopupLoadView
        v-if="showDenominacaoPopup"
        title="Carregar Denominação"
        @close="showDenominacaoPopup = false"
      />

      <PopupLoadView
        v-if="showCadastroQauntPopup"
        title="Carregar Quantitativo Cadastro"
        @close="showCadastroQauntPopup = false"
      />

      <PopupLoadView
        v-if="showContratosPopup"
        title="Carregar Contratos"
        @close="showContratosPopup = false"
      />

      <PopupLoadView v-if="showCji3Popup" title="Carregar CJI3" @close="showCji3Popup = false" />

      <PopupLoadView
        v-if="showPepsPopup"
        title="Carregar CJI3"
        @close="showPepsPopup = false"
        @upload="handlePepsUpload"
      />

      <Transition name="modal-fade">
        <div v-if="showFeedbackPopup" class="modal-overlay" @click="closeFeedbackPopup">
          <div class="feedback-card" :class="feedbackType" @click.stop>
            <div v-if="feedbackType === 'success'" class="feedback-icon-wrapper icon-success">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
              </svg>
            </div>

            <div v-else class="feedback-icon-wrapper icon-error">
              <svg
                xmlns="http://www.w3.org/2000/svg"
                fill="none"
                viewBox="0 0 24 24"
                stroke-width="2"
                stroke="currentColor"
              >
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
              </svg>
            </div>

            <h3 class="feedback-title">
              {{ feedbackType === 'success' ? 'Sucesso!' : 'Atenção' }}
            </h3>

            <p class="feedback-message">
              {{ feedbackMessage }}
            </p>

            <button @click="closeFeedbackPopup" class="feedback-btn" :class="feedbackType">
              Entendido
            </button>
          </div>
        </div>
      </Transition>

      <Transition name="modal-fade">
        <div v-if="showUserForm" class="modal-overlay" @click="closeUserForm">
          <Transition name="slide-down">
            <div v-if="errorMessage" class="floating-error-notification">
              <span class="error-icon">⚠️</span>
              {{ errorMessage }}
              <button type="button" @click="errorMessage = ''" class="close-error">&times;</button>
            </div>
          </Transition>

          <div class="modal-content" @click.stop>
            <div class="modal-header">
              <h3>{{ editingUser ? 'Informações do Usuário' : 'Informações do Novo Usuário' }}</h3>
              <button @click="closeUserForm" class="close-btn">&times;</button>
            </div>
            <hr class="modal-hr" />

            <form @submit.prevent="saveUser" class="user-form">
              <div class="form-group">
                <label for="name">Nome Completo:</label>
                <input
                  type="text"
                  id="name"
                  v-model="formData.name"
                  required
                  :disabled="isLoading"
                  placeholder="Nome do Colaborador"
                />
              </div>

              <div class="form-group">
                <label for="email">E-mail:</label>
                <input
                  type="email"
                  id="email"
                  v-model="formData.email"
                  required
                  :disabled="isLoading"
                  placeholder="E-mail de acesso do colaborador"
                />
              </div>

              <div class="form-group">
                <label for="password">Senha:</label>
                <input
                  type="password"
                  id="password"
                  v-model="formData.password"
                  :required="!editingUser"
                  :disabled="isLoading"
                  autocomplete="new-password"
                  placeholder="Mínimo de 8 caracteres, com letras e números."
                />
              </div>

              <div class="form-group">
                <label for="password_confirmation">Confirmar Senha:</label>
                <input
                  type="password"
                  id="password_confirmation"
                  v-model="formData.password_confirmation"
                  :required="!editingUser || (editingUser && formData.password)"
                  :disabled="isLoading"
                  autocomplete="new-password"
                  placeholder="Confirme a senha"
                />
              </div>
              <div class="form-group">
                <label for="nivel_acesso">Nível de Acesso:</label>
                <select
                  id="nivel_acesso"
                  v-model="formData.nivel_acesso"
                  required
                  :disabled="isLoading"
                  :class="{ 'placeholder-color': !formData.nivel_acesso }"
                >
                  <option value="" disabled selected>Defina a permissão do usuário</option>
                  <option value="admin">Administrador</option>
                  <option value="exata">Exata</option>
                  <option value="sabesp">SABESP</option>
                  <option value="usuario">Usuário</option>
                </select>
              </div>

              <div class="form-group">
                <label for="divisao_id">Divisão (opcional):</label>
                <select
                  id="divisao_id"
                  v-model="formData.divisao_id"
                  :disabled="isLoading || isLoadingDivisoes || divisoes.length === 0"
                >
                  <option :value="null" selected>
                    {{
                      isLoadingDivisoes
                        ? 'Carregando divisões...'
                        : 'Selecione uma divisão (opcional)'
                    }}
                  </option>
                  <option v-for="divisao in divisoes" :key="divisao.id" :value="divisao.id">
                    ID: {{ divisao.id }} - {{ divisao.nome }}
                  </option>
                </select>
                <small v-if="errorDivisoes" class="form-help error-divisoes">
                  {{ errorDivisoes }}
                  <button
                    type="button"
                    class="link-button"
                    @click="loadDivisoes"
                    :disabled="isLoadingDivisoes"
                  >
                    Tentar novamente
                  </button>
                </small>
              </div>

              <div class="form-actions">
                <button type="submit" class="submit-btn" :disabled="isLoading">
                  <span v-if="isLoading">{{ editingUser ? 'Salvando...' : 'Cadastrando...' }}</span>
                  <span v-else>{{ editingUser ? 'Salvar' : 'Cadastrar' }}</span>
                </button>
                <button
                  type="button"
                  @click="closeUserForm"
                  class="cancel-btn"
                  :disabled="isLoading"
                >
                  Cancelar
                </button>
              </div>
            </form>
          </div>
        </div>
      </Transition>
    </div>
  </div>
</template>

<style scoped>
/* Classe para corrigir a transição do Vue e evitar tela branca */
.page-root {
  width: 100%;
  min-height: 100vh;
}

td {
  text-align: center;
}

th {
  text-align: center;
  align-items: center;
}

.admin-content {
  padding: 1rem;
  max-width: 1900px;
  margin: 0 auto;
}

.admin-card {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
}

.admin-card h2 {
  color: #132c0d;
  margin-bottom: 0.5rem;
}

.admin-card p {
  color: #666;
  margin-bottom: 2rem;
}

.admin-buttons {
  display: flex;
  flex-wrap: nowrap;
  gap: 1.25rem;
  padding-top: 10px;
  align-items: stretch;
  overflow-x: auto;
  padding-bottom: 0.5rem;
}

.admin-buttons::-webkit-scrollbar {
  height: 6px;
}

.admin-buttons::-webkit-scrollbar-track {
  background: transparent;
}

.admin-buttons::-webkit-scrollbar-thumb {
  background-color: rgba(19, 44, 13, 0.3);
  border-radius: 3px;
}

.admin-buttons::-webkit-scrollbar-thumb:hover {
  background-color: rgba(19, 44, 13, 0.5);
}

@media (max-width: 768px) {
  .admin-buttons {
    flex-wrap: wrap;
  }
}

.admin-btn {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  padding: 1rem 1.5rem;
  border: none;
  border-radius: 8px;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.3s ease;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  justify-content: center;
  min-height: 60px;
  white-space: nowrap;
  flex-shrink: 0;
  flex: 1 1 auto;
  min-width: 180px;
  width: 100%;
}

.admin-btn:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.btn-icon {
  font-size: 1.2rem;
}

.users-section {
  margin-top: 2rem;
}

.users-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 1rem;
}

.users-section h3 {
  color: #132c0d;
  margin: 0;
  font-size: 1.3rem;
}

.table-container {
  overflow-x: auto;
  overflow-y: auto;
  max-height: 54vh;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  scrollbar-width: thin;
  scrollbar-color: rgba(19, 44, 13, 0.3) transparent;
}

.table-container::-webkit-scrollbar {
  width: 8px;
  height: 8px;
}

.table-container::-webkit-scrollbar-track {
  background: transparent;
}

.table-container::-webkit-scrollbar-thumb {
  background-color: rgba(19, 44, 13, 0.3);
  border-radius: 4px;
}

.table-container::-webkit-scrollbar-thumb:hover {
  background-color: rgba(19, 44, 13, 0.5);
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  table-layout: auto;
}

@media (max-width: 768px) {
  .users-table {
    font-size: 0.8rem;
  }
  .users-table th,
  .users-table td {
    padding: 0.6rem 0.5rem;
  }
}

.users-table th {
  background-color: rgba(19, 44, 13, 0.6);
  color: white;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
}

.users-table td {
  padding: 0.8rem 1rem;
  font-size: 0.875rem;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: middle;
  white-space: nowrap;
}

.users-table tr:hover {
  background-color: #f8f9fa;
}

.access-level {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  width: 140px;
  height: 30px;
  padding: 0;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.5px;
  white-space: nowrap;
}

.access-level.admin {
  background-color: #ffebee;
  color: #c62828;
}
.access-level.exata {
  background-color: #fff3e0;
  color: #ef6c00;
}
.access-level.sabesp {
  background-color: #e3f2fd;
  color: #1565c0;
}
.access-level.usuario {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.actions {
  display: flex;
  gap: 0.5rem;
  justify-content: center;
}

.action-btn {
  background: none;
  border: none;
  cursor: pointer;
  padding: 0.5rem;
  border-radius: 4px;
  transition: background-color 0.3s ease;
  font-size: 1.2rem;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  color: #0f2410;
}

.edit-btn:hover {
  background-color: #e3f2fd;
}
.delete-btn:hover {
  background-color: #ffebee;
}

.pagination-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  padding: 10px;
  border-radius: 8px;
  border-top: 2px solid #e0e0e0;
}

.pagination-info {
  text-align: center;
  color: #666;
  font-size: 0.9rem;
}

.pagination-controls {
  display: flex;
  justify-content: center;
  align-items: center;
  gap: 0.5rem;
  flex-wrap: wrap;
}

.pagination-btn {
  padding: 0.5rem 1rem;
  border: 1px solid #ddd;
  background-color: white;
  color: #333;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  min-width: 100px;
}

.pagination-btn:hover:not(:disabled) {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  border-color: rgba(19, 44, 13, 0.8);
  transform: translateY(-1px);
}

.pagination-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
  background-color: #f5f5f5;
}

.pagination-pages {
  display: flex;
  gap: 0.25rem;
  align-items: center;
  flex-wrap: wrap;
  justify-content: center;
}

.pagination-page-btn {
  min-width: 40px;
  height: 40px;
  padding: 0.5rem;
  border: 1px solid #ddd;
  background-color: white;
  color: #333;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.9rem;
  transition: all 0.2s ease;
  display: flex;
  align-items: center;
  justify-content: center;
}

.pagination-page-btn:hover:not(:disabled):not(.ellipsis) {
  background-color: #f0f0f0;
  border-color: rgba(19, 44, 13, 0.5);
}

.pagination-page-btn.active {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  border-color: rgba(19, 44, 13, 0.8);
  font-weight: 600;
}

.pagination-page-btn.ellipsis {
  cursor: default;
  border: none;
  background-color: transparent;
  min-width: auto;
  padding: 0 0.25rem;
}

.pagination-page-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.no-users {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.no-users-content {
  max-width: 500px;
  margin: 0 auto;
}

.no-users-content h4 {
  color: #132c0d;
  margin-bottom: 1rem;
}

.no-users-content ul {
  text-align: left;
  margin: 1rem 0;
  padding-left: 1.5rem;
}

.no-users-content li {
  margin-bottom: 0.5rem;
}

.no-users-content code {
  background-color: #f0f0f0;
  padding: 0.2rem 0.4rem;
  border-radius: 3px;
  font-family: monospace;
}

.test-buttons {
  display: flex;
  gap: 1rem;
  justify-content: center;
  margin-top: 1rem;
  flex-wrap: wrap;
}

.test-api-btn {
  background-color: #132c0d;
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 1rem;
  transition: background-color 0.3s ease;
  min-width: 200px;
}

.test-api-btn:hover {
  background-color: #0f2410;
}

.loading-users {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 3rem;
  color: #666;
}

.loading-spinner {
  width: 40px;
  height: 40px;
  border: 4px solid #f3f3f3;
  border-top: 4px solid #132c0d;
  border-radius: 50%;
  animation: spin 1s linear infinite;
  margin-bottom: 1rem;
}

@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.modal-overlay {
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
  padding: 1rem;
}

.modal-content {
  position: relative;
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 1000px;
  height: auto;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 1.5rem;
}

.modal-header h3 {
  color: rgba(0, 0, 0, 0.8);
  font-size: 25px;
  margin: 0;
  margin-left: 18px;
}

.modal-hr {
  width: 92%;
  margin: auto;
}

.modal-content::-webkit-scrollbar {
  width: 14px;
}

.modal-content::-webkit-scrollbar-track {
  background: transparent;
  border-right: 4px solid white;
  background-clip: content-box;
}

.modal-content::-webkit-scrollbar-thumb {
  background-color: #cccccc;
  border-radius: 10px;
  border: 4px solid white;
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background-color: #a8a8a8;
}

.floating-error-notification {
  position: absolute;
  top: 90px; /* Ajuste para ficar logo abaixo da linha HR */
  left: 50%;
  transform: translateX(-50%); /* Centraliza perfeitamente */

  background-color: #fef2f2; /* Fundo vermelho bem claro */
  color: #991b1b; /* Texto vermelho escuro */
  border: 1px solid #fca5a5;

  padding: 10px 20px;
  border-radius: 50px; /* Formato de Pílula moderno */

  display: flex;
  align-items: center;
  gap: 10px;

  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Sombra para dar profundidade */
  z-index: 100; /* Fica por cima dos inputs */

  font-size: 0.9rem;
  font-weight: 600;
  white-space: nowrap;
  max-width: 90%;
}

.error-icon {
  font-size: 1.1rem;
}

.close-error {
  background: none;
  border: none;
  color: #991b1b;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 0 0 0 10px;
  opacity: 0.6;
  transition: opacity 0.2s;
}

.close-error:hover {
  opacity: 1;
}

.slide-down-enter-active,
.slide-down-leave-active {
  transition: all 0.3s ease;
}

.slide-down-enter-from,
.slide-down-leave-to {
  opacity: 0;
  transform: translate(-50%, -20px);
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: black;
  padding: 0.1rem 0.6rem 0.2rem 0.6rem;
  border-radius: 30px;
  transition: background-color 0.3s ease;
  margin-right: 15px;
  justify-content: center;
  align-items: center;
  display: inline-flex;
}

.close-btn:hover {
  background-color: #f0f0f0;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease-out;
}

.modal-fade-enter-active .modal-content {
  transition:
    opacity 0.3s ease-out,
    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-leave-active .modal-content {
  transition:
    opacity 0.2s ease-in,
    transform 0.2s ease-in;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-content,
.modal-fade-leave-to .modal-content {
  opacity: 0;
  transform: translateY(20px);
}

.user-form {
  padding: 1.5rem;
  display: grid;
  grid-template-columns: repeat(2, 1fr);
  gap: 1.5rem 2rem;
  align-items: start;
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease-out;
}

.modal-fade-enter-active .modal-content,
.modal-fade-enter-active .confirmation-window {
  transition:
    opacity 0.3s ease-out,
    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-leave-active .modal-content,
.modal-fade-leave-active .confirmation-window {
  transition:
    opacity 0.2s ease-in,
    transform 0.2s ease-in;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .modal-content,
.modal-fade-leave-to .modal-content,
.modal-fade-enter-from .confirmation-window,
.modal-fade-leave-to .confirmation-window {
  opacity: 0;
  transform: translateY(20px);
}

.form-group {
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
}

.form-group label {
  font-weight: 600;
  color: rgba(0, 0, 0, 0.6);
  margin-top: 30px;
  margin-left: 23px;
  font-size: 19px;
}

.error-message,
.success-message,
.form-actions {
  grid-column: span 2;
}

.form-group select {
  color: #333;
}

.form-group select.placeholder-color {
  color: #999;
}

.form-group input::placeholder {
  color: #999;
}

.form-group input,
.form-group select {
  width: 90%;
  padding: 0.75rem;
  border: 1px solid #ddd;
  margin-left: 23px;
  border-radius: 4px;
  font-size: 1rem;
  transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus {
  outline: none;
  border-color: #132c0d;
}

.form-help {
  color: #666;
  font-size: 0.875rem;
  margin-top: 0.25rem;
}

.user-form .error-divisoes {
  display: block;
  margin-top: 4px;
  color: #c62828;
}

.link-button {
  background: none;
  border: none;
  color: #1565c0;
  cursor: pointer;
  font-size: 0.9rem;
  padding: 0;
  margin-left: 4px;
}

.link-button:disabled {
  color: #999;
  cursor: not-allowed;
}

.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 4px;
  border-left: 4px solid #c62828;
  font-size: 14px;
}

.success-message {
  background-color: #e8f5e8;
  color: #2e7d32;
  padding: 12px;
  border-radius: 4px;
  border-left: 4px solid #2e7d32;
  font-size: 14px;
}

.form-actions {
  display: flex;
  gap: 3rem;
  justify-content: center;
  margin-top: 1rem;
}

.cancel-btn {
  width: 45%;
  padding: 13px;
  font-size: 20px;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn {
  width: 45%;
  font-size: 20px;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  cursor: pointer;
  transition: all 0.3s ease;
}

.submit-btn:hover {
  background-color: #2e6931;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.8);
}

.cancel-btn:hover {
  background-color: rgba(209, 44, 44, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 14, 14, 0.8);
}

.confirmation-backdrop {
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
  padding: 1rem;
}

.confirmation-window {
  background-color: white;
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 700px;
  height: 90%;
  max-height: 500px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.5rem;
}

.confirmation-heading h1 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #222;
  margin: 0;
  line-height: 1.3;
  margin-top: 20%;
}

.target-user-display {
  display: inline-block;
  background-color: #eeeeee;
  color: #555;
  padding: 0.25rem 0.75rem;
  border-radius: 16px;
  font-size: 0.9rem;
  font-weight: 500;
}

.confirmation-text p {
  font-size: 1.1rem;
  color: #444;
  margin: 0;
}

.confirmation-text strong {
  color: #000;
  font-weight: 700;
}

.confirmation-actions {
  display: flex;
  gap: 1rem;
  width: 100%;
  margin-top: 0.5rem;
}

.action-button {
  flex-grow: 1;
  padding: 0.8rem;
  font-size: 1.1rem;
  font-weight: 600;
  border: 2px solid;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 10%;
}

.action-cancel {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  font-size: 20px;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(19, 44, 13, 0.8);
}

.action-cancel:hover {
  background-color: #2e6931;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.8);
}

.action-confirm {
  background-color: #b83a3a;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
}

.action-confirm:hover {
  background-color: rgba(209, 44, 44, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 14, 14, 0.8);
}

.feedback-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.6);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000;
  padding: 20px;
  box-sizing: border-box;
  animation: fadeIn 0.2s ease-out;
}

.feedback-card {
  background: white;
  width: 100%;
  max-width: 380px; /* Mais compacto que o modal de edição */
  padding: 32px 24px;
  border-radius: 20px;
  text-align: center;
  box-shadow:
    0 20px 25px -5px rgba(0, 0, 0, 0.1),
    0 10px 10px -5px rgba(0, 0, 0, 0.04);
  display: flex;
  flex-direction: column;
  align-items: center;
  position: relative;
  /* Reutiliza a animação modal-fade automaticamente */
}

/* Círculos dos Ícones */
.feedback-icon-wrapper {
  width: 64px;
  height: 64px;
  border-radius: 50%;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 20px;
}

.feedback-icon-wrapper svg {
  width: 32px;
  height: 32px;
}

/* Cores de Sucesso */
.icon-success {
  background-color: #dcfce7; /* Verde bem claro */
  color: #15803d; /* Verde escuro */
}

/* Cores de Erro */
.icon-error {
  background-color: #fee2e2; /* Vermelho bem claro */
  color: #b91c1c; /* Vermelho escuro */
}

/* Tipografia */
.feedback-title {
  font-size: 1.25rem;
  font-weight: 700;
  color: #111827;
  margin: 0 0 8px 0;
}

.feedback-message {
  font-size: 1rem;
  color: #6b7280; /* Cinza médio */
  line-height: 1.5;
  margin: 0 0 24px 0;
}

/* Botão de Ação */
.feedback-btn {
  width: 100%;
  padding: 12px;
  border-radius: 10px;
  border: none;
  font-size: 1rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  color: white;
}

/* Botão Verde (Sucesso) */
.feedback-btn.success {
  background-color: rgba(19, 44, 13, 0.9); /* Sua cor de marca */
}

.feedback-btn.success:hover {
  background-color: #0f2410;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.2);
}

/* Botão Vermelho (Erro) */
.feedback-btn.error {
  background-color: #dc2626;
}

.feedback-btn.error:hover {
  background-color: #b91c1c;
  transform: translateY(-1px);
  box-shadow: 0 4px 12px rgba(220, 38, 38, 0.2);
}

/* Ajuste para telas pequenas */
@media (max-width: 640px) {
  .feedback-card {
    max-width: 90%;
    padding: 24px 20px;
  }
}

@keyframes fadeIn {
  from {
    opacity: 0;
  }
  to {
    opacity: 1;
  }
}

.feedback-popup-content {
  background: white;
  border-radius: 16px;
  padding: 2rem;
  max-width: 400px;
  width: 100%;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
  text-align: center;
  animation: slideUp 0.3s ease-out;
  border-top: 4px solid;
}

@keyframes slideUp {
  from {
    opacity: 0;
    transform: translateY(20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.feedback-popup-content.success {
  border-top-color: #2e7d32;
}

.feedback-popup-content.error {
  border-top-color: #c62828;
}

.feedback-popup-icon {
  font-size: 4rem;
  margin-bottom: 1rem;
  line-height: 1;
}

.feedback-popup-message {
  font-size: 1.1rem;
  color: #333;
  margin-bottom: 1.5rem;
  line-height: 1.5;
  font-weight: 500;
}

.feedback-popup-close {
  width: 100%;
  padding: 12px 24px;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  border: none;
  border-radius: 8px;
  font-size: 16px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.feedback-popup-close:hover {
  background-color: #1a3a11;
  transform: translateY(-1px);
}

.feedback-popup-content.error .feedback-popup-close {
  background-color: rgba(198, 40, 40, 0.8);
}

.feedback-popup-content.error .feedback-popup-close:hover {
  background-color: rgba(198, 40, 40, 1);
}

@media (max-width: 600px) {
  .feedback-popup-content {
    max-width: 90%;
    padding: 1.5rem;
  }
  .feedback-popup-icon {
    font-size: 3rem;
  }
  .feedback-popup-message {
    font-size: 1rem;
  }
}

@media (max-width: 1024px) {
  .modal-content {
    max-width: 90%;
    height: auto;
    max-height: 90vh;
  }
  .user-form {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem 1.5rem;
    padding: 1rem;
  }
  .form-group label {
    font-size: 16px;
    margin-left: 15px;
  }
  .form-group input,
  .form-group select {
    margin-left: 15px;
    padding: 0.6rem;
  }
  .form-actions {
    gap: 1rem;
  }
  .submit-btn,
  .cancel-btn {
    padding: 12px;
    font-size: 18px;
  }
  .confirmation-window {
    max-width: 85%;
    height: auto;
    max-height: 80vh;
    padding: 2rem;
  }
  .confirmation-heading h1 {
    font-size: 1.8rem;
    margin-top: 10%;
  }
}

@media (max-width: 768px) {
  .modal-content {
    width: 95%;
    max-width: 500px;
    height: auto;
    max-height: 85vh;
    padding: 0.5rem;
  }
  .modal-header {
    padding: 1rem;
  }
  .modal-header h3 {
    font-size: 20px;
    margin-left: 10px;
  }
  .user-form {
    grid-template-columns: 1fr;
    gap: 1rem;
    padding: 1rem;
  }
  .form-group label {
    margin-top: 10px;
    margin-left: 0;
  }
  .form-group input,
  .form-group select {
    width: 100%;
    margin-left: 0;
  }
  .error-message,
  .success-message,
  .form-actions {
    grid-column: 1 / -1;
  }
  .form-actions {
    flex-direction: column;
    gap: 0.75rem;
    margin-top: 1rem;
  }
  .submit-btn,
  .cancel-btn {
    width: 100%;
    font-size: 16px;
    padding: 14px;
  }
  .confirmation-window {
    width: 95%;
    max-width: none;
    height: auto;
    max-height: 90vh;
    padding: 1.5rem;
  }
  .confirmation-heading h1 {
    font-size: 1.5rem;
    margin-top: 5%;
  }
  .confirmation-actions {
    flex-direction: column;
    gap: 0.8rem;
  }
  .action-button {
    width: 100%;
    margin-top: 0.5rem;
    padding: 12px;
  }
}

@media (min-width: 1200px) {
  .user-form {
    grid-template-columns: repeat(2, 1fr);
    gap: 2rem 2.5rem;
  }
}

@media (min-width: 900px) and (max-width: 1199px) {
  .user-form {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.5rem 2rem;
  }
}

@media (min-width: 768px) and (max-width: 899px) {
  .user-form {
    grid-template-columns: repeat(2, 1fr);
    gap: 1.25rem 1.5rem;
  }
}
</style>
