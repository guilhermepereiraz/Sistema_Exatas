<script setup>
import { useRouter } from 'vue-router'
import IconLogout from '@/components/icons/IconLogout.vue' // Certifique-se que o caminho está correto

const router = useRouter()

function fazerLogout() {
  localStorage.removeItem('auth_token')
  localStorage.removeItem('user_data')
  router.push({ name: 'login' })
}
</script>

<template>
  <div class="home_green">
    <div class="dados_principais">
      <img src="/Imagens/IconeExata.png" alt="Icone Exatas" />
      <div class="dados_usuarios">
        <IconLogout class="icone-logout" @click="fazerLogout" title="Sair do Sistema" />
      </div>
    </div>
  </div>
</template>

<style scoped>

.home_green {
  max-height: 12vh;
}

.dados_principais {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 2% 0px 0.5%;
}

.dados_principais img {
  width: 180px;
  transition: width 0.3s ease;
}

.dados_usuarios {
  position: relative;
  display: flex;
  align-items: center;
  color: white;
}

/* ======================================= */
/* ESTILO ADICIONADO PARA O ÍCONE DE LOGOUT */
/* ======================================= */
.icone-logout {
  width: 40px;
  height: 40px;
  color: white;
  cursor: pointer;
  transition:
    transform 0.3s ease,
    color 0.3s ease;
}

.icone-logout:hover {
  transform: scale(1.1);
  color: #ffdddd; /* Leve tom avermelhado no hover para indicar perigo/saída */
}
/* Fim do estilo adicionado */

/* =============================================== */
/* PARTE RESPONSIVA (Copiada do Header_no_HR)      */
/* =============================================== */
@media (max-width: 768px) {
  .dados_principais img {
    width: 150px;
  }
}

@media (max-width: 480px) {
  .dados_principais img {
    width: 120px;
  }
  .icone-logout {
    width: 35px;
    height: 35px;
  }
}
</style>
