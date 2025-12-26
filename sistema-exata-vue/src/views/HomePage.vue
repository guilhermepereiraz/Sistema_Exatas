<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { divisaoApi } from '../services/api'
import HeaderPrincipal from './Header_Principal.vue'

const router = useRouter()

// Estados do popup de divisão
const showDivisaoPopup = ref(false)
const divisaoSelecionada = ref(null)
const divisoes = ref([])
const isLoadingDivisoes = ref(false)
const errorMessage = ref('')

// Verifica se o usuário é admin
const isAdmin = computed(() => {
  const user = authService.getCurrentUser()
  return user?.nivel_acesso === 'admin'
})

// Carrega dados do usuário ao montar
onMounted(() => {
  loadDivisoes()
})

// Função para carregar divisões da API
async function loadDivisoes() {
  try {
    isLoadingDivisoes.value = true
    console.log('Carregando divisões da API...')

    const response = await divisaoApi.getDivisoes()
    console.log('Resposta da API de divisões:', response.data)

    // A API pode retornar de diferentes formas:
    // 1. Array direto: [{id: 1, nome: '...'}, ...]
    // 2. Objeto com data: {data: [{id: 1, nome: '...'}, ...]}

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

    console.log('Divisões carregadas:', divisoes.value.length)
  } catch (error) {
    console.error('Erro ao carregar divisões:', error)
    errorMessage.value = 'Erro ao carregar divisões. Tente novamente.'
    divisoes.value = []
  } finally {
    isLoadingDivisoes.value = false
  }
}

function irParaAdmin() {
  router.push({ name: 'admin' })
}

function irParaContrato(divisaoId = null) {
  // Se divisaoId foi fornecido, passa como query parameter
  if (divisaoId) {
    router.push({
      name: 'contrato',
      query: { divisao: divisaoId },
    })
  } else {
    router.push({ name: 'contrato' })
  }
}

async function handleImobilizacaoClick() {
  const user = authService.getCurrentUser()
  const nivelAcesso = user?.nivel_acesso || 'usuario'

  console.log('Nível de acesso do usuário:', nivelAcesso)

  // Se for exata ou admin, mostra popup para selecionar divisão
  if (nivelAcesso === 'exata' || nivelAcesso === 'admin') {
    // --- MUDANÇA AQUI ---
    // Removemos o 'loadDivisoes()'. Usamos os dados que já carregaram no onMounted.
    showDivisaoPopup.value = true
    divisaoSelecionada.value = null
  }
  // Se for sabesp, vai direto sem popup
  else if (nivelAcesso === 'sabesp') {
    irParaContrato()
  }
  // Para outros níveis
  else {
    irParaContrato()
  }
}

function confirmarDivisao() {
  if (divisaoSelecionada.value) {
    showDivisaoPopup.value = false
    irParaContrato(divisaoSelecionada.value)
  }
}

function cancelarDivisao() {
  showDivisaoPopup.value = false
  divisaoSelecionada.value = null
}
</script>

<template>
  <HeaderPrincipal />
  <div class="home">
    <img src="/Imagens/Imagem_admin_painel.png" alt="" class="img_princ" />
  </div>
  <div class="home_white">
    <div class="buttons">
      <button class="bnt_imob" @click="handleImobilizacaoClick">Imobilização</button>
      <button
        class="bnt_admin"
        :class="{ disabled: !isAdmin }"
        @click="irParaAdmin"
        :disabled="!isAdmin"
        :title="!isAdmin ? 'Acesso restrito a administradores' : 'Painel do Administrador'"
      >
        Painel do Administrador
      </button>
    </div>

    <hr class="hr_baixo" />
  </div>

  <!-- Popup de Seleção de Divisão -->
  <div v-if="showDivisaoPopup" class="modal-overlay" @click.self="cancelarDivisao">
    <div class="modal-content-divisao" @click.stop>
      <!-- <div class="modal-header-divisao">
        <h2>Escolha o polo responsável</h2>
        <button @click="cancelarDivisao" class="close-btn-divisao">&times;</button>
      </div> -->

      <div class="modal-body-divisao">
        <div class="titulomodaldiv">
          <h2>Escolha o polo responsável</h2>
          <p class="modal-description-divisao">Selecione a divisão para acessar a imobilização:</p>
        </div>

        <div v-if="isLoadingDivisoes" class="loading-divisoes">
          <div class="loading-spinner-divisao"></div>
          <span>Carregando divisões...</span>
        </div>

        <div v-else-if="errorMessage" class="error-message-divisao">
          {{ errorMessage }}
          <button @click="loadDivisoes" class="btn-recarregar-divisoes">🔄 Tentar Novamente</button>
        </div>

        <div v-else-if="divisoes.length === 0" class="no-divisoes">
          <p>Nenhuma divisão disponível.</p>
          <button @click="loadDivisoes" class="btn-recarregar-divisoes">🔄 Recarregar</button>
        </div>

        <div v-else class="divisao-select-container">
          <select v-model="divisaoSelecionada" class="divisao-select" :disabled="isLoadingDivisoes">
            <option value="" disabled selected>Selecione uma divisão</option>
            <option v-for="divisao in divisoes" :key="divisao.id" :value="divisao.id">
              ID: {{ divisao.id }} - {{ divisao.nome }}
            </option>
          </select>
        </div>

        <div class="modal-actions-divisao">
          <!-- <button
            type="button"
            @click="cancelarDivisao"
            class="btn-cancelar-divisao"
          >
            Cancelar
          </button> -->
          <button
            type="button"
            @click="confirmarDivisao"
            class="btn-confirmar-divisao"
            :disabled="!divisaoSelecionada"
          >
            Proxímo
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.buttons {
  display: flex;
  justify-content: space-around;
  margin-top: 7%;
  margin-bottom: 3%;
}

.img_princ {
  width: 100%;
}

.bnt_imob {
  border: none;
  padding: 15px;
  width: 180px;
  font-size: 19px;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  font-weight: bolder;
  border-radius: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.bnt_admin {
  border: none;
  width: 200px;
  font-size: 19px;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  font-weight: bolder;
  border-radius: 8px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.bnt_admin:hover:not(:disabled) {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.bnt_admin:disabled,
.bnt_admin.disabled {
  background-color: rgba(19, 44, 13, 0.3);
  color: rgba(255, 255, 255, 0.6);
  cursor: not-allowed;
  opacity: 0.6;
}

.bnt_admin:disabled:hover,
.bnt_admin.disabled:hover {
  transform: none;
  box-shadow: none;
  background-color: rgba(19, 44, 13, 0.3);
}

.bnt_imob:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.dados_principais {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5%;
}

.hr_baixo {
  width: 96%;
  margin: 10px auto 0 auto;
  border: 0;
  border-top: 2px solid rgb(0, 0, 0);
  margin: auto;
  margin-top: 2%;
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
  position: absolute; /* Posiciona em relação ao pai (.dados_usuarios) */
  top: 100%; /* Começa exatamente onde o pai termina (embaixo) */
  right: 0; /* Alinha à direita do pai */
  margin-top: 8px; /* Cria um pequeno espaço entre o header e o menu */

  width: 250px; /* Defina uma largura fixa para o menu */
  color: #333; /* Cor escura para o texto */
  font-size: 16px;
  text-align: center;
  padding: 10px;
  border-radius: 0px 0px 20px 20px;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15); /* Sombra suave */
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

@media (max-width: 768px) {
}

/* --- Modal de Seleção de Divisão --- */
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

.modal-content-divisao {
  background: white;
  border-radius: 20px;
  width: 100%;
  max-width: 700px;
  height: 50vh;
  max-height: 90vh;
  overflow-y: auto;
  box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
  /* animation: slideIn 0.3s ease-out; */
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

.modal-header-divisao {
  display: flex;
  align-items: center;
  padding: 20px;
  border-bottom: 1px solid #e0e0e0;
}

.modal-header-divisao h2 {
  margin: 0;
  font-size: 1.5rem;
  font-weight: 600;
  text-align: center;
}

.close-btn-divisao {
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

.close-btn-divisao:hover {
  background-color: #f0f0f0;
  color: #333;
}

.modal-body-divisao {
  padding: 20px;
}

.titulomodaldiv {
  text-align: center;
  margin-bottom: 20px;
}
.titulomodaldiv h2 {
  margin: 10% 0 0 0;
  font-size: 1.8rem;
  font-weight: 600;
}

.modal-description-divisao {
  color: #666;
  margin-bottom: 20px;
  font-size: 14px;
  line-height: 1.5;
}

.loading-divisoes {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  padding: 2rem;
  color: #666;
}

.loading-spinner-divisao {
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

.no-divisoes {
  text-align: center;
  padding: 2rem;
  color: #666;
}

.error-message-divisao {
  background-color: #ffebee;
  color: #c62828;
  padding: 12px;
  border-radius: 6px;
  margin-bottom: 20px;
  border-left: 4px solid #c62828;
  font-size: 14px;
  text-align: center;
}

.btn-recarregar-divisoes {
  margin-top: 12px;
  padding: 8px 16px;
  background-color: #132c0d;
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 14px;
  font-weight: 600;
  transition: all 0.2s ease;
}

.btn-recarregar-divisoes:hover {
  background-color: #1a3a11;
  transform: translateY(-1px);
}

.divisao-select-container {
  margin-bottom: 24px;
}

.divisao-select {
  width: 60%;
  margin: 5% 0 0 0;
  margin-left: 20%;
  padding: 12px;
  border: 2px solid #e0e0e0;
  border-radius: 8px;
  font-size: 16px;
  color: #333;
  background-color: white;
  cursor: pointer;
  transition: all 0.2s ease;
  font-family: inherit;
  appearance: none;
  background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' viewBox='0 0 12 12'%3E%3Cpath fill='%23333' d='M6 9L1 4h10z'/%3E%3C/svg%3E");
  background-repeat: no-repeat;
  background-position: right 12px center;
  background-size: 12px;
  padding-right: 40px;
}

.divisao-select:hover:not(:disabled) {
  border-color: rgba(19, 44, 13, 0.5);
}

.divisao-select:focus {
  outline: none;
  border-color: #132c0d;
  box-shadow: 0 0 0 3px rgba(19, 44, 13, 0.1);
}

.divisao-select:disabled {
  background-color: #f5f5f5;
  cursor: not-allowed;
  opacity: 0.6;
}

.divisao-select option {
  padding: 12px;
  font-size: 16px;
}

.modal-actions-divisao {
  display: flex;
  gap: 12px;
  justify-content: center;
  margin-top: 24px;
}

.btn-cancelar-divisao,
.btn-confirmar-divisao {
  margin-top: 10%;
  padding: 15px 24px;
  width: 60%;
  border: none;
  border-radius: 6px;
  font-size: 14px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-cancelar-divisao {
  background-color: #f5f5f5;
  color: #333;
}

.btn-cancelar-divisao:hover {
  background-color: #e0e0e0;
}

.btn-confirmar-divisao {
  background-color: #132c0d;
  color: white;
}

.btn-confirmar-divisao:hover:not(:disabled) {
  background-color: #1a3a11;
  transform: translateY(-1px);
}

.btn-confirmar-divisao:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

/* Responsividade do Modal */
@media (max-width: 600px) {
  .modal-content-divisao {
    max-width: 100%;
    margin: 10px;
  }

  .modal-header-divisao {
    padding: 15px;
  }

  .modal-body-divisao {
    padding: 15px;
  }

  .modal-actions-divisao {
    flex-direction: column;
  }

  .btn-cancelar-divisao,
  .btn-confirmar-divisao {
    width: 100%;
  }
}
</style>
