<script setup>
import { ref } from 'vue'
import IconUser from '@/components/icons/IconUser.vue'
import IconSeta from '@/components/icons/IconSeta.vue'
import { useRouter } from 'vue-router'

const router = useRouter()
const menuAberto = ref(false)

function irParaPerfil() {
    router.push ({ name: 'perfil'})
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
          <h1 @click="irParaPerfil">Meu Perfil</h1>
          <h1>Logout</h1>
        </div>
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
  padding: 0 2% 0px 0.5%; /* Usando porcentagem para o padding lateral */
}

.dados_principais img {
  width: 180px; /* Tamanho inicial do logo */
  transition: width 0.3s ease; /* Transição suave para a mudança de tamanho */
}

.icone-perfil {
  width: 45px;
  height: 45px;
  color: white;
  margin-right: 15px;
  border: 3px solid white;
  border-radius: 50%;
  flex-shrink: 0; /* MUITO IMPORTANTE: Impede que o ícone seja espremido e fique oval */
}

.icone-seta {
  width: 25px;
  height: 25px;
  cursor: pointer;
  margin-left: 10px;
  transition: transform 0.3s ease;
  flex-shrink: 0; /* Impede que a seta seja espremida */
}

.icone-seta.virado-para-baixo {
  transform: rotate(180deg);
}

.dados_usuarios {
  position: relative;
  display: flex;
  align-items: center;
  color: white;
}

.info-texto {
  text-align: left;
  line-height: 1.2; /* Melhora o espaçamento entre as linhas */
}

.dados_usuarios h1 {
  font-size: 18px;
  margin: 0; /* Removido o padding para melhor controle */
}

.dados_usuarios h2 {
  font-size: 14px;
  font-weight: 300; /* Lighter é mais fino que normal */
  margin: 0;
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


/* =============================================== */
/* PARTE RESPONSIVA - ADICIONE ISSO NO SEU CÓDIGO  */
/* =============================================== */

/* Para telas com largura máxima de 768px (tablets e celulares) */
@media (max-width: 768px) {
  .dados_principais img {
    width: 150px; /* Diminui um pouco o logo */
  }

  .dados_usuarios h1 {
    font-size: 16px; /* Diminui a fonte do nome */
  }

  .dados_usuarios h2 {
    font-size: 12px; /* Diminui a fonte do cargo */
  }

  .icone-perfil {
    width: 40px;   /* Diminui o ícone do usuário */
    height: 40px;
    margin-right: 10px; /* Diminui a margem */
  }
}

/* Para telas com largura máxima de 480px (celulares pequenos) */
@media (max-width: 480px) {
  .dados_principais img {
    width: 120px; /* Diminui ainda mais o logo */
  }

  /* Para economizar espaço, vamos esconder o cargo do usuário */
  .dados_usuarios h2 {
    display: none; 
  }

  .dados_usuarios h1 {
    font-size: 14px; /* Fonte ainda menor para o nome */
  }

  .icone-perfil {
    width: 35px;
    height: 35px;
    border-width: 2px;
  }

  /* Ajusta o menu dropdown para não ficar tão largo na tela pequena */
  .div_info_inv {
      width: 160px;
  }
}
</style>