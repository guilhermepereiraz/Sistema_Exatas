<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Reader\Csv;

class PepsImportService
{
    /**
     * Importa dados de PEPS de um arquivo CSV ou Excel
     * 
     * @param string $filePath Caminho completo do arquivo
     * @param string $extension Extensão do arquivo (csv, xlsx, xls, xlsm)
     * @return array Estatísticas da importação
     */
    public function import(string $filePath, string $extension): array
    {
        $imported = 0;
        $updated = 0;
        $errors = [];
        $totalProcessed = 0;

        try {
            // Carrega o arquivo usando PhpSpreadsheet
            $spreadsheet = $this->loadFile($filePath, $extension);
            $worksheet = $spreadsheet->getActiveSheet();
            $rows = $worksheet->toArray();

            // Remove o cabeçalho (primeira linha)
            $header = array_shift($rows);
            
            // Normaliza o cabeçalho (remove espaços, converte para lowercase)
            $header = array_map(function($col) {
                return strtolower(trim($col));
            }, $header);

            // Mapeia os índices das colunas
            $columnMap = $this->mapColumns($header);

            DB::beginTransaction();

            foreach ($rows as $rowIndex => $row) {
                $totalProcessed++;
                $lineNumber = $rowIndex + 2; // +2 porque removemos header e índice começa em 0

                try {
                    // Prepara os dados baseado no mapeamento
                    $data = $this->prepareData($row, $columnMap, $header);

                    if (empty($data)) {
                        continue; // Pula linhas vazias
                    }

                    // Verifica se já existe um PEPS com a mesma chave única
                    // Ajuste conforme sua migration (ex: número_peps, ano, etc)
                    $existing = DB::table('peps')
                        ->where('numero_peps', $data['numero_peps'] ?? null)
                        ->where('ano', $data['ano'] ?? null)
                        ->first();

                    if ($existing) {
                        // Atualiza registro existente
                        DB::table('peps')
                            ->where('id', $existing->id)
                            ->update(array_merge($data, [
                                'updated_at' => now(),
                            ]));
                        $updated++;
                    } else {
                        // Insere novo registro
                        DB::table('peps')->insert(array_merge($data, [
                            'created_at' => now(),
                            'updated_at' => now(),
                        ]));
                        $imported++;
                    }

                } catch (Exception $e) {
                    $errors[] = [
                        'line' => $lineNumber,
                        'error' => $e->getMessage(),
                        'data' => $row
                    ];
                    Log::warning("Erro ao processar linha {$lineNumber} do arquivo PEPS: " . $e->getMessage());
                }
            }

            DB::commit();

            return [
                'imported' => $imported,
                'updated' => $updated,
                'errors' => $errors,
                'total_processed' => $totalProcessed,
            ];

        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Erro ao importar PEPS: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Carrega o arquivo usando PhpSpreadsheet
     */
    private function loadFile(string $filePath, string $extension)
    {
        $extension = strtolower($extension);

        if ($extension === 'csv') {
            $reader = new Csv();
            $reader->setInputEncoding('UTF-8');
            $reader->setDelimiter(';'); // Ajuste conforme necessário (pode ser ',' ou ';')
            $reader->setEnclosure('"');
            $reader->setSheetIndex(0);
            return $reader->load($filePath);
        } else {
            return IOFactory::load($filePath);
        }
    }

    /**
     * Mapeia as colunas do cabeçalho para os campos do banco
     * Ajuste conforme as colunas da sua migration
     */
    private function mapColumns(array $header): array
    {
        $map = [];
        
        // Mapeamento flexível - ajuste conforme suas colunas
        $mappings = [
            'numero_peps' => ['numero_peps', 'numero', 'peps', 'nº peps', 'n peps'],
            'ano' => ['ano', 'year', 'exercicio'],
            'data_emissao' => ['data_emissao', 'data emissao', 'emissao', 'data'],
            'valor' => ['valor', 'valor_total', 'total'],
            'fornecedor' => ['fornecedor', 'fornecedor_nome', 'nome_fornecedor'],
            'descricao' => ['descricao', 'desc', 'objeto'],
            'status' => ['status', 'situacao', 'estado'],
            // Adicione mais mapeamentos conforme necessário
        ];

        foreach ($header as $index => $columnName) {
            $columnName = strtolower(trim($columnName));
            
            foreach ($mappings as $dbField => $possibleNames) {
                foreach ($possibleNames as $possibleName) {
                    if (strpos($columnName, $possibleName) !== false || $columnName === $possibleName) {
                        $map[$dbField] = $index;
                        break 2; // Sai dos dois loops
                    }
                }
            }
        }

        return $map;
    }

    /**
     * Prepara os dados para inserção no banco
     * Ajuste conforme os campos da sua migration
     */
    private function prepareData(array $row, array $columnMap, array $header): array
    {
        $data = [];

        // Mapeia os valores das colunas
        foreach ($columnMap as $dbField => $columnIndex) {
            if (isset($row[$columnIndex])) {
                $value = trim($row[$columnIndex]);
                
                // Converte valores vazios para null
                if ($value === '' || $value === null) {
                    $value = null;
                }

                // Conversões específicas por tipo
                switch ($dbField) {
                    case 'valor':
                        // Remove formatação de moeda e converte para float
                        $value = $this->parseCurrency($value);
                        break;
                    case 'data_emissao':
                    case 'data_vencimento':
                        // Converte data para formato YYYY-MM-DD
                        $value = $this->parseDate($value);
                        break;
                    case 'ano':
                        // Garante que seja inteiro
                        $value = is_numeric($value) ? (int)$value : null;
                        break;
                }

                $data[$dbField] = $value;
            }
        }

        return $data;
    }

    /**
     * Converte string de moeda para float
     */
    private function parseCurrency($value)
    {
        if (empty($value)) {
            return null;
        }

        // Remove caracteres não numéricos exceto vírgula e ponto
        $cleaned = preg_replace('/[^\d,.-]/', '', $value);
        
        // Substitui vírgula por ponto
        $cleaned = str_replace(',', '.', $cleaned);
        
        return is_numeric($cleaned) ? (float)$cleaned : null;
    }

    /**
     * Converte string de data para formato YYYY-MM-DD
     */
    private function parseDate($value)
    {
        if (empty($value)) {
            return null;
        }

        // Se já está no formato correto
        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $value)) {
            return $value;
        }

        // Tenta converter de DD/MM/YYYY
        if (preg_match('/^(\d{2})\/(\d{2})\/(\d{4})$/', $value, $matches)) {
            return $matches[3] . '-' . $matches[2] . '-' . $matches[1];
        }

        // Tenta usar DateTime
        try {
            $date = \DateTime::createFromFormat('d/m/Y', $value);
            if ($date) {
                return $date->format('Y-m-d');
            }
        } catch (Exception $e) {
            // Ignora erro
        }

        return null;
    }
}




