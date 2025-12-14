<script setup>
import { useRouter } from 'vue-router'
import HeaderPrincipal from './Header_Principal.vue'

const router = useRouter()

function irParaAdmin() {
  router.push({ name: 'admin' })
}

function irParaContrato () {
  router.push({name: 'contrato'})
}
</script>

<template>
  <HeaderPrincipal />
  <div class="home">
    <img src="/Imagens/Imagem_admin_painel.png" alt="" class="img_princ" />
  </div>
  <div class="home_white">
    <div class="buttons">
      <button class="bnt_imob" @click="irParaContrato">Imobilização</button>
      <button class="bnt_admin" @click="irParaAdmin">Painel do Administrador</button>
    </div>

    <hr class="hr_baixo" />
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
</style>
