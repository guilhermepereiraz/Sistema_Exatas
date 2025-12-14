# Instruções para Implementação de Upload de PEPS

## 📋 Arquivos Criados

Os seguintes arquivos foram criados na pasta `backend-laravel/`:

1. **PepsController.php** - Controller para gerenciar upload e listagem de PEPS
2. **PepsImportService.php** - Service para processar arquivos CSV/Excel
3. **UploadPepsRequest.php** - Request validation para upload
4. **routes-peps.php** - Exemplo de rotas (copiar para routes/api.php)

## 🚀 Passos para Implementação no Backend Laravel

### 1. Instalar Dependência PhpSpreadsheet

```bash
composer require phpoffice/phpspreadsheet
```

### 2. Copiar Arquivos para o Projeto Laravel

Copie os arquivos para as seguintes localizações:

- `PepsController.php` → `app/Http/Controllers/Api/v1/PepsController.php`
- `PepsImportService.php` → `app/Services/PepsImportService.php`
- `UploadPepsRequest.php` → `app/Http/Requests/UploadPepsRequest.php`

### 3. Adicionar Rotas

No arquivo `routes/api.php`, dentro do grupo `v1`, adicione:

```php
Route::middleware('auth:api')->group(function () {
    // Upload de arquivo PEPS
    Route::post('/peps/upload', [PepsController::class, 'upload']);
    
    // Listar PEPS com paginação
    Route::get('/peps', [PepsController::class, 'index']);
});
```

### 4. Ajustar o Mapeamento de Colunas

No arquivo `PepsImportService.php`, ajuste o método `mapColumns()` conforme as colunas da sua migration de PEPS:

```php
private function mapColumns(array $header): array
{
    // Ajuste os mapeamentos conforme suas colunas
    $mappings = [
        'numero_peps' => ['numero_peps', 'numero', 'peps'],
        'ano' => ['ano', 'year', 'exercicio'],
        // Adicione mais campos conforme sua migration
    ];
    // ...
}
```

### 5. Ajustar o Método prepareData()

Ajuste o método `prepareData()` para mapear corretamente os campos da sua tabela `peps`:

```php
private function prepareData(array $row, array $columnMap, array $header): array
{
    // Ajuste conforme os campos da sua migration
    // Exemplo:
    $data = [
        'numero_peps' => $row[$columnMap['numero_peps']] ?? null,
        'ano' => (int)($row[$columnMap['ano']] ?? date('Y')),
        // Adicione todos os campos da sua migration
    ];
    
    return $data;
}
```

### 6. Ajustar Chave Única para Update

No método `import()`, ajuste a verificação de registro existente:

```php
$existing = DB::table('peps')
    ->where('numero_peps', $data['numero_peps'] ?? null)
    ->where('ano', $data['ano'] ?? null)
    ->first();
```

Ajuste conforme a chave única da sua tabela.

### 7. Configurar Storage

Certifique-se de que o storage está configurado:

```bash
php artisan storage:link
```

### 8. Ajustar Delimitador CSV (se necessário)

No método `loadFile()`, ajuste o delimitador do CSV:

```php
$reader->setDelimiter(';'); // ou ',' conforme seu arquivo
```

## 📝 Estrutura Esperada do Arquivo

O arquivo CSV/Excel deve ter um cabeçalho na primeira linha com os nomes das colunas.

Exemplo de cabeçalho CSV:
```
numero_peps;ano;data_emissao;valor;fornecedor;descricao;status
```

## 🔧 Ajustes Necessários

1. **Campos da Migration**: Ajuste os campos no `mapColumns()` e `prepareData()` conforme sua migration
2. **Chave Única**: Defina qual campo(s) identificam um PEPS único para update
3. **Delimitador CSV**: Ajuste se usar vírgula ao invés de ponto e vírgula
4. **Validações**: Adicione validações específicas conforme necessário

## ✅ Frontend

O frontend já está preparado:
- Função `pepsApi.uploadPeps()` no `api.js`
- PopupLoadView atualizado com upload
- Integração no AdminPanelView

## 🧪 Teste

1. Acesse o painel admin
2. Clique em "Atualizar PEPS"
3. Selecione ou arraste um arquivo CSV/Excel
4. Clique em "Enviar Arquivo"
5. Verifique os logs e resposta da API

