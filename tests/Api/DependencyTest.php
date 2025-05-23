<?php

namespace Tests\Api;

use App\Models\LegacyEnrollment;
use App_Model_MatriculaSituacao;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class DependencyTest extends TestCase
{
    use DatabaseTransactions;
    use DiarioApiFakeDataTestTrait;
    use DiarioApiRequestTestTrait;

    /** @var LegacyEnrollment */
    private $enrollment;

    protected function setUp(): void
    {
        parent::setUp();
        $this->enrollment = $this->getPromotionFromAverageAndAttendanceWithoutRetake();
    }

    public function test_with_dependency_should_returns_approved_with_dependency()
    {
        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 4);
        $this->createDisciplines($schoolClass, 2);

        $evaluationRule = $schoolClass->grade->evaluationRules()->first();
        $evaluationRule->qtd_disciplinas_dependencia = 2;
        $evaluationRule->save();

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 4.3,
            2 => 5.4,
            3 => 6.7,
            4 => 3,
        ];

        $absence = [
            1 => 3,
            2 => 3,
            3 => 3,
            4 => 3,
        ];

        foreach ($disciplines as $discipline) {
            $this->postAbsenceForStages($absence, $discipline);
            $response = $this->postScoreForStages($score, $discipline);

            self::assertEquals('Retido', $response->situacao);
        }

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO_COM_DEPENDENCIA, $registration->refresh()->aprovado);
    }

    /**
     * Quando o número de disciplinas em dependencia é maior do que o configurado, incluindo reprovações por falta,
     * deve retornar Reprovado
     */
    public function test_should_returns_reproved_when_dependencies_is_over()
    {
        $this->enrollment = $this->getProgressionWithAverageCalculationWeightedRecovery();

        $schoolClass = $this->enrollment->schoolClass;
        $school = $schoolClass->school;

        $this->createStages($school, 2);
        $this->createDisciplines($schoolClass, 2);

        $evaluationRule = $schoolClass->grade->evaluationRules()->first();
        $evaluationRule->qtd_disciplinas_dependencia = 1;
        $evaluationRule->save();

        $disciplines = $schoolClass->disciplines;

        $score = [
            1 => 4,
            2 => 1,
        ];
        $absence = [
            1 => 1,
            2 => 3,
        ];

        $this->postAbsenceForStages($absence, $disciplines[0]);
        $this->postScoreForStages($score, $disciplines[0]);
        $response = $this->postExam($this->enrollment, $disciplines[0]->id, 'Rc', '1');
        self::assertEquals('Retido', $response->situacao);

        $score = [
            1 => 10,
            2 => 10,
        ];
        $absence = [
            1 => 100,
            2 => 3,
        ];

        $this->postAbsenceForStages($absence, $disciplines[1]);
        $response = $this->postScoreForStages($score, $disciplines[1]);
        self::assertEquals('Reprovado por faltas', $response->situacao);

        $registration = $this->enrollment->registration;
        self::assertEquals(App_Model_MatriculaSituacao::APROVADO_COM_DEPENDENCIA, $registration->refresh()->aprovado);
    }
}
