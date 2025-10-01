<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import IconUser from '@/components/icons/IconUser.vue' // Exemplo de caminho
import IconSeta from '@/components/icons/IconSeta.vue'

const menuAberto = ref(false)
const router = useRouter()


function irParaAdmin() {
  router.push({name: 'admin' })
}
</script>

<template>
  <div class="home_green">
    <div class="dados_principais">
      <img src="/Imagens/IconeExata.png" alt="Icone Exatas" />
      <div class="dados_usuarios">
        <IconUser class="icone-perfil" />

        <div class="info-texto">
          <h1>Guilherme Pereira(Teste)</h1>
          <h2>Administrador(Teste)</h2>
        </div>

        <IconSeta class="icone-seta" :class="{ 'virado-para-baixo': menuAberto}" @click="menuAberto = !menuAberto"/>
        <div v-if="menuAberto" class="div_info_inv">
          <h1>Meu Perfil</h1>
          <h1>Logout</h1>
        </div>
      </div>
    </div>
    <hr class="hr_cima" />
  </div>

  <div class="home_white">
    <div class="buttons">
      <button class="bnt_imob">Imobilização</button>
      <button class="bnt_admin" @click="irParaAdmin">Painel do Administrador</button>
    </div>

    <hr class="hr_baixo" />
  </div>
</template>

<style scoped>
.home_green {
  background-color: rgba(19, 44, 13, 1);
  width: 100%;
  height: 70vh;
}

.buttons {
  display: flex;
  justify-content: space-around;
  margin-top: 5%;
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

.bnt_admin:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
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

.icone-perfil {
  width: 40px; /* Ajuste o tamanho do ícone de perfil */
  height: 40px;
  color: white;
  margin-right: 10px;
  border: 4px solid white;
  border-radius: 50%;
}

.icone-seta {
  width: 30px; /* Ajuste o tamanho da seta */
  height: 20px;
  cursor: pointer;
  margin-top: 13%;
  margin-left: -25px;
  transition: transform 0.3s ease;
}

.icone-seta.virado-para-baixo {
  transform: rotate(180deg);
}

.hr_baixo {
  width: 95%;
  margin: auto;
  margin-top: 2%;
}
.hr_cima {
  width: 97%;
  margin: auto;
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
  position: absolute;   /* Posiciona em relação ao pai (.dados_usuarios) */
  top: 100%;            /* Começa exatamente onde o pai termina (embaixo) */
  right: 0;             /* Alinha à direita do pai */
  margin-top: 8px;      /* Cria um pequeno espaço entre o header e o menu */
  
  width: 250px;         /* Defina uma largura fixa para o menu */
  color: #333;          /* Cor escura para o texto */
  font-size: 16px;
  text-align: center;
  padding: 10px;
    border-radius: 0px 0px 20px 20px;
  box-shadow: 0 4px 12px rgba(0,0,0,0.15); /* Sombra suave */
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
</style>
