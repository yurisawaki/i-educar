<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Arr;

class SchoolClassPeriodRequest extends FormRequest
{
    protected function prepareForValidation()
    {
        $this->merge([
            'escola' => $this->has('escola') ? Arr::flatten($this->get('escola')): null,
            'curso' => $this->has('curso') ? Arr::flatten($this->get('curso')) : null
        ]);
    }

    public function rules()
    {
        return [
            'ref_cod_instituicao' => ['required', 'integer'],
            'escola' => ['nullable', 'array'],
            'curso' => ['nullable', 'array'],
            'etapas.*.data_inicio' => ['nullable','date_format:d/m/Y'],
            'etapas.*.data_fim' => ['nullable', 'date_format:d/m/Y', 'after_or_equal:etapas.*.data_inicio'],
            'etapas.*.dias_letivos' => ['nullable', 'integer', 'min:1', 'max:366'],
        ];
    }

    public function attributes()
    {
        return [
            'ref_cod_instituicao' => 'Instituição',
            'escola' => 'Escola',
            'curso' => 'Curso',
            'etapas.*.data_inicio' => 'Data inicial da etapa',
            'etapas.*.data_fim' => 'Data final da etapa',
            'etapas.*.dias_letivos' => 'Dias letivos da etapa',
        ];
    }

    public function messages()
    {
        return [
            'etapas.*.data_inicial.date' => 'A :attribute deve ser uma data válida.',
            'etapas.*.data_fim.date' => 'A :attribute deve ser uma data válida.',
            'etapas.*.data_fim.date_format' => 'A :attribute deve estar no formato dia/mês/ano (ex: 01/01/2024).',
            'etapas.*.data_fim.after_or_equal' => 'A :attribute deve ser igual ou posterior à data inicial da etapa.',
            'etapas.*.dias_letivos.integer' => 'O campo :attribute deve ser um número inteiro.',
            'etapas.*.dias_letivos.min' => 'O campo :attribute deve ser no mínimo 1.',
            'etapas.*.dias_letivos.max' => 'O campo :attribute deve ser no máximo 366.',
        ];
    }

    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $etapas = $this->input('etapas', []);
            $allEmpty = collect($etapas)->every(function ($etapa) {
                return empty($etapa['data_inicio']) &&
                    empty($etapa['data_fim']) &&
                    empty($etapa['dias_letivos']);
            });

            if ($allEmpty) {
                $validator->errors()->add('etapas', 'Nenhuma informação a ser atualizado nas etapas');
            }
        });
    }
}
