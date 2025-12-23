<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import { contratoApi } from '../services/api'

import Header_no_HR from './Header_no_HR.vue'
import ArrowBack from '@/components/icons/ArrowBack.vue'
import IconFormat from '@/components/icons/IconFormat.vue'

const todosContratos = ref([])
const termoBusca = ref('')
const mostrarLista = ref(false)
const contratosAdicionados = ref([])
const contratoSelecionadoTemp = ref(null)

onMounted(async () => {
  try {
    // Busca a lista de contratos
    const response = await contratoApi.getContratos(1, 1000)

    if (response.data && response.data.data) {
      todosContratos.value = response.data.data
    } else if (Array.isArray(response.data)) {
      todosContratos.value = response.data
    } else if (response.data.contratos) {
      todosContratos.value = response.data.contratos
    }
  } catch (error) {
    console.error('Erro ao carregar contratos para busca:', error)
  }
})

const contratosFiltrados = computed(() => {
  if (!termoBusca.value) {
    return todosContratos.value
  }

  const termo = termoBusca.value.toLowerCase()
  return todosContratos.value.filter(
    (c) =>
      (c.codigo_contrato && c.codigo_contrato.toString().toLowerCase().includes(termo)) ||
      (c.descricao && c.descricao.toLowerCase().includes(termo)),
  )
})

function selecionarContrato(contrato) {
  termoBusca.value = contrato.codigo_contrato
  contratoSelecionadoTemp.value = contrato
  mostrarLista.value = false
  console.log('Contrato selecionado:', contrato)
}

function fecharListaComDelay() {
  setTimeout(() => {
    mostrarLista.value = false
  }, 200)
}

function adicionarParaTabela() {
  if (!contratoSelecionadoTemp.value) {
    alert('Por favor, selecione um contrato da lista primeiro.')
    return
  }

  const jaExiste = contratosAdicionados.value.find((c) => c.id === contratoSelecionadoTemp.value.id)

  if (jaExiste) {
    alert('Este contrato já foi adicionado à tabela.')
    return
  }

  const novoItem = {
    ...contratoSelecionadoTemp.value,
    obs: '',
    status: '',
    statusOpen: false,
  }

  contratosAdicionados.value.push(novoItem)

  termoBusca.value = ''
  contratoSelecionadoTemp.value = null
}

function toggleStatusMenu(item) {
  contratosAdicionados.value.forEach((i) => {
    if (i !== item) i.statusOpen = false
  })
  item.statusOpen = !item.statusOpen
}

function selecionarStatus(item, valorStatus) {
  item.status = valorStatus
  item.statusOpen = false
}
// --- Formatadores ---
function formatDate(dateString) {
  if (!dateString) return '-'
  try {
    const date = new Date(dateString)
    return date.toLocaleDateString('pt-BR')
  } catch (e) {
    return dateString
  }
}

function formatMoney(value) {
  if (!value) return 'R$ 0,00'
  // Assume que vem como string ou numero. Ajuste conforme sua API.
  return new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' }).format(value)
}

function removerItem(index) {
  contratosAdicionados.value.splice(index, 1)
}
</script>
<template>
  <Header_no_HR />

  <div class="div_main">
    <div class="div_select">
      <div class="setavoltar" @click="$router.go(-1)"><ArrowBack /></div>

      <div class="center-content">
        <h1>Contratos</h1>

        <div class="search-container">
          <input
            type="text"
            placeholder="Código do Contrato"
            v-model="termoBusca"
            @focus="mostrarLista = true"
            @blur="fecharListaComDelay"
          />

          <ul v-if="mostrarLista" class="dropdown-list">
            <li
              v-for="contrato in contratosFiltrados"
              :key="contrato.id || contrato.codigo_contrato"
              @click="selecionarContrato(contrato)"
            >
              <strong>{{ contrato.codigo_contrato }}</strong>
              <span class="desc-preview" v-if="contrato.descricao">
                - {{ contrato.descricao }}</span
              >
            </li>

            <li v-if="contratosFiltrados.length === 0" class="no-result">
              Nenhum contrato encontrado.
            </li>
          </ul>
        </div>
      </div>

      <div class="bntadd">
        <button @click="adicionarParaTabela">Adicionar</button>
      </div>
    </div>

    <div class="content-wrapper">
      <div class="table-container">
        <table class="custom-table">
          <thead>
            <tr>
              <th>Referência</th>
              <th>Contrato</th>
              <th>Desc. Contrato</th>
              <th>Adm.</th>
              <th>Fornecedor</th>
              <th>Valor</th>
              <th>Término</th>
              <th>Obs.</th>
              <th>Status</th>
              <th>Format</th>
              <th>Excluir</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="(item, index) in contratosAdicionados" :key="item.id || index">
              <td>{{ item.referencia || '-' }}</td>
              <td>{{ item.codigo_contrato }}</td>
              <td class="col-desc">{{ item.descricao || '-' }}</td>
              <td>{{ item.administrador || '-' }}</td>
              <td>{{ item.fornecedor || '-' }}</td>
              <td>{{ item.centro_custo ? formatMoney(item.valor) : '-' }}</td>
              <td>{{ formatDate(item.data_termino) }}</td>

              <td>
                <input type="text" placeholder="Observação" class="tdobs" v-model="item.obs" />
              </td>
              <td class="status-cell">
                <div class="custom-select-container">
                  <div
                    class="select-trigger"
                    @click="toggleStatusMenu(item)"
                    :class="{ placeholder: !item.status }"
                  >
                    <span>{{ item.status ? item.status : 'Selecione Status' }}</span>
                    <span class="arrow-down">▼</span>
                  </div>

                  <div v-if="item.statusOpen" class="custom-options">
                    <div class="option-item ativo" @click="selecionarStatus(item, 'Ativo')">
                      <span class="dot">●</span> Ativo
                    </div>

                    <div class="option-item concluido" @click="selecionarStatus(item, 'Concluído')">
                      <span class="dot">●</span> Concluído
                    </div>

                    <div class="option-item vencido" @click="selecionarStatus(item, 'Vencido')">
                      <span class="dot">●</span> Vencido
                    </div>

                    <div
                      class="option-item proximo"
                      @click="selecionarStatus(item, 'Próximo Vencimento')"
                    >
                      <span class="dot">●</span> Próx. Vencimento
                    </div>
                  </div>
                </div>
              </td>

              <td>
                <button class="btn-format" title="Visualizar Arquivo">
                  <IconFormat />
                </button>
              </td>
              <td class="col-actions">
                <button class="btn-excluir" @click="removerItem(index)" title="Excluir">
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    width="20"
                    height="20"
                    viewBox="0 0 24 24"
                    fill="none"
                    stroke="currentColor"
                    stroke-width="2"
                    stroke-linecap="round"
                    stroke-linejoin="round"
                  >
                    <polyline points="3 6 5 6 21 6"></polyline>
                    <path
                      d="M19 6v14a2 2 0 0 1-2 2H7a2 2 0 0 1-2-2V6m3 0V4a2 2 0 0 1 2-2h4a2 2 0 0 1 2 2v2"
                    ></path>
                    <line x1="10" y1="11" x2="10" y2="17"></line>
                    <line x1="14" y1="11" x2="14" y2="17"></line>
                  </svg>
                </button>
              </td>
            </tr>

            <tr v-if="contratosAdicionados.length === 0">
              <td colspan="11" class="empty-table-cell">
                Nenhum contrato adicionado. Pesquise e clique em "Adicionar".
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>
  </div>
</template>
<style scoped>
.div_main {
  display: flex;
  flex-direction: column;
  width: 100%;
  padding: 20px;
}

.div_select {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 40px;
  margin-top: 2%;
  margin-bottom: 1%;
  width: 100%;
}

/* 1. Estilo da Seta (Maior e Verde) */
.setavoltar {
  cursor: pointer;
  color: #0f2410;
  display: flex;
  transform: scale(2);
  transition: transform 0.2s ease;
  margin-left: 10px;
}

.setavoltar:hover {
  transform: scale(2.3); /* Cresce ao passar o mouse */
}

.center-content {
  display: flex;
  flex-direction: column;
}

.center-content h1 {
  font-size: 20px;
  color: #0f2410;
  font-weight: bold;
  margin-bottom: 5px;
}

.center-content input {
  width: 250px;
  padding: 10px;
  border-radius: 10px;
  border: 2px solid #ccc;
  color: black;
}

.div_select input {
  width: 250px;
  padding: 10px;
  border-radius: 10px;
  border: 2px solid #ccc;
  color: black;
}

.div_select h1 {
  font-size: 20px;
  color: #0f2410; /* Verde */
  font-weight: bold;
  margin-bottom: 2%;
}

.bntadd {
  display: flex;
  align-items: center;
  justify-content: center;
}

.bntadd button {
  width: 150px;
  height: 40px;
  font-size: 15px;
  background-color: rgba(19, 44, 13, 0.6); /* Verde */
  color: white;
  border: none;
  border-radius: 5px;
  cursor: pointer;
  border-radius: 10px;
  transition: all 0.3s ease;
  margin-top: 15%;
}

.bntadd button:hover {
  background-color: #0f2410;
}

/* Container branco ao redor da tabela */
.content-wrapper {
  background: white;
  padding: 1rem;
  border-radius: 8px;
  box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
  margin-top: 20px;
  height: 100%;
}

/* Container com scroll caso a tabela seja muito larga */
.table-container {
  overflow-x: auto;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  min-height: 200px; /* Define uma altura mínima fixa (ajuste conforme sua tela) */
  height: 63vh;
}

/* Estilo da Tabela */
.custom-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  min-width: 1200px; /* Evita que esmague em telas pequenas */
}

/* Cabeçalho Verde */
.custom-table th {
  background-color: rgba(19, 44, 13, 0.6);
  color: white;
  padding: 1rem;
  text-align: center;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
  white-space: nowrap;
}

/* Células */
.custom-table td {
  padding: 0.8rem 1rem;
  font-size: 0.9rem;
  border-bottom: 1px solid #e0e0e0;
  vertical-align: middle;
  text-align: center;
  color: #333;
}

/* Efeito Hover na linha */
.custom-table tr:hover {
  background-color: #f8f9fa;
}

/* Ajustes Específicos de Coluna */
.col-desc {
  text-align: left !important;
  min-width: 200px;
}

.col-obs {
  text-align: left !important;
  max-width: 150px;
  white-space: nowrap;
  overflow: hidden;
  text-overflow: ellipsis;
}

/* Badge de Status */
.status-badge {
  display: inline-flex;
  justify-content: center;
  align-items: center;
  padding: 4px 12px;
  border-radius: 20px;
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  min-width: 80px;
}

.status-ativo {
  background-color: #e8f5e8;
  color: #2e7d32;
}

.status-concluido {
  background-color: #e3f2fd;
  color: #1565c0;
}

/* Botão de Excluir */
.btn-excluir {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  color: #0f2410;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-excluir:hover {
  color: #ef4444;
  background-color: #fef2f2;
}

.table-container::-webkit-scrollbar {
  height: 8px;
}
.table-container::-webkit-scrollbar-track {
  background: transparent;
}
.table-container::-webkit-scrollbar-thumb {
  background-color: rgba(19, 44, 13, 0.3);
  border-radius: 4px;
}
.tdobs {
  text-align: center;
  padding: 10px;
  border-radius: 10px;
  border: 2px solid #ccc;
}
.select-status {
  padding: 8px;
  border-radius: 10px;
  border: 2px solid #ccc;
  text-align: center;
}

.btn-format {
  background: none;
  border: none;
  cursor: pointer;
  padding: 6px;
  border-radius: 4px;
  color: #0f2410;
  transition: all 0.2s ease;
  display: inline-flex;
  align-items: center;
  justify-content: center;
}

.btn-format:hover {
  color: #2e7d32;
  background-color: #e8f5e8;
}

.search-container {
  position: relative;
  width: 250px;
}

.search-container input {
  width: 100%;
  padding: 10px;
  border-radius: 10px;
  border: 2px solid #ccc;
  color: black;
  box-sizing: border-box;
}

/* A Lista Flutuante */
.dropdown-list {
  position: absolute;
  top: 105%;
  left: 0;
  width: 100%;
  max-height: 250px;
  overflow-y: auto;
  background: white;
  border: 1px solid #ccc;
  border-radius: 8px;
  box-shadow: 0 4px 10px rgba(0, 0, 0, 0.15);
  list-style: none;
  padding: 0;
  margin: 0;
  z-index: 1000;
}

/* Itens da lista */
.dropdown-list li {
  padding: 10px;
  cursor: pointer;
  border-bottom: 1px solid #eee;
  font-size: 0.9rem;
  color: #333;
  text-align: left;
}

.dropdown-list li:last-child {
  border-bottom: none;
}

.dropdown-list li:hover {
  background-color: #e8f5e8;
  color: #0f2410;
}

.desc-preview {
  font-size: 0.8rem;
  color: #666;
  font-style: italic;
}

.no-result {
  color: #999;
  cursor: default;
  text-align: center;
}

.dropdown-list::-webkit-scrollbar {
  width: 6px;
}
.dropdown-list::-webkit-scrollbar-thumb {
  background-color: rgba(19, 44, 13, 0.3);
  border-radius: 4px;
}

.empty-table-cell {
  height: 55vh;
  vertical-align: middle;
  text-align: center;
  color: #888;
  font-size: 1.1rem;
  background-color: white;
  border-bottom: none;
}

.status-cell {
  overflow: visible !important;
  position: relative;
}

.custom-select-container {
  position: relative;
  width: 160px;
  margin: 0 auto;
}

.select-trigger {
  background: white;
  border: 2px solid #ccc;
  border-radius: 10px;
  padding: 8px 12px;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  font-size: 0.9rem;
  color: #333;
  transition: all 0.2s;
  text-transform: capitalize;
}

.select-trigger:hover {
  border-color: #0f2410;
}

.select-trigger.placeholder {
  color: #888;
}

.arrow-down {
  font-size: 0.6rem;
  color: #666;
}

.custom-options {
  position: absolute;
  top: 110%;
  left: 0;
  width: 100%;
  background: white;
  border: 1px solid #ddd;
  border-radius: 8px;
  box-shadow: 0 4px 15px rgba(0, 0, 0, 0.15);
  z-index: 999;
  overflow: hidden;
  text-align: left;
}

/* Item da lista */
.option-item {
  padding: 10px 12px;
  cursor: pointer;
  font-size: 0.9rem;
  color: #333;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: all 0.3s ease;
}

.option-item:hover {
  background-color: #e8f5e8;
  font-weight: 600;
}

.dot {
  font-size: 1.2rem;
  line-height: 0;
}
.option-item.ativo .dot {
  color: #2e7d32;
}
.option-item.concluido .dot {
  color: #1565c0;
}
.option-item.vencido .dot {
  color: #c62828;
}
.option-item.proximo .dot {
  color: #ef6c00;
}
</style>
