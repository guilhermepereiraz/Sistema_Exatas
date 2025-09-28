# Sistema Exatas - Vue.js Frontend

Sistema de login e autenticação desenvolvido com Vue.js 3, Vite e Axios.

## 🚀 Funcionalidades

- ✅ Login com validação de formulário
- ✅ Integração com API REST (http://127.0.0.1:8000/api/v1)
- ✅ Gerenciamento de estado de autenticação
- ✅ Tratamento de erros e estados de carregamento
- ✅ Interface responsiva e moderna
- ✅ Proteção de rotas
- ✅ Logout automático

## 🛠️ Tecnologias

- **Vue.js 3** - Framework JavaScript
- **Vite** - Build tool e dev server
- **Vue Router** - Roteamento
- **Axios** - Cliente HTTP
- **Pinia** - Gerenciamento de estado

## 📦 Instalação

1. Clone o repositório
2. Instale as dependências:

```bash
npm install
```

3. Configure as variáveis de ambiente:

```bash
# Copie o arquivo de exemplo
cp .env.example .env

# Edite o arquivo .env com suas configurações
```

## ⚙️ Configuração

### Variáveis de Ambiente

Crie um arquivo `.env` na raiz do projeto:

```env
# API Configuration
VITE_API_BASE_URL=http://127.0.0.1:8000/api/v1
VITE_APP_NAME=Sistema Exatas
VITE_APP_VERSION=1.0.0

# Development
VITE_NODE_ENV=development
```

### API Endpoints Esperados

O sistema espera que a API tenha os seguintes endpoints:

- `POST /api/v1/login` - Autenticação de usuário
- `POST /api/v1/password-recovery` - Recuperação de senha
- `POST /api/v1/reset-password` - Redefinição de senha

#### Formato de Resposta do Login

```json
{
  "token": "jwt_token_aqui",
  "user": {
    "id": 1,
    "email": "usuario@exemplo.com",
    "name": "Nome do Usuário"
  }
}
```

## 🚀 Executando o Projeto

### Desenvolvimento

```bash
npm run dev
```

### Build para Produção

```bash
npm run build
```

### Preview da Build

```bash
npm run preview
```

## 📁 Estrutura do Projeto

```
src/
├── components/          # Componentes reutilizáveis
├── services/           # Serviços de API
│   ├── api.js         # Configuração do Axios
│   └── auth.js        # Serviços de autenticação
├── views/             # Páginas/Vistas
│   ├── LoginView.vue  # Página de login
│   └── DashboardView.vue # Dashboard após login
├── router/            # Configuração de rotas
│   └── index.js
└── main.js           # Ponto de entrada da aplicação
```

## 🔐 Autenticação

### Fluxo de Login

1. Usuário preenche email e senha
2. Validação do formulário em tempo real
3. Requisição para API com credenciais
4. Armazenamento do token no localStorage
5. Redirecionamento para dashboard

### Proteção de Rotas

- Rotas protegidas verificam autenticação
- Redirecionamento automático para login se não autenticado
- Redirecionamento para dashboard se já autenticado

## 🎨 Interface

### Login

- Formulário com validação em tempo real
- Labels flutuantes
- Estados de carregamento
- Tratamento de erros
- Design responsivo

### Dashboard

- Interface simples após login
- Informações do usuário
- Botão de logout

## 🔧 Melhorias Implementadas

1. **Código Limpo**: Removidas variáveis não utilizadas
2. **Validação**: Formulário com validação em tempo real
3. **UX**: Estados de carregamento e feedback visual
4. **Segurança**: Interceptadores para tokens e tratamento de erros
5. **Estrutura**: Organização modular com serviços separados
6. **Configuração**: Variáveis de ambiente para diferentes ambientes

## 📱 Responsividade

O sistema é totalmente responsivo e funciona em:

- Desktop
- Tablet
- Mobile

## 🐛 Troubleshooting

### Erro de CORS

Se encontrar erros de CORS, configure o backend para aceitar requisições do frontend.

### Token Expirado

O sistema automaticamente redireciona para login quando o token expira.

### API Indisponível

Verifique se a API está rodando em `http://127.0.0.1:8000/api/v1`.

## 📄 Licença

Este projeto é privado e destinado ao uso interno da empresa.
