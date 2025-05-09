<?php

namespace App\Models\Builders;

use Illuminate\Database\Eloquent\Builder;

class EmployeeBuilder extends LegacyBuilder
{
    /**
     * Filtra por Instituição
     */
    public function whereInstitution(int $institution): self
    {
        return $this->where('ref_cod_instituicao', $institution);
    }

    /**
     * Filtra por servidor
     *
     *
     * @return $this
     */
    public function whereEmployee(int $id): self
    {
        return $this->where('cod_servidor', $id);
    }

    /**
     * Filtra por escola
     *
     *
     * @return $this
     */
    public function whereSchoolingDegree(int $schoolingDegree): self
    {
        return $this->where('ref_idesco', $schoolingDegree);
    }

    /**
     * Filtra por Carga Horária
     *
     *
     * @return $this
     */
    public function whereWorkload(int $workload): self
    {
        return $this->where('carga_horaria', $workload);
    }

    /**
     * Filtra por Nome
     *
     *
     * @return $this
     */
    public function whereName(string $name): self
    {
        return $this->whereHas('person', function (Builder $q) use ($name) {
            $q->whereRaw('unaccent(nome) ~* unaccent(?)', addcslashes($name, '[]()\''));
        });
    }

    /**
     * Filtra por escola
     *
     *
     * @return $this
     */
    public function whereSchool(int $school): self
    {
        return $this->whereHas('employeeAllocations', function (Builder $q) use ($school) {
            $q->where('ativo', 1);
            $q->when($school, fn ($q) => $q->where('ref_cod_escola', $school));
        });
    }

    /**
     * Filtra somente servidor com alocação
     *
     * @return $this
     */
    public function whereAllocation(bool $withNotAllocation, ?int $school = null, ?int $year = null): self
    {
        $this->where(function (Builder $q) use ($school, $year, $withNotAllocation) {
            $q->whereHas('employeeAllocations', function (Builder $q) use ($school, $year) {
                $q->where('ativo', 1);
                $q->when($school, fn ($q) => $q->where('ref_cod_escola', $school));
                $q->when($year, fn ($q) => $q->where('ano', $year));
            });

            if ($withNotAllocation) {
                $q->orWhereDoesntHave('employeeAllocations', function (Builder $q) use ($school, $year) {
                    $q->where('ativo', 1);
                    $q->when($school, fn ($q) => $q->where('ref_cod_escola', $school));
                    $q->when($year, fn ($q) => $q->where('ano', $year));
                });
            }
        });

        return $this;
    }

    /**
     * Filtra por função
     *
     *
     * @return $this
     */
    public function whereRole(string $role): self
    {
        return $this->whereHas('employeeRoles', function (Builder $q) use ($role) {
            $q->whereRaw('matricula ~* ?', $role);
        });
    }

    /**
     * Filtra por servidores ativos
     */
    public function active(): self
    {
        return $this->where('servidor.ativo', 1);
    }

    /**
     * Filtra por servidores que são professores
     */
    public function professor(bool $onlyTeacher = true): self
    {
        return $this->join('pmieducar.servidor_funcao', 'servidor_funcao.ref_cod_servidor', '=', 'servidor.cod_servidor')
            ->join('pmieducar.funcao', 'funcao.cod_funcao', '=', 'servidor_funcao.ref_cod_funcao')
            ->where('funcao.professor', (int) $onlyTeacher);
    }

    /**
     * Filtra servidores alocados no último ano
     */
    public function lastYear(): self
    {
        return $this->join('pmieducar.servidor_alocacao', 'servidor.cod_servidor', '=', 'servidor_alocacao.ref_cod_servidor')
            ->where('servidor_alocacao.ano', date('Y') - 1);
    }

    /**
     * Filtra servidores alocados no ano corrente
     */
    public function currentYear(): self
    {
        return $this->join('pmieducar.servidor_alocacao', 'servidor.cod_servidor', '=', 'servidor_alocacao.ref_cod_servidor')
            ->where('servidor_alocacao.ano', date('Y'));
    }
}
