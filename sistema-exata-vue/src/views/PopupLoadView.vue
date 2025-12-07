<template>
  <div class="popoverlay" >
    <div class="popupview">
      <div class="tituloload">
        <IconExit class="iconexit" @click="$emit('close')"/>
        <h1>{{ title }}</h1>
      </div>

      <div class="loadprocss">
        <IconLoadProcess class="icone-load-process" />
        <p>O arquivo precisa estar no formato: .xls, .xlsx, .xlsm ou .csv</p>
      </div>

      <div class="bnt">
        <button>Salvar</button>
      </div>
    </div>
  </div>
</template>

<script setup>
import IconLoadProcess from '@/components/icons/IconLoadProcess.vue'
import IconExit from '@/components/icons/IconExit.vue'

const emit = defineEmits(['close'])

defineProps({
  title: {
    type: String,
    default: 'Carregar'
  }
})

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

.bnt button {
  margin-top: 10px;
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

.bnt button:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
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
