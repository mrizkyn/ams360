<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\DB\Company;
use App\Models\DB\ItemRecommendation;
use App\Models\DB\Participant;
use App\Models\DB\TargetJob;
use App\Models\DB\TargetJobCompetency;
use App\Models\DB\Project;
use App\Models\DB\ProjectCompetenceStandart;
use App\Models\DB\ProjectParticipant;
use App\Models\DB\ProjectParticipantValue;
use App\Models\DB\Recommendation;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use PDF;
use stdClass;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Support\Facades\Validator;

class RecapPrintController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $companies = Company::all();

        return view('db_assessment.report.index', compact('companies'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validator = Validator::make(request()->all(), [
            'project_participant_ids' => 'required',
        ]);


        switch($request->report_type){
            case 1:
            return $this->assessmentResult($request);
            break;
            case 2:
            return $this->recommendationReport($request);
            break;
            case 3:
            return $this->gapRecommendationReport($request);
            break;
            case 4:
            if($this->validateParticipant($request)){
                return $this->comparisonReport($request);
            }else{
                $validator->after(function ($validator) {
                    $validator->errors()->add('alert', 'Asesi yang dipilih harus ada pada 2 project');
                });
            }
            break;
        }

        $validator->validate();
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return Project::where('company_id', $id)->get();
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }

    public function getCompanyTargetJobs($company_id){
        $targetJobIds = Project::where('company_id', $company_id)->get()->pluck('target_job_id')->unique();
        $targetJobs = TargetJob::whereIn('id', $targetJobIds)->orderBy('name')->get();

        return $targetJobs;
    }

    public function getCompanyRecommendationTypes($company_id){
        $recommendationIds = Project::where('company_id', $company_id)->get()->pluck('recommendation_id')->unique();
        $recommendationTypes = Recommendation::whereIn('id', $recommendationIds)->orderBy('name')->get();

        return $recommendationTypes;
    }

    public function showParticipants(Request $request)
    {
        if ($request->has('recommendationType')){
            $projectIds = Project::where('company_id', $request->company)
            ->where('target_job_id', $request->targetJob)
            ->whereBetween('end_date', [$request->startDate, $request->endDate])
            ->where('recommendation_id', $request->recommendationType)
            ->orderBy('end_date', 'desc')
            ->pluck('id');
        } else{
            $projectIds = Project::where('company_id', $request->company)
            ->where('target_job_id', $request->targetJob)
            ->whereBetween('end_date', [$request->startDate, $request->endDate])
            ->orderBy('end_date', 'desc')
            ->pluck('id');
        }

        $participants = ProjectParticipant::whereIn('project_id', $projectIds)
        ->get()
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id');

        return view('db_assessment.report.showParticipants',[
            'participants' => $participants,
            'company_id' => $request->company,
            'target_job_id' => $request->targetJob,
            'start_date' => $request->startDate,
            'end_date' => $request->endDate,
            'report_type' => $request->reportType,
            'recommendation_id' => $request->recommendationType
        ]);
    }

    public function showParticipantsGap(Request $request)
    {
        $reportType = $request->jenisLaporan;
        $participants = ProjectParticipant::where('project_id', $request->project)
        ->get();
        $projectId = $request->project;

        return view('db_assessment.report.showParticipantsGap', compact('participants', 'reportType', 'projectId'));
    }

    public function showParticipantsCompetency(Request $request)
    {
        $projectIds = Project::where('company_id', $request->namaPerusahaan)
        ->where('target_job_id', $request->targetJob)
        ->whereBetween('end_date', [$request->startDate, $request->endDate])
        ->where('type', $request->penamaanRekomendasi)
        ->orderBy('end_date', 'DESC')
        ->take(2)
        ->pluck('id');

        $participants = ProjectParticipant::whereIn('project_id', $projectIds)->get()->groupBy('participant_id');

        return view('db_assessment.report.showParticipantsCompetency', [
            'participants' => $participants,
            'projectIds' => $projectIds,
            'reportType' => $request->jenisLaporan,
            'recommendationType' => Project::find($request->project)->type
        ]);
    }

    public function showParticipantsRecapAssessment(Request $request)
    {
        $participants = ProjectParticipant::all();

        return view('db_assessment.report.showParticipantsRecapAssessment', [
            'participants' => $participants,
        ]);
    }

    public function showChart(Request $request)
    {
        $participants = $request->participant_ids;
        $recommendations = [];
        if ($request->typeRecommendation == "High , Middle High , Middle Low , Low") {
            $recommendations = ['High', 'Middle High', 'Middle Low', 'Low'];
        } elseif ($request->typeRecommendation == "Very High , High , Middle High , Middle Low , Low") {
            $recommendations = ['Very High', 'High', 'Middle High', 'Middle Low', 'Low'];
        } else {
            $recommendations = ['Siap Memenuhi Kriteria Kompetensi Yang Dipersyaratkan Target Job', 'Membutuhkan Pengembangan < 2 Tahun', 'Membutuhkan Pengembangan 2-4 Tahun', 'Membutuhkan Pengembangan > 4 Tahun', 'Belum Memenuhi Kriteria Kompetensi Yang Dipersyaratkan Target Job Dan Membutuhkan Pengembangan > 4 Tahun'];
        }
        $chart = DB::connection('mysql2')->table('project_participations')
        ->select(DB::raw('recommendation, count(recommendation) as jumlah'))
        ->whereIn('id', $participants)
        ->groupBy('recommendation')
        ->orderBy('recommendation', 'asc')
        ->get();

        $total = DB::connection('mysql2')->table('project_participations')
        ->select(DB::raw('count(recommendation) as total'))
        ->whereIn('id', $participants)
        ->count();
        $percent = array();
        foreach ($recommendations as $recommendation) {
            $data = $chart->where('recommendation', $recommendation)->first();
            if ($data == null) {
                array_push($percent, ['recommendation' => $recommendation, 'jumlah' => 0, 'percent' => 0]);
            } else {
                array_push($percent, ['recommendation' => $data->recommendation, 'jumlah' => $data->jumlah, 'percent' => number_format((float) ($data->jumlah / $total) * 100, 2, '.', '')]);
            }
        }
        return $percent;
    }

    public function showChartGap(Request $request)
    {
        $participants = $request->participant_ids;
        $participantValues = ProjectParticipantValue::whereIn('project_participant_id', $participants)
        ->get()->groupBy('competency_id');

        $projectid = ProjectParticipant::whereIn('id', $participants)
        ->get()->pluck('project_id')->unique();

        $participantStandarts = ProjectCompetenceStandart::with('competency')->whereIn('project_id', $projectid)->get();

        $gapValues = [];

        foreach ($participantStandarts as $participantStandart) {
            $standart = 0;
            $belowStandart = 0;
            $aboveStandart = 0;
            foreach ($participantValues[$participantStandart->competence_id] as $participantValue) {
                $gap = $participantValue->value - $participantStandart->value;
                if ($gap == 0) {
                    $standart++;
                } else if ($gap > 0) {
                    $aboveStandart++;
                } else if ($gap < 0) {
                    $belowStandart++;
                }
            }

            $total = $standart + $belowStandart + $aboveStandart;
            $standartPercent = ($standart / $total) * 100;
            $belowStandartPercent = ($belowStandart / $total) * 100;
            $aboveStandartPercent = ($aboveStandart / $total) * 100;
            $gapValue = [
                ['competency' => $participantStandart->competency->name, 'type' => '> Standar', 'value' => $aboveStandart, 'percent' => $aboveStandartPercent],
                ['competency' => $participantStandart->competency->name, 'type' => '= Standar', 'value' => $standart, 'percent' => $standartPercent],
                ['competency' => $participantStandart->competency->name, 'type' => '< Standar', 'value' => $belowStandart, 'percent' => $belowStandartPercent],
            ];

            array_push($gapValues, $gapValue);
        }

        return $gapValues;
    }

    public function comparationData()
    {
    }

    public function getParticipantValues(Request $request)
    {
        $participantIds = $request->participant_ids;
        $projectIds = json_decode($request->projectIds);
        $response = [];

        $projectParticipant = DB::connection('mysql2')->table('project_participations')
        ->select('*')
        ->whereIn('project_id', $projectIds)
        ->whereIn('participant_id', $participantIds)
        ->get()->pluck('id');

        $projectParticipantValues = DB::connection('mysql2')->table('project_participant_values')
        ->select('*')
        ->join('competencies', 'project_participant_values.competency_id', '=', 'competencies.id')
        ->join('project_participations', 'project_participant_values.project_participant_id', '=', 'project_participations.id')
        ->whereIn('project_participant_id', $projectParticipant)
        ->get()->groupBy('participant_id');

        $competencyTotal = count($projectParticipantValues->first()) / 2;

        array_push($response, $projectParticipantValues);

        $competencyGap = [];

        foreach ($projectParticipantValues as $value) {
            $gap = [];
            $index = 0;
            for ($i = 0; $i < $competencyTotal; $i++) {
                $object = new stdClass();
                $object->competency = $value[$index]->name;
                $object->value = $value[$index]->value - $value[$index + $competencyTotal]->value;
                $index++;
                array_push($gap, $object);
            }
            array_push($competencyGap, $gap);
        }

        array_push($response, $competencyGap);

        $competencyProgress = [];
        foreach ($competencyGap as $comptency) {
            $progress = [];
            $value = [0, 0, 0, 0];
            $type = ['Naik', 'Turun', 'Tetap', 'Berubah'];

            foreach ($comptency as $gap) {
                if ($gap->value > 0) {
                    $value[0]++;
                    $value[3]++;
                } else if ($gap->value == 0) {
                    $value[2]++;
                } else if ($gap->value < 0) {
                    $value[1]++;
                    $value[3]++;
                }
            }
            $total = $value[0] + $value[1] + $value[2];

            for ($i = 0; $i < count($type); $i++) {
                $object = new stdClass();
                $object->type = $type[$i];
                $object->value = $value[$i];
                $object->percent = ($value[$i] / $total) * 100;
                array_push($progress, $object);
            }
            array_push($competencyProgress, $progress);
        }
        array_push($response, $competencyProgress);

        $competenceProgresPopulation = [];
        for ($i = 0; $i < $competencyTotal; $i++) {
            $type = ['Σ Asesi Naik', 'Σ Asesi Turun', 'Σ Asesi Tetap', 'Σ Asesi Berubah'];
            $value = [0, 0, 0, 0];
            $competencyPopulationValue = [];

            foreach ($competencyGap as $gap) {
                if ($gap[$i]->value > 0) {
                    $value[0]++;
                    $value[3]++;
                } else if ($gap[$i]->value == 0) {
                    $value[2]++;
                } else if ($gap[$i]->value < 0) {
                    $value[1]++;
                    $value[3]++;
                }
            }

            $total = $value[0] + $value[1] + $value[2];

            for ($j = 0; $j < count($type); $j++) {
                $object = new stdClass();
                $object->competency = $response[1][0][$i]->competency;
                $object->type = $type[$j];
                $object->value = $value[$j];
                $object->percent = ($value[$j] / $total) * 100;
                array_push($competencyPopulationValue, $object);
            }
            array_push($competenceProgresPopulation, $competencyPopulationValue);
        }

        array_push($response, $competenceProgresPopulation);

        $projectAverages = [];

        foreach ($projectParticipantValues as $projectParticipantValue) {
            $participantAverage = [];
            $competencyCount = 0;
            $valueSum = 0;

            for ($i = 0; $i < 2; $i++) {
                for ($j = 0; $j < $competencyTotal; $j++) {
                    $valueSum += $projectParticipantValue[$j + $competencyCount]->value;
                }
                $valueAverage = $valueSum / $competencyTotal;
                array_push($participantAverage, $valueAverage);
                $valueSum = 0;
                $competencyCount += $competencyTotal;
            }

            array_push($projectAverages, $participantAverage);
        }

        array_push($response, $projectAverages);

        $projectAverageProgress = [];
        $projectAverageValues = [0, 0, 0, 0];
        $projectAverageTypes = ['Naik', 'Turun', 'Tetap', 'Berubah'];

        foreach ($projectAverages as $projectAverage) {
            $gap = $projectAverage[0] - $projectAverage[1];
            if ($gap > 0) {
                $projectAverageValues[0]++;
                $projectAverageValues[3]++;
            } else if ($gap == 0) {
                $projectAverageValues[2]++;
            } else if ($gap < 0) {
                $projectAverageValues[1]++;
                $projectAverageValues[3]++;
            }
        }

        for ($i = 0; $i < count($projectAverageTypes); $i++) {
            $projectAverageValueTotal = $projectAverageValues[0] + $projectAverageValues[1] + $projectAverageValues[2];
            $projectAveragePercent = ($projectAverageValues[$i] / $projectAverageValueTotal) * 100;

            $object = new stdClass();
            $object->type = $projectAverageTypes[$i];
            $object->value = $projectAverageValues[$i];
            $object->percent = $projectAveragePercent;

            array_push($projectAverageProgress, $object);
        }

        array_push($response, $projectAverageProgress);


        return $response;
    }

    public function getRecommendationProgress(Request $request)
    {
        $participantIds = $request->participant_ids;
        $projectIds = json_decode($request->projectIds);
        $recommendationProgress = [];

        $projectParticipants = ProjectParticipant::join('projects', 'project_participations.project_id', '=', 'projects.id')
        ->whereIn('project_id', $projectIds)
        ->whereIn('participant_id', $participantIds)
        ->orderBy('end_date', 'DESC')
        ->get()
        ->groupBy('project_id');

        $recommendations = [];
        if ($request->recommendationType == "High , Middle High , Middle Low , Low") {
            $recommendations = ['High', 'Middle High', 'Middle Low', 'Low'];
        } elseif ($request->recommendationType == "Very High , High , Middle High , Middle Low , Low") {
            $recommendations = ['Very High', 'High', 'Middle High', 'Middle Low', 'Low'];
        } else {
            $recommendations = ['Siap Memenuhi Kriteria Kompetensi Yang Dipersyaratkan Target Job', 'Membutuhkan Pengembangan < 2 Tahun', 'Membutuhkan Pengembangan 2-4 Tahun', 'Membutuhkan Pengembangan > 4 Tahun', 'Belum Memenuhi Kriteria Kompetensi Yang Dipersyaratkan Target Job Dan Membutuhkan Pengembangan > 4 Tahun'];
        }

        foreach ($projectParticipants as $projectParticipant) {
            $recommendationData = [];
            foreach ($recommendations as $recommendation) {
                $total = count($projectParticipant);
                $data = collect($projectParticipant)->where('recommendation', $recommendation);
                if (count($data) > 0) {
                    $object = new stdClass();
                    $object->recommendation = $recommendation;
                    $object->value = count($data);
                    $object->percent = (count($data) / $total) * 100;

                    array_push($recommendationData, $object);
                } else {
                    $object = new stdClass();
                    $object->recommendation = $recommendation;
                    $object->value = 0;
                    $object->percent = 0;

                    array_push($recommendationData, $object);
                }
            }
            array_push($recommendationProgress, $recommendationData);
        }

        return $recommendationProgress;
    }

    public function recommendationReport(Request $request){
        $projectParticipants = ProjectParticipant::whereIn('id', $request->project_participant_ids)->get();
        $projectParticipantByParticipant = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id')
        ->values()
        ->all();
        $projectParticipantByProject = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('project_id')
        ->values()
        ->all();
        $company = Company::find($request->company_id);
        $target = TargetJob::find($request->target_job_id);
        $recommendation = Recommendation::find($request->recommendation_id);
        $item_recommendations = ItemRecommendation::where('recommendation_id' , $recommendation->id)->get();

        $data = DB::connection('mysql2')->table('project_participations')
        ->join('item_recommendations', 'project_participations.item_recommendation_id', '=','item_recommendations.id')
        ->select(DB::raw('item_recommendations.name, COUNT(project_participations.item_recommendation_id) AS jumlah'))
        ->whereIn('project_participations.id', $request->project_participant_ids)
        ->groupBy('item_recommendations.name')
        ->get();


        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection(array('orientation' => 'landscape'));

        $paragraphCenter = 'pStyle';
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addText("Statistik Sebaran Peserta Per Rekomendasi",
            array('name' => 'Calibri',
              'size' => 12,
              'color' => 'black',
              'bold' => true),
            $paragraphCenter);
        $section->addTextBreak();
        $section->addText("Nama Perusahaan\t: ". $company->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        $section->addText("Target Job\t\t: ". $target->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));

        $projectDateOne = Carbon::parse(collect($projectParticipantByProject)->last()->last()->project->start_date)->translatedFormat('d F Y');
        $projectDateTwo = Carbon::parse(collect($projectParticipantByProject)->first()->first()->project->end_date)->translatedFormat('d F Y');
        $section->addText("Waktu Pengambilan Data\t: " . $projectDateOne . " - " . $projectDateTwo,
        array('name' => 'Calibri',
            'size' => 10,
            'color' => 'black'));
        $section->addTextBreak();

        $section->addTextBreak();
        $section->addText("Table 1. Sebaran Peserta Per Rekomendasi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        // property table
        $tableStyle = array(
            'cellMarginLeft' => 100,
            'cellMarginTop' => 80
        );
        $headerStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $Body1Style = array(
            'bgColor' => 'ededed',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $Body2Style = array(
            'bgColor' => 'ffffff',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $cellTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => true
        );
        $cellCenter = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center');
        $cellLeft = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'valign' => 'center');

        $persentase = [];

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000)->addText('N (Jumlah Peserta)' , $cellTextStyle);
        $table->addCell(1500)->addText(count($request->project_participant_ids) , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(4000 , $headerStyle)->addText("Kategori Rekomendasi" , $cellTextStyle , $cellLeft);
        $table->addCell(1000 , $headerStyle)->addText("Jumlah" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerStyle)->addText("%" , $cellTextStyle , $cellCenter);

        $jumlahItem = 0;
        if (count($item_recommendations) == count($data)) {
            for ($l=0; $l < count($data); $l++) {
                $jumlahItem += $data[$l]->jumlah;
            }
            foreach ($data as $item_recommendation) {
                $table->addRow();
                $table->addCell(4000 , $Body1Style)->addText($item_recommendation->name , $cellTextStyle , $cellLeft);
                $table->addCell(1000 , $Body2Style)->addText($item_recommendation->jumlah , $cellTextStyle , $cellCenter);
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)(($item_recommendation->jumlah / $jumlahItem) * 100), 2, '.', '')."%" , $cellTextStyle , $cellCenter);
                $persenPerItem = number_format((float)(($item_recommendation->jumlah / $jumlahItem) * 100), 2, '.', '');
                // $persenPerItem = number_format((float)($item_recommendation->jumlah / $jumlahItem), 2, '.', '');
                array_push($persentase, $persenPerItem);
            }
        } else {
            $items = ItemRecommendation::where('recommendation_id' , $recommendation->id)->pluck('name')->toArray();
            $someItems = array_column(json_decode($data), 'name');
            $result = array_values(array_diff($items, $someItems));


            for ($k=0; $k < count($data); $k++) {
                $jumlahItem += $data[$k]->jumlah;
            }

            for ($i=0; $i < count($data) ; $i++) {
                $table->addRow();
                $table->addCell(4000 , $Body1Style)->addText($data[$i]->name , $cellTextStyle , $cellLeft);
                $table->addCell(1000 , $Body2Style)->addText($data[$i]->jumlah , $cellTextStyle , $cellCenter);
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)(($data[$i]->jumlah / $jumlahItem) * 100), 2, '.', '')."%" , $cellTextStyle , $cellCenter);
                $persenPerItem = number_format((float)(($data[$i]->jumlah / $jumlahItem) * 100), 2, '.', '');
                array_push($persentase, $persenPerItem);
            }
            for ($j=0; $j < count($result); $j++) {
                $table->addRow();
                $table->addCell(4000 , $Body1Style)->addText($result[$j] , $cellTextStyle , $cellLeft);
                $table->addCell(1000 , $Body2Style)->addText("0" , $cellTextStyle , $cellCenter);
                $table->addCell(1000 , $Body2Style)->addText("0%" , $cellTextStyle , $cellCenter);
            }
        }

        $section = $phpWord->addSection(array('orientation' => 'landscape'));
        $section->addText("Grafik 1. Sebaran Peserta Per Rekomendasi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $rekomendasi = array_column($data->toArray(), 'name');
        $jumlahRekomendasi = array_column($data->toArray(), 'jumlah');

        // $chartTypes = array('pie');
        $showGridLines = false;
        $showAxisLabels = false;

        $chart = $section->addChart("pie", $rekomendasi, $persentase);
        $chart->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(3));
        $chart->getStyle()->setShowGridX($showGridLines);
        $chart->getStyle()->setShowGridY($showGridLines);
        $chart->getStyle()->setShowAxisLabels($showAxisLabels);

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objectWriter->save(storage_path('Statistik Sebaran Perserta Per Rekomendasi.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('Statistik Sebaran Perserta Per Rekomendasi.docx'));
    }

    public function assessmentResult(Request $request){
        $projectParticipants = ProjectParticipant::whereIn('id', $request->project_participant_ids)->get();
        $projectParticipantByParticipant = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id')
        ->values()
        ->all();
        $projectParticipantByProject = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('project_id')
        ->values()
        ->all();

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $section = $phpWord->addSection(array('orientation' => 'landscape'));

        $paragraphCenter = 'pStyle';
        $tableStyle = array(
            'cellMarginLeft' => 100,
            'bgColor' => 'ededed',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $headerMergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center' ,
            'vMerge' => 'restart'
        );
        $mergeStyleStandard = array(
            'bgColor' => 'f4b184',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center' ,
            'vMerge' => 'continue'
        );
        $mergeStyleStandardTitle = array(
            'bgColor' => 'f4b184',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'gridSpan' => 3,
            'valign' => 'center' ,
            'vMerge' => 'continue'
        );
        $mergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'vMerge' => 'continue'
        );
        $headerGridStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'gridSpan' => $projectParticipants->first()->project->projectCompetenceStandarts->count(),
            'valign' => 'center'
        );
        $headerLeftStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'textDirection'=>\PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR,
            'valign' => 'center' ,
            'vMerge' => 'restart'
        );
        $BodyStyle = array(
            'bgColor' => 'ffffff',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $cellTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => true
        );
        $cellCenter = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center',);
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addText("Rekap Hasil Assessment",
            array('name' => 'Calibri',
              'size' => 12,
              'color' => 'black',
              'bold' => true),
            $paragraphCenter);
        $section->addTextBreak();

        $section->addText("Nama Perusahaan\t: " . $projectParticipants->first()->participant->company->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        $section->addText("Target Job\t\t: " . $projectParticipants->first()->project->targetJob->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));

        $projectDateOne = Carbon::parse(collect($projectParticipantByProject)->last()->last()->project->start_date)->translatedFormat('d F Y');
        $projectDateTwo = Carbon::parse(collect($projectParticipantByProject)->first()->first()->project->end_date)->translatedFormat('d F Y');
        $section->addText("Waktu Pengambilan Data\t: " . $projectDateOne . " - " . $projectDateTwo,
        array('name' => 'Calibri',
            'size' => 10,
            'color' => 'black'));
        $section->addTextBreak();

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(3000 , $mergeStyleStandardTitle)->addText("Standar Penilaian" , $cellTextStyle ,$cellCenter);

        $projectCompetenceStandarts =  $projectParticipants->first()->project->projectCompetenceStandarts;
        foreach($projectCompetenceStandarts as $projectCompetenceStandart){
            $table->addCell(300 , $mergeStyleStandard)->addText($projectCompetenceStandart->value , $cellTextStyle , $cellCenter);
        }

        $table->addRow();
        $table->addCell(400 , $headerMergeStyle)->addText("No" , $cellTextStyle , $cellCenter);
        $table->addCell(4000 , $headerMergeStyle)->addText("Nama" , $cellTextStyle , $cellCenter);
        $table->addCell(3000 , $headerMergeStyle)->addText("Jabatan Saat Ini" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridStyle)->addText("Nilai Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Rata-rata" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridStyle)->addText("Gap Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Σ Kompetensi < Standar" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Σ Kompetensi = Standar" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Σ Kompetensi > Standar" , $cellTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Rekomendasi" , $cellTextStyle , $cellCenter);
        $table->addRow(1400);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        foreach($projectCompetenceStandarts as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellTextStyle , $cellCenter);
        }
        $table->addCell(null , $mergeStyle);
        foreach($projectCompetenceStandarts as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellTextStyle , $cellCenter);
        }

        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);

        $no = 0;
        foreach($projectParticipants as $projectParticipant){
            $no++;
            $gaps = [];
            $below = 0;
            $equal = 0;
            $above = 0;

            $table->addRow();
            $table->addCell(1000 , $BodyStyle)->addText($no , $cellTextStyle ,$cellCenter);
            $table->addCell(1000 , $BodyStyle)->addText($projectParticipant->participant->name , $cellTextStyle ,$cellCenter);
            $table->addCell(1000 , $BodyStyle)->addText($projectParticipant->participant->position->name , $cellTextStyle ,$cellCenter);

            $projectParticipantValues = $projectParticipant->projectParticipantValues;
            foreach($projectParticipantValues as $projectParticipantValue){
                $table->addCell(1000 , $BodyStyle)->addText($projectParticipantValue->value , $cellTextStyle ,$cellCenter);
            }
            $table->addCell(1000 , $BodyStyle)->addText($projectParticipantValues->average('value') , $cellTextStyle ,$cellCenter);

            for($i = 0; $i < count($projectParticipantValues); $i++){
                $gap = $projectParticipantValues[$i]->value - $projectCompetenceStandarts[$i]->value;
                array_push($gaps, $gap);
                if($gap < 0){
                    $below++;
                }else if($gap == 0){
                    $equal++;
                }else if($gap > 0){
                    $above++;
                }
            }

            foreach($gaps as $gap){
                $table->addCell(1000 , $BodyStyle)->addText($gap , $cellTextStyle ,$cellCenter);
            }

            $table->addCell(1000 , $BodyStyle)->addText($below , $cellTextStyle ,$cellCenter);
            $table->addCell(1000 , $BodyStyle)->addText($equal , $cellTextStyle ,$cellCenter);
            $table->addCell(1000 , $BodyStyle)->addText($above , $cellTextStyle ,$cellCenter);
            $table->addCell(1600 , $BodyStyle)->addText($projectParticipant->itemRecommendation->name , $cellTextStyle ,$cellCenter);
        }

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objectWriter->save(storage_path('Laporan Rekap Hasil Assessment - '.$projectParticipants->first()->participant->company->name.'.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('Laporan Rekap Hasil Assessment - '.$projectParticipants->first()->participant->company->name.'.docx'));
    }

    public function gapRecommendationReport(Request $request){
        $projectParticipants = ProjectParticipant::whereIn('id', $request->project_participant_ids)->get();
        $projectParticipantByParticipant = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id')
        ->values()
        ->all();

        $projectParticipantByProjectFirst = $projectParticipants
        ->sortBy('project.start_date')
        ->groupBy('project_id')
        ->values()
        ->first();

        $projectParticipantByProjectTwo = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('project_id')
        ->values()
        ->first();

        // return collect($projectParticipantByProjectFirst)->first()->project->start_date;
        // return collect($projectParticipantByProjectTwo)->first()->project->end_date;

        $company = Company::find($request->company_id);
        $target_job = TargetJob::find($request->target_job_id);
        $target_job_competencies = TargetJobCompetency::with('competency')->where('target_job_id' , $target_job->id)->get();
        $standar = array();
        $data = array();
        $data_standar = array();

        for ($i=0; $i < count($request->project_participant_ids) ; $i++) {
            foreach ($target_job_competencies as $target_job_competencie) {
                $project_participants = ProjectParticipant::find($request->project_participant_ids[$i]);
                $standar_competencies = ProjectCompetenceStandart::with('competency')->where(['competence_id' => $target_job_competencie->competency_id , 'project_id' => $project_participants->project_id])->get();
                foreach ($standar_competencies as $standar_competency) {
                    $standar[$request->project_participant_ids[$i]][$standar_competency->competency->name] = $standar_competency->value;
                }
            }
        }

        foreach ($target_job_competencies as $target_job_competencie) {
            $value1 = 0;
            $value2 = 0;
            $value3 = 0;
            $value4 = 0;
            $value5 = 0;
            for ($i=0; $i < count($request->project_participant_ids) ; $i++) {
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $target_job_competencie->competency_id , 'project_participant_id' => $request->project_participant_ids[$i] ])->get();
                if($project_participant_values[0]->value == 1){
                    $value1 += 1;
                    $data[$project_participant_values[0]->competency->name][1] = $value1;
                }else if($project_participant_values[0]->value == 2){
                    $value2 += 1;
                    $data[$project_participant_values[0]->competency->name][2] = $value2;
                }else if($project_participant_values[0]->value == 3){
                    $value3 += 1;
                    $data[$project_participant_values[0]->competency->name][3] = $value3;
                }else if($project_participant_values[0]->value == 4){
                    $value4 += 1;
                    $data[$project_participant_values[0]->competency->name][4] = $value4;
                }else{
                    $value5 += 1;
                    $data[$project_participant_values[0]->competency->name][5] = $value5;
                }
            }
        }

        foreach ($target_job_competencies as $target_job_competencie) {
            $lebih = 0;
            $sama = 0;
            $kurang = 0;
            for ($i=0; $i < count($request->project_participant_ids) ; $i++) {
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $target_job_competencie->competency_id , 'project_participant_id' => $request->project_participant_ids[$i] ])->get();
                if($project_participant_values[0]->value > $standar[$request->project_participant_ids[$i]][$project_participant_values[0]->competency->name]){
                    $lebih += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['lebih'] = $lebih;
                }else if($project_participant_values[0]->value == $standar[$request->project_participant_ids[$i]][$project_participant_values[0]->competency->name]){
                    $sama += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['sama'] = $sama;
                }else{
                    $kurang += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['kurang'] = $kurang;
                }
            }
        }


        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $section = $phpWord->addSection(array('orientation' => 'landscape'));

        $paragraphCenter = 'pStyle';
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addText("Statistik Gap Kompetensi",
            array('name' => 'Calibri',
              'size' => 12,
              'color' => 'black',
              'bold' => true),
            $paragraphCenter);
        $section->addTextBreak();
        $section->addText("Nama Perusahaan\t: ".$company->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        $section->addText("Target Job\t\t: ".$target_job->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));

        $projectDateOne = Carbon::parse(collect($projectParticipantByProjectFirst)->first()->project->start_date)->translatedFormat('d F Y');
        $projectDateTwo = Carbon::parse(collect($projectParticipantByProjectTwo)->first()->project->end_date)->translatedFormat('d F Y');
        $section->addText("Waktu Pengambilan Data\t: " . $projectDateOne . " - " . $projectDateTwo,
        array('name' => 'Calibri',
            'size' => 10,
            'color' => 'black'));
        $section->addTextBreak();

        // return collect($projectParticipantByParticipant)->last()->first()->project->start_date;
        // if(sizeof($projectParticipantByParticipant[0]) > 0){
        //     $projectDateOne = Carbon::parse(collect($projectParticipantByProject)->last()->first()->project->start_date)->translatedFormat('d F y');
        //     $projectDateTwo = Carbon::parse(collect($projectParticipantByProject)->first()->first()->project->end_date)->translatedFormat('d F y');
        //     $section->addText("Waktu Pengambilan Data\t: " . $projectDateOne . " - " . $projectDateTwo,
        //     array('name' => 'Calibri',
        //         'size' => 10,
        //         'color' => 'black'));
        // $section->addTextBreak();
        // } else{
        //     $section->addText("Waktu Pengambilan Data\t: " . Carbon::parse($projectParticipants->first()->project->start_date)->translatedFormat('%d %B') . " - " . Carbon::parse($projectParticipants->first()->project->end_date)->translatedFormat('d F y'),
        //     array('name' => 'Calibri',
        //         'size' => 10,
        //         'color' => 'black'));
        //     $section->addTextBreak();
        // }

        // property table
        $tableStyle = array(
            'cellMarginLeft' => 100,
            'cellMarginTop' => 50,
            'bgColor' => 'ededed',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $headerStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $headerMergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center' ,
            'vMerge' => 'restart'
        );
        $mergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'vMerge' => 'continue'
        );
        $headerGridStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'gridSpan' => $target_job->total,
            'valign' => 'center');
        $headerLeftStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'textDirection'=>\PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR,
            'layout' => \PhpOffice\PhpWord\Style\Table::LAYOUT_FIXED,
            'valign' => 'center' ,
            'vMerge' => 'restart');
        $Body1Style = array(
            'bgColor' => 'ededed',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $Body2Style = array(
            'bgColor' => 'ffffff',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1);
        $cellTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => true
        );
        $cellCenter = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center');
        $cellLeft = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'valign' => 'center');

        $section->addText("Table 1. Data Umum" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(3000 , $headerStyle)->addText("Σ Kompetensi" , $cellTextStyle , $cellLeft);
        $table->addCell(1000 , $Body2Style)->addText($target_job->total , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(3000 , $headerStyle)->addText("Σ Asesi" , $cellTextStyle , $cellLeft);
        $table->addCell(1000 , $Body2Style)->addText(count($request->project_participant_ids) , $cellTextStyle , $cellCenter);

        $section->addTextBreak();

        $section->addText("Table 2. Sebaran Peserta terhadap Nilai/Rating/Level untuk Tiap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000 , $headerMergeStyle)->addText("Nilai/Rating/Level" , $cellTextStyle , $cellCenter);
        $table->addCell(4000 , $headerGridStyle)->addText("Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(1000 , $headerLeftStyle)->addText($target_job_competencie->competency->name , $cellTextStyle , $cellCenter);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('5' , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][5])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data[$target_job_competencie->competency->name][5] , $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('4' , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][4])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data[$target_job_competencie->competency->name][4] , $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('3' , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][3])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data[$target_job_competencie->competency->name][3] , $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('2' , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][2])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data[$target_job_competencie->competency->name][2] , $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('1' , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][1])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data[$target_job_competencie->competency->name][1] , $cellTextStyle ,$cellCenter);
            }
        }
        

        $section = $phpWord->addSection(array('orientation' => 'landscape'));
        $section->addText("Tabel 3. Persentase Sebaran Peserta terhadap Nilai/Rating/Level Untuk Tiap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000 , $headerMergeStyle)->addText("Nilai/Rating/Level" , $cellTextStyle , $cellCenter);
        $table->addCell(4000 , $headerGridStyle)->addText("Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        $kompetensi = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(1000 , $headerLeftStyle)->addText($target_job_competencie->competency->name , $cellTextStyle , $cellCenter);
            array_push($kompetensi, $target_job_competencie->competency->name);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('5' , $cellTextStyle ,$cellCenter);
        $persentase5 = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][5])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($persentase5, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)($data[$target_job_competencie->competency->name][5] * 100) / count($request->project_participant_ids), 2, '.', '')."%" , $cellTextStyle ,$cellCenter);
                $nilaiPersen5 = number_format((float)(($data[$target_job_competencie->competency->name][5]) / count($request->project_participant_ids)),2,'.','');
                array_push($persentase5, $nilaiPersen5);
            }
        }

        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('4' , $cellTextStyle ,$cellCenter);
        $persentase4 = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][4])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($persentase4, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)($data[$target_job_competencie->competency->name][4] * 100) / count($request->project_participant_ids), 2, '.', '')."%", $cellTextStyle ,$cellCenter);
                $nilaiPersen4 = number_format((float)(($data[$target_job_competencie->competency->name][4]) / count($request->project_participant_ids)),2,'.','');
                array_push($persentase4, $nilaiPersen4);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('3' , $cellTextStyle ,$cellCenter);
        $persentase3 = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][3])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($persentase3, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)($data[$target_job_competencie->competency->name][3] * 100) / count($request->project_participant_ids), 2, '.', '')."%" , $cellTextStyle ,$cellCenter);
                $nilaiPersen3 = number_format((float)(($data[$target_job_competencie->competency->name][3]) / count($request->project_participant_ids)),2,'.','');
                array_push($persentase3, $nilaiPersen3);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('2' , $cellTextStyle ,$cellCenter);
        $persentase2 = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][2])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($persentase2, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)($data[$target_job_competencie->competency->name][2] * 100) / count($request->project_participant_ids), 2, '.', '')."%" , $cellTextStyle ,$cellCenter);
                $nilaiPersen2 = number_format((float)(($data[$target_job_competencie->competency->name][2]) / count($request->project_participant_ids)),2,'.','');
                array_push($persentase2, $nilaiPersen2);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText('1' , $cellTextStyle ,$cellCenter);
        $persentase1 = [];
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data[$target_job_competencie->competency->name][1])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($persentase1, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)($data[$target_job_competencie->competency->name][1] * 100) / count($request->project_participant_ids), 2, '.', '')."%", $cellTextStyle ,$cellCenter);
                $nilaiPersen1 = number_format((float)(($data[$target_job_competencie->competency->name][1]) / count($request->project_participant_ids)),2,'.','');
                array_push($persentase1, $nilaiPersen1);
            }
        }
        // return $persentase1;
        $section = $phpWord->addSection(array('orientation' => 'landscape'));
        $section->addText("Grafik 1. Sebaran Peserta terhadap Nilai/Rating/Level Untuk Tiap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        // create chart
        $showGridLines = true;
        $showAxisLabels = true;

        $chart = $section->addChart("column", $kompetensi, $persentase5, null, "Nilai 5");
        $chart->getStyle()->setWidth(Converter::inchToEmu(9))->setHeight(Converter::inchToEmu(5));
        $chart->getStyle()->setShowGridX($showGridLines);
        $chart->getStyle()->setShowGridY($showGridLines);
        $chart->getStyle()->setShowAxisLabels($showAxisLabels);

        $chart->addSeries($kompetensi, $persentase4, "Nilai 4");
        $chart->addSeries($kompetensi, $persentase3, "Nilai 3");
        $chart->addSeries($kompetensi, $persentase2, "Nilai 2");
        $chart->addSeries($kompetensi, $persentase1, "Nilai 1");

        $section->addPageBreak();
        $section = $phpWord->addSection(array('orientation' => 'landscape'));
        $section->addText("Tabel 4. Sebaran Peserta berdasarkan Gap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));


        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000 , $headerMergeStyle)->addText("Nilai/Rating/Level" , $cellTextStyle , $cellCenter);
        $table->addCell(4000 , $headerGridStyle)->addText("Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(1000 , $headerLeftStyle)->addText($target_job_competencie->competency->name , $cellTextStyle , $cellCenter);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText("> Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['lebih'])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data_standar[$target_job_competencie->competency->name]['lebih'], $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText("= Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['sama'])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data_standar[$target_job_competencie->competency->name]['sama'], $cellTextStyle ,$cellCenter);
            }
        }
        $table->addRow();
        $table->addCell(2000 , $Body1Style)->addText("< Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['kurang'])){
                $table->addCell(1000 , $Body2Style)->addText('0' , $cellTextStyle ,$cellCenter);
            }else{
                $table->addCell(1000 , $Body2Style)->addText($data_standar[$target_job_competencie->competency->name]['kurang'], $cellTextStyle ,$cellCenter);
            }
        }

        $section->addTextBreak();
        $section->addText("Tabel. 5 Persentase Sebaran Peserta berdasarkan Gap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000 , $headerMergeStyle)->addText("Nilai/Rating/Level" , $cellTextStyle , $cellCenter);
        $table->addCell(4000 , $headerGridStyle)->addText("Kompetensi" , $cellTextStyle , $cellCenter);
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(1000 , $headerLeftStyle)->addText($target_job_competencie->competency->name , $cellTextStyle , $cellCenter);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $table->addCell(null , $mergeStyle);
        foreach ($target_job_competencies as $target_job_competencie ) {
            $table->addCell(null , $mergeStyle);
        }
        $table->addRow();
        $diatasStandar = [];
        $table->addCell(2000 , $Body1Style)->addText("> Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['lebih'])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($diatasStandar, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)(($data_standar[$target_job_competencie->competency->name]['lebih'] * 100) / count($request->project_participant_ids)), 2, '.', '')."%", $cellTextStyle ,$cellCenter);
                $nilaiDiatas = number_format((float)(($data_standar[$target_job_competencie->competency->name]['lebih']) / count($request->project_participant_ids)), 2, '.', '');
                array_push($diatasStandar, $nilaiDiatas);
            }
        }
        $table->addRow();
        $samaDenganStandar = [];
        $table->addCell(2000 , $Body1Style)->addText("= Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['sama'])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($samaDenganStandar, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)(($data_standar[$target_job_competencie->competency->name]['sama'] * 100) / count($request->project_participant_ids)), 2, '.', '')."%", $cellTextStyle ,$cellCenter);
                $nilaiSama = number_format((float)(($data_standar[$target_job_competencie->competency->name]['sama']) / count($request->project_participant_ids)), 2, '.', '');
                array_push($samaDenganStandar, $nilaiSama);
            }
        }
        $table->addRow();
        $dibawahStandar = [];
        $table->addCell(2000 , $Body1Style)->addText("< Standar" , $cellTextStyle ,$cellCenter);
        foreach ($target_job_competencies as $target_job_competencie ) {
            if(empty($data_standar[$target_job_competencie->competency->name]['kurang'])){
                $table->addCell(1000 , $Body2Style)->addText('0%' , $cellTextStyle ,$cellCenter);
                array_push($dibawahStandar, 0);
            }else{
                $table->addCell(1000 , $Body2Style)->addText(number_format((float)(($data_standar[$target_job_competencie->competency->name]['kurang'] * 100) / count($request->project_participant_ids)), 2, '.', '')."%", $cellTextStyle ,$cellCenter);
                $nilaiDibawah = number_format((float)(($data_standar[$target_job_competencie->competency->name]['kurang']) / count($request->project_participant_ids)), 2, '.', '');
                array_push($dibawahStandar, $nilaiDibawah);
            }
        }

        $section->addPageBreak();
        $section = $phpWord->addSection(array('orientation' => 'landscape'));
        $section->addText("Grafik 2. Sebaran Peserta berdasarkan Gap Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        // create chart
        $showGridLines = true;
        $showAxisLabels = true;

        $chart = $section->addChart("column", $kompetensi, $diatasStandar, null, htmlspecialchars("> Standar"));
        $chart->getStyle()->setWidth(Converter::inchToEmu(9))->setHeight(Converter::inchToEmu(5));
        $chart->getStyle()->setShowGridX($showGridLines);
        $chart->getStyle()->setShowGridY($showGridLines);
        $chart->getStyle()->setShowAxisLabels($showAxisLabels);

        $chart->addSeries($kompetensi, $samaDenganStandar, htmlspecialchars("= Standar"));
        $chart->addSeries($kompetensi, $dibawahStandar, htmlspecialchars("< Standar"));

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objectWriter->save(storage_path('Statistik Gap Kompetensi.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('Statistik Gap Kompetensi.docx'));
    }

    public function comparisonReport(Request $request){
        $projectParticipants = ProjectParticipant::whereIn('id', $request->project_participant_ids)->get();
        $projectParticipantByParticipant = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id')
        ->values()
        ->all();
        $projectParticipantByProject = $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('project_id')
        ->values()
        ->all();
        $targetJob = TargetJob::find($request->target_job_id);
        $projectCompetenceStandarts = ProjectCompetenceStandart::whereIn('project_id', $projectParticipants->pluck('project_id'))->get()->groupBy('project_id');
        $recommendationItems = ItemRecommendation::where('recommendation_id', $request->recommendation_id)->get();
        $typeLabels = ['Naik', 'Turun', 'Tetap', 'Berubah'];
        $participantGaps = [];
        $participantGapTypes = [];
        $participantGapTypePercents = [];
        $progressValues = [];
        $progressPercents = [];
        $averageProgress = [];
        $recommendationValues = [];
        $recommendationPercents = [];

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $section = $phpWord->addSection(array('orientation' => 'landscape'));

        $paragraphCenter = 'pStyle';
        $tableStyle = array(
            'cellMarginLeft' => 100,
            'bgColor' => 'ededed',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $headerMergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center' ,
            'vMerge' => 'restart'
        );
        $bodyColorStyle = array(
            'bgColor' => 'f2f2f2',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $mergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'vMerge' => 'continue'
        );
        $headerGridStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center',
            'gridSpan' => $projectCompetenceStandarts->first()->count() + 1,
        );
        $headerGridTable3Style = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center',
            'gridSpan' => $projectCompetenceStandarts->first()->count(),
        );
        $headerStaticGridStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'valign' => 'center',
            'gridSpan' => 4,
        );
        $headerLeftStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1,
            'textDirection'=>\PhpOffice\PhpWord\Style\Cell::TEXT_DIR_BTLR,
            'valign' => 'center' ,
            'vMerge' => 'restart'
        );
        $BodyStyle = array(
            'bgColor' => 'ffffff',
            'borderTopColor' =>'000000',
            'borderTopSize' => 1,
            'borderRightColor' =>'000000',
            'borderRightSize' => 1,
            'borderBottomColor' =>'000000',
            'borderBottomSize' => 1,
            'borderLeftColor' =>'000000',
            'borderLeftSize' => 1
        );
        $cellTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => false
        );
        $cellBoldTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => true
        );
        $cellCenter = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center',);
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addText("Komparasi Hasil Assessment Per Perusahaan",
            array('name' => 'Calibri',
              'size' => 12,
              'color' => 'black',
              'bold' => true),
            $paragraphCenter);
        $section->addTextBreak();

        // assign data start here
        $section->addText("Nama Perusahaan\t: " . $projectParticipants->first()->participant->company->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        $section->addText("Target Job\t\t: " . $targetJob->name,
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        // end data here
        $section->addText("Waktu Pengambilan Data\t: ",
            array('name' => 'Calibri',
               'size' => 10,
               'color' => 'black'));
        $section->addTextBreak();

        $table = $section->addTable();
        $table->addRow();
        $table->addCell(800)->addText("Data II", $cellTextStyle, $cellCenter);
        $table->addCell(2400)->addText("Pengambilan Data Tanggal", $cellTextStyle, $cellCenter);
        // assign data here
        $table->addCell(3000)->addText(Carbon::parse($projectParticipantByParticipant[0][0]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][0]->project->end_date)->translatedFormat('d F Y'), $cellTextStyle, $cellCenter);
        $table->addRow();
        $table->addCell()->addText("Data I", $cellTextStyle, $cellCenter);
        $table->addCell()->addText("Pengambilan Data Tanggal", $cellTextStyle, $cellCenter);
        // assign data here
        $table->addCell()->addText(Carbon::parse($projectParticipantByParticipant[0][1]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][1]->project->end_date)->translatedFormat('d F Y'), $cellTextStyle, $cellCenter);
        $section->addTextBreak();

        $section->addText("Tabel 1. Data Komparasi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(600 , $headerMergeStyle)->addText("No" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(4000 , $headerMergeStyle)->addText("Nama" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(3000 , $headerMergeStyle)->addText("Jabatan Saat Ini" , $cellBoldTextStyle , $cellCenter);

        $table->addCell(1000 , $headerGridStyle)->addText("Data II (".Carbon::parse($projectParticipantByParticipant[0][0]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][0]->project->end_date)->translatedFormat('d F Y').")" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridStyle)->addText("Data I (".Carbon::parse($projectParticipantByParticipant[0][1]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][1]->project->end_date)->translatedFormat('d F Y').")" , $cellBoldTextStyle , $cellCenter);
        $table->addRow(1400);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        // assign data here
        foreach($projectCompetenceStandarts->first() as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellBoldTextStyle , $cellCenter);
        }
        $table->addCell(1000 , $headerLeftStyle)->addText("Rata-rata" , $cellBoldTextStyle , $cellCenter);
        // assign data here
        foreach($projectCompetenceStandarts->first() as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellBoldTextStyle , $cellCenter);
        }
        $table->addCell(1000 , $headerLeftStyle)->addText("Rata-rata" , $cellBoldTextStyle , $cellCenter);
        // assign data here
        $no = 0;
        foreach($projectParticipantByParticipant as $projectParticipant){
            $table->addRow();
            $no++;
            $table->addCell(600, $BodyStyle)->addText($no, $cellTextStyle ,$cellCenter);
            $table->addCell(600, $BodyStyle)->addText($projectParticipant->first()->participant->name, $cellTextStyle ,$cellCenter);
            $table->addCell(600, $BodyStyle)->addText($projectParticipant->first()->participant->position->name, $cellTextStyle ,$cellCenter);

            $firstProjectParticipantValues = $projectParticipant[0]->projectParticipantValues;
            foreach($firstProjectParticipantValues as $projectParticipantValue){
                $table->addCell(1000, $BodyStyle)->addText($projectParticipantValue->value, $cellTextStyle ,$cellCenter);
            }
            $table->addCell(1000, $BodyStyle)->addText($firstProjectParticipantValues->average('value'), $cellTextStyle ,$cellCenter);

            $secondProjectParticipantValues = $projectParticipant[1]->projectParticipantValues;
            foreach($secondProjectParticipantValues as $projectParticipantValue){
                $table->addCell(1000, $BodyStyle)->addText($projectParticipantValue->value, $cellTextStyle ,$cellCenter);
            }
            $table->addCell(1000, $BodyStyle)->addText($secondProjectParticipantValues->average('value'), $cellTextStyle ,$cellCenter);

            $gaps = [];
            $above = 0;
            $below = 0;
            $equal = 0;
            $change = 0;

            for($i = 0; $i < count($firstProjectParticipantValues); $i++) {
                $gap = $firstProjectParticipantValues[$i]->value - $secondProjectParticipantValues[$i]->value;
                if($gap < 0){
                    $below++;
                    $change++;
                } else if ($gap == 0){
                    $equal++;
                } else if($gap > 0){
                    $above ++;
                    $change++;
                }

                array_push($gaps, $gap);
            }

            $total = $above + $below + $equal + $change;
            $gapTypes = [$above, $below, $equal, $change];
            $gapTypePercents = [number_format((float)($above/$total), 2, '.', ''), number_format((float)($below/$total), 2, '.', ''), number_format((float)($equal/$total), 2, '.', ''), number_format((float)($change/$total), 2, '.', '')];

            array_push($participantGaps, $gaps);
            array_push($participantGapTypes, $gapTypes);
            array_push($participantGapTypePercents, $gapTypePercents);
        }
        $section->addPageBreak();

        $section->addText("Tabel 2. Komparasi Hasil Asessment Peserta" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(600 , $headerMergeStyle)->addText("No" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(4000 , $headerMergeStyle)->addText("Nama" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(3000 , $headerMergeStyle)->addText("Jabatan Saat Ini" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridStyle)->addText("Perkembangan (Data 2 - Data 1)" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerStaticGridStyle)->addText("Σ Nilai Kompetensi" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerStaticGridStyle)->addText("% Nilai Kompetensi" , $cellBoldTextStyle , $cellCenter);
        $table->addRow(1400);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        $table->addCell(null , $mergeStyle);
        // assign data start here
        foreach($projectCompetenceStandarts->first() as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellBoldTextStyle , $cellCenter);
        }
        $table->addCell(1000 , $headerLeftStyle)->addText("Rata-rata" , $cellBoldTextStyle , $cellCenter);
        // end here
        $table->addCell(1000 , $headerLeftStyle)->addText("Naik" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Turun" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Tetap" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Berubah" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Naik" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Turun" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Tetap" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerLeftStyle)->addText("Berubah" , $cellBoldTextStyle , $cellCenter);
        // assign data start here
        $above = 0;
        $below = 0;
        $equal = 0;
        $change = 0;
        for($i = 0; $i < count($participantGaps); $i++){
            $total = 0;

            $table->addRow();
            $table->addCell(600, $BodyStyle)->addText($i + 1, $cellTextStyle ,$cellCenter);
            $table->addCell(4000, $BodyStyle)->addText($projectParticipantByParticipant[$i]->first()->participant->name, $cellTextStyle ,$cellCenter);
            $table->addCell(3000, $BodyStyle)->addText($projectParticipantByParticipant[$i]->first()->participant->position->name, $cellTextStyle ,$cellCenter);

            for($j = 0; $j < count($participantGaps[$i]); $j++){
                $total += $participantGaps[$i][$j];
                $table->addCell(1000, $BodyStyle)->addText($participantGaps[$i][$j], $cellTextStyle ,$cellCenter);
            }

            $average = $total/count($participantGaps[$i]);
            if ($average > 0){
                $above++;
                $change++;
            } else if ($average == 0){
                $equal;
            } else if ($average < 0){
                $below++;
                $change++;
            }
            $table->addCell(1000, $BodyStyle)->addText($average, $cellTextStyle ,$cellCenter);

            for($j = 0; $j < count($participantGapTypes[$i]); $j++){
                $table->addCell(1000, $BodyStyle)->addText($participantGapTypes[$i][$j], $cellTextStyle ,$cellCenter);
            }

            for($j = 0; $j < count($participantGapTypePercents[$i]); $j++){
                $table->addCell(1000, $BodyStyle)->addText($participantGapTypePercents[$i][$j] * 100 ."%", $cellTextStyle ,$cellCenter);
            }
        }
        $total = $above + $below + $equal;
        $averageProgress = [
            [$above, number_format((float)($above/$total), 2, '.', '')],
            [$below, number_format((float)($below/$total), 2, '.', '')],
            [$equal, number_format((float)($equal/$total), 2, '.', '')],
            [$change, number_format((float)($change/$total), 2, '.', '')]
        ];
        $section->addPageBreak();

        for($i = 0; $i < count($projectCompetenceStandarts->first()); $i++){
            $above = 0;
            $below = 0;
            $equal = 0;
            $change = 0;

            for($j = 0; $j < count($participantGaps); $j++){
                if ($participantGaps[$j][$i] > 0){
                    $above++;
                    $change++;
                } else if ($participantGaps[$j][$i] == 0){
                    $equal++;
                } else if ($participantGaps[$j][$i] < 0){
                    $below++;
                    $change++;
                }
            }
            $total = $above + $below + $equal;

            array_push($progressValues, [$above, $below, $equal, $change]);
            array_push($progressPercents, [number_format((float)($above/$total), 2, '.', ''), number_format((float)($below/$total), 2, '.', ''), number_format((float)($equal/$total), 2, '.', ''), number_format((float)($change/$total), 2, '.', '')]);
        }

        $section->addText("Tabel 3. Sebaran Peserta berdasarkan Perbandingan Hasil Assessment" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000 , $headerMergeStyle)->addText("Komparasi Hasil Asessment" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridTable3Style)->addText("Kompetensi" , $cellBoldTextStyle , $cellCenter);
        $table->addRow(1400);
        $table->addCell(null , $mergeStyle);
        // assign data here
        foreach($projectCompetenceStandarts->first() as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellBoldTextStyle , $cellCenter);
        }

        for($i = 0; $i < count($typeLabels); $i++){
            $table->addRow();
            $table->addCell(400, $BodyStyle)->addText($typeLabels[$i], $cellTextStyle ,$cellCenter);
            for($j = 0; $j < count($progressValues); $j++){
                $table->addCell(100, $BodyStyle)->addText($progressValues[$j][$i], $cellTextStyle ,$cellCenter);
            }
        }
        $section->addPageBreak();

        $section->addText("Tabel 4. Presentase Sebaran Peserta berdasarkan Perbandingan Hasil Assessment" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000 , $headerMergeStyle)->addText("% Asesi" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerGridTable3Style)->addText("Kompetensi" , $cellBoldTextStyle , $cellCenter);
        $table->addRow(1400);
        $table->addCell(null , $mergeStyle);
        foreach($projectCompetenceStandarts->first() as $projectCompetenceStandart){
            $table->addCell(1000 , $headerLeftStyle)->addText($projectCompetenceStandart->competency->name , $cellBoldTextStyle , $cellCenter);
        }

        for($i = 0; $i < count($typeLabels); $i++){
            $table->addRow();
            $table->addCell(400, $BodyStyle)->addText($typeLabels[$i], $cellTextStyle ,$cellCenter);
            for($j = 0; $j < count($progressPercents); $j++){
                $table->addCell(100, $BodyStyle)->addText($progressPercents[$j][$i] * 100 .'%', $cellTextStyle ,$cellCenter);
            }
        }
        $section->addTextBreak();

        $section->addText("Grafik 1. Sebaran Peserta berdasarkan Perbandingan Hasil Assessment" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));

        // create chart
        $categories = $projectCompetenceStandarts->first()->pluck('competency.name');
        $serieses = [];

        for($i = 0; $i < count($typeLabels); $i++){
            $series = [];
            for($j = 0; $j < count($progressPercents); $j++){
                array_push($series, $progressPercents[$j][$i]);
            }
            array_push($serieses, $series);
        }

        $chart = $section->addChart("column", $categories, $serieses[0], null, "% Asesi Naik");
        $chart->addSeries($categories, $serieses[1], "% Asesi Turun");
        $chart->addSeries($categories, $serieses[2], "% Asesi Tetap");
        $chart->addSeries($categories, $serieses[3], "% Asesi Berubah");
        $chart->getStyle()->setWidth(Converter::inchToEmu(7))->setHeight(Converter::inchToEmu(4));
        $chart->getStyle()->setShowGridX(false);
        $chart->getStyle()->setShowGridY(true);
        $chart->getStyle()->setShowLegend(true);
        $chart->getStyle()->setShowAxisLabels(true);
        $chart->getStyle()->setDataLabelOptions(
            ['showVal' => false,
            'showCatName' => false,
            'showLegendKey' => true]);
        $section->addPageBreak();

        $section->addText("Tabel 5. Komparasi Nilai Rata-rata Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();

        $table->addCell(4000 , $headerMergeStyle)->addText("Nilai Rata-rata" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerMergeStyle)->addText("Σ" , $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerMergeStyle)->addText("%" , $cellBoldTextStyle , $cellCenter);

        for($i = 0; $i < count($typeLabels); $i++){
            $table->addRow();
            $table->addCell(400, $BodyStyle)->addText($typeLabels[$i], $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($averageProgress[$i][0], $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($averageProgress[$i][1] * 100 ."%", $cellTextStyle ,$cellCenter);
        }
        $section->addTextBreak();

        $section->addText("Grafik 2. Komparasi Nilai Rata-rata Kompetensi" ,
            array('name' => 'Calibri',
              'size' => 10,
              'color' => 'black'));
         // create chart percent
         $categories = array('%');
         $chart = $section->addChart("column", $categories, [$averageProgress[0][1]], null, 'Naik');
         $chart->addSeries($categories, [$averageProgress[1][1]], 'Turun');
         $chart->addSeries($categories, [$averageProgress[2][1]], 'Tetap');
         $chart->addSeries($categories, [$averageProgress[3][1]], 'Berubah');
         $chart->getStyle()->setWidth(Converter::inchToEmu(5))->setHeight(Converter::inchToEmu(3));
         $chart->getStyle()->setColors( array('e27cbe', '7c8be2', '7ce2a0'));
         $chart->getStyle()->setShowGridX(false);
         $chart->getStyle()->setShowGridY(true);
         $chart->getStyle()->setShowLegend(true);
         $chart->getStyle()->setShowAxisLabels(true);
         $chart->getStyle()->setDataLabelOptions(
            ['showVal' => true,
            'showCatName' => false,
            'showLegendKey' => true]);
        $section->addTextBreak();


        for($i = 0; $i < count($projectParticipantByProject); $i++){
            $value = [];
            for($k = 0; $k < count($recommendationItems); $k++){
                array_push($value, 0);
            }
            array_push($recommendationValues, $value);
            array_push($recommendationPercents, $value);
        }

        for($i = 0; $i < count($projectParticipantByProject); $i++){
            for($j = 0; $j < count($projectParticipantByProject[$i]); $j++){
                for($k = 0; $k < count($recommendationItems); $k++){
                    if($projectParticipantByProject[$i][$j]->item_recommendation_id == $recommendationItems[$k]->id){
                        $recommendationValues[$i][$k]++;
                    }
                }
            }
        }

        for($i = 0; $i < count($recommendationPercents); $i++){
            for($j = 0; $j < count($recommendationPercents[$i]); $j++){
                $recommendationPercents[$i][$j] = number_format((float)($recommendationValues[$i][$j] / count($projectParticipantByProject[0])), 2, '.', '');
            }
        }

        $section->addText("Tabel 6. Sebaran Peserta Berdasarkan Perubahan Rekomendasi" ,
            array('name' => 'Calibri',
                'size' => 10,
                'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000 , $headerMergeStyle)->addText("Kategori" , $cellBoldTextStyle , $cellCenter);

        $table->addCell(1000 , $headerMergeStyle)->addText(Carbon::parse($projectParticipantByParticipant[0][0]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][0]->project->end_date)->translatedFormat('d F Y'), $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerMergeStyle)->addText(Carbon::parse($projectParticipantByParticipant[0][1]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][1]->project->end_date)->translatedFormat('d F Y'), $cellBoldTextStyle , $cellCenter);

        for($i = 0; $i < count($recommendationItems); $i++){
            $table->addRow();
            $table->addCell(400, $bodyColorStyle)->addText("Σ ".$recommendationItems[$i]->name, $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($recommendationValues[0][$i], $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($recommendationValues[1][$i], $cellTextStyle ,$cellCenter);
        }
        $section->addTextBreak();

        $section->addText("Tabel 7. Persentase Sebaran Peserta Berdasarkan Perubahan Rekomendasi" ,
            array('name' => 'Calibri',
                'size' => 10,
                'color' => 'black'));
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000 , $headerMergeStyle)->addText("Kategori" , $cellBoldTextStyle , $cellCenter);

        $table->addCell(1000 , $headerMergeStyle)->addText(Carbon::parse($projectParticipantByParticipant[0][0]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][0]->project->end_date)->translatedFormat('d F Y'), $cellBoldTextStyle , $cellCenter);
        $table->addCell(1000 , $headerMergeStyle)->addText(Carbon::parse($projectParticipantByParticipant[0][1]->project->start_date)->translatedFormat('d F Y') . " - " . Carbon::parse($projectParticipantByParticipant[0][1]->project->end_date)->translatedFormat('d F Y'), $cellBoldTextStyle , $cellCenter);

        for($i = 0; $i < count($recommendationItems); $i++){
            $table->addRow();
            $table->addCell(400, $bodyColorStyle)->addText("% ".$recommendationItems[$i]->name, $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($recommendationPercents[0][$i] * 100 ."%", $cellTextStyle ,$cellCenter);
            $table->addCell(100, $BodyStyle)->addText($recommendationPercents[1][$i] * 100 ."%", $cellTextStyle ,$cellCenter);
        }
        $section->addTextBreak();

        $section->addText("Grafik 3. Sebaran Peserta Berdasarkan Perubahan Rekomendasi" ,
        array('name' => 'Calibri',
        'size' => 10,
        'color' => 'black'));
        // create chart percent
        $categories = array('Data I', 'Data II');
        $series = [];
        for($i = 0; $i < count($recommendationItems); $i++){
            array_push($series, [$recommendationPercents[1][$i], $recommendationPercents[0][$i]]);
        }
        $chart = $section->addChart("column", $categories, $series[0], null, $recommendationItems[0]->name);
        for($i = 1; $i < count($recommendationItems); $i++){
            $chart->addSeries($categories, $series[$i], $recommendationItems[$i]->name);
        }
        $chart->getStyle()->setColors( array('e27cbe', '7c8be2', '7ce2a0'));
        $chart->getStyle()->setWidth(Converter::inchToEmu(4))->setHeight(Converter::inchToEmu(3));
        $chart->getStyle()->setShowGridX(false);
        $chart->getStyle()->setShowGridY(true);
        $chart->getStyle()->setShowLegend(true);
        $chart->getStyle()->setShowAxisLabels(true);
        $chart->getStyle()->setDataLabelOptions(
            ['showVal' => true,
            'showCatName' => false,
            'showLegendKey' => true]);

        $objectWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objectWriter->save(storage_path('Laporan Komparasi Hasil Assessment Per Perusahaan.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('Laporan Komparasi Hasil Assessment Per Perusahaan.docx'));
    }

    protected function validateParticipant(Request $request){
        $projectParticipantByParticipant = ProjectParticipant::whereIn('id', $request->project_participant_ids)->get()
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id')
        ->values()
        ->all();

        foreach($projectParticipantByParticipant as $projectParticipants){
            if(count($projectParticipants) != 2){
                return false;
            }
        }

        return true;
    }
}
