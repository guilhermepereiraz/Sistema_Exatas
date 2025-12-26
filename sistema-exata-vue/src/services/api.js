import axios from 'axios'

// Cria instância do axios com configuração base
const api = axios.create({
  baseURL: import.meta.env.VITE_API_BASE_URL || 'http://127.0.0.1:8000/api/v1',
  timeout: 10000, // Timeout de 10 segundos para requisições
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
})

// Interceptador de requisição para adicionar token de autenticação
api.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem('auth_token')
    if (token) {
      // Adiciona o token Bearer no header Authorization
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  }
)

// Interceptador de resposta para tratar erros comuns
api.interceptors.response.use(
  (response) => {
    return response
  },
  (error) => {
    if (error.response?.status === 401) {
      // Token expirado ou inválido - limpa dados e redireciona para login
      localStorage.removeItem('auth_token')
      localStorage.removeItem('user_data')
      // window.location.href = '/'
    }
    return Promise.reject(error)
  }
)

// Funções auxiliares para endpoints de usuários
export const userApi = {
  // Listar todos os usuários
  getUsers: (page = 1, limit = 10) => {
    // Adiciona timestamp para evitar cache
    const params = { page, limit, _t: Date.now() }
    console.log('userApi.getUsers chamado com params:', params)
    return api.get('/users', { params })
  },

  // Obter usuário por ID
  getUser: (id) => api.get(`/users/${id}`),

  // Criar novo usuário
  createUser: (userData) => api.post('/register', userData),

  // Atualizar usuário
  updateUser: (id, userData) => api.put(`/users/${id}`, userData),

  // Excluir usuário
  deleteUser: (id) => api.delete(`/users/${id}`),

  // Upload de foto de perfil
  uploadPhoto: (userId, photoFile) => {
    const formData = new FormData()
    formData.append('photo', photoFile)

    return api.post(`/users/${userId}/photo`, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  // Remover foto de perfil
  removePhoto: (userId) => api.delete(`/users/${userId}/photo`),

  // Alterar senha (usuário logado)
  changePassword: (passwordData) => api.post('/change-password', passwordData)
}

export const contratoApi = {
  // Listar contratos com paginação e filtros
  getContratos: (page = 1, limit = 10, filters = {}) => {
    const params = { page, limit, ...filters }
    return api.get('/contratos', { params })
  },

  getContrato: (id) => api.get(`/contratos/${id}`),

  createContrato: (contratoData) => api.post('/contratos', contratoData),

  updateContrato: (id, contratoData) => api.put(`/contratos/${id}`, contratoData),

  deleteContrato: (id) => api.delete(`/contratos/${id}`),

  // Upload de arquivo vinculado a um contrato existente
  uploadContrato: (codigoContrato, file) => {
    const formData = new FormData()
    formData.append('codigo_contrato', codigoContrato)
    formData.append('file', file)

    return api.post('/contratos/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data'
      }
    })
  }
}

export const pepsApi = {
  // Upload de arquivo PEPS (CSV ou Excel)
  uploadPeps: (file) => {
    const formData = new FormData()
    formData.append('file', file)

    return api.post('/p_e_p_s/upload', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
      timeout: 120000, // 120 segundos para arquivos grandes
    })
  },

  // Listar PEPS com paginação e filtros
  getPeps: (page = 1, limit = 15, filters = {}) => {
    const params = { page, limit, ...filters }
    return api.get('/p_e_p_s', { params })
  },

  // Obter PEP por ID
  getPep: (id) => api.get(`/p_e_p_s/${id}`),

  // Criar novo PEP
  createPep: (pepData) => api.post('/p_e_p_s', pepData),

  // Atualizar PEP
  updatePep: (id, pepData) => api.put(`/p_e_p_s/${id}`, pepData),

  // Excluir PEP
  deletePep: (id) => api.delete(`/p_e_p_s/${id}`),

  // Buscar PEPs por contrato
  getPepsByContrato: (codigoContrato, page = 1, limit = 20) =>
    api.get(`/peps/contrato/${codigoContrato}`, { params: { page, limit } }),

  // Buscar PEPs sem contrato
  getPepsSemContrato: (page = 1, limit = 15) =>
    api.get('/peps/sem-contrato', { params: { page, limit } }),

  // Estatísticas de PEPs
  getEstatisticas: () => api.get('/peps/estatisticas')
}

// API para solicitações de cadastro
export const cadastroApi = {
  // Solicitar cadastro (quando email não está cadastrado)
  solicitarCadastro: (email, nome = null, motivo = null) => {
    return api.post('/solicitar-cadastro', {
      email,
      nome,
      motivo
    })
  }
}

// API para divisões
export const divisaoApi = {
  // Listar todas as divisões
  getDivisoes: () => api.get('/divisaos')
}

// API para reset de senha
export const passwordResetApi = {
  // Solicitar reset de senha (envia email com token)
  forgotPassword: (email) => api.post('/forgot-password', { email }),

  // Verificar se o token é válido
  verifyToken: (email, token) => api.post('/verify-token', { email, token }),

  // Resetar senha
  resetPassword: (email, token, password, password_confirmation) =>
    api.post('/reset-password', { email, token, password, password_confirmation })
}

export const contratoPepApi = {
  getContratoPeps: (page = 1, limit = 10, filters = {}) => {
    const params = { page, limit, ...filters }
    
    // CORREÇÃO: Adicione os hífens para bater com o api.php
    return api.get('/contrato-p-e-p-s', { params }) 
  },
  
  // CORREÇÃO: Mesma coisa aqui no get por ID
  getContratoPep: (id) => api.get(`/contrato-p-e-p-s/${id}`),
}

export default api