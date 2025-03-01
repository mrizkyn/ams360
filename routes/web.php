<?php

use App\Http\Controllers\Assessment\{
    DepartementController, DivisionController, CompetenceController, BehaviorController,
    PositionController, DevelopmentSourceController, UserController, BusinessFieldController,
    DashboardController, ProjectController, ParticipantController, CompanyController,
    AssessmentController, ProjectParticipantController, ProjectParticipantRespondentController,
    DevelopmentActivityController, ReportAssessmentController, ReportController
};
use App\Http\Controllers\Db\{
    DashboardController as DbDashboardController, ParticipantController as DbParticipantController,
    AssessmentController as DbAssessmentController, RecapPrintController, ComparisonController,
    DepartementController as DbDepartementController, DivisionController as DbDivisionController,
    CompetencyController as DbCompetencyController, CompanyController as DbCompanyController,
    TargetJobController, DbPositionController, IndustrialSectorController, RecommendationController
};
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\Middleware\Assesment;

Route::get('/', fn() => view('welcome'));

Auth::routes(['register' => false]);

Route::get('/home', fn() => redirect('/'))->name('home')->middleware('auth');

Route::get('picture/{file}', fn($file) => response()->file(storage_path("app/public/picture/$file")))->middleware('auth');

Route::middleware(['auth', 'assessment', 'admin'])->prefix('assessment')->group(function () {
    Route::resources([
        'departements' => DepartementController::class,
        'divisions' => DivisionController::class,
        'competencies' => CompetenceController::class,
        'behaviors' => BehaviorController::class,
        'positions' => PositionController::class,
        'developments-source' => DevelopmentSourceController::class,
        'users' => UserController::class,
        'business-fields' => BusinessFieldController::class,
    ]);
});

Route::middleware(['auth', 'assessment'])->prefix('assessment')->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index']);
    Route::resource('projects', ProjectController::class);
    Route::get('projects/{id}/question-pdf', [ProjectController::class, 'printQuestionPDF']);
    Route::get('projects/{id}/question-word', [ProjectController::class, 'printQuestionWord']);
    Route::resource('participants', ParticipantController::class, ['as' => 'assessment']);
    Route::resource('companies', CompanyController::class);
    Route::get('assessments', [AssessmentController::class, 'index']);
    Route::post('assessments', [AssessmentController::class, 'create']);
    Route::get('project-participants/{id}', [ProjectParticipantController::class, 'show']);
    Route::put('project-participants/{id}', [ProjectParticipantController::class, 'update']);
    Route::resource('development-activities', DevelopmentActivityController::class);
    Route::get('report-assessment', [ReportAssessmentController::class, 'index']);
    Route::post('report-assessment-data', [ReportAssessmentController::class, 'getAssessmentSumarry']);
    Route::post('report-assessment', [ReportAssessmentController::class, 'getAssessmentReport']);
    Route::get('report-competence', [ReportController::class, 'index']);
});

Route::middleware(['auth', 'db.assessment'])->prefix('db-assessment')->group(function () {
    Route::get('dashboard', [DbDashboardController::class, 'index']);
    Route::resource('db-participants', DbParticipantController::class);
    Route::resource('db-assessments', DbAssessmentController::class);
    Route::resource('recapsPrint', RecapPrintController::class);
    Route::resource('comparisons', ComparisonController::class);
});

Route::middleware(['auth', 'db.assessment', 'admin'])->prefix('db-assessment')->group(function () {
    Route::resources([
        'db-departements' => DbDepartementController::class,
        'db-divisions' => DbDivisionController::class,
        'db-competencies' => DbCompetencyController::class,
        'db-companies' => DbCompanyController::class,
        'db-targetJobs' => TargetJobController::class,
        'db-positions' => DbPositionController::class,
        'db-business-fields' => IndustrialSectorController::class,
        'db-recommendations' => RecommendationController::class,
    ]);
});

Route::middleware(['auth'])->prefix('data')->group(function () {
    Route::get('assessment-project-data', [ProjectController::class, 'projectData']);
    Route::get('assessment-company-data', [CompanyController::class, 'companyData']);
    Route::get('dbassessment-competency-data', [DbCompetencyController::class, 'competencyData']);
    Route::get('dbassessment-company-data', [DbCompanyController::class, 'companyData']);
    Route::get('assessment-participant-data', [ParticipantController::class, 'participantData']);
    Route::get('assessment-competency-result-by-project-participant/{projectParticipantId}', [ReportAssessmentController::class, 'getAssessmentCompetencyResult']);
    Route::get('assessment-key-behavior-result-by-project-participant/{projectParticipantId}', [ReportAssessmentController::class, 'getAssessmentKeyBehaviorResult']);
    Route::get('assessment-position-data', [PositionController::class, 'positionData']);
    Route::get('assessment-division-data', [DivisionController::class, 'divisionData']);
    Route::get('assessment-development-data', [DevelopmentSourceController::class, 'developmentData']);
    Route::get('assessment-departement-data', [DepartementController::class, 'departementData']);
    Route::get('assessment-behavior-data/{id}', [BehaviorController::class, 'behaviorData']);
    Route::get('assessment-definition-data/{id}', [CompetenceController::class, 'definitionData']);
    Route::get('assessment-user-data', [UserController::class, 'usersData']);
    Route::get('assessment-user-detail/{id}', [UserController::class, 'userDetail']);
    Route::get('assessment-detail-participant/{id}', [ParticipantController::class, 'participantDetail']);
    Route::get('assessment-project-participant-data/{companyId}', [ProjectController::class, 'getParticipantByCompany'])
    ->name('project.getParticipantByCompany');
    Route::get('assessment-assesion-data/{projectId}', [AssessmentController::class, 'getAssesionByProjectName'])
    ->name('assessment.getAssesionByProjectName');

    Route::get('assessment-assesion-type-data/{id}', [AssessmentController::class, 'getAssesionTypeByProjectParticipantId'])
        ->name('assessment.getAssesionTypeByProjectParticipantId');

    Route::get('assessment-question-data/{projectId}', [AssessmentController::class, 'getQuestionByProjectName'])
        ->name('assessment.getQuestionByProjectName');
    Route::get('assessment-project-data/{companyId}', [DashboardController::class, 'projectData'])
        ->name('dashboard.getProjectsByCompany');
});
