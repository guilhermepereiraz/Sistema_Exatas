<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { cadastroApi } from '../services/api'

const router = useRouter()

const email = ref('')
const password = ref('')

const isLoading = ref(false)
const errorMessage = ref('')

const showCadastroModal = ref(false)
const nomeSolicitacao = ref('')
const motivoSolicitacao = ref('')
const isLoadingSolicitacao = ref(false)
const successMessage = ref('')
const errorSolicitacao = ref('')
const toastTimer = ref(null)
const toastType = ref('error')

const isFormValid = ref(false) //

const validateForm = () => {
  isFormValid.value = email.value.trim() !== '' && password.value.trim() !== ''
}

function isEmailNotRegistered(error) {
  const status = error.response?.status
  const message = error.response?.data?.message || error.message || ''
  const errorMessage = message.toLowerCase()

  return (
    status === 404 ||
    errorMessage.includes('não encontrado') ||
    errorMessage.includes('não cadastrado') ||
    errorMessage.includes('email não existe')
  )
}

function exibirToast(mensagem, tipo = 'error') {
  errorMessage.value = mensagem
  toastType.value = tipo

  if (toastTimer.value) clearTimeout(toastTimer.value)

  toastTimer.value = setTimeout(() => {
    errorMessage.value = ''
  }, 2000)
}

async function fazerLogin() {
  if (!isFormValid.value) return

  isLoading.value = true
  showCadastroModal.value = false

  const startTime = Date.now()

  try {
    const credentials = {
      email: email.value.trim(),
      password: password.value,
    }


    await authService.login(credentials)


    const elapsedTime = Date.now() - startTime
    if (elapsedTime < 3000) {
  
      await new Promise(resolve => setTimeout(resolve, 3000 - elapsedTime))
    }


    exibirToast('Login realizado com sucesso!', 'success')


    await new Promise(resolve => setTimeout(resolve, 2000))

    // Redireciona
    router.push('/home')

  } catch (error) {
    // --- ERRO ---
    
    const elapsedTime = Date.now() - startTime
    if (elapsedTime < 3000) {
      await new Promise(resolve => setTimeout(resolve, 3000 - elapsedTime))
    }

    isLoading.value = false

    console.error('Erro no login:', error)

    if (isEmailNotRegistered(error)) {
      showCadastroModal.value = true
      nomeSolicitacao.value = ''
      motivoSolicitacao.value = ''
      errorMessage.value = '' 
    } else {

      const msg = error.response?.data?.message || error.message || 'Erro ao fazer login.'
      exibirToast(msg, 'error')
    }
  }
}

async function enviarSolicitacaoCadastro() {
  if (!email.value.trim()) {
    errorSolicitacao.value = 'Email é obrigatório'
    return
  }

  isLoadingSolicitacao.value = true
  errorSolicitacao.value = ''
  successMessage.value = ''

  try {
    await cadastroApi.solicitarCadastro(
      email.value.trim(),
      nomeSolicitacao.value.trim() || null,
      motivoSolicitacao.value.trim() || null,
    )

    successMessage.value = 'Solicitação enviada com sucesso! Entraremos em contato em breve.'

    setTimeout(() => {
      showCadastroModal.value = false
      nomeSolicitacao.value = ''
      motivoSolicitacao.value = ''
      successMessage.value = ''
    }, 3000)
  } catch (error) {
    console.error('Erro ao enviar solicitação:', error)
    errorSolicitacao.value =
      error.response?.data?.message || 'Erro ao enviar solicitação. Tente novamente.'
  } finally {
    isLoadingSolicitacao.value = false
  }
}

function fecharModalCadastro() {
  showCadastroModal.value = false
  nomeSolicitacao.value = ''
  motivoSolicitacao.value = ''
  errorSolicitacao.value = ''
  successMessage.value = ''
}

function recuperarSenha() {
  console.log('Recuperação de senha ainda não implementada')
}
</script>

<template>
  <Transition name="toast-slide">
    <div v-if="errorMessage" class="toast-notification" :class="toastType">
      <div class="toast-icon">
        <svg
          v-if="toastType === 'error'"
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
        >
          <circle cx="12" cy="12" r="10"></circle>
          <line x1="12" y1="8" x2="12" y2="12"></line>
          <line x1="12" y1="16" x2="12.01" y2="16"></line>
        </svg>

        <svg
          v-else
          width="24"
          height="24"
          viewBox="0 0 24 24"
          fill="none"
          stroke="currentColor"
          stroke-width="3"
          stroke-linecap="round"
          stroke-linejoin="round"
        >
          <polyline points="20 6 9 17 4 12"></polyline>
        </svg>
      </div>
      <div class="toast-content">
        <span class="toast-title">{{ toastType === 'success' ? 'Sucesso' : 'Atenção' }}</span>
        <span class="toast-message">{{ errorMessage }}</span>
      </div>
      <button class="toast-close" @click="errorMessage = ''">✕</button>
    </div>
  </Transition>

  <div class="container">
    <div class="login-form">
      <Transition name="form-fade" mode="out-in">
        <div v-if="!isLoading" key="form-content" class="form-wrapper">
          <form @submit.prevent="fazerLogin">
            <h1>Acesse sua Conta</h1>

            <div class="campo-flutuante">
              <input
                id="email"
                v-model="email"
                @input="validateForm"
                type="email"
                class="input_class"
                placeholder=" "
                required
                :disabled="isLoading"
              />
              <label for="email" class="label-flutuante">E-mail</label>
            </div>

            <div class="campo-flutuante">
              <input
                id="password"
                v-model="password"
                @input="validateForm"
                type="password"
                class="input_class"
                placeholder=" "
                required
                :disabled="isLoading"
              />
              <label for="password" class="label-flutuante">Senha</label>
            </div>

            <div class="forgot-password">Esqueci minha senha</div>

            <button type="submit" class="buttonentre" :disabled="isLoading || !isFormValid">
              Entrar
            </button>
          </form>
        </div>

        <div v-else key="loading-content" class="loading-container">
          <div class="spinner"></div>
          <p>Autenticando...</p>
        </div>
      </Transition>
    </div>

    <div class="cadastro-form">
      <img src="/Imagens/E__3_-removebg-preview (1).png" alt="Logo da Empresa" />
      <h3>Não possui acesso ainda?</h3>
      <button @click="showCadastroModal = true">Enviar E-mail</button>
    </div>

    <div v-if="showCadastroModal" class="modal-overlay" @click.self="fecharModalCadastro">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Solicitar Cadastro</h2>
          <button @click="fecharModalCadastro" class="close-btn">&times;</button>
        </div>

        <div class="modal-body">
          <p class="modal-description">
            Seu email não está cadastrado no sistema. Preencha os dados abaixo para solicitar
            acesso.
          </p>

          <div v-if="errorSolicitacao" class="error-message-modal">
            {{ errorSolicitacao }}
          </div>
          <div v-if="successMessage" class="success-message-modal">
            {{ successMessage }}
          </div>

          <form @submit.prevent="enviarSolicitacaoCadastro">
            <div class="form-group">
              <label for="email-solicitacao">E-mail *</label>
              <input
                type="email"
                id="email-solicitacao"
                v-model="email"
                required
                :disabled="isLoadingSolicitacao"
                placeholder="seu@email.com"
              />
            </div>

            <div class="form-group">
              <label for="nome-solicitacao">Nome Completo</label>
              <input
                type="text"
                id="nome-solicitacao"
                v-model="nomeSolicitacao"
                :disabled="isLoadingSolicitacao"
                placeholder="Seu nome completo (opcional)"
              />
            </div>

            <div class="form-group">
              <label for="motivo-solicitacao">Motivo da Solicitação</label>
              <textarea
                id="motivo-solicitacao"
                v-model="motivoSolicitacao"
                :disabled="isLoadingSolicitacao"
                placeholder="Descreva o motivo da sua solicitação de acesso (opcional)"
                rows="4"
              ></textarea>
            </div>

            <div class="modal-actions">
              <button
                type="button"
                @click="fecharModalCadastro"
                class="btn-cancelar"
                :disabled="isLoadingSolicitacao"
              >
                Cancelar
              </button>
              <button
                type="submit"
                class="btn-enviar"
                :disabled="isLoadingSolicitacao || !email.trim()"
              >
                <span v-if="isLoadingSolicitacao">Enviando...</span>
                <span v-else>Enviar Solicitação</span>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.container {
  display: flex;
  width: 100vw;
  height: 100vh;
  background-color: #fff;
}

.login-form {
  width: 40%;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px;
  box-sizing: border-box;
}

.login-form h1 {
  margin-bottom: 40px;
  font-size: 2em;
  font-weight: 300;
  color: #333;
}

.login-form form {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  max-width: 350px;
}

.loading-container {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  min-height: 300px;
  color: #132c0d;
}

.loading-container p {
  margin-top: 20px;
  font-weight: 600;
  font-size: 1.1rem;
  letter-spacing: 1px;
}

.spinner {
  width: 50px;
  height: 50px;
  border: 4px solid rgba(19, 44, 13, 0.2);
  border-top: 4px solid #132c0d;
  border-radius: 50%;
  animation: spin 1s linear infinite;
}

/* Animação de rotação */
@keyframes spin {
  0% {
    transform: rotate(0deg);
  }
  100% {
    transform: rotate(360deg);
  }
}

.form-fade-enter-active,
.form-fade-leave-active {
  transition:
    opacity 0.4s ease,
    transform 0.4s ease;
}

.form-fade-enter-from,
.form-fade-leave-to {
  opacity: 0;
  transform: translateY(10px);
}

.form-fade-enter-to,
.form-fade-leave-from {
  opacity: 1;
  transform: translateY(0);
}

.toast-notification {
  position: fixed;
  top: 30px;
  left: 50%;
  transform: translateX(-50%);
  z-index: 9999;
  display: flex;
  align-items: center;
  gap: 15px;
  background-color: white;
  padding: 16px 24px;
  border-radius: 12px;
  box-shadow: 0 10px 40px rgba(0, 0, 0, 0.2);
  min-width: 320px;
  max-width: 90%;
  border-left: 5px solid #d32f2f;
}


.toast-content {
  display: flex;
  flex-direction: column;
  flex: 1;
}


.toast-message {
  font-size: 0.95rem;
  color: #333;
  font-weight: 500;
}

.toast-close {
  background: none;
  border: none;
  color: #999;
  font-size: 1.2rem;
  cursor: pointer;
  padding: 4px;
  transition: color 0.2s;
}

.toast-close:hover {
  color: #333;
}

.toast-slide-enter-active {
  transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-slide-leave-active {
  transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
}

.toast-slide-enter-from,
.toast-slide-leave-to {
  transform: translate(-50%, -150%);
  opacity: 0;
}

.toast-slide-enter-active {
  transition: all 0.6s cubic-bezier(0.16, 1, 0.3, 1);
}

.toast-slide-leave-active {
  transition: all 0.4s ease-in;
}

.toast-slide-enter-to,
.toast-slide-leave-from {
  transform: translate(-50%, 0);
  opacity: 1;
}

.toast-notification.error {
  border-left: 4px solid #ef4444; /* Vermelho */
}

.toast-notification.error .toast-icon {
  color: #ef4444;
  background-color: rgba(239, 68, 68, 0.1);
}

.toast-notification.error .toast-title {
  color: #ef4444; /* Título vermelho */
}

/* --- ESTILO DE SUCESSO (Novo) --- */
.toast-notification.success {
  border-left: 4px solid #4ade80; /* Verde Neon/Claro para destaque */
}

.toast-notification.success .toast-icon {
  color: #4ade80;
  background-color: rgba(74, 222, 128, 0.1);
}

.toast-notification.success .toast-title {
  color: #4ade80; /* Título verde claro */
}

/* Resto dos estilos (ícone, content, close) mantém igual */
.toast-icon {
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 6px;
  border-radius: 50%;
}

.toast-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  margin-bottom: 2px;
  letter-spacing: 0.5px;
}

.campo-flutuante {
  position: relative;
  width: 100%;
  margin-bottom: 25px;
}

.input_class {
  padding: 15px;
  width: 100%;
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box;
}

.label-flutuante {
  position: absolute;
  pointer-events: none;
  left: 15px;
  top: 15px;
  transition: all 0.2s ease;
  color: #888;
  background-color: white;
  padding: 0 5px;
}

.input_class::placeholder {
  color: transparent;
}

.input_class:focus + .label-flutuante,
.input_class:not(:placeholder-shown) + .label-flutuante {
  top: -8px;
  font-size: 12px;
  color: #555;
}

.input_class:focus {
  outline: none;
  border-color: #132c0dbe;
}

.forgot-password {
  margin-top: -25px;
  margin-left: 140px;
  margin-bottom: 70px;
  color: #132c0d;
  cursor: pointer;
  border-bottom: 1px solid transparent;
  transition: border-bottom-color 0.2s ease-out;
}

.forgot-password:hover {
  border-bottom: 1px solid #132c0d;
}

.buttonentre {
  width: 100%;
  padding: 15px;
  transition:
    background-color 0.4s ease,
    opacity 0.3s ease;
  background-color: #132c0d67;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.1em;
  transition: all 0.3s ease;
  background-color: #132c0d;
}

.buttonentre:hover:not(:disabled) {
  background-color: #1a3a11;
  transform: translateY(-2px);
}


.buttonentre:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.buttonentre.ativo {
  background-color: #132c0d;
}

.buttonentre.ativo:hover:not(:disabled) {
  background-color: #1a3a11;
  transform: translateY(-2px);
}

.cadastro-form {
  width: 100%;
  height: 100%;
  background-color: #132c0d;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  border-top-left-radius: 35px;
  border-bottom-left-radius: 35px;
  box-shadow: 2px 2px 10px 10px rgba(134, 133, 133, 0.658);
  padding: 40px;
  box-sizing: border-box;
}

.cadastro-form img {
  width: 100%;
  max-width: 500px;
  height: auto;
  margin-bottom: 20px;
  margin-top: 200px;
}

.cadastro-form h3 {
  color: white;
  font-weight: 200;
  font-size: 1.5em;
  margin-top: 100px;
}

.cadastro-form button {
  font-weight: bold;
  margin-top: 20px;
  padding: 12px 30px;
  width: auto;
  font-size: 1em;
  border-radius: 5px;
  border: none;
  background-color: #fff;
  color: #132c0d;
  cursor: pointer;
  transition: all 0.3s ease;
}

.cadastro-form button:hover {
  background-color: #f0f0f0;
  transform: translateY(-2px);
}

@media (max-width: 800px) {
  .container {
    flex-direction: column;
    height: auto;
  }

  .login-form,
  .cadastro-form {
    width: 100%;
    height: auto;
  }

  .login-form {
    height: auto;
    min-height: 100vh;
    padding: 40px 20px;
  }

  .cadastro-form {
    padding: 50px 20px;
    border-radius: 0;
    box-shadow: none;
  }

  .cadastro-form img {
    max-width: 200px;
    margin-bottom: 30px;
    margin-top: 50px;
  }

  .cadastro-form h3 {
    font-size: 1.2em;
    text-align: center;
    margin-top: 20px;
  }

  .cadastro-form button {
    width: 80%;
    max-width: 300px;
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
  padding: 20px;
  box-sizing: border-box;
}

.modal-content {
  background: white;
  border-radius: 12px;
  width: 100%;
  max-width: 500px;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  animation: slideIn 0.3s ease-out;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-20px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.modal-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header h2 {
  margin: 0;
  color: #132c0d;
  font-size: 1.5rem;
  font-weight: 600;
}

.close-btn {
  background: none;
  border: none;
  font-size: 28px;
  cursor: pointer;
  color: #666;
  padding: 0;
  width: 32px;
  height: 32px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  transition: all 0.2s ease;
}

.close-btn:hover {
  background-color: #f0f0f0;
  color: #333;
}

.modal-body {
  padding: 20px;
}

.modal-description {
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
  line-height: 1.5;
}

.form-group {
  margin-bottom: 20px;
}

.form-group label {
  display: block;
  margin-bottom: 8px;
  color: #333;
  font-weight: 500;
  font-size: 14px;
}

.form-group input,
.form-group textarea {
  width: 100%;
  padding: 12px;
  border: 1px solid #ccc;
  border-radius: 6px;
  font-size: 14px;
  font-family: inherit;
  box-sizing: border-box;
  transition: border-color 0.2s ease;
}

.form-group input:focus,
.form-group textarea:focus {
  outline: none;
  border-color: #132c0d;
}

.form-group input:disabled,
.form-group textarea:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
}

.form-group textarea {
  resize: vertical;
  min-height: 100px;
}

.error-message-modal {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #c62828;
  font-size: 14px;
}

.success-message-modal {
  background-color: #e8f5e8;
  color: #2e7d32;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #2e7d32;
  font-size: 14px;
}

.modal-actions {
  display: flex;
  gap: 12px;
  justify-content: flex-end;
  margin-top: 24px;
}

.btn-cancelar,
.btn-enviar {
  padding: 12px 24px;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancelar {
  background-color: #f5f5f5;
  color: #333;
}

.btn-cancelar:hover:not(:disabled) {
  background-color: #e0e0e0;
}

.btn-enviar {
  background-color: #132c0d;
  color: white;
}

.btn-enviar:hover:not(:disabled) {
  background-color: #1a3a11;
  transform: translateY(-1px);
}

.btn-cancelar:disabled,
.btn-enviar:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

@media (max-width: 600px) {
  .modal-content {
    max-width: 100%;
    margin: 10px;
  }

  .modal-header {
    padding: 15px;
  }

  .modal-body {
    padding: 15px;
  }

  .modal-actions {
    flex-direction: column;
  }

  .btn-cancelar,
  .btn-enviar {
    width: 100%;
  }
}
</style>
