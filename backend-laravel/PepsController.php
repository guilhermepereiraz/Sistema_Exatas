<?php

namespace App\Http\Controllers\Api\v1;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Requests\UploadPepsRequest;
use App\Services\PepsImportService;
use Exception;

class PepsController extends Controller
{
    protected $pepsImportService;

    public function __construct(PepsImportService $pepsImportService)
    {
        $this->pepsImportService = $pepsImportService;
    }

    /**
     * Upload e processamento de arquivo PEPS (CSV ou Excel)
     */
    public function upload(Request $request): JsonResponse
    {
        try {
            // Validação do arquivo
            $request->validate([
                'file' => 'required|file|mimes:csv,xlsx,xls,xlsm|max:10240', // Max 10MB
            ]);

            $file = $request->file('file');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Salva o arquivo temporariamente
            $path = $file->store('temp/peps', 'local');
            $fullPath = storage_path('app/' . $path);

            try {
                // Processa o arquivo
                $result = $this->pepsImportService->import($fullPath, $extension);

                // Remove o arquivo temporário após processamento
                Storage::disk('local')->delete($path);

                return response()->json([
                    'message' => 'Arquivo PEPS processado com sucesso!',
                    'data' => [
                        'imported' => $result['imported'],
                        'updated' => $result['updated'],
                        'errors' => $result['errors'],
                        'total_processed' => $result['total_processed'],
                    ]
                ], 200);

            } catch (Exception $e) {
                // Remove o arquivo temporário em caso de erro
                Storage::disk('local')->delete($path);
                
                Log::error('Erro ao processar arquivo PEPS: ' . $e->getMessage());
                
                return response()->json([
                    'message' => 'Erro ao processar arquivo PEPS',
                    'error' => $e->getMessage()
                ], 500);
            }

        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Erro de validação',
                'errors' => $e->errors()
            ], 422);
        } catch (Exception $e) {
            Log::error('Erro no upload de PEPS: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erro ao fazer upload do arquivo',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Lista todos os PEPS com paginação
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $perPage = $request->get('limit', 10);
            $page = $request->get('page', 1);

            $peps = DB::table('peps')
                ->orderBy('created_at', 'desc')
                ->paginate($perPage, ['*'], 'page', $page);

            return response()->json([
                'data' => $peps->items(),
                'meta' => [
                    'current_page' => $peps->currentPage(),
                    'last_page' => $peps->lastPage(),
                    'per_page' => $peps->perPage(),
                    'total' => $peps->total(),
                ]
            ], 200);

        } catch (Exception $e) {
            Log::error('Erro ao listar PEPS: ' . $e->getMessage());
            
            return response()->json([
                'message' => 'Erro ao listar PEPS',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}

