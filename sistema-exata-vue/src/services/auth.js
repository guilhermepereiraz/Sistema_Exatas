import api from './api'

// Serviço de autenticação para gerenciar login, logout e dados do usuário
export const authService = {
  // Realiza login do usuário
  async login(credentials) {
    try {
      const response = await api.post('/login', credentials)
      
      // A API retorna access_token como objeto, precisamos extrair o token
      if (response.data.access_token && response.data.access_token.id) {
        // Armazena o ID do token como identificador de autenticação
        localStorage.setItem('auth_token', response.data.access_token.id)
        localStorage.setItem('user_data', JSON.stringify(response.data.user))
        localStorage.setItem('token_type', response.data.token_type || 'Bearer')
      }
      
      return response.data
    } catch (error) {
      console.error('Erro no login:', error)
      throw new Error(error.response?.data?.message || 'Erro ao fazer login')
    }
  },

  // Realiza logout do usuário removendo dados do localStorage
  logout() {
    localStorage.removeItem('auth_token')
    localStorage.removeItem('user_data')
    localStorage.removeItem('token_type')
  },

  // Retorna dados do usuário atual armazenados no localStorage
  getCurrentUser() {
    const userData = localStorage.getItem('user_data')
    return userData ? JSON.parse(userData) : null
  },

  // Verifica se o usuário está autenticado (possui token válido)
  isAuthenticated() {
    return !!localStorage.getItem('auth_token')
  },

  // Solicita recuperação de senha via email
  async requestPasswordRecovery(email) {
    try {
      const response = await api.post('/password-recovery', { email })
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Erro ao solicitar recuperação de senha')
    }
  },

  // Redefine senha do usuário com código de verificação
  async resetPassword(data) {
    try {
      const response = await api.post('/reset-password', data)
      return response.data
    } catch (error) {
      throw new Error(error.response?.data?.message || 'Erro ao redefinir senha')
    }
  }
}