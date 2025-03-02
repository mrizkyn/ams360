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

Route::prefix('assessment')->middleware(['auth', 'assessment'])->group(function () {
    Route::controller(DashboardController::class)->group(function () {
        Route::get('dashboard', 'index');
    });

    Route::resource('projects', ProjectController::class);

    Route::controller(ProjectController::class)->group(function () {
        Route::get('projects/{id}/question-pdf', 'printQuestionPDF');
        Route::get('projects/{id}/question-word', 'printQuestionWord');
        Route::get('projects/{id}/question-docx', 'printQuestionPDF');
    });

    Route::resource('participants', ParticipantController::class);
    Route::resource('companies', CompanyController::class);

    Route::controller(AssessmentController::class)->group(function () {
        Route::get('assessments', 'index');
        Route::post('assessments', 'create');
    });

    Route::controller(ProjectParticipantController::class)->group(function () {
        Route::get('project-participants/{id}', 'show');
        Route::get('project-participants/{id}/edit', 'edit');
        Route::put('project-participants/{id}', 'update');
    });

    Route::controller(ProjectParticipantRespondentController::class)->group(function () {
        Route::get('project-participant-respondents/{id}', 'show');
        Route::get('project-participant-respondents/{id}/edit', 'edit');
        Route::put('project-participant-respondents/{id}', 'update');
    });

    Route::resource('development-activities', DevelopmentActivityController::class);

    Route::controller(ReportAssessmentController::class)->group(function () {
        Route::get('report-assessment', 'index');
        Route::post('report-assessment-data', 'getAssessmentSumarry');
        Route::post('report-assessment', 'getAssessmentReport');
        Route::get('report-assessment-document/{document}', 'getAssssmentDocument');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('report-competence', 'index');
    });
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

Route::prefix('data')->middleware(['auth'])->group(function () {
    Route::controller(ProjectController::class)->group(function () {
        Route::get('assessment-project-data', 'projectData');
        Route::get('assessment-project-participant-data/{companyName}', 'getParticipantByCompany');
    });

    Route::controller(DepartementController::class)->group(function () {
        Route::get('assessment-departement-data', 'departementData');
    });

    Route::controller(DivisionController::class)->group(function () {
        Route::get('assessment-division-data', 'divisionData');
    });

    Route::controller(CompetenceController::class)->group(function () {
        Route::get('assessment-definition-data/{id}', 'definitionData')->whereNumber('id');
    });

    Route::controller(BehaviorController::class)->group(function () {
        Route::get('assessment-behavior-data/{id}', 'behaviorData')->whereNumber('id');
    });

    Route::controller(CompanyController::class)->group(function () {
        Route::get('assessment-company-data', 'companyData');
    });

    Route::controller(DbCompetencyController::class)->group(function () {
        Route::get('dbassessment-competency-data', 'competencyData');
    });

    Route::controller(CompanyController::class)->group(function () {
        Route::get('dbassessment-company-data', 'companyData');
    });

    Route::controller(ParticipantController::class)->group(function () {
        Route::get('assessment-participant-data', 'participantData');
        Route::get('assessment-detail-participant/{id}', 'participantDetail');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('assessment-competence-pdf', 'competencePDF');
        Route::get('assessment-competence-doc', 'competenceDOC');
    });

    Route::controller(ParticipantController::class)->group(function () {
        Route::get('dbassessment-particpant-data', 'participantData');
    });

    Route::controller(PositionController::class)->group(function () {
        Route::get('assessment-position-data', 'positionData');
    });

    Route::controller(TargetJobController::class)->group(function () {
        Route::get('dbassessment-targetJob-data', 'targetJobData');
    });

    Route::controller(AssessmentController::class)->group(function () {
        Route::get('assessment-assesion-data/{projectName}', 'getAssesionByProjectName');
        Route::get('assessment-assesion-type-data/{id}', 'getAssesionTypeByProjectParticipantId');
        Route::get('assessment-question-data/{projectName}', 'getQuestionByProjectName');
    });

    Route::controller(DevelopmentSourceController::class)->group(function () {
        Route::get('assessment-development-data', 'developmentData');
    });

    Route::controller(DevelopmentActivityController::class)->group(function () {
        Route::get('assessment-development-activity-data', 'developmentActivityData');
    });

    Route::controller(AssessmentController::class)->group(function () {
        Route::get('dbassessment-competence-data', 'competenceData');
        Route::get('dbassessment-participants-data', 'participantsData');
        Route::get('dbassessment-company-name', 'companyData');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('assessment-project-data/{companyId}', 'getProjectsByCompanyId');
        Route::get('assessment-projectparticipant-data/{projectId}', 'getParticipantByProjectId');
    });

    Route::controller(DashboardController::class)->group(function () {
        Route::get('dbassessment-project-data', 'projectData');
    });

    Route::controller(ReportAssessmentController::class)->group(function () {
        Route::get('assessment-project-data-by-company/{companyName}', 'getProjectByCompanyName');
        Route::get('assessment-participant-data-by-project/{projectName}', 'getParticipantByProjectName');
        Route::get('assessment-competency-result-by-project-participant/{projectParticipantId}', 'getAssessmentCompetencyResult');
        Route::get('assessment-key-behavior-result-by-project-participant/{projectParticipantId}', 'getAssessmentKeyBehaviorResult');
    });

    Route::controller(UserController::class)->group(function () {
        Route::get('assessment-user-data', 'usersData');
        Route::get('assessment-user-detail/{id}', 'userDetail');
    });

    Route::controller(ComparisonController::class)->group(function () {
        Route::get('dbassessment-target-job-by-company', 'getTargetJobByCompany');
        Route::get('dbassessment-recommendation-type-by-company', 'getRecommendationTypeByCompany');
        Route::post('dbassessment-comparison', 'getComparisonData');
    });

    Route::controller(RecapPrintController::class)->group(function () {
        Route::get('dbassessment-project-data/{id}', 'show');
        Route::post('dbassessment-participant-values', 'getParticipantValues');
        Route::post('dbassessment-recommendation-progress', 'getRecommendationProgress');
    });

    Route::controller(IndustrialSectorController::class)->group(function () {
        Route::get('dbassessment-business-fields', 'sectorData');
    });

    Route::controller(DepartementController::class)->group(function () {
        Route::get('dbassessment-departement-data', 'departementData');
    });

    Route::controller(DivisionController::class)->group(function () {
        Route::get('dbassessment-division-data', 'divisionData');
    });

    Route::controller(RecommendationController::class)->group(function () {
        Route::get('dbassessment-recommendations', 'recommendationData');
    });

    Route::controller(DbPositionController::class)->group(function () {
        Route::get('dbassessment-position-data', 'positionData');
    });

    Route::controller(ReportController::class)->group(function () {
        Route::get('assessment-report-division', 'getDivision');
        Route::get('assessment-report-departement', 'getDepartement');
        Route::get('assessment-report-position', 'getPosition');
    });
});
