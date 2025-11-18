<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'

const router = useRouter()

// Dados do formulário de login
const email = ref('')
const password = ref('')

// Estados da interface do usuário
const isLoading = ref(false) // Controla estado de carregamento
const errorMessage = ref('') // Mensagem de erro exibida ao usuário

// Validação do formulário
const isFormValid = ref(false) // Indica se o formulário está válido para envio

// Valida o formulário em tempo real
const validateForm = () => {
  isFormValid.value = email.value.trim() !== '' && password.value.trim() !== ''
}

// Função principal de login
async function fazerLogin() {
  // Verifica se o formulário está válido antes de prosseguir
  if (!isFormValid.value) return

  // Ativa estado de carregamento e limpa mensagens de erro
  isLoading.value = true
  errorMessage.value = ''

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
    // Exibe mensagem de erro para o usuário
    errorMessage.value = error.message || 'Erro ao fazer login. Tente novamente.'
  } finally {
    // Desativa estado de carregamento independente do resultado
    isLoading.value = false
  }
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
      <button>Enviar E-mail</button>
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
</style>
