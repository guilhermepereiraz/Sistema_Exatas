<template>
  <div class="popoverlay" >
    <div class="popupview">
      <div class="tituloload">
        <IconExit class="iconexit" @click="handleClose"/>
        <h1>{{ title }}</h1>
      </div>

      <!-- Mensagens de feedback -->
      <div v-if="errorMessage" class="message error-message">
        {{ errorMessage }}
      </div>
      <div v-if="successMessage" class="message success-message">
        {{ successMessage }}
      </div>

      <!-- Área de upload -->
      <div 
        class="loadprocss"
        :class="{ 'dragging': isDragging, 'has-file': selectedFile }"
        @click="triggerFileInput"
        @dragover.prevent="isDragging = true"
        @dragleave.prevent="isDragging = false"
        @drop.prevent="handleDrop"
      >
        <input
          type="file"
          ref="fileInput"
          @change="handleFileSelect"
          style="display: none"
          :accept="accept"
        />
        
        <IconLoadProcess class="icone-load-process" />
        
        <div v-if="!selectedFile" class="upload-text">
          <p>Clique aqui ou arraste o arquivo</p>
          <p class="file-format">{{ hintText }}</p>
        </div>
        
        <div v-else class="file-info">
          <p class="file-name">📄 {{ selectedFile.name }}</p>
          <p class="file-size">{{ formatFileSize(selectedFile.size) }}</p>
          <button @click.stop="clearFile" class="btn-remove-file">Remover</button>
        </div>
      </div>

      <div class="bnt">
        <button 
          @click="handleUpload" 
          :disabled="!selectedFile || isUploading"
          class="btn-upload"
        >
          <span v-if="isUploading">⏳ Processando...</span>
          <span v-else>{{ uploadLabel }}</span>
        </button>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import IconLoadProcess from '@/components/icons/IconLoadProcess.vue'
import IconExit from '@/components/icons/IconExit.vue'

const emit = defineEmits(['close', 'upload'])

const props = defineProps({
  title: {
    type: String,
    default: 'Carregar'
  },
  accept: {
    type: String,
    default: '.csv,.xlsx,.xls,.xlsm'
  },
  allowedExtensions: {
    type: Array,
    default: () => ['.csv', '.xls', '.xlsx', '.xlsm']
  },
  allowedMimeTypes: {
    type: Array,
    default: () => [
      'text/csv',
      'application/vnd.ms-excel',
      'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
      'application/vnd.ms-excel.sheet.macroEnabled.12'
    ]
  },
  maxSizeMB: {
    type: Number,
    default: 10
  },
  uploadLabel: {
    type: String,
    default: '📤 Enviar Arquivo'
  },
  hintText: {
    type: String,
    default: 'Formatos aceitos: .xls, .xlsx, .xlsm ou .csv'
  }
})

const fileInput = ref(null)
const selectedFile = ref(null)
const isDragging = ref(false)
const isUploading = ref(false)
const errorMessage = ref('')
const successMessage = ref('')

function triggerFileInput() {
  fileInput.value?.click()
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

function handleDrop(event) {
  isDragging.value = false
  const file = event.dataTransfer.files[0]
  if (file) {
    validateAndSetFile(file)
  }
}

function validateAndSetFile(file) {
  errorMessage.value = ''
  successMessage.value = ''
  
  // Validação de tipo
  const fileExtension = '.' + file.name.split('.').pop().toLowerCase()
  
  const normalizedAllowedExtensions = props.allowedExtensions.map(ext => ext.toLowerCase())
  const normalizedAllowedMimeTypes = props.allowedMimeTypes.map(type => type.toLowerCase())
  const extensionAllowed = normalizedAllowedExtensions.includes(fileExtension)
  const mimeAllowed = file.type ? normalizedAllowedMimeTypes.includes(file.type.toLowerCase()) : true

  if (!extensionAllowed && !mimeAllowed) {
    errorMessage.value = 'Formato de arquivo não permitido.'
    return
  }
  
  // Validação de tamanho (10MB)
  const maxSize = props.maxSizeMB * 1024 * 1024
  if (file.size > maxSize) {
    errorMessage.value = `Arquivo muito grande. Tamanho máximo: ${props.maxSizeMB}MB.`
    return
  }
  
  selectedFile.value = file
}

function clearFile() {
  selectedFile.value = null
  if (fileInput.value) {
    fileInput.value.value = ''
  }
  errorMessage.value = ''
  successMessage.value = ''
}

function formatFileSize(bytes) {
  if (bytes === 0) return '0 Bytes'
  const k = 1024
  const sizes = ['Bytes', 'KB', 'MB', 'GB']
  const i = Math.floor(Math.log(bytes) / Math.log(k))
  return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i]
}

async function handleUpload() {
  if (!selectedFile.value) return
  
  isUploading.value = true
  errorMessage.value = ''
  successMessage.value = ''
  
  try {
    // Emite evento para o componente pai processar o upload
    emit('upload', selectedFile.value)
    
    // O componente pai deve fechar o popup após sucesso
    // Se quiser fechar automaticamente, descomente:
    // setTimeout(() => {
    //   handleClose()
    // }, 2000)
    
  } catch (error) {
    console.error('Erro no upload:', error)
    errorMessage.value = 'Erro ao processar arquivo. Tente novamente.'
  } finally {
    isUploading.value = false
  }
}

function handleClose() {
  clearFile()
  emit('close')
}
</script>

<style scoped>
.popoverlay {
  position: fixed;
  top: 0;
  left: 0;
  width: 100%;
  height: 100%;
  background-color: rgba(0, 0, 0, 0.5);
  display: flex;
  justify-content: center;
  align-items: center;
  z-index: 100;
  padding: 20px;
  box-sizing: border-box;
}

.popupview {
  position: relative;
  text-align: center;
  z-index: 101;
  background-color: white;
  padding: 20px;
  border-radius: 20px;
  width: 100%;
  max-width: 700px;
  height: auto;
  min-height: 470px;
}

.tituloload h1 {
  font-size: 2.2rem;
  margin-top: 40px;
  margin-bottom: 20px;
}

.iconexit {
  position: absolute;
  top: 25px;
  left: 25px;
  width: 50px;
  height: 50px;
  cursor: pointer;
  border-radius: 50%;
  padding: 10px;
  transition: all 0.3s ease;
}

.iconexit:hover {
  background-color: rgba(229, 229, 229, 1);
}

.icone-load-process {
  width: 200px;
  height: 100px;
  margin-top: 50px;
}

.icone-load-process :deep(svg) {
  width: 100%;
  height: 100%;
}

.loadprocss {
  display: flex;
  flex-direction: column;
  align-items: center;
  gap: 20px;
  margin: 0 auto 20px auto;
  background-color: rgba(229, 229, 229, 1);
  width: 90%;
  max-width: 525px;
  height: 250px;
  border-radius: 20px;
border: 2px solid #868686;
  font-size: 13px;
  font-weight: bolder;
  padding: 10px;
  box-sizing: border-box;
  transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
  cursor: pointer;
}

.loadprocss:hover {
  border: 2px dashed #4caf50;
  background-color: #e8f5e9;
}

.loadprocss:hover p {
  color: #2e7d32;
}

/* Se você quiser que o ícone SVG mude de cor também */
.loadprocss:hover :deep(path),
.loadprocss:hover :deep(line) {
  stroke: #2e7d32 !important;
}

.loadprocss.dragging {
  border: 2px dashed #2e7d32;
  background-color: #e8f5e9;
  transform: scale(1.02);
}

.loadprocss.has-file {
  border: 2px solid #2e7d32;
  background-color: #e8f5e9;
}

.upload-text {
  display: flex;
  flex-direction: column;
  gap: 10px;
}

.file-format {
  font-size: 12px;
  color: #666;
  font-weight: normal;
}

.file-info {
  display: flex;
  flex-direction: column;
  gap: 10px;
  align-items: center;
}

.file-name {
  font-weight: bold;
  color: #2e7d32;
  word-break: break-word;
  max-width: 100%;
}

.file-size {
  font-size: 12px;
  color: #666;
}

.btn-remove-file {
  margin-top: 10px;
  padding: 5px 15px;
  background-color: rgba(139, 14, 14, 0.8);
  color: white;
  border: none;
  border-radius: 6px;
  cursor: pointer;
  font-size: 12px;
  transition: all 0.2s ease;
}

.btn-remove-file:hover {
  background-color: rgba(163, 8, 8, 0.8);
}

.message {
  margin: 15px auto;
  padding: 12px 20px;
  border-radius: 8px;
  font-size: 14px;
  font-weight: 500;
  max-width: 90%;
  animation: slideIn 0.3s ease-out;
}

.error-message {
  background-color: #fee;
  color: #c33;
  border: 1px solid #fcc;
}

.success-message {
  background-color: #efe;
  color: #3c3;
  border: 1px solid #cfc;
}

@keyframes slideIn {
  from {
    opacity: 0;
    transform: translateY(-10px);
  }
  to {
    opacity: 1;
    transform: translateY(0);
  }
}

.bnt {
  margin-top: 10px;
}

.bnt button,
.btn-upload {
  width: 200px;
  height: 50px;
  border-radius: 10px;
  border: none;
  background-color: rgba(19, 44, 13, 0.8);
  color: white;
  font-weight: bolder;
  font-size: 20px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.bnt button:hover:not(:disabled),
.btn-upload:hover:not(:disabled) {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.bnt button:disabled,
.btn-upload:disabled {
  opacity: 0.6;
  cursor: not-allowed;
  transform: none;
}

@media (max-width: 768px) {
  .tituloload h1 {
    font-size: 1.5rem;
    margin-top: 60px;
  }

  .iconexit {
    width: 40px;
    height: 40px;
    top: 15px;
    left: 15px;
  }

  .icone-load-process {
    margin-top: 20px;
    width: 120px;
    height: auto;
  }

  .loadprocss {
    height: auto;
    padding-bottom: 20px;
  }
}
</style>
