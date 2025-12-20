# Endpoint de Solicitação de Cadastro

## 📋 Descrição

Quando um usuário tenta fazer login com um email que não está cadastrado, o frontend automaticamente abre um modal para solicitar cadastro. Este endpoint recebe essas solicitações.

## 🔗 Rota

```
POST /api/v1/solicitar-cadastro
```

## 📝 Request Body

```json
{
  "email": "usuario@exemplo.com",
  "nome": "Nome Completo do Usuário", // Opcional
  "motivo": "Motivo da solicitação de acesso" // Opcional
}
```

## ✅ Response (200)

```json
{
  "message": "Solicitação de cadastro enviada com sucesso!",
  "data": {
    "id": 1,
    "email": "usuario@exemplo.com",
    "nome": "Nome Completo do Usuário",
    "motivo": "Motivo da solicitação",
    "status": "pendente",
    "created_at": "2024-01-01T00:00:00.000000Z"
  }
}
```

## ❌ Response (422 - Validação)

```json
{
  "message": "Os dados fornecidos são inválidos.",
  "errors": {
    "email": ["O campo email é obrigatório."]
  }
}
```

## 🔧 Implementação Sugerida

### 1. Migration

```php
Schema::create('solicitacoes_cadastro', function (Blueprint $table) {
    $table->id();
    $table->string('email')->unique();
    $table->string('nome')->nullable();
    $table->text('motivo')->nullable();
    $table->enum('status', ['pendente', 'aprovado', 'rejeitado'])->default('pendente');
    $table->timestamps();
});
```

### 2. Model

```php
// app/Models/SolicitacaoCadastro.php
class SolicitacaoCadastro extends Model
{
    protected $fillable = ['email', 'nome', 'motivo', 'status'];
}
```

### 3. Controller

```php
// app/Http/Controllers/Api/v1/SolicitacaoCadastroController.php
public function store(Request $request)
{
    $validated = $request->validate([
        'email' => 'required|email|unique:users,email|unique:solicitacoes_cadastro,email',
        'nome' => 'nullable|string|max:255',
        'motivo' => 'nullable|string|max:1000',
    ]);

    $solicitacao = SolicitacaoCadastro::create($validated);

    // Opcional: Enviar email de notificação para admin
    // Mail::to(config('mail.admin_email'))->send(new NovaSolicitacaoCadastro($solicitacao));

    return response()->json([
        'message' => 'Solicitação de cadastro enviada com sucesso!',
        'data' => $solicitacao
    ], 201);
}
```

### 4. Rota

```php
// routes/api.php
Route::post('/solicitar-cadastro', [SolicitacaoCadastroController::class, 'store']);
```

## 📧 Notificação (Opcional)

Você pode configurar para enviar um email ao administrador quando uma nova solicitação for recebida.

## ✅ Validações

- Email deve ser válido
- Email não pode já estar cadastrado (users ou solicitacoes_cadastro)
- Nome é opcional, mas se fornecido, máximo 255 caracteres
- Motivo é opcional, mas se fornecido, máximo 1000 caracteres




