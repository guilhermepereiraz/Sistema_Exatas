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
      password: password.value
    }
    
    console.log('Tentando fazer login com:', credentials)
    const response = await authService.login(credentials)
    console.log('Resposta do login:', response)
    
    // Redireciona para o dashboard após login bem-sucedido
    router.push('/dashboard')
    
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

      <div></div>

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
            type="submit" 
            class="buttonentre" 
            :class="{ 'ativo': isFormValid && !isLoading }"
            :disabled="!isFormValid || isLoading"
          >
            <span v-if="isLoading">Entrando...</span>
            <span v-else>Entrar</span>
          </button>
        </form>
      </div>
    </div>

    <div class="cadastro-form">
      <h1>Exata - Nomes</h1>
      <img src="/Imagens/E__3_-removebg-preview (1).png" alt="Logo da Empresa" />
      <h3>Não possui acesso ainda?</h3>
      <button>Enviar E-mail</button>
    </div>
  </div>
</template>

<style scoped>
/* Estilo do container principal que segura as duas colunas */
.container {
  display: flex;
  width: 100vw; /* vw = 100% da LARGURA da tela */
  height: 100vh; /* vh = 100% da ALTURA da tela */
}

/* --- AQUI ESTÁ A CORREÇÃO PRINCIPAL --- */
.login-form {
  width: 30%;
  display: flex; /* Ativa o modo flexbox */
  flex-direction: column; /* Empilha os itens um em cima do outro */
  align-items: center; /* Centraliza tudo horizontalmente (no meio) */
  justify-content: center; /* Centraliza tudo verticalmente (opcional, mas bom) */
}

.login-form h1 {
  padding: 0; /* Remove padding antigo para um melhor alinhamento */
  margin-bottom: 50px; /* Adiciona espaço abaixo do título */
  text-align: center;
  font-size: 2em;
  font-weight: 200;
  color: rgba(0, 0, 0, 0.712);
}

/* Estiliza o formulário para organizar os inputs e botão */
.login-form form {
  display: flex;
  flex-direction: column; /* Organiza os inputs um embaixo do outro */
  align-items: center; /* Garante que eles também fiquem centralizados */
}

.input_class {
  padding: 15px;
  width: 300px; /* Use uma unidade fixa como 'px' ou '%' para melhor controle */
  margin-bottom: 15px; /* Espaçamento entre os campos */
  border: 1px solid #ccc;
  border-radius: 5px;
}

/* Error message styling */
.error-message {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 4px;
  margin-bottom: 20px;
  border-left: 4px solid #c62828;
  font-size: 14px;
  text-align: center;
}

/* Estilo para o parágrafo "Esqueci minha senha" */
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

.buttonentre {
  width: 300px; /* Mesma largura dos inputs para um visual consistente */
  padding: 15px;
  transition:
    background-color 0.4s ease,
    transform 0.4s ease,
    width 0.4s ease,
    opacity 0.3s ease;
  background-color: #132c0d67;
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  font-size: 1.1em;
}

.buttonentre:disabled {
  background-color: #ccc;
  cursor: not-allowed;
  opacity: 0.6;
}

.buttonentre.ativo {
  background-color: #4caf50; /* Cor verde de sucesso */
  cursor: pointer; /* Muda o cursor para a "mãozinha" */
}

/* 3. A MÁGICA: O efeito de HOVER que só se aplica quando o botão tem a classe .ativo */
.buttonentre.ativo:hover:not(:disabled) {
  background-color: #45a049; /* Um verde um pouco mais escuro no hover */
  transform: translateY(-4px);
  width: 290px;
}

.campo-flutuante {
  position: relative; /* Essencial para o posicionamento do label */
  margin-bottom: 25px; /* Espaço entre os campos */
}

/* O estilo para o label que vai flutuar */
.label-flutuante {
  position: absolute;
  pointer-events: none; /* Permite clicar através do label no input */
  left: 15px;
  top: 15px; /* Posição inicial, centralizado no campo */
  transition: all 0.2s ease; /* Animação suave */
  color: #888;
  background-color: white; /* Fundo branco para criar o "corte" na borda */
  padding: 0 5px; /* Espaço nas laterais do texto do label */
}

/* Remove o placeholder padrão do input */
.input_class::placeholder {
  color: transparent;
}

/* A MÁGICA: Quando o input está focado OU preenchido... */
.input_class:focus + .label-flutuante,
.input_class:not(:placeholder-shown) + .label-flutuante {
  /* ... o label sobe e diminui! */
  top: -8px; /* Move para cima da borda */
  font-size: 12px;
  color: #555;
}

/* Muda a cor da borda quando o input está focado */
.input_class:focus {
  outline: none; /* Remove o contorno padrão do navegador */
  border-color: #132c0dbe; /* Uma cor que combina com seu botão */
}

.cadastro-form {
  width: 70%; /* Coluna verde ocupará os 65% restantes */
  height: 100%; /* Ocupa 100% da altura do container */
  background-color: #132c0d; /* A cor verde que você queria */
  /* As linhas abaixo centralizam o conteúdo da coluna verde */
  display: flex;
  flex-direction: column;
  align-items: center;
  border-top-left-radius: 25px;
  border-end-start-radius: 25px;
  box-shadow: 2px 2px 3px 10px rgba(192, 192, 192, 0.658);
}

.cadastro-form h1 {
  padding: 0; /* Remove padding antigo para um melhor alinhamento */
  margin-top: 100px; /* Espaço acima do título */
  text-align: center;
  font-size: 4em;
  font-weight: 200;
  color: white;
}

.cadastro-form img {
  width: 500px; /* Ajuste o tamanho da imagem conforme necessário */
  height: auto; /* Mantém a proporção da imagem */
  margin-top: 15%; /* Espaço abaixo da imagem */
}

.cadastro-form h3 {
  color: white;
  font-weight: 200;
  margin-top: 15%;
  font-size: 1.5em;
}

.cadastro-form button {
  font-weight: bolder;
  margin-top: 2%;
  padding: 10px;
  width: 15%;
  font-size: 1em;
  border-radius: 5px;
  border: none;
}

.cadastro-form button:hover {
  cursor: pointer;
}

.cadastro-form button:active {
  border: 2px solid #132c0d; /* Adiciona uma borda verde quando o botão é clicado */
}

/* Para Telas Médias (Tablets) - até 992px de largura */
@media (max-width: 992px) {
  .login-form {
    width: 45%; /* Aumenta um pouco o espaço do formulário */
  }
  .cadastro-form {
    width: 55%; /* Diminui um pouco o espaço da área verde */
  }
  .cadastro-form h1 {
    font-size: 3em; /* Diminui um pouco o título */
  }
  .cadastro-form img {
    width: 350px; /* Diminui a imagem */
  }
}

/* Para Telas Pequenas (Celulares) - até 768px de largura */
@media (max-width: 768px) {
  /* Faz o layout de colunas virar uma pilha vertical */
  .container {
    flex-direction: column;
  }

  /* As duas seções agora ocupam 100% da largura */
  .login-form,
  .cadastro-form {
    width: 100%;
    height: auto; /* A altura se ajusta ao conteúdo */
    min-height: 50vh; /* Garante que ocupem pelo menos metade da tela cada */
    padding: 40px 20px; /* Adiciona espaçamento interno */
    box-sizing: border-box; /* Garante que o padding não estoure a largura */
  }

  /* Remove a sombra da direita no modo celular */
  .cadastro-form {
    box-shadow: none;
  }

  /* Ajusta o título do formulário de login */
  .login-form h1 {
    font-size: 1.8em;
    margin-bottom: 30px;
  }

  /* Faz os inputs e o botão ocuparem uma largura relativa, não fixa */
  .input_class,
  .buttonentre {
    width: 90%;
    max-width: 350px; /* Limita a largura máxima para não ficar gigante */
  }

  /* Centraliza o parágrafo "Esqueci minha senha" */
  .login-form p {
    margin-left: 0;
    text-align: center;
    width: 90%;
    max-width: 350px;
    margin-bottom: 40px;
  }

  /* Ajusta os textos e a imagem da seção verde */
  .cadastro-form h1 {
    font-size: 2.5em;
    margin-top: 20px;
  }

  .cadastro-form img {
    width: 200px;
    margin-top: 10%;
  }

  .cadastro-form h3 {
    font-size: 1.5em;
    margin-top: 15%;
  }

  .cadastro-form button {
    width: 60%;
    max-width: 250px;
  }
}
</style>
