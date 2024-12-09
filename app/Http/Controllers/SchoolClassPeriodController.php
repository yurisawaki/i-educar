<?php

namespace App\Http\Controllers;

use App\Http\Requests\SchoolClassPeriodRequest;
use App\Models\LegacySchoolClass;
use App\Models\LegacySchoolClassStage;
use App\Process;
use iEducar\Support\Exceptions\Exception;
use Illuminate\Support\Facades\DB;

class SchoolClassPeriodController extends Controller
{
    public function update(SchoolClassPeriodRequest $request)
    {
        $schoolClasses = LegacySchoolClass::query()
            ->whereInstitution($request->get('ref_cod_instituicao'))
            ->whereYearEq($request->get('ano'))
            ->when($request->get('escola'), function ($q, $schools) {
                $q->whereIn('ref_ref_cod_escola', $schools);
            })
            ->when($request->get('curso'), function ($q, $courses) {
                $q->whereIn('ref_cod_curso', $courses);
            })
            ->pluck('cod_turma');

        if ($schoolClasses->count() === 0) {
            return redirect()->route('school-class-period.edit')->withInput()->with('error', 'Nenhuma turma encontrada com os filtros selecionados');
        }

        foreach ($request->get('etapas') as $stage => $data) {
            try {
                DB::beginTransaction();

                $updateData = array_filter([
                    'data_inicio' => $data['data_inicio'] ?? null,
                    'data_fim' => $data['data_fim'] ?? null,
                    'dias_letivos' => $data['dias_letivos'] ?? null,
                ]);

                if (!empty($updateData)) {
                    LegacySchoolClassStage::whereIn('ref_cod_turma', $schoolClasses)
                        ->where('sequencial', $stage)
                        ->update($updateData);

                    DB::commit();
                    session()->flash('success', "Atualização em lote efetuada com sucesso em {$schoolClasses->count()} turmas.");
                }
            } catch (Exception) {
                DB::rollBack();
                session()->flash('error', 'Atualização em lote não realizada.');
            }
        }

        return redirect()->route('schoolclass-period.edit');
    }

    public function edit()
    {
        $this->menu(Process::SCHOOLCLASS_PERIOD);
        $this->breadcrumb('Atualizar etapas da turma', [
            url('/intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        return view('schoolclass-period.edit', ['user' => request()->user()]);
    }
}
