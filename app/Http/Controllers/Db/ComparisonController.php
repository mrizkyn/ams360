<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Company;
use App\Models\DB\TargetJob;
use App\Models\DB\ItemRecommendation;
use App\Models\DB\Project;
use App\Models\DB\ProjectParticipant;
use App\Models\DB\ProjectParticipantValue;
use App\Models\DB\ProjectCompetenceStandart;
use App\Models\DB\Recommendation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\DB\TargetJobCompetency;
use PDF;
use PhpOffice\PhpWord\Shared\Converter;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class ComparisonController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('db_assessment.comparison.index', [
            'companies' => Company::all(),
            'targetJobs' => TargetJob::all(),
            'recommendationTypes' => Recommendation::all()
        ]);
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

        if($this->validateParticipant($request->first_project_participant_ids) && $this->validateParticipant($request->second_project_participant_ids)){
            return $this->comparisonReport($request);
        }else{
            $validator->after(function ($validator) {
                $validator->errors()->add('alert', 'Asesi yang dipilih harus ada pada 2 project');
            });
        }

        $validator->validate();
    }

    public function showParticipants(Request $request)
    {
        $firstProjectIds = Project::where('company_id', $request->first_company_id)
        ->where('target_job_id', $request->target_job_id)
        ->where('recommendation_id', $request->recommendation_type)
        ->whereBetween('end_date', [$request->start_date, $request->end_date])
        ->orderBy('end_date', 'DESC')
        ->pluck('id');

        $secondProjectIds = Project::where('company_id', $request->second_company_id)
        ->where('target_job_id', $request->target_job_id)
        ->where('recommendation_id', $request->recommendation_type)
        ->whereBetween('end_date', [$request->start_date, $request->end_date])
        ->orderBy('end_date', 'DESC')
        ->pluck('id');

        $firstProjectParticipants = ProjectParticipant::whereIn('project_id', $firstProjectIds)
        ->get()
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id');
        $secondProjectParticipants = ProjectParticipant::whereIn('project_id', $secondProjectIds)
        ->get()
        ->sortByDesc('project.end_date')
        ->groupBy('participant_id');

        return view('db_assessment.comparison.showParticipant', [
            'first_participants' => $firstProjectParticipants,
            'second_participants' => $secondProjectParticipants,
            'recommendation_type' => $request->recommendation_type,
            'first_company_id' =>  $request->first_company_id,
            'second_company_id' => $request->second_company_id,
            'target_job_id' => $request->target_job_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date
        ]);
    }

    public function getComparisonData(Request $request)
    {
        $firstProjectIds = Project::where('company_id', $request->first_company)
        ->where('target_job_id', $request->first_target_job)
        ->whereBetween('end_date', [$request->startDate, $request->endDate])
        ->orderBy('end_date', 'DESC')
        ->take(2)
        ->pluck('id');

        $secondProjectIds = Project::where('company_id', $request->second_company)
        ->where('target_job_id', $request->second_target_job)
        ->whereBetween('end_date', [$request->startDate, $request->endDate])
        ->orderBy('end_date', 'DESC')
        ->take(2)
        ->pluck('id');

        $firstProjectParticipants = ProjectParticipant::whereIn('project_id', $firstProjectIds)->get()->groupBy('participant_id');
        $secondProjectParticipants = ProjectParticipant::whereIn('project_id', $secondProjectIds)->get()->groupBy('participant_id');

        $firstAverage = $this->calculateGap($firstProjectParticipants);
        $secondAverage = $this->calculateGap($secondProjectParticipants);
        $totalAverage = $this->calculateTotal($firstAverage, $secondAverage);

        return array_merge($firstAverage, $secondAverage, $totalAverage);
    }

    protected function calculateTotal($firstAverage, $secondAverage)
    {
        $total = collect(array_merge($firstAverage, $secondAverage));

        $constant = $total->where('type', 'constant')->sum('value');
        $change = $total->where('type', 'change')->sum('value');
        $significant = $total->where('type', 'significant')->sum('value');

        $sum = $constant + $change + $significant;

        $averageConstant = ($constant / $sum) * 100;
        $averageChange = ($change / $sum) * 100;
        $averageSignificant = ($significant / $sum) * 100;

        return [
            ['type' => 'constant', 'value' => $constant, 'percent' => $averageConstant],
            ['type' => 'change', 'value' => $change, 'percent' => $averageChange],
            ['type' => 'significant', 'value' => $significant, 'percent' => $averageSignificant]
        ];
    }

    protected function calculateGap($projectParticipants)
    {
        $constant = 0;
        $change = 0;
        $significant = 0;

        foreach ($projectParticipants as $projectParticipant) {
            if (count($projectParticipant) == 2) {
                $averages = [];
                foreach ($projectParticipant->pluck('id') as $projectParticipantId) {
                    $average = ProjectParticipantValue::where('project_participant_id', $projectParticipantId)->avg('value');
                    array_push($averages, $average);
                }

                $gap = $averages[1] - $averages[0];

                if ($gap == 0) {
                    $constant++;
                } else if ($gap != 0) {
                    $change++;
                } else if ($gap >= 1) {
                    $significant++;
                }
            }
        }

        $total = $constant + $change + $significant;
        $constantPercent = ($constant / $total) * 100;
        $changePercent = ($change / $total) * 100;;
        $significantPercent = ($significant / $total) * 100;;

        return [
            ['type' => 'constant', 'value' => $constant, 'percent' => $constantPercent],
            ['type' => 'change', 'value' => $change, 'percent' => $changePercent],
            ['type' => 'significant', 'value' => $significant, 'percent' => $significantPercent]
        ];
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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

    public function getTargetJobByCompany(Request $request)
    {
        $firstTargetJobIds = Project::where('company_id', $request->first_company_id)
        ->get()
        ->pluck('target_job_id')
        ->unique();

        $secondTargetJobIds = Project::where('company_id', $request->second_company_id)
        ->get()
        ->pluck('target_job_id')
        ->unique();

        $targetJobIds = $firstTargetJobIds->intersect($secondTargetJobIds)->all();
        $targetJobs = TargetJob::whereIn('id', $targetJobIds)->get();
        return $targetJobs;
    }

    public function getRecommendationTypeByCompany(Request $request)
    {
        $firstRecommendationIds = Project::where('company_id', $request->first_company_id)
        ->get()
        ->pluck('recommendation_id')
        ->unique();

        $secondRecommendationIds = Project::where('company_id', $request->second_company_id)
        ->get()
        ->pluck('recommendation_id')
        ->unique();

        $recommendationIds = $firstRecommendationIds->intersect($secondRecommendationIds);
        $recommendationTypes = Recommendation::whereIn('id', $recommendationIds)->get();
        return $recommendationTypes;
    }

    public function comparisonReport(Request $request)
    {
        $competencies = array();
        $header_competencies = array();
        $recomendation = array();
        $data_project = array();
        $data_standar = array();
        $standar = array();

        $tglAwalProject1Perusahaan1 = null;
        $tglAwalProject2Perusahaan1 = null;
        $tglAwalProjectPerusahaan1 = null;
        $tglAkhirProject1Perusahaan1 = null;
        $tglAkhirProject2Perusahaan1 = null;
        $tglAkhirProjectPerusahaan1 = null;

        $tglAwalProject1Perusahaan2 = null;
        $tglAwalProject2Perusahaan2 = null;
        $tglAwalProjectPerusahaan2 = null;
        $tglAkhirProject1Perusahaan2 = null;
        $tglAkhirProject2Perusahaan2 = null;
        $tglAkhirProjectPerusahaan2 = null;

        $projectIds1 = ProjectParticipant::whereIn('id', $request->first_project_participant_ids)
        ->groupBy('project_id')
        ->pluck('project_id');

        $projectIds2 = ProjectParticipant::whereIn('id', $request->second_project_participant_ids)
        ->groupBy('project_id')
        ->pluck('project_id');

        //isi variabel awal akhir pt ke 1
        for ($i=0; $i < count($projectIds1); $i++) { 
            $project = Project::find($projectIds1[$i]);
            if ($i == 0) {
                $tglAwalProject1Perusahaan1 = $project->start_date;
                $tglAkhirProject1Perusahaan1 = $project->end_date;
            } else {
                $tglAwalProject2Perusahaan1 = $project->start_date;
                $tglAkhirProject2Perusahaan1 = $project->end_date;
            }
        }

        // tgl awal project pt ke 1
        if ($tglAwalProject1Perusahaan1 < $tglAwalProject2Perusahaan1) {
            $tglAwalProjectPerusahaan1 = $tglAwalProject1Perusahaan1;
        } else {
            $tglAwalProjectPerusahaan1 = $tglAwalProject2Perusahaan1;
        }

        // tgl akhir project pt ke 1
        if ($tglAkhirProject1Perusahaan1 > $tglAkhirProject2Perusahaan1) {
            $tglAkhirProjectPerusahaan1 = $tglAkhirProject1Perusahaan1;
        } else {
            $tglAkhirProjectPerusahaan1 = $tglAkhirProject2Perusahaan1;
        }

        //isi variabel awal akhir pt ke 2
        for ($i=0; $i < count($projectIds2); $i++) { 
            $project = Project::find($projectIds2[$i]);
            if ($i == 0) {
                $tglAwalProject1Perusahaan2 = $project->start_date;
                $tglAkhirProject1Perusahaan2 = $project->end_date;
            } else {
                $tglAwalProject2Perusahaan2 = $project->start_date;
                $tglAkhirProject2Perusahaan2 = $project->end_date;
            }
        }

        // tgl awal project pt ke 2
        if ($tglAwalProject1Perusahaan2 < $tglAwalProject2Perusahaan2) {
            $tglAwalProjectPerusahaan2 = $tglAwalProject1Perusahaan2;
        } else {
            $tglAwalProjectPerusahaan2 = $tglAwalProject2Perusahaan2;
        }

        // tgl akhir project pt ke 2
        if ($tglAkhirProject1Perusahaan2 > $tglAkhirProject2Perusahaan2) {
            $tglAkhirProjectPerusahaan2 = $tglAkhirProject1Perusahaan2;
        } else {
            $tglAkhirProjectPerusahaan2 = $tglAkhirProject2Perusahaan2;
        }

        $first_company = Company::find($request->first_company_id);
        $second_company = Company::find($request->second_company_id);
        $recomendations = Recommendation::find($request->recommendation_type);
        $item_recomendastions = ItemRecommendation::where('recommendation_id', $recomendations->id)->get();

        $target_job = TargetJob::find($request->target_job_id);
        $target_job_competencies = TargetJobCompetency::with('competency')->where('target_job_id', $target_job->id)->get();

        $participant = [
            $first_company->name => count($request->first_project_participant_ids),
            $second_company->name => count($request->second_project_participant_ids)
        ];

        $participants = [
            $first_company->name => count($this->participantsComparison($request->first_project_participant_ids)),
            $second_company->name => count($this->participantsComparison($request->second_project_participant_ids))
        ];

        // return $participants;

        $companies = [
            $first_company->name,
            $second_company->name
        ];

        foreach ($target_job_competencies as $item) {
            array_push($competencies, $item->competency->name);
        }

        foreach ($competencies as $competency) {
            $header_competencies[$competency] = [
                $first_company->name,
                $second_company->name
            ];
        }

        $first_participants = $this->participantsComparison($request->first_project_participant_ids);
        $second_participants = $this->participantsComparison($request->second_project_participant_ids);
        
        
        $first_data = $this->recapComparison($first_participants);
        $second_data = $this->recapComparison($second_participants);
        
        
        // return $first_data;

        // $first_data = $this->recapComparison($request->first_project_participant_ids);
        // $second_data = $this->recapComparison($request->second_project_participant_ids);
        
        
        $recomendation[$first_company->name] = $this->recomendations($first_data);
        $recomendation[$second_company->name] = $this->recomendations($second_data);
        
        
        $typeLabels = ['Naik', 'Turun', 'Tetap', 'Berubah'];
        $firstAverageProgress = $this->assessmentComparison($request->first_project_participant_ids);
        $secondAverageProgress = $this->assessmentComparison($request->second_project_participant_ids);
        
        $data_project[$first_company->name] = $this->dataProject($target_job_competencies, $first_participants);
        $data_project[$second_company->name] = $this->dataProject($target_job_competencies, $second_participants);
        
        // $data_project[$first_company->name] = $this->dataProject2($target_job_competencies, $request->first_project_participant_ids);
        // $data_project[$second_company->name] = $this->dataProject2($target_job_competencies, $request->second_project_participant_ids);
        
        $standar[$first_company->name] = $this->standarComparison($first_participants, $target_job_competencies);
        $standar[$second_company->name] = $this->standarComparison($second_participants, $target_job_competencies);
        
        
        // $standar[$first_company->name] = $this->standarComparison2($request->first_project_participant_ids, $target_job_competencies);
        // $standar[$second_company->name] = $this->standarComparison2($request->second_project_participant_ids, $target_job_competencies);
        
        $data_standar[$first_company->name] = $this->standarValues($target_job_competencies, $first_participants, $standar, $first_company);
        $data_standar[$second_company->name] = $this->standarValues($target_job_competencies, $second_participants, $standar, $second_company);

        
        // $data_standar[$first_company->name] = $this->standarValues2($target_job_competencies, $request->first_project_participant_ids, $standar, $first_company);
        // $data_standar[$second_company->name] = $this->standarValues2($target_job_competencies, $request->second_project_participant_ids, $standar, $second_company);
        
        // return $data_standar;
        // return number_format((float) ($data_project['PT. A']['Analisa'][3] * 100) / $participant, 2, '.', '');

        // return $data_project;

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $section = $phpWord->addSection(array('orientation' => 'landscape'));

        $paragraphCenter = 'pStyle';
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $section->addText(
            "Komparasi Hasil Assessment Antar Perusahaan",
            array(
                'name' => 'Calibri',
                'size' => 12,
                'color' => 'black',
                'bold' => true
            ),
            $paragraphCenter
        );

        $tableStyle = array(
            'cellMarginLeft' => 100,
            'cellMarginTop' => 80
        );

        $headerStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1
        );

        $headerMergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1,
            'valign' => 'center',
            'vMerge' => 'restart'
        );

        $headerGridStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1,
            'gridSpan' => 2,
            'valign' => 'center'
        );

        $bodyStyle = array(
            'bgColor' => 'ffffff',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1
        );

        $body1Style = array(
            'bgColor' => 'ededed',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1
        );

        $cellTextStyle = array(
            'name' => 'Calibri',
            'size' => 10,
            'color' => 'black',
            'bold' => true
        );

        $mergeStyle = array(
            'bgColor' => 'bfbfbf',
            'borderTopColor' => '000000',
            'borderTopSize' => 1,
            'borderRightColor' => '000000',
            'borderRightSize' => 1,
            'borderBottomColor' => '000000',
            'borderBottomSize' => 1,
            'borderLeftColor' => '000000',
            'borderLeftSize' => 1,
            'vMerge' => 'continue'
        );

        // return $participant;

        $cellCenter = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center');
        $cellLeft = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'valign' => 'center');

        $section->addTextBreak();
        $section->addTextBreak();
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000, $headerStyle)->addText("Nama Perusahaan", $cellTextStyle, $cellLeft);
        foreach ($companies as $company) {
            $table->addCell(4000, $bodyStyle)->addText($company, $cellTextStyle, $cellCenter);
        }
        $table->addRow();
        $table->addCell(4000, $headerStyle)->addText("Target Job", $cellTextStyle, $cellLeft);
        foreach ($companies as $company) {
            $table->addCell(4000, $bodyStyle)->addText($target_job->name, $cellTextStyle, $cellCenter);
        }
        $table->addRow();
        $table->addCell(4000, $headerStyle)->addText("Tanggal Pengambilan Data", $cellTextStyle, $cellLeft);
        $table->addCell(4000, $bodyStyle)->addText(Carbon::parse($tglAwalProjectPerusahaan1)->translatedFormat('d F Y') . " - " . Carbon::parse($tglAkhirProjectPerusahaan1)->translatedFormat('d F Y'), $cellTextStyle, $cellCenter);
        $table->addCell(4000, $bodyStyle)->addText(Carbon::parse($tglAwalProjectPerusahaan2)->translatedFormat('d F Y') . " - " . Carbon::parse($tglAkhirProjectPerusahaan2)->translatedFormat('d F Y'), $cellTextStyle, $cellCenter);

        $section->addTextBreak();
        $section->addText(
            "A. Komparasi Statistik Sebaran Peserta Per Rekomendasi",
            array(
                'name' => 'Calibri',
                'size' => 12,
                'color' => 'black',
                'bold' => true
            )
        );

        $section->addTextBreak();
        $section->addText(
            "Tabel 1. Komparasi Jumlah Peserta",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );
        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000, $headerStyle)->addText("", $cellTextStyle, $cellLeft);
        foreach ($companies as $company) {
            $table->addCell(4000, $bodyStyle)->addText($company, $cellTextStyle, $cellCenter);
        }
        $table->addRow();
        $table->addCell(2000, $headerStyle)->addText("Jumlah Peserta", $cellTextStyle, $cellLeft);
        foreach ($companies as $company) {
            $table->addCell(3000, $bodyStyle)->addText($participants[$company], $cellTextStyle, $cellCenter);
        }

        $section->addPageBreak();
        $section->addText(
            "Tabel 2. Komparasi Sebaran Peserta Per Rekomendasi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(4000, $headerStyle)->addText("Kategori Rekomendasi", $cellTextStyle, $cellLeft);
        foreach ($companies as $company) {
            $table->addCell(2000, $headerStyle)->addText($company, $cellTextStyle, $cellCenter);
        }

        $indexValue = -1;
        foreach ($item_recomendastions as $item) {
            $indexValue++;
            ${"value" . $indexValue} = [];
            $table->addRow();
            $table->addCell(4000, $body1Style)->addText($item->name, $cellTextStyle, $cellLeft);
            foreach ($companies as $company) {
                if (!empty($recomendation[$company][$item->name])) {
                    $table->addCell(2000, $bodyStyle)->addText(number_format((float) (($recomendation[$company][$item->name] / $participants[$company]) * 100), 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push(${"value" . $indexValue}, number_format((float) (($recomendation[$company][$item->name] / $participants[$company])), 2, '.', ''));
                } else {
                    $table->addCell(2000, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push(${"value" . $indexValue}, 0);
                }
            }
        }

        $section->addPageBreak();
        $section->addText(
            "Grafik 1. Komparasi Sebaran Peserta Per Rekomendasi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        $showGridLines = true;
        $showAxisLabels = true;
        for ($i = 0; $i < count($item_recomendastions); $i++) {
            if ($i == 0) {
                $chart = $section->addChart("column", $companies, ${"value" . $i}, null, $item_recomendastions[$i]->name);
                $chart->getStyle()->setWidth(Converter::inchToEmu(9))->setHeight(Converter::inchToEmu(5));
                $chart->getStyle()->setShowGridX($showGridLines);
                $chart->getStyle()->setShowGridY($showGridLines);
                $chart->getStyle()->setShowLegend($showGridLines);
                $chart->getStyle()->setShowAxisLabels($showAxisLabels);
                $chart->getStyle()->setDataLabelOptions(
                    ['showVal' => true,
                    'showCatName' => false,
                    'showLegendKey' => true]);
            } else {
                $chart->addSeries($companies, ${"value" . $i}, $item_recomendastions[$i]->name);
            }
        }

        $section->addPageBreak();
        $section->addText(
            "B. Komparasi Statistik Gap Kompetensi",
            array(
                'name' => 'Calibri',
                'size' => 12,
                'color' => 'black',
                'bold' => true
            )
        );

        $section->addTextBreak();
        $section->addText(
            "Tabel 1.Komparasi Sebaran Peserta terhadap Nilai/Rating/Level untuk Tiap Kompetensi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000, $headerMergeStyle)->addText("Σ Nilai/Rating/Level", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            $table->addCell(3000, $headerGridStyle)->addText($competency, $cellTextStyle, $cellCenter);
        }

        $kompetensiPerPerusahaan = [];
        $table->addRow();
        $table->addCell(null, $mergeStyle);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                $table->addCell(1500, $headerStyle)->addText($company, $cellTextStyle, $cellCenter);
                array_push($kompetensiPerPerusahaan, $competency . " " . $company);
            }
        }
        $nilai5 = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("Nilai 5", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_project[$company][$competency][5])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($nilai5, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) ($data_project[$company][$competency][5] * 100) / $participants[$company], 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($nilai5, number_format((float) ($data_project[$company][$competency][5]) / $participants[$company], 2, '.', ''));
                }
            }
        }

        $nilai4 = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("Nilai 4", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_project[$company][$competency][4])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($nilai4, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) ($data_project[$company][$competency][4] * 100) / $participants[$company], 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($nilai4, number_format((float) ($data_project[$company][$competency][4]) / $participants[$company], 2, '.', ''));
                }
            }
        }
        $nilai3 = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("Nilai 3", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_project[$company][$competency][3])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($nilai3, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) ($data_project[$company][$competency][3] * 100) / $participants[$company], 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($nilai3, number_format((float) ($data_project[$company][$competency][3]) / $participants[$company], 2, '.', ''));
                }
            }
        }
        $nilai2 = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("Nilai 2", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_project[$company][$competency][2])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($nilai2, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) ($data_project[$company][$competency][2] * 100) / $participants[$company], 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($nilai2, number_format((float) ($data_project[$company][$competency][2]) / $participants[$company], 2, '.', ''));
                }
            }
        }
        $nilai1 = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("Nilai 1", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_project[$company][$competency][1])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($nilai1, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) ($data_project[$company][$competency][1] * 100) / $participants[$company], 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($nilai1, number_format((float) ($data_project[$company][$competency][1]) / $participants[$company], 2, '.', ''));
                }
            }
        }

        $section->addPageBreak();
        $section->addText(
            "Grafik 1. Komparasi Sebaran Peserta terhadap Nilai/Rating/Level untuk Tiap Kompetensi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        // create chart
        $showGridLines = true;
        $showAxisLabels = true;

        $chart = $section->addChart("column", $kompetensiPerPerusahaan, $nilai5, null, htmlspecialchars("Nilai 5"));
        $chart->getStyle()->setWidth(Converter::inchToEmu(9))->setHeight(Converter::inchToEmu(5));
        $chart->getStyle()->setShowGridX($showGridLines);
        $chart->getStyle()->setShowGridY($showGridLines);
        $chart->getStyle()->setShowLegend(true);
        $chart->getStyle()->setShowAxisLabels($showAxisLabels);
        $chart->getStyle()->setDataLabelOptions(
            ['showVal' => false,
            'showCatName' => false,
            'showLegendKey' => true]);

        $chart->addSeries($kompetensiPerPerusahaan, $nilai4, htmlspecialchars("Nilai 4"));
        $chart->addSeries($kompetensiPerPerusahaan, $nilai3, htmlspecialchars("Nilai 3"));
        $chart->addSeries($kompetensiPerPerusahaan, $nilai2, htmlspecialchars("Nilai 2"));
        $chart->addSeries($kompetensiPerPerusahaan, $nilai1, htmlspecialchars("Nilai 1"));

        $section->addPageBreak();
        $section->addText(
            "Tabel 2.Komparasi Sebaran Peserta berdasarkan Gap Kompetensi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000, $headerMergeStyle)->addText("% Kompetensi", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            $table->addCell(3000, $headerGridStyle)->addText($competency, $cellTextStyle, $cellCenter);
        }
        $table->addRow();
        $table->addCell(null, $mergeStyle);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                $table->addCell(1500, $headerStyle)->addText($company, $cellTextStyle, $cellCenter);
            }
        }
        $standarAtas = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("> Standar", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_standar[$company][$competency]['lebih'])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($standarAtas, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) (($data_standar[$company][$competency]['lebih'] * 100) / $participants[$company]), 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($standarAtas, number_format((float) (($data_standar[$company][$competency]['lebih']) / $participants[$company]), 2, '.', ''));
                }
            }
        }
        $standarSama = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("= Standar", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_standar[$company][$competency]['sama'])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($standarSama, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) (($data_standar[$company][$competency]['sama'] * 100) / $participants[$company]), 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($standarSama, number_format((float) (($data_standar[$company][$competency]['sama']) / $participants[$company]), 2, '.', ''));
                }
            }
        }
        $standarBawah = [];
        $table->addRow();
        $table->addCell(2000, $body1Style)->addText("< Standar", $cellTextStyle, $cellCenter);
        foreach ($competencies as $competency) {
            foreach ($companies as $company) {
                if (empty($data_standar[$company][$competency]['kurang'])) {
                    $table->addCell(1500, $bodyStyle)->addText("0%", $cellTextStyle, $cellCenter);
                    array_push($standarBawah, 0);
                } else {
                    $table->addCell(1500, $bodyStyle)->addText(number_format((float) (($data_standar[$company][$competency]['kurang'] * 100) / $participants[$company]), 2, '.', '') . "%", $cellTextStyle, $cellCenter);
                    array_push($standarBawah, number_format((float) (($data_standar[$company][$competency]['kurang']) / $participants[$company]), 2, '.', ''));
                }
            }
        }

        $section->addPageBreak();
        $section->addText(
            "Grafik 2.Komparasi Sebaran Peserta berdasarkan Gap Kompetensi",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        // create chart
        $showGridLines = true;
        $showAxisLabels = true;

        $chart = $section->addChart("column", $kompetensiPerPerusahaan, $standarAtas, null, htmlspecialchars("> Standar"));
        $chart->getStyle()->setWidth(Converter::inchToEmu(9))->setHeight(Converter::inchToEmu(5));
        $chart->getStyle()->setShowGridX($showGridLines);
        $chart->getStyle()->setShowGridY($showGridLines);
        $chart->getStyle()->setShowLegend(true);
        $chart->getStyle()->setShowAxisLabels($showAxisLabels);
        $chart->getStyle()->setDataLabelOptions(
            ['showVal' => false,
            'showCatName' => false,
            'showLegendKey' => true]);

        $chart->addSeries($kompetensiPerPerusahaan, $standarSama, htmlspecialchars("= Standar"));
        $chart->addSeries($kompetensiPerPerusahaan, $standarBawah, htmlspecialchars("< Standar"));

        $section->addPageBreak();
        $section->addText(
            "C. Komparasi Sebaran Peserta Berdasarkan Perbandingan Hasil Assessment",
            array(
                'name' => 'Calibri',
                'size' => 12,
                'color' => 'black',
                'bold' => true
            )
        );
        $section->addTextBreak();

        $section->addText(
            "Tabel 1. Komparasi Sebaran Peserta berdasarkan Perbandingan Hasil Assessment",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );

        $table = $section->addTable($tableStyle);
        $table->addRow();
        $table->addCell(2000, $headerMergeStyle)->addText("Nilai Rata-rata", $cellTextStyle, $cellCenter);
        $table->addCell(3000, $headerGridStyle)->addText("Σ", $cellTextStyle, $cellCenter);
        $table->addCell(3000, $headerGridStyle)->addText("%", $cellTextStyle, $cellCenter);
        $table->addRow();
        $table->addCell(null, $mergeStyle);
        $table->addCell(1500, $headerStyle)->addText($companies[0], $cellTextStyle, $cellCenter);
        $table->addCell(1500, $headerStyle)->addText($companies[1], $cellTextStyle, $cellCenter);
        $table->addCell(1500, $headerStyle)->addText($companies[0], $cellTextStyle, $cellCenter);
        $table->addCell(1500, $headerStyle)->addText($companies[1], $cellTextStyle, $cellCenter);
        for($i = 0; $i < count($typeLabels); $i++){
            $table->addRow();
            $table->addCell(2000, $bodyStyle)->addText($typeLabels[$i], $cellTextStyle ,$cellCenter);
            $table->addCell(2000, $bodyStyle)->addText($firstAverageProgress[$i][0], $cellTextStyle ,$cellCenter);
            $table->addCell(2000, $bodyStyle)->addText($secondAverageProgress[$i][0], $cellTextStyle ,$cellCenter);
            $table->addCell(2000, $bodyStyle)->addText($firstAverageProgress[$i][1] * 100 ."%", $cellTextStyle ,$cellCenter);
            $table->addCell(2000, $bodyStyle)->addText($secondAverageProgress[$i][1] * 100 ."%", $cellTextStyle ,$cellCenter);
        }
        $section->addTextBreak();

        $section->addText(
            "Grafik 1. Komparasi Sebaran Peserta berdasarkan Perbandingan Hasil Assessment",
            array(
                'name' => 'Calibri',
                'size' => 10,
                'color' => 'black'
            )
        );
        // create chart
        $categories = array($companies[0], $companies[1]);
        $series = [];
        for($i = 0; $i < count($typeLabels); $i++){
            array_push($series, [$firstAverageProgress[$i][1], $secondAverageProgress[$i][1]]);
        }
        $chart = $section->addChart("column", $categories, $series[0], null, $typeLabels[0]);
        for($i = 1; $i < count($typeLabels); $i++){
            $chart->addSeries($categories, $series[$i], $typeLabels[$i]);
        }
        $chart->getStyle()->setWidth(Converter::inchToEmu(7))->setHeight(Converter::inchToEmu(3));
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
            $objectWriter->save(storage_path('Komparasi hasil asessment antar perusahaan.docx'));
        } catch (Exception $e) {
        }

        return response()->download(storage_path('Komparasi hasil asessment antar perusahaan.docx'));
    }

    protected function assessmentComparison($project_participant_ids){
        $projectParticipants = ProjectParticipant::whereIn('id', $project_participant_ids)->get();
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

        $participantGaps = [];
        foreach($projectParticipantByParticipant as $projectParticipant){
            $firstProjectParticipantValues = $projectParticipant[0]->projectParticipantValues;
            $secondProjectParticipantValues = $projectParticipant[1]->projectParticipantValues;

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

            array_push($participantGaps, $gaps);
        }

        $above = 0;
        $below = 0;
        $equal = 0;
        $change = 0;
        for($i = 0; $i < count($participantGaps); $i++){
            $total = 0;

            for($j = 0; $j < count($participantGaps[$i]); $j++){
                $total += $participantGaps[$i][$j];
            }

            $average = $total/count($participantGaps[$i]);
            if ($average > 0){
                $above++;
                $change++;
            } else if ($average == 0){
                $equal++;
            } else if ($average < 0){
                $below++;
                $change++;
            }
        }

        $total = $above + $below + $equal;
        $averageProgress = [
            [$above, number_format((float)($above/$total), 2, '.', '')],
            [$below, number_format((float)($below/$total), 2, '.', '')],
            [$equal, number_format((float)($equal/$total), 2, '.', '')],
            [$change, number_format((float)($change/$total), 2, '.', '')]
        ];

        return $averageProgress;
    }

    public function participantsComparison($project_participant_ids){
        $projectParticipants = ProjectParticipant::whereIn('id', $project_participant_ids)->get();
        $projectParticipantByParticipant =  $projectParticipants
        ->sortByDesc('project.end_date')
        ->groupBy('project_id')
        ->values()
        ->all();

        return $projectParticipantByParticipant[0];
    }

    public function recapComparison($project_participant_ids){
        $participants = array();
        foreach ($project_participant_ids as $participant) {
            array_push( $participants , $participant->id);
        }
        return DB::connection('mysql2')->table('project_participations')
        ->join('item_recommendations', 'project_participations.item_recommendation_id', '=', 'item_recommendations.id')
        ->select(DB::raw('item_recommendations.name, COUNT(project_participations.item_recommendation_id) AS jumlah'))
        ->whereIn('project_participations.id', $participants)
        ->groupBy('item_recommendations.name')
        ->get();
    }

    public function recapComparison2($project_participant_ids){
        $participants = array();
        foreach ($project_participant_ids as $participant) {
            array_push( $participants , $participant);
        }
        return DB::connection('mysql2')->table('project_participations')
        ->join('item_recommendations', 'project_participations.item_recommendation_id', '=', 'item_recommendations.id')
        ->select(DB::raw('item_recommendations.name, COUNT(project_participations.item_recommendation_id) AS jumlah'))
        ->whereIn('project_participations.id', $participants)
        ->groupBy('item_recommendations.name')
        ->get();
    }

    public function recomendations($data_recap){
        $recomendation = array();
        for ($i = 0; $i < count($data_recap); $i++) {
            $recomendation[$data_recap[$i]->name] = $data_recap[$i]->jumlah;
        }

        return $recomendation;
    }

    public function dataProject($target_job_competencies , $project_participant_ids){
        $data_project = array();
        foreach ($target_job_competencies as $item) {
            $value1 = 0;
            $value2 = 0;
            $value3 = 0;
            $value4 = 0;
            $value5 = 0;
            for ($i = 0; $i < count($project_participant_ids); $i++) {
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $item->competency_id, 'project_participant_id' => $project_participant_ids[$i]->id])->get();
                if ($project_participant_values[0]->value == 1) {
                    $value1 += 1;
                    $data_project[$project_participant_values[0]->competency->name][1] = $value1;
                } else if ($project_participant_values[0]->value == 2) {
                    $value2 += 1;
                    $data_project[$project_participant_values[0]->competency->name][2] = $value2;
                } else if ($project_participant_values[0]->value == 3) {
                    $value3 += 1;
                    $data_project[$project_participant_values[0]->competency->name][3] = $value3;
                } else if ($project_participant_values[0]->value == 4) {
                    $value4 += 1;
                    $data_project[$project_participant_values[0]->competency->name][4] = $value4;
                } else {
                    $value5 += 1;
                    $data_project[$project_participant_values[0]->competency->name][5] = $value5;
                }
            }
        }

        return $data_project;
    }

    public function dataProject2($target_job_competencies , $project_participant_ids){
        $data_project = array();
        foreach ($target_job_competencies as $item) {
            $value1 = 0;
            $value2 = 0;
            $value3 = 0;
            $value4 = 0;
            $value5 = 0;
            for ($i = 0; $i < count($project_participant_ids); $i++) {
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $item->competency_id, 'project_participant_id' => $project_participant_ids[$i]])->get();
                if ($project_participant_values[0]->value == 1) {
                    $value1 += 1;
                    $data_project[$project_participant_values[0]->competency->name][1] = $value1;
                } else if ($project_participant_values[0]->value == 2) {
                    $value2 += 1;
                    $data_project[$project_participant_values[0]->competency->name][2] = $value2;
                } else if ($project_participant_values[0]->value == 3) {
                    $value3 += 1;
                    $data_project[$project_participant_values[0]->competency->name][3] = $value3;
                } else if ($project_participant_values[0]->value == 4) {
                    $value4 += 1;
                    $data_project[$project_participant_values[0]->competency->name][4] = $value4;
                } else {
                    $value5 += 1;
                    $data_project[$project_participant_values[0]->competency->name][5] = $value5;
                }
            }
        }

        return $data_project;
    }

    public function standarComparison($project_participant_ids , $target_job_competencies){
        $standar = array();
        for ($i = 0; $i < count($project_participant_ids); $i++) {
            foreach ($target_job_competencies as $target_job_competencie) {
                $project_participants = ProjectParticipant::find($project_participant_ids[$i]->id);
                $standar_competencies = ProjectCompetenceStandart::with('competency')->where(['competence_id' => $target_job_competencie->competency_id, 'project_id' => $project_participants->project_id])->get();
                foreach ($standar_competencies as $standar_competency) {
                    $standar[$project_participants->project_id][$standar_competency->competency->name] = $standar_competency->value;
                }
            }
        }
        return $standar;
    }

    public function standarComparison2($project_participant_ids , $target_job_competencies){
        $standar = array();
        for ($i = 0; $i < count($project_participant_ids); $i++) {
            foreach ($target_job_competencies as $target_job_competencie) {
                $project_participants = ProjectParticipant::find($project_participant_ids[$i]);
                $standar_competencies = ProjectCompetenceStandart::with('competency')->where(['competence_id' => $target_job_competencie->competency_id, 'project_id' => $project_participants->project_id])->get();
                foreach ($standar_competencies as $standar_competency) {
                    $standar[$project_participants->project_id][$standar_competency->competency->name] = $standar_competency->value;
                }
            }
        }
        return $standar;
    }

    public function standarValues($target_job_competencies, $project_participant_ids, $standar , $company){
        $data_standar = array();
        foreach ($target_job_competencies as $target_job_competencie) {
            $lebih = 0;
            $sama = 0;
            $kurang = 0;
            for ($i = 0; $i < count($project_participant_ids); $i++) {
                $project_participants = ProjectParticipant::find($project_participant_ids[$i]->id);
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $target_job_competencie->competency_id, 'project_participant_id' => $project_participant_ids[$i]->id])->get();
                if ($project_participant_values[0]->value > $standar[$company->name][$project_participants->project_id][$project_participant_values[0]->competency->name]) {
                    $lebih += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['lebih'] = $lebih;
                } else if ($project_participant_values[0]->value == $standar[$company->name][$project_participants->project_id][$project_participant_values[0]->competency->name]) {
                    $sama += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['sama'] = $sama;
                } else {
                    $kurang += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['kurang'] = $kurang;
                }
            }
        }
        return $data_standar;
    }

    public function standarValues2($target_job_competencies, $project_participant_ids, $standar , $company){
        $data_standar = array();
        foreach ($target_job_competencies as $target_job_competencie) {
            $lebih = 0;
            $sama = 0;
            $kurang = 0;
            for ($i = 0; $i < count($project_participant_ids); $i++) {
                $project_participants = ProjectParticipant::find($project_participant_ids[$i]);
                $project_participant_values = ProjectParticipantValue::with('competency')->where(['competency_id' => $target_job_competencie->competency_id, 'project_participant_id' => $project_participant_ids[$i]])->get();
                if ($project_participant_values[0]->value > $standar[$company->name][$project_participants->project_id][$project_participant_values[0]->competency->name]) {
                    $lebih += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['lebih'] = $lebih;
                } else if ($project_participant_values[0]->value == $standar[$company->name][$project_participants->project_id][$project_participant_values[0]->competency->name]) {
                    $sama += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['sama'] = $sama;
                } else {
                    $kurang += 1;
                    $data_standar[$project_participant_values[0]->competency->name]['kurang'] = $kurang;
                }
            }
        }
        return $data_standar;
    }

    protected function validateParticipant($project_participant_ids){
        $projectParticipantByParticipant = ProjectParticipant::whereIn('id', $project_participant_ids)->get()
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
