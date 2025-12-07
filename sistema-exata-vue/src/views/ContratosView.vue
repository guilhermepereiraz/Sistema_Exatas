<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
// 1. Importa a API de contratos
import { contratoApi } from '../services/api'
import HeaderNoHR from './Header_no_HR.vue'

const router = useRouter()

// 2. Refs para a lista de contratos e estado de carregamento
const contratos = ref([])
const isLoadingContratos = ref(false)
const errorMessage = ref('')

// 3. Carrega dados quando o componente é montado
onMounted(async () => {
  const user = authService.getCurrentUser()
  if (!user) {
    router.push('/')
    return
  }
  // 4. Chama a função para carregar contratos
  await loadContratos()
})

// 5. Função para carregar CONTRATOS
async function loadContratos() {
  isLoadingContratos.value = true
  errorMessage.value = '' // Limpa erros anteriores
  try {
    const response = await contratoApi.getContratos()

    // Ajusta a lógica de acordo com a resposta da sua API
    if (response.data && Array.isArray(response.data)) {
      contratos.value = response.data
    } else if (response.data && response.data.data && Array.isArray(response.data.data)) {
      contratos.value = response.data.data
    } else {
      console.warn('Formato de resposta inesperado para contratos:', response.data)
      contratos.value = []
    }
  } catch (error) {
    console.error('Erro ao carregar contratos:', error)
    errorMessage.value = 'Erro ao carregar lista de contratos.'
    contratos.value = []
  } finally {
    isLoadingContratos.value = false
  }
}

// 6. Função para formatar o Status (baseado na coluna 'concluido' 0 ou 1)
function getStatusLabel(concluido) {
  if (concluido === 1) {
    return 'Concluído'
  }
  if (concluido === 0) {
    return 'Ativo'
  }
  return concluido || '-' // Retorna o valor original ou '-'
}

// 7. Função para formatar moeda
function formatCurrency(value) {
  if (value === null || value === undefined) return '-'
  const BRL = new Intl.NumberFormat('pt-BR', {
    style: 'currency',
    currency: 'BRL',
  })
  return BRL.format(value)
}

// 8. Função para formatar data (sem ajuste de fuso)
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
                  <th>Ref. Contrato</th>
                  <th>Nº Contrato</th>
                  <th>Descrição</th>
                  <th>Centro Custo</th>
                  <th>Planta Global</th>
                  <th>Administrador</th>
                  <th>Fornecedor</th>
                  <th>Valor Contábil</th>
                  <th>Status</th>
                  <th>Data Término</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="contrato in contratos" :key="contrato.id">
                  <!-- 
                    CORREÇÃO AQUI: Trocado 'ref_contrato' por 'ref_ner' 
                  -->
                  <td>{{ contrato.referencia || '-' }}</td>
                  <td>{{ contrato.numero || '-' }}</td>
                  <td>{{ contrato.descricao || '-' }}</td>
                  <td>{{ contrato.centro_custo || '-' }}</td>
                  <td>{{ contrato.planta_global || '-' }}</td>
                  <td>{{ contrato.administrador || '-' }}</td>
                  <td>{{ contrato.fornecedor || '-' }}</td>
                  <td>{{ formatCurrency(contrato.valor_contabil) }}</td>
                  <td>
                    <span class="status" :class="getStatusLabel(contrato.concluido)">
                      {{ getStatusLabel(contrato.concluido) }}
                    </span>
                  </td>
                  <td>{{ formatDate(contrato.data_termino) }}</td>
                </tr>
              </tbody>
            </table>

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
    </main>

    <div class="bntproximo">
      <button>Próximo</button>
    </div>
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
  min-width: 1600px; /* Força scroll horizontal em telas menores */
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

.bntproximo {
  display: flex;
  justify-content: center;

}

.bntproximo button {
background-color: rgba(19, 44, 13, 0.8);
  color: white;
  padding: 0.75rem 1.5rem;
  border: none;
  border-radius: 7px;
  cursor: pointer;
  font-size: 1rem;
  font-weight: bolder;
  width: 9%;
  margin-top: -10px;
  transition: all 0.3s ease;
}

.bntproximo button:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}
</style>
