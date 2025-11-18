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
  getUsers: () => api.get('/users'),
  
  // Obter usuário por ID
  getUser: (id) => api.get(`/users/${id}`),
  
  // Criar novo usuário
  createUser: (userData) => api.post('/register', userData),
  
  // Atualizar usuário
  updateUser: (id, userData) => api.put(`/users/${id}`, userData),
  
  // Excluir usuário
  deleteUser: (id) => api.delete(`/users/${id}`)
}

export default api