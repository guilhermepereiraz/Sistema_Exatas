<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UploadPepsRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Ajuste conforme sua política de autorização
        // Por exemplo, apenas admin pode fazer upload
        return $this->user() && ($this->user()->isAdmin() || $this->user()->isExata());
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'file' => [
                'required',
                'file',
                'mimes:csv,xlsx,xls,xlsm',
                'max:10240', // 10MB máximo
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'file.required' => 'É necessário selecionar um arquivo.',
            'file.file' => 'O arquivo enviado é inválido.',
            'file.mimes' => 'O arquivo deve ser do tipo: CSV, XLSX, XLS ou XLSM.',
            'file.max' => 'O arquivo não pode ser maior que 10MB.',
        ];
    }
}

