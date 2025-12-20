<script setup>
import { ref, onMounted, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { authService } from '../services/auth'
// 1. Importa a API de contratos
import { contratoApi } from '../services/api'
import HeaderNoHR from './Header_no_HR.vue'
import PopupLoadView from './PopupLoadView.vue'

const router = useRouter()
const route = useRoute()

// 2. Refs para a lista de contratos e estado de carregamento
const contratos = ref([])
const isLoadingContratos = ref(false)
const errorMessage = ref('')

// Paginação
const currentPage = ref(1)
const totalPages = ref(1)
const totalContratos = ref(0)

// Filtro de divisão
const divisaoFiltro = ref(null)
const nomeDivisao = ref('')

// Upload de contrato
const showUploadPopup = ref(false)
const contratoSelecionado = ref(null)
const uploadFeedback = ref({ type: '', message: '' })
const isUploadingContrato = ref(false)

// 3. Carrega dados quando o componente é montado
onMounted(async () => {
  const user = authService.getCurrentUser()
  if (!user) {
    router.push('/')
    return
  }
  
  // Verifica se há filtro de divisão na URL
  const divisaoId = route.query.divisao
  if (divisaoId) {
    divisaoFiltro.value = Number(divisaoId)
    // TODO: Buscar nome da divisão da API se necessário
    nomeDivisao.value = `Divisão ${divisaoId}`
  }
  
  // 4. Chama a função para carregar contratos
  await loadContratos(1)
})

// Observa mudanças na rota para atualizar filtro
watch(() => route.query.divisao, (newDivisao) => {
  if (newDivisao) {
    divisaoFiltro.value = Number(newDivisao)
    nomeDivisao.value = `Divisão ${newDivisao}`
    // Recarrega contratos com novo filtro
    loadContratos(1)
  } else {
    divisaoFiltro.value = null
    nomeDivisao.value = ''
    // Recarrega contratos sem filtro
    loadContratos(1)
  }
})

// 5. Função para carregar CONTRATOS com paginação e filtros
async function loadContratos(page = 1) {
  isLoadingContratos.value = true
  errorMessage.value = ''
  try {
    // Prepara filtros
    const filters = {}
    if (divisaoFiltro.value) {
      filters.divisao_id = divisaoFiltro.value
    }
    
    console.log('Carregando contratos com filtros:', filters)
    const response = await contratoApi.getContratos(page, 10, filters)

    // Laravel Paginator response structure (ContratoCollection)
    if (response.data) {
      // Verifica se é a estrutura do Laravel Resource Collection
      if (response.data.data && Array.isArray(response.data.data) && response.data.meta) {
        contratos.value = response.data.data
        currentPage.value = response.data.meta.current_page || page
        totalPages.value = response.data.meta.last_page || 1
        totalContratos.value = response.data.meta.total || 0
      } 
      // Fallback: se data é um array direto (sem paginação)
      else if (Array.isArray(response.data)) {
        contratos.value = response.data
        totalPages.value = 1
        currentPage.value = 1
        totalContratos.value = response.data.length
      } 
      // Fallback: estrutura alternativa
      else if (response.data.contratos && Array.isArray(response.data.contratos)) {
        contratos.value = response.data.contratos
        currentPage.value = response.data.current_page || page
        totalPages.value = response.data.last_page || 1
        totalContratos.value = response.data.total || response.data.contratos.length
      } 
      else {
        console.warn('Formato de resposta inesperado para contratos:', response.data)
        contratos.value = []
        totalPages.value = 1
        currentPage.value = 1
        totalContratos.value = 0
      }
    } else {
      contratos.value = []
      totalPages.value = 1
      currentPage.value = 1
      totalContratos.value = 0
    }
  } catch (error) {
    console.error('Erro ao carregar contratos:', error)
    errorMessage.value = 'Erro ao carregar lista de contratos: ' + (error.response?.data?.message || error.message || 'Erro desconhecido')
    contratos.value = []
    totalPages.value = 1
    currentPage.value = 1
    totalContratos.value = 0
  } finally {
    isLoadingContratos.value = false
  }
}

// Navega para uma página específica
function goToPage(page) {
  console.log('goToPage chamado com:', { page, type: typeof page })
  
  // Valida se a página é válida (não pode ser string como '...')
  if (typeof page === 'string' && page !== '...' && isNaN(Number(page))) {
    console.warn('Página inválida (string não numérica):', page)
    return
  }
  
  if (page === '...') {
    console.warn('Tentativa de navegar para ellipsis')
    return
  }
  
  const targetPage = Number(page)
  
  if (isNaN(targetPage)) {
    console.warn('Página não é um número:', page)
    return
  }
  
  console.log('Página convertida:', { targetPage, currentPage: currentPage.value, totalPages: totalPages.value })
  
  // Valida se a página está dentro do range válido
  if (targetPage < 1 || targetPage > totalPages.value) {
    console.warn('Página fora do range:', { targetPage, totalPages: totalPages.value })
    return
  }
  
  // Evita recarregar a mesma página
  if (targetPage === currentPage.value) {
    console.log('Já está na página:', targetPage)
    return
  }
  
  console.log('Carregando página:', targetPage)
  // Carrega a página
  loadContratos(targetPage)
  
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

// Função helper para calcular range de contratos exibidos
function getContratosRange() {
  const start = ((currentPage.value - 1) * 10) + 1
  const end = Math.min(currentPage.value * 10, totalContratos.value)
  return { start, end }
}

// Função para remover filtro de divisão
function removerFiltroDivisao() {
  divisaoFiltro.value = null
  nomeDivisao.value = ''
  // Remove o query parameter da URL
  router.push({ name: 'contrato' })
  // loadContratos será chamado automaticamente pelo watch
}

// Abre popup de upload para um contrato específico
function abrirUploadContrato(contrato) {
  contratoSelecionado.value = contrato
  uploadFeedback.value = { type: '', message: '' }
  showUploadPopup.value = true
}

function fecharUploadPopup() {
  showUploadPopup.value = false
  contratoSelecionado.value = null
}

// Envia arquivo do contrato para o backend
async function handleContratoUpload(file) {
  if (!contratoSelecionado.value) return

  isUploadingContrato.value = true
  uploadFeedback.value = { type: '', message: '' }

  try {
    await contratoApi.uploadContrato(contratoSelecionado.value.codigo_contrato, file)
    uploadFeedback.value = { type: 'success', message: 'Arquivo enviado com sucesso.' }
    fecharUploadPopup()
    // Recarrega para refletir eventual alteração
    await loadContratos(currentPage.value)
  } catch (error) {
    const apiMessage = error.response?.data?.message
    uploadFeedback.value = { 
      type: 'error', 
      message: apiMessage || 'Erro ao enviar arquivo. Tente novamente.' 
    }
  } finally {
    isUploadingContrato.value = false
  }
}

// 6. Função para formatar o Status (baseado na coluna 'concluido' boolean)
function getStatusLabel(concluido) {
  if (concluido === true || concluido === 1) {
    return 'Concluído'
  }
  if (concluido === false || concluido === 0) {
    return 'Ativo'
  }
  return 'Ativo' // Default para false
}

// 7. Função para formatar data (sem ajuste de fuso)
function formatDate(dateString) {
  if (!dateString) return '-'
  try {
    // Pega a data YYYY-MM-DD e converte para DD/MM/YYYY
    const [year, month, day] = dateString.split('-')
    if (day && month && year) {
      // Pega apenas a parte da data, ignorando T...Z
      return `${day.split('T')[0]}/${month}/${year}`
    }
    // Se não for formato YYYY-MM-DD, tenta formatar
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR')
  } catch (e) {
    return dateString // Retorna a string original se falhar
  }
}
</script>

<template>
  <!-- 
    Div principal agora impede o overflow da PÁGINA 
  -->
  <div class="admin-panel">
    <HeaderNoHR />

    <main class="admin-content">
      <h2 class="h2projeto">Projetos</h2>

      <div 
        v-if="uploadFeedback.message" 
        :class="['upload-feedback', uploadFeedback.type]"
      >
        <span>{{ uploadFeedback.message }}</span>
        <button class="upload-feedback-close" @click="uploadFeedback = { type: '', message: '' }" title="Fechar alerta">
          ✕
        </button>
      </div>
      
      <!-- Indicador de Filtro de Divisão -->
      <div v-if="divisaoFiltro" class="filtro-divisao-badge">
        <span class="filtro-label">Filtrando por:</span>
        <span class="filtro-divisao-nome">{{ nomeDivisao }}</span>
        <button @click="removerFiltroDivisao" class="btn-remover-filtro" title="Remover filtro">
          ✕
        </button>
      </div>
      
      <div class="admin-card">
        <div class="users-section">
          <!-- <div class="users-header">
            <button @click="loadContratos" class="reload-btn" :disabled="isLoadingContratos">
              <span v-if="isLoadingContratos">🔄</span>
              <span v-else>↻</span>
              Recarregar
            </button>
          </div> -->
          <div class="table-container">
            <div v-if="isLoadingContratos" class="loading-users">
              <div class="loading-spinner"></div>
              <span>Carregando contratos...</span>
            </div>

            <div v-else-if="errorMessage" class="no-users">
              <div class="no-users-content">
                <h4>{{ errorMessage }}</h4>
                <p>Verifique sua conexão ou tente recarregar.</p>
              </div>
            </div>

            <table v-else class="users-table">
              <thead>
                <tr>
                  <th>Código Contrato</th>
                  <th>Referência</th>
                  <th>Descrição</th>
                  <th>Centro Custo</th>
                  <th>Divisão</th>
                  <th>Administrador</th>
                  <th>Fornecedor</th>
                  <th>Status</th>
                  <th>Data Término</th>
                  <th>Ações</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="contrato in contratos" :key="contrato.codigo_contrato">
                  <td>{{ contrato.codigo_contrato || '-' }}</td>
                  <td>{{ contrato.referencia || '-' }}</td>
                  <td>{{ contrato.descricao || '-' }}</td>
                  <td>{{ contrato.centro_custo || '-' }}</td>
                  <td>{{ contrato.divisao_id || '-' }}</td>
                  <td>{{ contrato.administrador || '-' }}</td>
                  <td>{{ contrato.fornecedor || '-' }}</td>
                  <td>
                    <span class="status" :class="getStatusLabel(contrato.concluido)">
                      {{ getStatusLabel(contrato.concluido) }}
                    </span>
                  </td>
                  <td>{{ formatDate(contrato.data_termino) }}</td>
                  <td class="acoes-col">
                    <button 
                      class="btn-upload-contrato" 
                      @click="abrirUploadContrato(contrato)"
                      :disabled="isUploadingContrato"
                    >
                      Atualizar contrato
                    </button>
                  </td>
                </tr>
              </tbody>
            </table>

            <!-- Controles de Paginação -->
            <div v-if="!isLoadingContratos && contratos.length > 0" class="pagination-container">
              <div class="pagination-info">
                <span>
                  Mostrando {{ getContratosRange().start }} a {{ getContratosRange().end }} 
                  de {{ totalContratos }} contrato{{ totalContratos !== 1 ? 's' : '' }}
                </span>
              </div>
              
              <div class="pagination-controls">
                <button 
                  @click="goToPage(currentPage - 1)" 
                  :disabled="currentPage === 1 || isLoadingContratos"
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
                    :disabled="isLoadingContratos"
                    :class="['pagination-page-btn', { 'active': page === currentPage, 'ellipsis': page === '...' }]"
                  >
                    {{ page }}
                  </button>
                </div>
                
                <button 
                  @click="goToPage(currentPage + 1)" 
                  :disabled="currentPage >= totalPages || isLoadingContratos || totalPages <= 1"
                  class="pagination-btn"
                  title="Próxima página"
                >
                  Próxima →
                </button>
              </div>
            </div>

            <div
              v-if="!isLoadingContratos && contratos.length === 0 && !errorMessage"
              class="no-users"
            >
              <div class="no-users-content">
                <h4>Nenhum contrato cadastrado</h4>
                <p>Nenhum contrato encontrado no banco de dados.</p>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div
        v-if="!isLoadingContratos && contratos.length > 0 && totalPages > 1"
        class="bottom-next-container"
      >
        <button
          class="bottom-next-btn"
          @click="goToPage(currentPage + 1)"
          :disabled="currentPage >= totalPages"
        >
          Próxima →
        </button>
      </div>
    </main>

    <!-- Popup de upload de contrato -->
    <PopupLoadView
      v-if="showUploadPopup"
      :title="`Upload do contrato ${contratoSelecionado ? contratoSelecionado.codigo_contrato : ''}`"
      :accept="'.pdf,.doc,.docx,.xls,.xlsx'"
      :allowed-extensions="['.pdf', '.doc', '.docx', '.xls', '.xlsx']"
      :allowed-mime-types="[
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
      ]"
      :max-size-m-b="15"
      upload-label="📤 Enviar contrato"
      hint-text="Formatos: PDF, DOC, DOCX, XLS, XLSX (até 15MB)"
      @close="fecharUploadPopup"
      @upload="handleContratoUpload"
    />
  </div>
</template>

<style scoped>
/* --- ESTILOS GERAIS --- */

/* NOVA REGRA: Impede que a PÁGINA inteira tenha scroll horizontal 
*/
.admin-panel {
  overflow-x: hidden;
}

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

.h2projeto {
  color: black;
  margin-bottom: 1rem;
  font-size: 3rem;
  text-align: center;
}

/* Badge de Filtro de Divisão */
.filtro-divisao-badge {
  display: flex;
  align-items: center;
  gap: 12px;
  padding: 12px 20px;
  background-color: rgba(19, 44, 13, 0.1);
  border: 2px solid rgba(19, 44, 13, 0.3);
  border-radius: 8px;
  margin-bottom: 1.5rem;
  max-width: 1900px;
  margin-left: auto;
  margin-right: auto;
}

.filtro-label {
  font-weight: 600;
  color: #132c0d;
  font-size: 14px;
}

.filtro-divisao-nome {
  flex: 1;
  font-weight: 500;
  color: #333;
  font-size: 16px;
}

.btn-remover-filtro {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  border: none;
  border-radius: 50%;
  width: 28px;
  height: 28px;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  font-size: 16px;
  transition: all 0.2s ease;
  padding: 0;
  line-height: 1;
}

.btn-remover-filtro:hover {
  background-color: #1a3a11;
  transform: scale(1.1);
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
  overflow-x: auto; /* Permite scroll horizontal */
  overflow-y: auto;
  max-height: 60vh; /* Reduzido para 60vh para evitar scroll da página */
  border-radius: 8px;
  border: 1px solid #e0e0e0;
}

.users-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  min-width: 1400px; /* Ajustado para os novos campos */
}

.users-table th {
  background-color: rgba(19, 44, 13, 0.6);
  color: white;
  /* AJUSTE DE FONTE: Títulos maiores e com padding ajustado 
  */
  padding: 0.9rem 1rem; /* Padding ajustado */
  font-size: 1rem; /* 16px - Fonte maior */
  text-align: left;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
  white-space: nowrap;
}

.users-table td {
  /* AJUSTE DE FONTE: Linhas menores e com padding ajustado 
  */
  padding: 0.8rem 1rem; /* Padding ajustado */
  font-size: 0.875rem; /* 14px - Fonte menor */
  border-bottom: 1px solid #e0e0e0;
  vertical-align: middle;
  white-space: nowrap;
}

/* Permite que a descrição (coluna 3) quebre a linha */
.users-table th:nth-child(3),
.users-table td:nth-child(3) {
  white-space: normal;
  min-width: 250px;
}

.users-table tr:hover {
  background-color: #f8f9fa;
}

/* Estilos para o STATUS */
.status {
  padding: 0.25rem 0.75rem;
  border-radius: 12px;
  font-size: 0.8rem; /* Fonte do status um pouco menor */
  font-weight: 500;
  text-transform: uppercase;
}

/* Classe para Status 'Ativo' (baseado no 0) */
.status.Ativo {
  background-color: #e8f5e8;
  color: #2e7d32;
}

/* Classe para Status 'Concluído' (baseado no 1) */
.status.Concluído {
  background-color: #e3f2fd;
  color: #1565c0;
}

/* Classe de fallback caso o valor seja outro */
.status:not(.Ativo):not(.Concluído) {
  background-color: #f0f0f0;
  color: #555;
}

/* Estilos para Loading e "Nenhum Usuário" */
.no-users {
  text-align: center;
  padding: 2rem;
  color: #666;
}
.no-users-content {
  max-width: 500px;
  margin: 0 auto;
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

/* Feedback de upload */
.upload-feedback {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  padding: 12px 16px;
  border-radius: 8px;
  margin-bottom: 16px;
  border: 1px solid transparent;
}

.upload-feedback.success {
  background-color: #e8f5e9;
  border-color: #c8e6c9;
  color: #1b5e20;
}

.upload-feedback.error {
  background-color: #ffebee;
  border-color: #ffcdd2;
  color: #b71c1c;
}

.upload-feedback-close {
  background: transparent;
  border: none;
  cursor: pointer;
  font-size: 16px;
  line-height: 1;
}

.acoes-col {
  text-align: center;
}

.btn-upload-contrato {
  background-color: rgba(19, 44, 13, 0.85);
  color: white;
  border: none;
  border-radius: 8px;
  padding: 10px 12px;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.2s ease;
  width: 100%;
  min-width: 150px;
}

.btn-upload-contrato:hover {
  background-color: #0f2410;
  transform: translateY(-1px);
}

.btn-upload-contrato:active {
  transform: translateY(0);
}

.bottom-next-container {
  display: flex;
  justify-content: center;
  margin-top: 24px;
  margin-bottom: 8px;
}

.bottom-next-btn {
  background-color: rgba(19, 44, 13, 0.9);
  color: white;
  border: none;
  border-radius: 10px;
  padding: 14px 28px;
  font-weight: 700;
  font-size: 1rem;
  cursor: pointer;
  transition: all 0.2s ease;
  min-width: 180px;
  box-shadow: 0 6px 18px rgba(0, 0, 0, 0.08);
}

.bottom-next-btn:hover:not(:disabled) {
  background-color: #0f2410;
  transform: translateY(-2px);
}

.bottom-next-btn:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}
</style>
