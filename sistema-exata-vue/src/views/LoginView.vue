<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { cadastroApi } from '../services/api'

const router = useRouter()

// Dados do formulário de login
const email = ref('')
const password = ref('')

// Estados da interface do usuário
const isLoading = ref(false) // Controla estado de carregamento
const errorMessage = ref('') // Mensagem de erro exibida ao usuário

// Estados do modal de solicitação de cadastro
const showCadastroModal = ref(false)
const nomeSolicitacao = ref('')
const motivoSolicitacao = ref('')
const isLoadingSolicitacao = ref(false)
const successMessage = ref('')
const errorSolicitacao = ref('')

// Validação do formulário
const isFormValid = ref(false) // Indica se o formulário está válido para envio

// Valida o formulário em tempo real
const validateForm = () => {
  isFormValid.value = email.value.trim() !== '' && password.value.trim() !== ''
}

// Verifica se o erro é de email não cadastrado
function isEmailNotRegistered(error) {
  const status = error.response?.status
  const message = error.response?.data?.message || error.message || ''
  const errorMessage = message.toLowerCase()
  
  // Verifica se é erro 401/404 ou se a mensagem indica email não encontrado
  return (
    status === 401 || 
    status === 404 ||
    errorMessage.includes('não encontrado') ||
    errorMessage.includes('não cadastrado') ||
    errorMessage.includes('email não existe') ||
    errorMessage.includes('credenciais inválidas')
  )
}

// Função principal de login
async function fazerLogin() {
  // Verifica se o formulário está válido antes de prosseguir
  if (!isFormValid.value) return

  // Ativa estado de carregamento e limpa mensagens de erro
  isLoading.value = true
  errorMessage.value = ''
  showCadastroModal.value = false

  try {
    // Prepara credenciais para envio
    const credentials = {
      email: email.value.trim(),
      password: password.value,
    }

    console.log('Tentando fazer login com:', credentials)
    const response = await authService.login(credentials)
    console.log('Resposta do login:', response)

    // Redireciona para o dashboard após login bem-sucedido
    router.push('/home')
  } catch (error) {
    console.error('Erro no login:', error)
    
    // Verifica se o erro é de email não cadastrado
    if (isEmailNotRegistered(error)) {
      // Abre modal para solicitar cadastro
      showCadastroModal.value = true
      nomeSolicitacao.value = '' // Limpa campos do modal
      motivoSolicitacao.value = ''
      errorMessage.value = '' // Limpa mensagem de erro do login
    } else {
      // Exibe mensagem de erro para o usuário
      errorMessage.value = error.response?.data?.message || error.message || 'Erro ao fazer login. Tente novamente.'
    }
  } finally {
    // Desativa estado de carregamento independente do resultado
    isLoading.value = false
  }
}

// Função para enviar solicitação de cadastro
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
      motivoSolicitacao.value.trim() || null
    )

    successMessage.value = 'Solicitação enviada com sucesso! Entraremos em contato em breve.'
    
    // Fecha o modal após 3 segundos
    setTimeout(() => {
      showCadastroModal.value = false
      nomeSolicitacao.value = ''
      motivoSolicitacao.value = ''
      successMessage.value = ''
    }, 3000)

  } catch (error) {
    console.error('Erro ao enviar solicitação:', error)
    errorSolicitacao.value = error.response?.data?.message || 'Erro ao enviar solicitação. Tente novamente.'
  } finally {
    isLoadingSolicitacao.value = false
  }
}

// Função para fechar modal
function fecharModalCadastro() {
  showCadastroModal.value = false
  nomeSolicitacao.value = ''
  motivoSolicitacao.value = ''
  errorSolicitacao.value = ''
  successMessage.value = ''
}

// Função para recuperação de senha (a ser implementada)
function recuperarSenha() {
  // TODO: Implementar modal de recuperação de senha ou redirecionamento
  console.log('Recuperação de senha ainda não implementada')
}
</script>

<template>
  <div class="container">
    <div class="login-form">
      <h1>Acesse sua Conta</h1>

      <div class="input-container">
        <form @submit.prevent="fazerLogin">
          <!-- Error message -->
          <div v-if="errorMessage" class="error-message">
            {{ errorMessage }}
          </div>

          <div class="campo-flutuante">
            <input
              type="email"
              id="email"
              class="input_class"
              name="email"
              placeholder=" "
              required
              v-model="email"
              @input="validateForm"
              :disabled="isLoading"
            />
            <label for="email" class="label-flutuante">E-mail</label>
          </div>

          <div class="campo-flutuante">
            <input
              type="password"
              id="password"
              class="input_class"
              name="password"
              placeholder=" "
              required
              v-model="password"
              @input="validateForm"
              :disabled="isLoading"
            />
            <label for="password" class="label-flutuante">Senha</label>
          </div>

          <p @click="recuperarSenha" class="forgot-password">Esqueci minha senha</p>
          <button
            type="button"
            @click="fazerLogin"
            class="buttonentre"
            :class="{ ativo: isFormValid && !isLoading }"
            :disabled="!isFormValid || isLoading"
          >
            <span v-if="isLoading">Entrando...</span>
            <span v-else>Entrar</span>
          </button>
        </form>
      </div>
    </div>

    <div class="cadastro-form">
      <img src="/Imagens/E__3_-removebg-preview (1).png" alt="Logo da Empresa" />
      <h3>Não possui acesso ainda?</h3>
      <button @click="showCadastroModal = true">Enviar E-mail</button>
    </div>

    <!-- Modal de Solicitação de Cadastro -->
    <div v-if="showCadastroModal" class="modal-overlay" @click.self="fecharModalCadastro">
      <div class="modal-content" @click.stop>
        <div class="modal-header">
          <h2>Solicitar Cadastro</h2>
          <button @click="fecharModalCadastro" class="close-btn">&times;</button>
        </div>

        <div class="modal-body">
          <p class="modal-description">
            Seu email não está cadastrado no sistema. Preencha os dados abaixo para solicitar acesso.
          </p>

          <!-- Mensagens de feedback -->
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
/* --- ESTILOS GERAIS E DESKTOP (BASE) --- */

/* Container principal que segura as duas colunas */
.container {
  display: flex;
  width: 100vw;
  height: 100vh;
  background-color: #fff; /* Fundo branco para a parte do formulário */
}

/* --- Coluna da Esquerda (Login) --- */
.login-form {
  width: 40%; /* Aumentei um pouco, 30% era muito apertado */
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 40px; /* Adiciona respiro interno */
  box-sizing: border-box; /* Garante que o padding não aumente a largura */
}

.login-form h1 {
  margin-bottom: 40px;
  font-size: 2em;
  font-weight: 300; /* Um pouco mais leve */
  color: #333;
}

/* O formulário em si */
.login-form form {
  display: flex;
  flex-direction: column;
  align-items: center;
  width: 100%;
  max-width: 350px; /* <<< ESSA É A CHAVE! Limitamos o formulário */
}

/* Mensagem de erro */
.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 20px;
  border-left: 4px solid #c62828;
  font-size: 14px;
  width: 100%; /* Ocupa 100% do form */
  box-sizing: border-box;
}

/* Container do campo flutuante */
.campo-flutuante {
  position: relative;
  width: 100%; /* Ocupa 100% do form */
  margin-bottom: 25px;
}

/* Inputs (E-mail e Senha) */
.input_class {
  padding: 15px;
  width: 100%; /* <<< ALTERADO! Ocupa 100% do .campo-flutuante */
  border: 1px solid #ccc;
  border-radius: 5px;
  box-sizing: border-box; /* Importante */
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
  border-bottom: 1px solid #132c0d; /* Adiciona um efeito de sublinhado ao passar o mouse */
}

/* Botão de Entrar */
.buttonentre {
  width: 100%; /* <<< ALTERADO! Ocupa 100% do form */
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
}

.buttonentre:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.buttonentre.ativo {
  background-color: #132c0d; /* Cor verde mais forte */
}

.buttonentre.ativo:hover:not(:disabled) {
  background-color: #1a3a11; /* Verde um pouco mais escuro */
  transform: translateY(-2px); /* Efeito de subir sutil */
}

/* --- Coluna da Direita (Logo/Marca) --- */
.cadastro-form {
  width: 100%; /* Ocupa os 60% restantes */
  height: 100%;
  background-color: #132c0d;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center; /* Centraliza verticalmente */
  border-top-left-radius: 35px;
  border-bottom-left-radius: 35px; /* Mudei para border-bottom */
  box-shadow: 2px 2px 10px 10px rgba(134, 133, 133, 0.658);
  padding: 40px;
  box-sizing: border-box;
}

.cadastro-form img {
  width: 100%;
  max-width: 500px; /* Limite para a logo não ficar gigante */
  height: auto;
  margin-bottom: 20px; /* Espaço abaixo da logo */
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
  padding: 12px 30px; /* Mais padding lateral */
  width: auto; /* Largura automática */
  font-size: 1em;
  border-radius: 5px;
  border: none;
  background-color: #fff;
  color: #132c0d; /* Texto do botão verde */
  cursor: pointer;
  transition: all 0.3s ease;
}

.cadastro-form button:hover {
  background-color: #f0f0f0;
  transform: translateY(-2px);
}

/*
 * =======================================================
 * A MÁGICA DA RESPONSIVIDADE (MOBILE)
 * =======================================================
 */
@media (max-width: 800px) {
  .container {
    flex-direction: column; /* Empilha as colunas */
    height: auto; /* Altura deixa de ser 100vh */
  }

  /* Na C-Soluções, as duas colunas ocupam 100% da largura */
  .login-form,
  .cadastro-form {
    width: 100%;
    height: auto; /* Altura automática */
  }

  .login-form {
    height: auto;
    min-height: 100vh; /* O form ocupa pelo menos a tela inteira */
    padding: 40px 20px; /* Menos respiro nas laterais */
  }

  .cadastro-form {
    /* A coluna verde vira um "rodapé" ou seção separada */
    padding: 50px 20px;
    border-radius: 0; /* Remove cantos arredondados */
    box-shadow: none; /* Remove sombra */
  }

  .cadastro-form img {
    max-width: 200px; /* Logo menor no mobile */
    margin-bottom: 30px;
    margin-top: 50px;
  }

  .cadastro-form h3 {
    font-size: 1.2em;
    text-align: center;
    margin-top: 20px;
  }

  .cadastro-form button {
    width: 80%; /* Botão mais largo e "tocável" */
    max-width: 300px;
  }
}

/* --- Modal de Solicitação de Cadastro --- */
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

/* Responsividade do Modal */
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
