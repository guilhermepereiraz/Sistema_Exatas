
<template>
  <div class="admin-panel">

  <HeaderNoHR />

<div class="confirmation-backdrop" v-if="isDeleteUser">
  <div class="confirmation-window">
    
    <div class="confirmation-heading">
      <h1>Tem certeza que deseja<br>excluir o usuário?</h1>
    </div>

    <div>
       <span class="target-user-display">({{ userToDelete ? userToDelete.name : 'usuário'}})</span>
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

    <main class="admin-content">
      <div class="admin-card">
        <!-- <h2>Painel do Administrador</h2>
        <p>Gerencie usuários e funcionalidades do sistema</p> -->

        <!-- Botões de ação -->
        <div class="admin-buttons">
          <button @click="atualizarPEPS" class="admin-btn">
            <span class="btn-icon"></span>
            Atualizar PEPS
          </button>
          <button @click="atualizarContratos" class="admin-btn">
            <span class="btn-icon"></span>
            Atualizar Contratos
          </button>
          <button @click="atualizarMedicao" class="admin-btn">
            <span class="btn-icon"></span>
            Atualizar Medição
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

        <!-- Tabela de usuários -->
        <div class="users-section">
          <!-- <div class="users-header">
            <h3>Usuários Cadastrados</h3>
            <button @click="loadUsers" class="reload-btn" :disabled="isLoadingUsers">
              <span v-if="isLoadingUsers">🔄</span>
              <span v-else>↻</span>
              Recarregar
            </button>
          </div> -->
          <div class="table-container">
            <div v-if="isLoadingUsers" class="loading-users">
              <div class="loading-spinner"></div>
              <span>Carregando usuários...</span>
            </div>
            <table v-else class="users-table">
              <thead>
                <tr>
                  <th>Nome Completo</th>
                  <th>Email</th>
                  <th>Nível de Acesso</th>
                  <th>Divisão</th>
                  <th>Ações</th>
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
                      ✏️
                    </button>
                    <button @click="promptDeleteUser(user)" class="action-btn delete-btn" title="Excluir">
                      🗑️
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>
            
            <!-- Controles de Paginação -->
            <div v-if="!isLoadingUsers && users.length > 0" class="pagination-container">
              <div class="pagination-info">
                <span>
                  Mostrando {{ getUsersRange().start }} a {{ getUsersRange().end }} 
                  de {{ totalUsers }} usuário{{ totalUsers !== 1 ? 's' : '' }}
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
                    :class="['pagination-page-btn', { 'active': page === currentPage, 'ellipsis': page === '...' }]"
                  >
                    {{ page }}
                  </button>
                </div>
                
                <button 
                  @click="goToPage(currentPage + 1)" 
                  :disabled="currentPage >= totalPages || isLoadingUsers"
                  class="pagination-btn"
                  title="Próxima página"
                >
                  Próxima →
                </button>
              </div>
            </div>
            
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
    </main>

      <PopupLoadView 
      v-if="showDenominacaoPopup" 
      title="Carregar Denominação"
      @close="showDenominacaoPopup = false" 
    />

    <PopupLoadView v-if="showCadastroQauntPopup"
    title="Carregar Quantitativo Cadastro" @close="showCadastroQauntPopup = false"/>

    <PopupLoadView v-if="showContratosPopup"
    title="Carregar Contratos" @close="showContratosPopup = false"/>

    <PopupLoadView v-if="showCji3Popup" 
    title="Carregar CJI3" @close="showCji3Popup = false"/>

    <PopupLoadView 
      v-if="showPepsPopup" 
      title="Carregar PEPS" 
      @close="showPepsPopup = false"
      @upload="handlePepsUpload"
    />

    <!-- Modal de formulário de usuário -->
    <div v-if="showUserForm" class="modal-overlay" @click="closeUserForm">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ editingUser ? 'Informações do Usuário' : 'Informações do Novo Usuário' }}</h3>
          <button @click="closeUserForm" class="close-btn">&times;</button>
    
        </div>
        <hr class="modal-hr" />

        <form @submit.prevent="saveUser" class="user-form">
          <div class="form-group">
            <label for="name">Nome Completo:</label>
            <input type="text" id="name" v-model="formData.name" required :disabled="isLoading" placeholder="Nome do Colaborador" />
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
              placeholder="Mínimo de 8 caracteres, com letras e números."
            />
            <small v-if="editingUser" class="form-help"
              >Deixe em branco para manter a senha atual</small
            >
          </div>
<!-- 
          <div v-if="!editingUser" class="form-group">
            <label for="password_confirmation">Confirmar Senha:</label>
            <input
              type="password"
              id="password_confirmation"
              v-model="formData.password_confirmation"
              :required="!editingUser"
              :disabled="isLoading"
            />
          </div> -->
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
            <label for="divisao_id">Divisão ID (opcional):</label>
            <input
              type="number"
              id="divisao_id"
              v-model="formData.divisao_id"
              :disabled="isLoading"
              placeholder="Código da divisão (se aplicável)"
            />
          </div>


          <!-- Mensagem de erro -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <!-- Mensagem de sucesso -->
          <div v-if="successMessage" class="success-message">
            {{ successMessage }}
          </div>

          <div class="form-actions">
                        <button type="submit" class="submit-btn" :disabled="isLoading">
              <span v-if="isLoading">{{ editingUser ? 'Salvando...' : 'Cadastrando...' }}</span>
              <span v-else>{{ editingUser ? 'Salvar' : 'Cadastrar' }}</span>
            </button>
            <button type="button" @click="closeUserForm" class="cancel-btn" :disabled="isLoading">
              Cancelar
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { userApi, pepsApi } from '../services/api'
import HeaderNoHR from './Header_no_HR.vue'
import PopupLoadView  from './PopupLoadView.vue'

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

const showDenominacaoPopup = ref(false);
const showCadastroQauntPopup = ref(false);
const showContratosPopup = ref(false);
const showCji3Popup = ref(false);
const showPepsPopup = ref(false);

// Dados do formulário de cadastro
const formData = ref({
  name: '',
  email: '',
  password: '',
  password_confirmation: '',
  nivel_acesso: '',
  divisao_id: null,
})

// Estados da interface
const isLoading = ref(false)
const isLoadingUsers = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

const isDeleteUser = ref(false)
const userToDelete = ref(null)



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
})

// Carrega lista de usuários
async function loadUsers(page = 1) {
  isLoadingUsers.value = true
  errorMessage.value = ''
  
  console.log('loadUsers chamado com página:', page)
  
  try {
    const response = await userApi.getUsers(page, 10)
    
    console.log('Resposta da API:', response.data)

    // Laravel Paginator response structure (UserCollection)
    // A resposta vem como: { data: [...], meta: {...}, links: {...} }
    if (response.data) {
      // Verifica se é a estrutura do Laravel Resource Collection
      if (response.data.data && Array.isArray(response.data.data) && response.data.meta) {
        users.value = response.data.data
        currentPage.value = response.data.meta.current_page || page
        totalPages.value = response.data.meta.last_page || 1
        totalUsers.value = response.data.meta.total || 0
        
        console.log('Paginação atualizada:', {
          currentPage: currentPage.value,
          totalPages: totalPages.value,
          totalUsers: totalUsers.value,
          usersCount: users.value.length
        })
      } 
      // Fallback: se data é um array direto (sem paginação)
      else if (Array.isArray(response.data)) {
        users.value = response.data
        totalPages.value = 1
        currentPage.value = 1
        totalUsers.value = response.data.length
        console.log('Resposta sem paginação (array direto)')
      } 
      // Fallback: estrutura alternativa
      else if (response.data.users && Array.isArray(response.data.users)) {
        users.value = response.data.users
        currentPage.value = response.data.current_page || page
        totalPages.value = response.data.last_page || 1
        totalUsers.value = response.data.total || response.data.users.length
        console.log('Estrutura alternativa detectada')
      } 
      else {
        console.warn('Formato de resposta inesperado:', response.data)
        users.value = []
        totalPages.value = 1
        currentPage.value = 1
        totalUsers.value = 0
      }
    } else {
      console.warn('Resposta sem data')
      users.value = []
      totalPages.value = 1
      currentPage.value = 1
      totalUsers.value = 0
    }
  } catch (error) {
    console.error('Erro ao carregar usuários:', error)
    console.error('Detalhes do erro:', {
      message: error.message,
      response: error.response?.data,
      status: error.response?.status
    })
    errorMessage.value =
      'Erro ao carregar lista de usuários: ' + (error.response?.data?.message || error.message || 'Erro desconhecido')
    users.value = []
    totalPages.value = 1
    currentPage.value = 1
    totalUsers.value = 0
  } finally {
    isLoadingUsers.value = false
  }
}

// Navega para uma página específica
function goToPage(page) {
  // Valida se a página é válida (não pode ser string como '...')
  if (typeof page === 'string' || page === '...' || isNaN(page)) {
    return
  }
  
  const targetPage = parseInt(page, 10)
  
  // Valida se a página está dentro do range válido
  if (targetPage < 1 || targetPage > totalPages.value) {
    console.warn('Página fora do range:', { targetPage, totalPages: totalPages.value })
    return
  }
  
  // Evita recarregar a mesma página
  if (targetPage === currentPage.value) {
    return
  }
  
  // Carrega a página
  loadUsers(targetPage)
  
  // Scroll para o topo da tabela após um pequeno delay
  setTimeout(() => {
    const tableContainer = document.querySelector('.table-container')
    if (tableContainer) {
      tableContainer.scrollIntoView({ behavior: 'smooth', block: 'start' })
    } else {
      window.scrollTo({ top: 0, behavior: 'smooth' })
    }
  }, 100)
}

// Gera array de números de página para exibição (com ellipsis)
function getPageNumbers() {
  const pages = []
  const total = totalPages.value
  const current = currentPage.value
  
  if (total <= 7) {
    // Se há 7 ou menos páginas, mostra todas
    for (let i = 1; i <= total; i++) {
      pages.push(i)
    }
  } else {
    // Sempre mostra primeira página
    pages.push(1)
    
    if (current <= 3) {
      // Perto do início
      for (let i = 2; i <= 4; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    } else if (current >= total - 2) {
      // Perto do fim
      pages.push('...')
      for (let i = total - 3; i <= total; i++) {
        pages.push(i)
      }
    } else {
      // No meio
      pages.push('...')
      for (let i = current - 1; i <= current + 1; i++) {
        pages.push(i)
      }
      pages.push('...')
      pages.push(total)
    }
  }
  
  return pages
}

// Funções dos botões de ação
function atualizarPEPS() {
  showPepsPopup.value = true;
}

function atualizarContratos() {
  showContratosPopup.value = true;
}

function atualizarMedicao() {
  showCadastroQauntPopup.value = true;
}

function atualizarDenominacao() {
  showDenominacaoPopup.value = true;
}



// Função para salvar usuário (criar ou editar)
async function saveUser() {
  isLoading.value = true
  errorMessage.value = ''
  successMessage.value = ''

  try {
    // Prepara dados para envio (remove campos vazios)
    const userData = {
      name: formData.value.name.trim(),
      email: formData.value.email.trim(),
      nivel_acesso: formData.value.nivel_acesso,
    }

    // Adiciona senha apenas se foi preenchida
    if (formData.value.password) {
      userData.password = formData.value.password
    }

    // Adiciona confirmação de senha apenas se foi preenchida (apenas para novos usuários)
    if (!editingUser.value && formData.value.password_confirmation) {
      userData.password_confirmation = formData.value.password_confirmation
    }

    // Adiciona divisao_id apenas se foi preenchido
    if (formData.value.divisao_id) {
      userData.divisao_id = parseInt(formData.value.divisao_id)
    }

    let response
    if (editingUser.value) {
      // Editar usuário existente
      response = await userApi.updateUser(editingUser.value.id, userData)
      successMessage.value = 'Usuário atualizado com sucesso!'
    } else {
      // Criar novo usuário
      response = await userApi.createUser(userData)
      successMessage.value = 'Usuário cadastrado com sucesso!'
    }

    console.log('Usuário salvo:', response.data)

    await loadUsers(currentPage.value)

    // Fecha modal após 2 segundos
    setTimeout(() => {
      closeUserForm()
    }, 2000)
  } catch (error) {
    console.error('Erro ao salvar usuário:', error)
    errorMessage.value = error.response?.data?.message || 'Erro ao salvar usuário. Tente novamente.'
  } finally {
    isLoading.value = false
  }
}

// Função para editar usuário
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


// Função para excluir usuário
async function confirmDeleteUser() {
  if (!userToDelete.value) return 
  try {
    
    await userApi.deleteUser(userToDelete.value.id) // Usa o ID salvo
    successMessage.value = 'Usuário excluído com sucesso!'
    closeDeleteModalUser() 
    await loadUsers(currentPage.value)
  } catch (error) {
    console.error('Erro ao excluir usuário:', error)
    errorMessage.value = 'Erro ao excluir usuário. Tente novamente.'
  } 
}

// Função para fechar modal
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

// Função para testar conexão com API
async function testApiConnection() {
  console.log('=== TESTE DE CONEXÃO COM API ===')

  try {
    // Teste 1: Verificar se a API está respondendo
    console.log('1. Testando conectividade básica...')
    const response = await fetch('http://127.0.0.1:8000/api/v1/users', {
      method: 'GET',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })

    console.log('Status da resposta:', response.status)
    console.log('Headers da resposta:', Object.fromEntries(response.headers.entries()))

    if (!response.ok) {
      console.error('Erro HTTP:', response.status, response.statusText)
      const errorText = await response.text()
      console.error('Conteúdo do erro:', errorText)
      alert(`Erro HTTP ${response.status}: ${response.statusText}\n\nDetalhes: ${errorText}`)
      return
    }

    // Teste 2: Verificar conteúdo da resposta
    console.log('2. Analisando conteúdo da resposta...')
    const data = await response.json()
    console.log('Dados recebidos:', data)
    console.log('Tipo dos dados:', typeof data)
    console.log('É array?', Array.isArray(data))

    if (data && typeof data === 'object') {
      console.log('Propriedades do objeto:', Object.keys(data))
    }

    // Teste 3: Verificar autenticação
    console.log('3. Verificando autenticação...')
    const token = localStorage.getItem('auth_token')
    console.log('Token encontrado:', token ? 'Sim' : 'Não')

    if (token) {
      console.log('Testando com token...')
      const authResponse = await fetch('http://127.0.0.1:8000/api/v1/users', {
        method: 'GET',
        headers: {
          Accept: 'application/json',
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      })

      console.log('Status com token:', authResponse.status)
      if (authResponse.ok) {
        const authData = await authResponse.json()
        console.log('Dados com autenticação:', authData)
      }
    }

    alert('Teste concluído! Verifique o console para detalhes.')
  } catch (error) {
    console.error('Erro no teste de conexão:', error)
    alert(`Erro de conexão: ${error.message}\n\nVerifique se o backend Laravel está rodando.`)
  }
}

// Função para testar cadastro de usuário
async function testUserRegistration() {
  console.log('=== TESTE DE CADASTRO DE USUÁRIO ===')

  try {
    // Dados de teste para cadastro
    const testUser = {
      name: 'Usuário Teste',
      email: `teste${Date.now()}@exemplo.com`,
      password: '12345678',
      password_confirmation: '12345678',
      nivel_acesso: 'exata',
      divisao_id: 1,
    }

    console.log('1. Dados do usuário de teste:', testUser)

    // Teste 1: Verificar endpoint de cadastro
    console.log('2. Testando endpoint /api/v1/register...')
    const response = await fetch('http://127.0.0.1:8000/api/v1/register', {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify(testUser),
    })

    console.log('Status da resposta:', response.status)
    console.log('Headers da resposta:', Object.fromEntries(response.headers.entries()))

    const responseData = await response.json()
    console.log('Dados da resposta:', responseData)

    if (response.ok) {
      console.log('✅ Cadastro realizado com sucesso!')
      alert(
        `✅ Usuário cadastrado com sucesso!\n\nEmail: ${testUser.email}\n\nAgora teste listar os usuários.`,
      )

      // Recarrega a lista de usuários após cadastro bem-sucedido
      setTimeout(() => {
        loadUsers()
      }, 1000)
    } else {
      console.error('❌ Erro no cadastro:', responseData)
      alert(
        `❌ Erro no cadastro:\n\nStatus: ${response.status}\nMensagem: ${responseData.message || 'Erro desconhecido'}\n\nDetalhes: ${JSON.stringify(responseData, null, 2)}`,
      )
    }
  } catch (error) {
    console.error('Erro no teste de cadastro:', error)
    alert(
      `❌ Erro de conexão no cadastro: ${error.message}\n\nVerifique se o backend Laravel está rodando.`,
    )
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
  return labels[nivel] || nivel
}

// Função helper para calcular range de usuários exibidos
function getUsersRange() {
  const start = ((currentPage.value - 1) * 10) + 1
  const end = Math.min(currentPage.value * 10, totalUsers.value)
  return { start, end }
}

// Função para upload de PEPS
async function handlePepsUpload(file) {
  try {
    isLoading.value = true
    errorMessage.value = ''
    successMessage.value = ''

    const response = await pepsApi.uploadPeps(file)
    
    // O controller retorna: { message, statistics: { total_imported, total_skipped, total_failed }, failures? }
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
      failures.slice(0, 5).forEach((failure, index) => {
        message += `\n  Linha ${failure.row}: ${failure.errors?.join(', ') || 'Erro desconhecido'}`
      })
      if (failures.length > 5) {
        message += `\n  ... e mais ${failures.length - 5} falha(s)`
      }
      console.warn('Falhas no processamento:', failures)
    }

    successMessage.value = message
    showPepsPopup.value = false

    setTimeout(() => {
      successMessage.value = ''
    }, 8000) // Aumentado para 8 segundos para dar tempo de ler

  } catch (error) {
    console.error('Erro ao fazer upload de PEPS:', error)
    
    // Tratamento específico para erros de validação (422)
    if (error.response?.status === 422) {
      const errors = error.response.data.errors || []
      let errorMsg = error.response.data.message || 'Erro ao processar arquivo. Algumas linhas contêm erros.'
      
      if (errors.length > 0) {
        errorMsg += '\n\nDetalhes:'
        errors.slice(0, 5).forEach((err, index) => {
          errorMsg += `\n  Linha ${err.row}: ${err.errors?.join(', ') || 'Erro desconhecido'}`
        })
        if (errors.length > 5) {
          errorMsg += `\n  ... e mais ${errors.length - 5} erro(s)`
        }
      }
      
      errorMessage.value = errorMsg
    } else {
      errorMessage.value = error.response?.data?.message || 
                          error.response?.data?.error || 
                          'Erro ao fazer upload do arquivo PEPS. Verifique o formato do arquivo e tente novamente.'
    }
  } finally {
    isLoading.value = false
  }
}

// Funções placeholder para outros uploads
function handleDenominacaoUpload(file) {
  console.log('Upload de Denominação:', file)
  // Implementar quando necessário
}

function handleCadastroQuantUpload(file) {
  console.log('Upload de Cadastro Quantitativo:', file)
  // Implementar quando necessário
}

function handleContratosUpload(file) {
  console.log('Upload de Contratos:', file)
  // Implementar quando necessário
}

function handleCji3Upload(file) {
  console.log('Upload de CJI3:', file)
  // Implementar quando necessário
}


</script>

<style scoped>

.admin-content {
  padding: 2rem;
  max-width: 1900px;
  margin: 0 auto;
}

.admin-card {
  background: white;
  padding: 2rem;
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

/* Botões de ação */
.admin-buttons {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
  margin-bottom: 2rem;
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

}

.admin-btn:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.btn-icon {
  font-size: 1.2rem;
}

/* Seção de usuários */
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

.reload-btn {
  background-color: #132c0d;
  color: white;
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  font-size: 0.875rem;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  transition: background-color 0.3s ease;
}

.reload-btn:hover:not(:disabled) {
  background-color: #0f2410;
}

.reload-btn:disabled {
  background-color: #ccc;
  cursor: not-allowed;
}

.table-container {
  overflow-x: auto;
  overflow-y: auto;  /* <-- ADICIONADO: Permite o scroll vertical */
  max-height: 63vh;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
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
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.875rem;
  font-weight: 500;
  text-transform: uppercase;
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
}

.edit-btn:hover {
  background-color: #e3f2fd;
}

.delete-btn:hover {
  background-color: #ffebee;
}

/* Controles de Paginação */
.pagination-container {
  display: flex;
  flex-direction: column;
  gap: 1rem;
  margin-top: 1.5rem;
  padding: 1rem;
  background-color: #f9f9f9;
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

/* Modal */
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
  border-radius: 10px; /* Deixa a barra arredondada */
  border: 4px solid white; 
}

.modal-content::-webkit-scrollbar-thumb:hover {
  background-color: #a8a8a8; /* Cor ao passar o mouse */
}

.close-btn {
  background: none;
  border: none;
  font-size: 1.5rem;
  cursor: pointer;
  color: #666;
  padding: 0.25rem;
  border-radius: 4px;
  transition: background-color 0.3s ease;
  margin-right: 15px;
}

.close-btn:hover {
  background-color: #f0f0f0;
}

.user-form {
  padding: 1.5rem;
  display: grid;
  flex-direction: column;
  grid-template-columns: 1fr 1fr;
  gap: 1.5rem;
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
  /* Esta linha faz o elemento "pular" e ocupar as 2 colunas da grade */
  grid-column: span 2;
}

.form-group select {
  color: #333; /* Uma cor de texto normal, como preto ou cinza escuro */
}

/* ESTA É A MÁGICA: Cor do select quando o placeholder está visível */
.form-group select.placeholder-color {
  color: #999; /* Um cinza mais claro, típico de placeholders */
}

/* Garante que os placeholders dos INPUTS também usem essa cor */
.form-group input::placeholder {
  color: #999; /* O mesmo cinza claro */
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
  border:  1px solid black;
  border-radius: 10px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
    cursor: pointer;
      transition: all 0.3s ease;

}

.submit-btn {
  width: 45%;
  font-size: 20px;
  border:  1px solid black;
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
  background-color: #B83A3A;
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


/* Responsividade adicional para o Modal */

/* Telas Médias (tablets) - a partir de 1024px para baixo */
@media (max-width: 1024px) {
  .modal-content {
    max-width: 90%; /* Aumenta a largura em telas médias */
    height: auto; /* Altura se ajusta ao conteúdo */
    max-height: 90vh; /* Limita a altura máxima */
  }

  .user-form {
    /* Mantém as duas colunas, mas com menos espaçamento */
    gap: 1rem;
    padding: 1rem;
  }

  .form-group label {
    font-size: 16px; /* Reduz um pouco o tamanho da fonte */
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

  .submit-btn, .cancel-btn {
    padding: 12px;
    font-size: 18px;
  }

  .confirmation-window {
    max-width: 85%; /* Aumenta um pouco a largura */
    height: auto; /* Altura automática para não sobrar espaço */
    max-height: 80vh; /* Limite para não estourar a tela */
    padding: 2rem;
  }

  .confirmation-heading h1 {
    font-size: 1.8rem; /* Reduz um pouco o título */
    margin-top: 10%;
  }
}


/* Telas Pequenas (celulares) - a partir de 768px para baixo */
@media (max-width: 768px) {
  .modal-content {
    /* Ocupa quase toda a tela, com uma pequena margem */
    width: 95%;
    max-width: 500px; /* Evita que fique muito largo em celulares maiores */
    height: auto;
    max-height: 85vh; /* Garante espaço para a interface do navegador */
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
    /* Muda para uma única coluna */
    grid-template-columns: 1fr;
    gap: 1rem; /* Espaçamento entre os campos */
    padding: 1rem;
  }

  .form-group label {
    margin-top: 10px; /* Reduz o espaçamento superior */
    margin-left: 0;
  }

  .form-group input,
  .form-group select {
    width: 100%; /* Ocupa a largura total */
    margin-left: 0;
  }
  
  /* Faz com que as mensagens e os botões continuem ocupando a largura total */
  .error-message,
  .success-message,
  .form-actions {
    grid-column: 1 / -1; /* Garante que ocupe toda a grade de 1 coluna */
  }

  .form-actions {
    flex-direction: column; /* Botões um em cima do outro */
    gap: 0.75rem; /* Espaçamento entre os botões */
    margin-top: 1rem;
  }

  .submit-btn,
  .cancel-btn {
    width: 100%; /* Botões ocupam a largura toda */
    font-size: 16px;
    padding: 14px;
  }

  .confirmation-window {
    width: 95%; /* Ocupa quase toda a largura */
    max-width: none; 
    height: auto;
    max-height: 90vh;
    padding: 1.5rem; /* Menos padding interno para sobrar espaço pro conteúdo */
  }

  .confirmation-heading h1 {
    font-size: 1.5rem; /* Título menor para não quebrar linha demais */
    margin-top: 5%;
  }

  .confirmation-actions {
    flex-direction: column; /* Botões um em cima do outro */
    gap: 0.8rem;
  }

  .action-button {
    width: 100%; /* Botões ocupam a largura total */
    margin-top: 0.5rem; /* Menos margem superior */
    padding: 12px; /* Ajuste de área de toque */
  }
}
</style>
