<?php

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\CountryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\DisciplineController;
use App\Http\Controllers\Api\DistrictController;
use App\Http\Controllers\Api\EmployeeWithdrawalController;
use App\Http\Controllers\Api\GradeController;
use App\Http\Controllers\Api\InstitutionController;
use App\Http\Controllers\Api\ItinerarioApiController;
use App\Http\Controllers\Api\People\LegacyDeficiencyController;
use App\Http\Controllers\Api\PeriodController;
use App\Http\Controllers\Api\RegistrationController;
use App\Http\Controllers\Api\ReligionController;
use App\Http\Controllers\Api\SchoolClassController;
use App\Http\Controllers\Api\SchoolController;
use App\Http\Controllers\Api\SituationController;
use App\Http\Controllers\Api\StageController;
use App\Http\Controllers\Api\StateController;
use App\Http\Controllers\Api\PontoTransporteApiController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\UsuarioTransporteApiController;
use App\Http\Controllers\Api\RotaTransporteApiController;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\ConfiguracaoController;
use App\Http\Controllers\InPontoTransporteApiController;
use App\Http\Controllers\Api\RotaTrajetoController;
use App\Http\Controllers\Api\OutRotaTrajetoApiController;



/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(
    [
        'middleware' => 'auth:sanctum',
    ],
    static fn() => Route::apiResources([
        'country' => CountryController::class,
        'state' => StateController::class,
        'district' => DistrictController::class,
        'city' => CityController::class,
        'religion' => ReligionController::class,
        'person/deficiency' => LegacyDeficiencyController::class,
    ])
);

Route::get('version', 'Api\\VersionController@version');

Route::get('/postal-code/{postalCode}', 'Api\PostalCodeController@search');

Route::post('/students/{student}/rotate-picture', 'Api\StudentRotatePictureController@rotate');
Route::group([
    'middleware' => 'api:rest',
], function () {
    Route::put('/students/{student}/update-state-registration', 'Api\StudentController@updateStateRegistration');
});

Route::get('/school-class/calendars', 'Api\SchoolClassController@getCalendars');
Route::get('/school-class/stages/{schoolClass}', 'Api\SchoolClassController@getStages');

Route::delete('/employee-withdrawal/{id}', [EmployeeWithdrawalController::class, 'remove']);

Route::group(['middleware' => 'auth:sanctum', 'namespace' => 'Api'], static function () {
    Route::resource('institution', InstitutionController::class)->only(['index']);
    Route::resource('school', SchoolController::class)->only(['index']);
    Route::resource('course', CourseController::class)->only(['index']);
    Route::resource('grade', GradeController::class)->only(['index']);
    Route::resource('school-class', SchoolClassController::class)->only(['index']);
    Route::resource('registration', RegistrationController::class)->only(['index']);
    Route::resource('situation', SituationController::class)->only(['index']);
    Route::resource('period', PeriodController::class)->only(['index']);
    Route::resource('discipline', DisciplineController::class)->only(['index']);
    Route::resource('stage', StageController::class)->only(['index']);
});

Route::group(['prefix' => 'resource', 'as' => 'api.resource.', 'namespace' => 'Api\Resource'], static function () {
    Route::get('course', 'Course\ResourceCourseController@index')->name('course');
    Route::get('grade', 'Grade\ResourceGradeController@index')->name('grade');
    Route::get('school-academic-year', 'SchoolAcademicYear\ResourceSchoolAcademicYearController@index')->name('school-academic-year');
    Route::get('school', 'School\ResourceSchoolController@index')->name('school');
    Route::get('school-class', 'SchoolClass\ResourceSchoolClassController@index')->name('school-class');
    Route::get('evaluation-rule', 'EvaluationRule\ResourceEvaluationRuleController@index')->name('evaluation-rule');
    Route::get('discipline', 'Discipline\ResourceDisciplineController@index')->name('discipline');
    Route::get('country', 'Country\ResourceCountryController@index')->name('country');
});





Route::post('/login', function (Request $request) {
    $matricula = $request->input('matricula');
    $senha = $request->input('senha');

    // Buscar usuário pelo relacionamento employee com matricula
    $user = User::whereHas('employee', function ($query) use ($matricula) {
        $query->where('matricula', $matricula)->where('ativo', 1);
    })->first();

    if (!$user) {
        return response()->json(['message' => 'Usuário não encontrado'], 401);
    }

    // Verificar senha via relacionamento employee (que está no model User)
    if (!Hash::check($senha, $user->password)) {
        return response()->json(['message' => 'Senha incorreta'], 401);
    }

    // Criar token com Sanctum
    $token = $user->createToken('api-token')->plainTextToken;

    return response()->json([
        'token' => $token,
        'nome' => $user->name,
        'cod_usuario' => $user->id,
    ]);
});

Route::get('/transporte/validar', function () {
    return response()->json([
        'code' => 200,
        'status' => 'success',
    ], 200);
});

Route::prefix('transporte')->middleware('auth:sanctum')->group(function () {

    Route::get('/usuarios', [UsuarioTransporteApiController::class, 'index']);

    Route::get('/pontos', [PontoTransporteApiController::class, 'index']);

    Route::get('/rotas', [RotaTransporteApiController::class, 'index']);

    Route::get('/rota_ponto', [ItinerarioApiController::class, 'index']);

    Route::post('/sincronizar-pontos', [InPontoTransporteApiController::class, 'sincronizar']);

    Route::post('/rota-trajetos', [RotaTrajetoController::class, 'store']);


    Route::get('/out-rota-trajetos', [OutRotaTrajetoApiController::class, 'index']);





});






