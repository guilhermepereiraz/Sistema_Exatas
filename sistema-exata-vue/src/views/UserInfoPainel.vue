<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import { authService } from '../services/auth'
import IconLogout from '@/components/icons/IconLogout.vue' // Exemplo de caminho
import HeaderLogout from './Header_Logout.vue'
</script>

<template>
  <HeaderLogout />

  <div class="info-container">
    <div class="info-header">
      <h1 class="info_userh1">Informações do Usuário</h1>
      <button class="info_button">Editar Informações</button>
    </div>

    <hr class="hr_cima" />

    <div class="user-details">
      <div class="detail-item item-nome">
        <h1>Nome Completo:</h1>
        <h2>Guilherme(Teste)</h2>
      </div>

      <div class="detail-item item-criado">
        <h1>Usuário Criado em:</h1>
        <h2>24 de Fevereiro de 2025</h2>
      </div>

      <div class="detail-item item-divisao">
        <h1>Divisão:</h1>
        <h2>Santana - Mirante</h2>
      </div>

      <div class="detail-item item-avatar">
        <img src="/Imagens/3d_avatar_21.png" alt="Avatar do usuário" class="avatar" />
      </div>

      <div class="detail-item item-email">
        <h1>E-mail:</h1>
        <h2>Guilherme@exatas.com.br(Teste)</h2>
      </div>

      <div class="detail-item item-senha">
        <h1>Senha:</h1>
        <button class="alt_bnt">Alterar Senha</button>
      </div>
    </div>
  </div>
</template>

<style setup>
.home_green {
  background-color: rgba(19, 44, 13, 1);
  width: 100%;
  height: 13vh;
}

.dados_principais {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 0 1.5%;
}

.icone-logout {
  width: 40px; /* Ajuste o tamanho do ícone de perfil */
  height: 40px;
  color: white;
  margin-right: 10px;
  cursor: pointer;
}

img {
  width: 250px;
}

.info-container {
  width: 75%;
  min-width: 620px;
  margin: 5% auto;
  height: auto;
  border-radius: 20px;
  border: 1px solid rgba(180, 180, 180, 0.8);
  box-shadow: -3px 3px 2px 2px rgba(197, 197, 197, 0.726);
  background-color: #fafcff;
}

.info-header {
  display: flex;
  justify-content: space-between;
  align-items: center;
  padding: 20px 55px 10px 55px;
}

.info_userh1 {
  font-size: 25px;
  color: rgba(0, 0, 0, 0.8);
}

.hr_cima {
  width: 92%;
  margin: auto;
  margin-top: 5px;
}

.info_button {
  border: 1px solid black;
  padding: 10px;
  font-size: 20px;
  font-weight: bolder;
  color: white;
  background-color: rgba(19, 44, 13, 0.8);
  border-radius: 10px;
  transition: all 0.3s ease;
  cursor: pointer;
}

.info_button:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

.user-details {
  padding: 60px 55px;
  display: grid;
  grid-template-columns: minmax(300px, 2fr) minmax(180px, 1fr);
  grid-template-areas:
    'nome    criado'
    'divisao avatar'
    'email   avatar'
    'senha   avatar';
  gap: 2.5rem 4rem;
}

.item-nome {
  grid-area: nome;
}
.item-criado {
  grid-area: criado;
}
.item-divisao {
  grid-area: divisao;
}
.item-email {
  grid-area: email;
}
.item-senha {
  grid-area: senha;
}
.item-avatar {
  grid-area: avatar;
  display: flex;
  align-items: center;
  justify-content: center;
}

.detail-item h1 {
  font-size: 20px;
  color: rgba(0, 0, 0, 0.6);
  font-weight: bold;
}

.detail-item h2 {
  font-size: 21px;
  color: #000000;
  word-break: break;
}

.password-btn {
  border: 1px solid #666;
  padding: 8px 16px;
  font-size: 14px;
  font-weight: bold;
  color: #333;
  background-color: transparent;
  border-radius: 8px;
  cursor: pointer;
  transition: all 0.2s ease;
}

.password-btn:hover {
  background-color: #f0f0f0;
  border-color: #333;
}

.avatar {
  width: 100%;
  max-width: 160px;
  height: auto;
  border-radius: 12px;
  object-fit: cover;
}

.user_passowrd {
  padding: 0px 65px;
}

.user_passowrd h1 {
  font-size: 20px;
  color: rgba(0, 0, 0, 0.6);
  font-weight: bold;
}

.user_passowrd h2 {
  font-size: 21px;
  color: #000000;
}

.alt_bnt {
  border: 1px solid black;
  padding: 10px;
  margin-top: 10px;
  font-size: 20px;
  font-weight: bolder;
  color: white;
  background-color: rgba(19, 44, 13, 0.8);
  border-radius: 10px;
  cursor: pointer;
  transition: all 0.3s ease;
}

.alt_bnt:hover {
  background-color: #0f2410;
  transform: translateY(-2px);
  box-shadow: 0 4px 12px rgba(19, 44, 13, 0.3);
}

@media (max-width: 992px) {
  .info-container {
    width: 90%;
  }
  .user-details {
    gap: 2rem 2.5rem;
    padding: 40px 30px;
  }
  .avatar {
    max-width: 120px;
  }
  .info-header {
    padding: 20px 30px 10px 30px;
  }
  .info_userh1,
  .detail-item h1,
  .detail-item h2 {
    font-size: 95%;
  }
}

@media (max-width: 768px) {
  .info-container {
    width: 95%;
    /* REMOVEMOS A LARGURA MÍNIMA PARA O LAYOUT DE CELULAR FUNCIONAR */
    min-width: 0;
    margin: 2rem auto;
    padding-bottom: 2rem;
  }
  .info-header {
    flex-direction: column;
    align-items: stretch;
    gap: 1rem;
    padding: 20px;
    text-align: center;
  }
  .user-details {
    grid-template-columns: 1fr;
    grid-template-areas:
      'avatar'
      'nome'
      'email'
      'criado'
      'divisao'
      'senha';
    gap: 1.5rem;
    padding: 20px;
    text-align: center;
  }
  .avatar {
    max-width: 140px;
  }
  .item-avatar {
    justify-self: center;
    margin-bottom: 1rem;
  }
  .detail-item h1 {
    font-size: 16px;
  }
  .detail-item h2 {
    font-size: 18px;
  }
  .alt_bnt,
  .info_button {
    width: 100%;
    font-size: 18px;
  }
}
</style>
