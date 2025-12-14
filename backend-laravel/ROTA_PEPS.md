# Configuração da Rota de Upload de PEPS

## ⚠️ Importante: Verificar a Rota

O endpoint de upload pode variar dependendo de como está configurado no seu `routes/api.php`. 

### Opção 1: Se a rota for `/peps/upload-peps`
O código do frontend já está configurado para usar este endpoint.

### Opção 2: Se a rota for `/peps/upload`
Você precisa alterar no arquivo `sistema-exata-vue/src/services/api.js`:

```javascript
// Alterar de:
return api.post('/peps/upload-peps', formData, {

// Para:
return api.post('/peps/upload', formData, {
```

## 📋 Exemplo de Rota no Laravel

No arquivo `routes/api.php`, dentro do grupo `v1`, adicione:

```php
Route::middleware('auth:api')->group(function () {
    // ... outras rotas ...
    
    // Upload de PEPs
    Route::post('/peps/upload-peps', [PEPController::class, 'uploadPEPs']);
    // OU
    // Route::post('/peps/upload', [PEPController::class, 'uploadPEPs']);
    
    // Outras rotas de PEPs
    Route::get('/peps', [PEPController::class, 'index']);
    Route::post('/peps', [PEPController::class, 'store']);
    Route::get('/peps/{pep}', [PEPController::class, 'show']);
    Route::put('/peps/{pep}', [PEPController::class, 'update']);
    Route::delete('/peps/{pep}', [PEPController::class, 'destroy']);
    Route::get('/peps/por-contrato/{codigoContrato}', [PEPController::class, 'porContrato']);
    Route::get('/peps/sem-contrato', [PEPController::class, 'semContrato']);
    Route::get('/peps/estatisticas', [PEPController::class, 'estatisticas']);
});
```

## ✅ Estrutura Esperada do Arquivo

O arquivo CSV/Excel deve ter as seguintes colunas (conforme a migration):

- `codigo_contrato` (opcional)
- `IEA`
- `valor_contabil`
- `descricao_projeto`
- `produto`
- `aplicacao`
- `programa`
- `natureza`
- `segmento`

## 🔧 Verificar o Import Class

Certifique-se de que a classe `PEPsImport` está implementada e mapeia corretamente as colunas do arquivo para os campos do banco de dados.

