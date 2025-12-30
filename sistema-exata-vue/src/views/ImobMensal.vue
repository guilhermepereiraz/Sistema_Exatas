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

const isDeleteModalOpen = ref(false)
const itemToDelete = ref(null)
const itemToDeleteIndex = ref(null)

function removerItem(index) {
  itemToDeleteIndex.value = index
  itemToDelete.value = contratosAdicionados.value[index]
  isDeleteModalOpen.value = true
}

// 2. Ao confirmar no modal (Apaga de verdade)
function confirmarExclusao() {
  if (itemToDeleteIndex.value !== null) {
    contratosAdicionados.value.splice(itemToDeleteIndex.value, 1)
  }
  fecharModalExclusao()
}

// 3. Ao cancelar ou fechar
function fecharModalExclusao() {
  isDeleteModalOpen.value = false
  itemToDelete.value = null
  itemToDeleteIndex.value = null
}

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

  const jaExiste = contratosAdicionados.value.find(
    (c) => c.codigo_contrato === contratoSelecionadoTemp.value.codigo_contrato,
  )

  if (jaExiste) {
    alert('Este contrato já foi adicionado à tabela.')
    return
  }

  const novoItem = {
    ...contratoSelecionadoTemp.value,
    localId: Date.now(),
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
</script>

<template>
  <div class="page-root">
    <Header_no_HR />

    <Transition name="modal-fade">
      <div class="confirmation-backdrop" v-if="isDeleteModalOpen" @click="fecharModalExclusao">
        <div class="confirmation-window" @click.stop>
          <div class="confirmation-heading">
            <h1>
              Tem certeza que deseja <br />
              excluir esse contrato da sua <br />
              lista?
            </h1>
          </div>

          <div>
            <span class="target-user-display">
              ({{ itemToDelete ? itemToDelete.codigo_contrato : 'contrato' }})
            </span>
          </div>

          <div class="confirmation-text">
            <p>O contrato NÃO será excluir do banco de dados</p>
          </div>

          <div class="confirmation-actions">
            <button class="action-button action-cancel" @click="fecharModalExclusao">Não</button>
            <button class="action-button action-confirm" @click="confirmarExclusao">Sim</button>
          </div>
        </div>
      </div>
    </Transition>

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
              <tr class="trtable">
                <th>Referência</th>
                <th>Contrato</th>
                <th>Desc. Contrato</th>
                <th>Adm.</th>
                <th>Fornecedor</th>
                <th>Valor</th>
                <th>Término</th>
                <th style="width: 15%">Obs.</th>

                <th style="width: 170px">Status</th>
                <th>Format</th>
                <th>Excluir</th>
              </tr>
            </thead>
            <TransitionGroup name="list" tag="tbody">
              <tr
                v-for="(item, index) in contratosAdicionados"
                :key="item.localId || item.id || item.codigo_contrato"
              >
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
                      <div
                        class="option-item concluido"
                        @click="selecionarStatus(item, 'Concluído')"
                      >
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

              <tr
                v-if="contratosAdicionados.length === 0"
                key="no-contracts-row"
                class="static-row"
              >
                <td colspan="11" class="empty-table-cell">
                  Nenhum contrato adicionado. Pesquise e clique em "Adicionar".
                </td>
              </tr>
            </TransitionGroup>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
/* Classe para corrigir a transição do Vue e evitar tela branca */
.page-root {
  width: 100%;
  min-height: 100vh;
}

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
  overflow-y: auto;
  scroll-behavior: smooth;
  border-radius: 8px;
  border: 1px solid #e0e0e0;
  min-height: 200px; /* Define uma altura mínima fixa (ajuste conforme sua tela) */
  height: 63vh;
  contain: paint;
}

.custom-table {
  width: 100%;
  border-collapse: collapse;
  background: white;
  min-width: 1200px;
  table-layout: fixed;
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

/* Cabeçalho Verde */
.custom-table th {
  color: white;
  padding: 1rem;
  text-align: center;
  font-weight: 600;
  border-bottom: 2px solid #e0e0e0;
  white-space: nowrap;

  /* --- O TRUQUE PARA FIXAR --- */
  position: sticky;
  top: 0;
  z-index: 100; /* Garante que fique por cima do conteúdo */
  box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1); /* Sombra suave para destacar */
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

/* --- ESTILOS DO MODAL DE CONFIRMAÇÃO --- */
.confirmation-backdrop {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  align-items: center;
  justify-content: center;
  z-index: 2000; /* Alto z-index para ficar por cima de tudo */
  padding: 1rem;
}

.confirmation-window {
  background-color: white;
  padding: 2.5rem;
  border-radius: 20px;
  box-shadow: 0 5px 20px rgba(0, 0, 0, 0.2);
  width: 90%;
  max-width: 700px;
  height: 90%;
  max-height: 500px;
  display: flex;
  flex-direction: column;
  align-items: center;
  text-align: center;
  gap: 1.5rem;
}
.confirmation-heading h1 {
  font-size: 2.2rem;
  font-weight: 700;
  color: #222;
  margin: 0;
  line-height: 1.3;
  margin-top: 20%;
}

.target-user-display {
  display: inline-block;
  background-color: #eeeeee;
  color: #555;
  padding: 0.25rem 0.75rem;
  border-radius: 16px;
  font-size: 0.9rem;
  font-weight: 500;
}

.confirmation-text p {
  font-size: 1.1rem;
  color: #444;
  margin: 0;
}

.confirmation-text strong {
  color: #000;
  font-weight: 700;
}

.confirmation-actions {
  display: flex;
  gap: 1rem;
  width: 100%;
  margin-top: 0rem;
}

.action-button {
  flex-grow: 1;
  padding: 0.8rem;
  font-size: 1.1rem;
  font-weight: 600;
  border: 2px solid;
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.2s ease;
  margin-top: 2%;
}

.action-cancel {
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  font-size: 20px;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(19, 44, 13, 0.8);
}

.action-cancel:hover {
  background-color: #2e6931;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.8);
}

.action-confirm {
  background-color: #b83a3a;
  border: 1px solid black;
  border-radius: 10px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
}

.action-confirm:hover {
  background-color: rgba(209, 44, 44, 0.8);
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(139, 14, 14, 0.8);
}

.modal-fade-enter-active,
.modal-fade-leave-active {
  transition: opacity 0.3s ease-out;
}

.modal-fade-enter-active .confirmation-window {
  transition:
    opacity 0.3s ease-out,
    transform 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}

.modal-fade-leave-active .confirmation-window {
  transition:
    opacity 0.2s ease-in,
    transform 0.2s ease-in;
}

.modal-fade-enter-from,
.modal-fade-leave-to {
  opacity: 0;
}

.modal-fade-enter-from .confirmation-window,
.modal-fade-leave-to .confirmation-window {
  opacity: 0;
  transform: translateY(20px);
}

.list-enter-active,
.list-leave-active {
  transition:
    opacity 0.3s ease,
    background-color 0.3s ease;
}

/* 2. ENTRADA (Adicionar) */
.list-enter-from {
  opacity: 0;
  background-color: #dcfce7; /* Verde claro ao nascer */
}

/* Durante a entrada, mantemos o verde um pouco para dar destaque */
.list-enter-active {
  background-color: #dcfce7;
}

.list-enter-to {
  opacity: 1;
  background-color: transparent; /* Volta ao branco suavemente */
}

/* 3. SAÍDA (Excluir) */
.list-leave-from {
  opacity: 1;
  background-color: transparent;
}

.list-leave-active {
  /* O truque: definimos a cor explicitamente durante a saída para não piscar */
  background-color: #fee2e2; /* Vermelho claro ao morrer */
}

.list-leave-to {
  opacity: 0;
  background-color: #fee2e2;
}

/* Keyframes para garantir o flash de cor */
@keyframes highlight-green {
  0% {
    background-color: rgba(46, 125, 50, 0.2);
  }
  100% {
    background-color: transparent;
  }
}

.list-leave-active.static-row {
  display: none; /* Some instantaneamente */
  transition: none !important;
}

/* Garante que a mensagem de vazio não pisque verde ao aparecer de volta */
.list-enter-active.static-row {
  animation: none !important;
  transition: opacity 0.3s ease; /* Apenas um fade suave simples */
}
</style>
