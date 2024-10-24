<?php

namespace Api;

use App\Events\StudentCreated;
use Database\Factories\LegacyIndividualFactory;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Event;
use Tests\Api\DiarioApiRequestTestTrait;
use Tests\TestCase;

class StudentCreatedEventTest extends TestCase
{
    use DatabaseTransactions;
    use DiarioApiRequestTestTrait;

    public function testStudentCreatedEvent()
    {
        Event::fake();

        $studentIndividual = LegacyIndividualFactory::new()->create();
        $guardianIndividual = LegacyIndividualFactory::new()->create();

        $data = [
            'oper' => 'post',
            'resource' => 'aluno',
            'pessoa_id' => $studentIndividual->getKey(),
            'mae_id' => $guardianIndividual->getKey(),
            'tipo_responsavel' => 'mae',
            'alfabetizado' => 'checked',
            'material' => 'A',
            'tipo_transporte' => 'nenhum',
            'deficiencias' => [],
            'transtornos' => [],
        ];

        $response = $this->getResource('/module/Api/Aluno', $data);
        $response->assertStatus(200);

        $studentId = $response->json('id');

        Event::assertDispatched(StudentCreated::class, function ($e) use ($studentId) {
            return $e->student->getKey() === $studentId;
        });
    }
}
