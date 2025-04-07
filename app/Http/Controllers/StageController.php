<?php

namespace App\Http\Controllers;

use App\Http\Requests\StageRequest;
use App\Models\LegacyAcademicYearStage;
use App\Models\LegacySchoolAcademicYear;
use App\Models\LegacySchoolClass;
use App\Models\LegacySchoolClassStage;
use App\Process;
use Carbon\Carbon;
use iEducar\Support\Exceptions\Exception;
use Illuminate\Support\Facades\DB;

class StageController extends Controller
{
    private function getDate($date, $format)
    {
        try {
            return Carbon::createFromFormat($format, $date);
        } catch (Exception) {
            return null;
        }
    }

    public function update(StageRequest $request)
    {
        $stageDates = $request->get('etapas');
        $institution = $request->get('ref_cod_instituicao');
        $year = $request->get('ano');
        $stage = $request->get('ref_cod_modulo');
        $courses = $request->get('curso');
        $schools = $request->get('escola');
        if ($request->get('tipo') === 'schoolclass') {
            $schoolClasses = LegacySchoolClass::query()
                ->whereInstitution($institution)
                ->whereYearEq($year)
                ->when($schools, fn ($q) => $q->whereIn('ref_ref_cod_escola', $schools))
                ->when($courses, fn ($q) => $q->whereIn('ref_cod_curso', $courses))
                ->whereHas('schoolClassStages', function ($q) use ($stage) {
                    $q->when($stage, fn ($q) => $q->where('ref_cod_modulo', $stage));
                })
                ->whereHas('course', fn ($q) => $q->whereStandardCalendar(0))
                ->pluck('cod_turma');

            if ($schoolClasses->count() === 0) {
                return redirect()->route('stage.edit')->withInput()->with('error', 'Nenhuma turma encontrada com os filtros selecionados');
            }

            DB::beginTransaction();
            try {
                foreach ($stageDates as $stage => $data) {
                    $startDate = $data['data_inicio'] ?? null;
                    $endDate = $data['data_fim'] ?? null;
                    $updateData = array_filter([
                        'data_inicio' => $startDate ? $this->getDate($startDate, 'd/m/Y') : null,
                        'data_fim' => $endDate ? $this->getDate($endDate, 'd/m/Y') : null,
                        'dias_letivos' => $data['dias_letivos'] ?? null,
                    ]);
                    if (!empty($updateData)) {
                        LegacySchoolClassStage::whereIn('ref_cod_turma', $schoolClasses)
                            ->where('sequencial', $stage)
                            ->update($updateData);
                    }
                }
            } catch (Exception) {
                DB::rollBack();
                session()->flash('error', 'Atualização em lote não realizada.');
            }
            DB::commit();

            session()->flash('success', "Atualização em lote efetuada com sucesso em {$schoolClasses->count()} turmas.");
        } else {
            $schoolAcademicYears = LegacySchoolAcademicYear::query()
                ->whereHas('school', fn ($q) => $q->whereInstitution($institution))
                ->whereYearEq($year)
                ->when($schools, fn ($q, $schools) => $q->whereIn('ref_cod_escola', $schools))
                ->whereHas('academicYearStages', function ($q) use ($stage) {
                    $q->when($stage, fn ($q) => $q->where('ref_cod_modulo', $stage));
                })
                ->active()
                ->where('escola_ano_letivo.andamento', '<>', LegacySchoolAcademicYear::FINALIZED)
                ->get(['id', 'ref_cod_escola']);

            $schoolAcademicYearsIds = $schoolAcademicYears->pluck('id');

            if ($schoolAcademicYears->count() === 0) {
                return redirect()->route('stage.edit')->withInput()->with('error', 'Nenhuma escola encontrada com os filtros selecionados');
            }

            DB::beginTransaction();
            try {
                foreach ($stageDates as $stage => $data) {
                    $startDate = $data['data_inicio'] ?? null;
                    $endDate = $data['data_fim'] ?? null;
                    $updateData = array_filter([
                        'data_inicio' => $startDate ? $this->getDate($startDate, 'd/m/Y') : null,
                        'data_fim' => $endDate ? $this->getDate($endDate, 'd/m/Y') : null,
                        'dias_letivos' => $data['dias_letivos'] ?? null,
                    ]);
                    if (!empty($updateData)) {
                        LegacyAcademicYearStage::whereIn('escola_ano_letivo_id', $schoolAcademicYearsIds)
                            ->where('sequencial', $stage)
                            ->update($updateData);

                        LegacySchoolAcademicYear::whereIn('id', $schoolAcademicYearsIds)
                            ->update(['updated_at' => now()]);
                    }
                }
            } catch (Exception) {
                DB::rollBack();
                session()->flash('error', 'Atualização em lote não realizada.');
            }
            DB::commit();

            session()->flash('success', "Atualização em lote efetuada com sucesso em {$schoolAcademicYears->unique('ref_cod_escola')->count()} escolas.");
        }

        return redirect()->route('stage.edit');
    }

    public function edit()
    {
        $this->menu(Process::STAGE);
        $this->breadcrumb('Atualizar etapas da escola e turma', [
            url('/intranet/educar_configuracoes_index.php') => 'Configurações',
        ]);

        return view('stage.edit', ['user' => request()->user()]);
    }
}
