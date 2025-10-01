<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import api, { userApi } from '../services/api'
import IconUser from '@/components/icons/IconUser.vue' // Exemplo de caminho
import IconSeta from '@/components/icons/IconSeta.vue'

const router = useRouter()

// Dados do usuário para exibição no header
const userName = ref('')
const userType = ref('')

// Lista de usuários
const users = ref([])

// Estados do modal e formulário
const showUserForm = ref(false)
const editingUser = ref(null)

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

const menuAberto = ref(false)


// Carrega dados do usuário quando o componente é montado
onMounted(async () => {
  const user = authService.getCurrentUser()

  // Se não há usuário logado, redireciona para login
  if (!user) {
    router.push('/')
    return
  }

  // Preenche dados do usuário para exibição
  userName.value = user.name || 'Usuário'
  userType.value = user.nivel_acesso || 'Usuário'

  // Carrega lista de usuários
  await loadUsers()
})

// Carrega lista de usuários
async function loadUsers() {
  isLoadingUsers.value = true
  try {
    const response = await userApi.getUsers()
    console.log('Resposta completa da API:', response)
    console.log('Status da resposta:', response.status)
    console.log('Dados da resposta:', response.data)

    // Verifica se a resposta tem a estrutura esperada
    if (response.data && Array.isArray(response.data)) {
      // Se response.data é um array direto
      users.value = response.data
      console.log('Usuários carregados (array direto):', users.value.length)
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      // Se response.data tem uma propriedade 'data' que é um array
      users.value = response.data.data
      console.log('Usuários carregados (data.data):', users.value.length)
    } else if (response.data && response.data.users && Array.isArray(response.data.users)) {
      // Se response.data tem uma propriedade 'users' que é um array
      users.value = response.data.users
      console.log('Usuários carregados (data.users):', users.value.length)
    } else {
      console.warn('Formato de resposta inesperado:', response.data)
      console.log('Tipo de response.data:', typeof response.data)
      console.log('É array?', Array.isArray(response.data))
      users.value = []
    }
  } catch (error) {
    console.error('Erro ao carregar usuários:', error)
    console.error('Detalhes do erro:', error.response)
    errorMessage.value =
      'Erro ao carregar lista de usuários: ' + (error.message || 'Erro desconhecido')
    users.value = []
  } finally {
    isLoadingUsers.value = false
  }
}

// Funções dos botões de ação
function atualizarPEPS() {
  alert('Funcionalidade de atualização de PEPS será implementada em breve')
}

function atualizarContratos() {
  alert('Funcionalidade de atualização de contratos será implementada em breve')
}

function arquivosMedicao() {
  alert('Funcionalidade de arquivos de medição será implementada em breve')
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

    // Recarrega lista de usuários
    await loadUsers()

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

// Função para excluir usuário
async function deleteUser(user) {
  if (!confirm(`Tem certeza que deseja excluir o usuário ${user.name}?`)) {
    return
  }

  try {
    await userApi.deleteUser(user.id)
    successMessage.value = 'Usuário excluído com sucesso!'
    await loadUsers()
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

// Volta para o dashboard
const goBack = () => {
  router.push('/dashboard')
}

// Função de logout - limpa dados e redireciona para login
const logout = () => {
  authService.logout()
  router.push('/')
}
</script>

<template>
  <div class="admin-panel">
    <header class="admin-header">
      <!-- <div class="header-left">
        <div class="logo-section">
          <img src="/Imagens/E__3_-removebg-preview (1).png" alt="Logo Exata" class="header-logo" />
        </div>
      </div>
      <div class="user-section">
        <div class="user-info-header">
          <span class="user-name">{{ userName }}</span>
          <span class="user-type">{{ userType }}</span>
        </div>
        <button @click="goBack" class="back-btn">Voltar</button>
        <button @click="logout" class="logout-btn">Sair</button>
      </div> -->

      <div class="home_green">
        <div class="dados_principais">
          <img src="/Imagens/IconeExata.png" alt="Icone Exatas" />
          <div class="dados_usuarios">
            <IconUser class="icone-perfil" />

            <div class="info-texto">
              <h1>Guilherme Pereira(Teste){{ userName }}</h1>
              <h2>Administrador(Teste){{ userType }}</h2>
            </div>

            <IconSeta
              class="icone-seta"
              :class="{ 'virado-para-baixo': menuAberto }"
              @click="menuAberto = !menuAberto"
            />
            <div v-if="menuAberto" class="div_info_inv">
              <h1>Meu Perfil</h1>
              <h1>Logout</h1>
            </div>
          </div>
        </div>
      </div>
    </header>

    <main class="admin-content">
      <div class="admin-card">
        <h2>Painel do Administrador</h2>
        <p>Gerencie usuários e funcionalidades do sistema</p>

        <!-- Botões de ação -->
        <div class="admin-buttons">
          <button @click="atualizarPEPS" class="admin-btn">
            <span class="btn-icon">📊</span>
            Atualizar PEPS
          </button>
          <button @click="atualizarContratos" class="admin-btn">
            <span class="btn-icon">📋</span>
            Atualizar Contratos
          </button>
          <button @click="arquivosMedicao" class="admin-btn">
            <span class="btn-icon">📁</span>
            Arquivos de Medição
          </button>
          <button @click="showUserForm = true" class="admin-btn">
            <span class="btn-icon">👤</span>
            Novo Usuário
          </button>
        </div>

        <!-- Tabela de usuários -->
        <div class="users-section">
          <div class="users-header">
            <h3>Usuários Cadastrados</h3>
            <button @click="loadUsers" class="reload-btn" :disabled="isLoadingUsers">
              <span v-if="isLoadingUsers">🔄</span>
              <span v-else>↻</span>
              Recarregar
            </button>
          </div>
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
                    <button @click="deleteUser(user)" class="action-btn delete-btn" title="Excluir">
                      🗑️
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
    </main>

    <!-- Modal de formulário de usuário -->
    <div v-if="showUserForm" class="modal-overlay" @click="closeUserForm">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h3>{{ editingUser ? 'Editar Usuário' : 'Informações do Novo Usuário' }}</h3>
          <button @click="closeUserForm" class="close-btn">&times;</button>
    
        </div>
        <hr class="modal-hr"></hr>

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

<style scoped>
.admin-panel {
  min-height: 100vh;
  background-color: #f5f5f5;
}

/* .admin-header {
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

.back-btn, .logout-btn {
  padding: 0.5rem 1rem;
  border: none;
  border-radius: 4px;
  cursor: pointer;
  transition: background-color 0.3s ease;
  font-size: 0.875rem;
}

.back-btn {
  background-color: #6c757d;
  color: white;
}

.back-btn:hover {
  background-color: #5a6268;
}

.logout-btn {
  background-color: #4caf50;
  color: white;
}

.logout-btn:hover {
  background-color: #45a049;
} */

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

.icone-perfil {
  width: 40px; /* Ajuste o tamanho do ícone de perfil */
  height: 40px;
  color: white;
  margin-right: 10px;
  border: 4px solid white;
  border-radius: 50%;
}

.icone-seta {
  width: 30px; /* Ajuste o tamanho da seta */
  height: 20px;
  cursor: pointer;
  margin-top: 13%;
  margin-left: -25px;
  transition: transform 0.3s ease;
}

.icone-seta.virado-para-baixo {
  transform: rotate(180deg);
}

.dados_usuarios {
  position: relative;
  display: flex;
  align-items: center;
  color: white;
  font-size: 12px;
}

.dados_usuarios h1 {
  font-size: 20px;
  padding-bottom: 5px;
}

.dados_usuarios h2 {
  font-size: 15px;
  font-weight: lighter;
  width: 80%;
}

img {
  width: 250px;
}

.div_info_inv {
 background-color: white;
  position: absolute;   /* Posiciona em relação ao pai (.dados_usuarios) */
  top: 100%;            /* Começa exatamente onde o pai termina (embaixo) */
  right: 0;             /* Alinha à direita do pai */
  margin-top: 8px;      /* Cria um pequeno espaço entre o header e o menu */
  
  width: 250px;         /* Defina uma largura fixa para o menu */
  color: #333;          /* Cor escura para o texto */
  font-size: 16px;
  text-align: center;
  padding: 10px;
    border-radius: 0px 0px 20px 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15); /* Sombra suave */
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

.admin-content {
  padding: 2rem;
  max-width: 1200px;
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
  background-color: #132c0d;
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
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
}

.users-table th {
  background-color: #f8f9fa;
  color: #132c0d;
  padding: 1rem;
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
}

.users-table td {
  padding: 1rem;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: middle;
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
  height: 62vh;
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




/* Responsividade */
@media (max-width: 768px) {
  .admin-header {
    flex-direction: column;
    gap: 1rem;
    padding: 1rem;
  }

  .user-section {
    flex-direction: column;
    gap: 0.5rem;
    width: 100%;
  }

  .back-btn,
  .logout-btn {
    width: 100%;
    max-width: 200px;
  }

  .admin-content {
    padding: 1rem;
  }

  .admin-card {
    padding: 1.5rem;
  }

  .admin-buttons {
    grid-template-columns: 1fr;
  }

  .users-table {
    font-size: 0.875rem;
  }

  .users-table th,
  .users-table td {
    padding: 0.75rem 0.5rem;
  }

  .form-actions {
    flex-direction: column;
  }

  .cancel-btn,
  .submit-btn {
    width: 100%;
  }
}
</style>
