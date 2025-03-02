<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectParticipant as ProjectParticipantResource;
use App\Models\Assessment\Company;
use App\Models\Assessment\Project;
use App\Models\Assessment\ProjectParticipant;
use App\Models\Assessment\ProjectParticipantResult;
use App\Models\Assessment\ProjectQuestion;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use PDF;
use PhpOffice\PhpWord\Shared\Converter;

class ReportAssessmentController extends Controller
{
    public function index(){
        return view('assessment.report.assessment', [
            'companies' => Company::orderBy('name')->get()
        ]);
    }

    public function getAssessmentSumarry(Request $request){
        $projectQuestions = ProjectQuestion::where('project_id', $request->project_id)->get();

        ProjectParticipantResult::where('project_participant_id', $request->project_participant_id)->delete();
        foreach($projectQuestions as $question){
            switch($question->project->type){
                case '1':
                    $cpSelf = $this->getCalculateSelf($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpSuper = $this->getCalculateSuperior($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpOther = $this->getCalculateOther($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpAll = $this->getCalculateAll($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpr = $this->getCalculate($request->project_participant_id, $question->key_behavior_id, 'CPR');
                    $gap = $cpAll - $cpr;
                    $loa = 100 - ($this->getStandardDeviation([$cpSelf, $cpSuper, $cpOther, $cpAll]) * (100 / 3.8));

                    $this->saveProjectParticipantResult($request->project_participant_id, $question->key_behavior_id, 'CPR', $cpr, $gap, $loa);
                    break;
                case '2':
                    $cpSelf = $this->getCalculateSelf($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpSuper = $this->getCalculateSuperior($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpOther = $this->getCalculateOther($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $cpAll = $this->getCalculateAll($request->project_participant_id, $question->key_behavior_id, 'CP');
                    $fpr = $this->getCalculate($request->project_participant_id, $question->key_behavior_id, 'FPR');
                    $gap = $cpAll - $fpr;
                    $loa = 100 - ($this->getStandardDeviation([$cpSelf, $cpSuper, $cpOther, $cpAll]) * (100 / 3.8));

                    $this->saveProjectParticipantResult($request->project_participant_id, $question->key_behavior_id, 'FPR', $fpr, $gap, $loa);
                    break;
                case '3':
                    $cfSelf = $this->getCalculateSelf($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfSuper = $this->getCalculateSuperior($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfOther = $this->getCalculateOther($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfAll = $this->getCalculateAll($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfr = $this->getCalculate($request->project_participant_id, $question->key_behavior_id, 'CFR');
                    $gap = $cfAll - $cfr;
                    $loa = 100 - ($this->getStandardDeviation([$cfSelf, $cfSuper, $cfOther, $cfAll]) * (100 / 3.8));

                    $this->saveProjectParticipantResult($request->project_participant_id, $question->key_behavior_id, 'CFR', $cfr, $gap, $loa);
                    break;
                case '4':
                    $cfSelf = $this->getCalculateSelf($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfSuper = $this->getCalculateSuperior($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfOther = $this->getCalculateOther($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $cfAll = $this->getCalculateAll($request->project_participant_id, $question->key_behavior_id, 'CF');
                    $ffr = $this->getCalculate($request->project_participant_id, $question->key_behavior_id, 'FFR');
                    $gap = $cfAll - $ffr;
                    $loa = 100 - ($this->getStandardDeviation([$cfSelf, $cfSuper, $cfOther, $cfAll]) * (100 / 3.8));

                    $this->saveProjectParticipantResult($request->project_participant_id, $question->key_behavior_id, 'FFR', $ffr, $gap, $loa);
                    break;
            };
        }

        $results = DB::table('project_participant_results')
        ->join('key_behaviors', 'project_participant_results.key_behavior_id', '=', 'key_behaviors.id')
        ->join('competencies', 'key_behaviors.competence_id', '=', 'competencies.id')
        ->select('competencies.name')
        ->selectRaw('avg(project_participant_results.gap) as gap, avg(project_participant_results.loa) as loa')
        ->where('project_participant_id', $request->project_participant_id)
        ->groupBy('name')
        ->orderBy('name')
        ->get();

        return $results;
    }


    public function getAssessmentReport(Request $request){
        $competencyCharts = json_decode($request->competencyCharts);
        $keyBehaviorCharts = json_decode($request->keyBehaviorCharts);
        $summaryChart = $request->summaryChart;
        $assessmentSummary = $this->getAssessmentSumarry($request);
        $colors = ["B71C1C", "4A148C", "01579B", "1B5E20", "F57F17"];

        $projectParticipant = ProjectParticipant::find($request->project_participant_id);
        $projectParticipantResults = ProjectParticipantResult::where('project_participant_id', $request->project_participant_id)
            ->join('key_behaviors', 'project_participant_results.key_behavior_id', '=', 'key_behaviors.id')
            ->join('competencies', 'key_behaviors.competence_id', '=', 'competencies.id')
            ->get()->unique('key_behavior_id')->groupBy('name')->sortBy('name');
        $competencyResults = $this->getAssessmentCompetencyResult($request->project_participant_id);
        $keyBehaviorResults = $this->getAssessmentKeyBehaviorResult($request->project_participant_id);

        $competency = 0;
        $keybehavior = 0;
        $no = 1;
        $row = 1;

        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $phpWord->setDefaultFontName('Calibri');
        $phpWord->setDefaultFontSize(10);
        $phpWord->setDefaultParagraphStyle(['spacing' => Converter::pointToTwip(1.2),
            "spaceBefore" => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(3),
            "spaceAfter" => \PhpOffice\PhpWord\Shared\Converter::pointToTwip(3)
        ]);
        \PhpOffice\PhpWord\Settings::setOutputEscapingEnabled(true);
        $section = $phpWord->addSection();
        $section->getStyle()->setMarginTop(Converter::inchToTwip(1.2));
        $section->getStyle()->setMarginLeft(Converter::inchToTwip(1));
        $section->getStyle()->setMarginBottom(Converter::inchToTwip(0.9));
        $section->getStyle()->setMarginRight(Converter::inchToTwip(1));

        $section->addTextBreak();
        $section->addImage('storage/BPI.png', array('width' => Converter::cmToPoint(7.55), 'height' => Converter::cmToPoint(3.31), 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));
        $section->addTextBreak(3);
        $section->addText('LAPORAN HASIL', ['bold' => true, 'size' => 22], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun = $section->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('360 ', ['bold' => true, 'size' => 22]);
        $textRun->addText('DEGREE ASSESSMENT', ['bold' => true, 'italic' => true, 'size' => 22]);
        $section->addTextBreak(3);

        $coverTable = $section->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'borderSize' => 6, 'borderColor' => '000000', 'cellMarginLeft' => 80]);
        $coverTable->addRow();
        $coverTable->addCell(Converter::cmToTwip(15), ['bgColor' => 'd9d9d9', 'gridSpan' => 2])->addText('IDENTITAS', ['bold' => true, 'size' => 18], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $coverTable->addRow(Converter::cmToTwip(0.98));
        $coverTable->addCell(Converter::cmToTwip(3), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('Nama', [ 'size' => 16]);
        $coverTable->addCell(Converter::cmToTwip(12), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText(': '.$projectParticipant->participant->name, [ 'size' => 16]);
        $coverTable->addRow(Converter::cmToTwip(0.98));
        $coverTable->addCell(Converter::cmToTwip(3), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('Jabatan', [ 'size' => 16]);
        $coverTable->addCell(Converter::cmToTwip(12), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText(': '.$projectParticipant->participant->position->name, [ 'size' => 16]);

        $section2 = $phpWord->addSection();
        $section2->getStyle()->setMarginTop(Converter::inchToTwip(1.2));
        $section2->getStyle()->setMarginLeft(Converter::inchToTwip(1));
        $section2->getStyle()->setMarginBottom(Converter::inchToTwip(0.9));
        $section2->getStyle()->setMarginRight(Converter::inchToTwip(1));
        $section2->getStyle()->setPageNumberingStart(1);

        $sec2Header = $section2->addHeader();
        $sec2Header->addImage('storage/BPI.png', array('width' => Converter::cmToPoint(4.4), 'height' => Converter::cmToPoint(1.8), 'alignment' => \PhpOffice\PhpWord\SimpleType\Jc::END));

        $footer = $section2->addFooter();
        $table = $footer->addTable(['borderColor' =>'ffffff', 'borderSize' => 0, 'borderTopColor' => '000000']);
        $table->addRow();
        $textRun = $table->addCell(5000)->addTextRun();
        $textRun->addText('Laporan Hasil 360 ', ['size' => 8]);
        $textRun->addText('Degree Assessment, ', ['size' => 8, 'italic' => true]);
        $textRun->addText($projectParticipant->project->company->name, ['size' => 8]);
        $textRun->addText(', ' . \Carbon\Carbon::now()->translatedFormat('Y'), ['size' => 8]);
        $table->addCell(5000)->addPreserveText($projectParticipant->participant->name.'/'.'Hal. {PAGE} dari {SECTIONPAGES}.', ['size' => 8], array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::RIGHT));

        $section2->addText('Pengantar', ['bold' => true, 'size' => 12], ['spaceAfter' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.11), 'spaceBefore' => \PhpOffice\PhpWord\Shared\Converter::cmToTwip(0.11)]);
        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Laporan ini merupakan tindakan lanjutan yang diambil oleh perusahaan dalam melihat perubahan hasil pengembangan kompetensi SDM yang telah dilakukan melalui ');
        $textRun->addText('Competency Development Program. ',
        ['italic' => true]);
        $textRun->addText('Laporan ini berisi informasi penting tentang kompetensi ‘Target Pengembangan’ yang dinilai oleh orang-orang yang dipilih yang sering berinteraksi dalam proses pelaksanaan tugasnya.');

        $indent = ['indentation' => ['left' => Converter::cmToTwip(1)]];
        $section2->addText('Laporan ini terdiri dari:', ['bold' => true, 'size' => 10]);
        $section2->addText('1.  360 Degree Result', ['italic' => true], $indent);
        $section2->addText('2.  Individual Profile Reports', ['italic' => true], $indent);
        $section2->addText('3.  Summary', ['italic' => true], $indent);
        $section2->addText('4.  Saran', null, $indent);
        $section2->addTextBreak();

        $section2->addText('1.        360 Degree Result', ['bold' => true, "italic" => true, 'size' => 11]);
        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'indentation' => ['left' => Converter::cmToTwip(1)]]);
        $textRun->addText('Bagian ini merangkum profil “Target (Peserta)” berdasarkan hasil penilaian dari semua “');
        $textRun->addText('Rater', ["italic" => true]);
        $textRun->addText(' (Penilai)”. ');
        $section2->addText('Grafik di bawah ini menunjukkan nilai rata-rata dari nilai efektivitas perilaku yang ditampilkan peserta saat ini dibandingkan dengan nilai efektivitas perilaku yang dipersyaratkan', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'indentation' => ['left' => Converter::cmToTwip(1)]]);

        $categories = [];
        $data = [];
        foreach($competencyResults as $competencyResult){
            array_push($categories, $competencyResult[0]['name']);
        }

        for($i = 0; $i < 2; $i++){
            $series = [];
            foreach($competencyResults as $competencyResult){
                array_push($series, number_format((float)$competencyResult[$i]['value'], 2, '.', ''));
            }
            array_push($data, $series);
        }

        $chartTable = $section2->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'borderColor' =>'ffffff', 'borderSize' => 0]);
        $chartTable->addRow();
        $cellTable = $chartTable->addCell();
        $chart = $cellTable->addChart('line', $categories, $data[0], null, $competencyResults[0][0]['type']);
        $chart->addSeries($categories, $data[1], $competencyResults[0][1]['type']);
        $chart->getStyle()->setWidth(Converter::cmToEmu(10.19))->setHeight(Converter::cmToEmu(7.45));
        $chart->getStyle()->setShowGridX(false);
        $chart->getStyle()->setShowGridY(true);
        $chart->getStyle()->setShowLegend(false);
        $chart->getStyle()->setShowAxisLabels(true);
        $chart->getStyle()->setDataLabelOptions(['showVal' => false, 'showCatName' => false, 'showLegendKey' => true]);
        $chart->getStyle()->setValueAxisTitle('Rating');
        $chart->getStyle()->setCategoryAxisTitle('Kompetensi');

        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'indentation' => ['left' => Converter::cmToTwip(1)]]);
        $textRun->addText('Tabel di bawah ini menunjukan Kekuatan ');
        $textRun->addText('(Strength) ', ["italic" => true]);
        $textRun->addText('dan Area Pengambangan ');
        $textRun->addText('(Areas of Development) ', ["italic" => true]);
        $textRun->addText('dari "Target (Peserta)".');

        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'indentation' => ['left' => Converter::cmToTwip(1)]]);
        $textRun->addText('Gap ', ["italic" => true]);
        $textRun->addText('kompetensi yang nilainya positif menunjukan kekuatan ');
        $textRun->addText('(Strength) ', ["italic" => true]);
        $textRun->addText('sedangkan ');
        $textRun->addText('gap ', ["italic" => true]);
        $textRun->addText('kompetensi yang nilainya negatif menunjukan area yang harus dikembangkan.');

        $table2 = $section2->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'borderSize' => 6, 'borderColor' => '000000']);
        $table2->addRow();
        $textRun = $table2->addCell(5000, ['gridSpan' => 2, 'valign' => 'center', 'bgColor' => 'a6a6a6'])->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('Hasil ', ["bold" => true]);
        $textRun->addText('Proficiency ', ["bold" => true, "italic" => true]);
        $textRun->addText('360 ', ["bold" => true]);
        $textRun->addText('Degrees', ["bold" => true, "italic" => true]);

        $table2->addRow();
        $table2->addCell(3000, ['bgColor' => 'd9d9d9'])->addText('Kompetensi', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table2->addCell(2000, ['bgColor' => 'd9d9d9'])->addText('Hasil', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        foreach($assessmentSummary as $summary){
            $table2->addRow();
            $table2->addCell(3000)->addText($summary->name, null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
            $table2->addCell(2000)->addText(number_format((float)$summary->gap, 2, '.', ''), null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        }

        $table2->addRow();
        $table2->addCell(5000, ['gridSpan' => 2, 'valign' => 'center', 'bgColor' => 'd9d9d9'])->addText('Kriteria Penilaian', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $table2->addRow();
        $table2->addCell(3000)->addText('Strength', ['italic' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun = $table2->addCell(2000)->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('Gap ', ['bold' => true, 'italic' => true]);
        $textRun->addText('>=1', ['bold' => true]);

        $table2->addRow();
        $table2->addCell(3000)->addText('Meet Expectations', ['italic' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun = $table2->addCell(2000)->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('1> ', ['bold' => true]);
        $textRun->addText('Gap ', ['bold' => true, 'italic' => true]);
        $textRun->addText('> -1', ['bold' => true]);

        $table2->addRow();
        $table2->addCell(3000)->addText('Development Area', ['italic' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun = $table2->addCell(2000)->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('Gap ', ['bold' => true, 'italic' => true]);
        $textRun->addText('<= -1', ['bold' => true]);

        $section2->addText('2.        Individual Profile Report', ['bold' => true, "italic" => true, 'size' => 11]);
        $section2->addText('Bagian ini menggambarkan secara detil bagaimana “Target” menilai dirinya dan bagaimana orang lain menilai “Target” dalam menampilkan setiap kunci perilaku.',
        null,
        ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH, 'indentation' => ['left' => Converter::cmToTwip(1)]]);
        $section2->addText('Keterangan :',
        ['bold' => true],
        ['indentation' => ['left' => Converter::cmToTwip(1)]]);

        $table4 = $section2->addTable(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'borderSize' => 6, 'borderColor' => '000000', 'cellMarginLeft' => 80, 'cellMarginRight' => 80]);
        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95), ['bgColor' => 'd9d9d9'])->addText('No.', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21), ['bgColor' => 'd9d9d9'])->addText('Istilah', ['bold' => true]);
        $table4->addCell(Converter::cmToTwip(11), ['gridSpan' => 2, 'valign' => 'center', 'bgColor' => 'd9d9d9'])->addText('Kriteria Penilaian', ['bold' => true]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95))->addText('1', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21))->addText('n', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(0.5), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('=', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $table4->addCell(Converter::cmToTwip(10.5), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText('Jumlah penilai (rater) yang menilai kunci perilaku yang ditunjukkan', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95))->addText('2', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21))->addText('All', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(0.5), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('=', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $table4->addCell(Converter::cmToTwip(10.5), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText('Nilai/skor dari semua penilai (rater) kecuali nilai/skor yang diberikan “Target”', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95))->addText('3', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21))->addText('Self', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(0.5), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('=', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $table4->addCell(Converter::cmToTwip(10.5), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText('nilai/skor yang diberikan oleh target', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95))->addText('4', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21))->addText('Others', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(0.5), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('=', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $table4->addCell(Converter::cmToTwip(10.5), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText('nilai/skor dari semua penilai (rater) kecuali nilai/skor yang diberikan “Target” dan “Superior”', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(0.95))->addText('5', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(2.21))->addText('Gap', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table4->addCell(Converter::cmToTwip(0.5), ['borderRightColor' => 'ffffff', 'borderRightSize' => 0])->addText('=', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $table4->addCell(Converter::cmToTwip(10.5), ['borderLeftColor' => 'ffffff', 'borderLeftSize' => 0])->addText('Seberapa besar kesenjangan antara efektivitas/frekuensi kunci perilaku yang dipersyaratkan (all) dengan efektivitas/frekuensi kunci perilaku yang ditampilkan saat ini (all)', null, ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT]);
        $section2->addTextBreak();

        //Loa Addrow

        $section2->addText('Key Behaviours Report', ['bold' => true, "italic" => true, 'size' => 11]);
        $table3 = $section2->addTable(['cellMarginLeft' => 80, 'cellMarginRight' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER, 'borderSize' => 6, 'borderColor' => '000000']);
        $table3->addRow(null, ['tblHeader' => true, 'cantSplit' => true]);
        $table3->addCell(Converter::cmToTwip(5.72), ['bgColor' => 'd9d9d9'])->addText('COMPETENCY', ['bold' => true, 'italic' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table3->addCell(Converter::cmToTwip(0.92), ['bgColor' => 'd9d9d9'])->addText('n', ['bold' => true, 'italic' => true, 'size' => 12], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table3->addCell(Converter::cmToTwip(7.3), ['bgColor' => 'd9d9d9'])->addText('', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $table3->addCell(Converter::cmToTwip(2.03), ['bgColor' => 'd9d9d9'])->addText('', ['bold' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);

        $categories = [];
        foreach($competencyResults[0] as $category){
            array_push($categories, $category['type']);
        }

        foreach ($projectParticipantResults as $ProjectParticipantResult){
            $series = [];
            foreach($competencyResults[$competency] as $seriesValue){
                array_push($series, $seriesValue['value']);
            }

            $table3->addRow(Converter::pointToTwip(140), ['cantSplit' => true]);
            $definition = $table3->addCell(Converter::cmToTwip(5.72), ['vMerge' => 'restart'])->addTextRun();
            $definition->addText($ProjectParticipantResult[0]->name, ['bold' => true]);
            $definition->addTextBreak();
            $definition->addText($ProjectParticipantResult[0]->definition);
            $cell = $table3->addCell(Converter::cmToTwip(0.92));
            $cell->addText(' ', ['size' => 9], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
            $cell->addText(($projectParticipant->superior_number + $projectParticipant->collegue_number +  $projectParticipant->subordinate_number) * count($ProjectParticipantResult), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
            $cell->addText(($projectParticipant->superior_number + $projectParticipant->collegue_number +  $projectParticipant->subordinate_number) * count($ProjectParticipantResult), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
            $cell->addText(($projectParticipant->collegue_number +  $projectParticipant->subordinate_number) * count($ProjectParticipantResult), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
            $cell->addText($projectParticipant->superior_number * count($ProjectParticipantResult), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
            $cell->addText(1 * count($ProjectParticipantResult), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
            $chart = $table3->addCell(Converter::cmToTwip(7.3), ['valign' => 'top', 'vMerge' => 'restart'])->addChart('bar', array_reverse($categories), array_reverse($series));
            $chart->getStyle()->setWidth(Converter::pointToEmu(190))->setHeight(Converter::pointToEmu(140));
            $chart->getStyle()->setShowGridX(false);
            $chart->getStyle()->setShowGridY(true);
            $chart->getStyle()->setShowLegend(false);
            $chart->getStyle()->setShowAxisLabels(true);
            $chart->getStyle()->setColors($colors);
            $chart->getStyle()->setDataLabelOptions(['showVal' => false, 'showCatName' => false, 'showLegendKey' => true]);
            $textRun = $table3->addCell(Converter::cmToTwip(2.03), ['vMerge' => 'restart'])->addTextRun();
            $textRun->addText('Gap ', ['italic' => true]);
            $textRun->addText('= '.number_format((float)$assessmentSummary[$competency]->gap, 2, '.', ''));
            $textRun->addTextBreak(); // Tambahkan jarak
            $textRun->addText('LOA ', ['italic' => true]);
            $textRun->addText('= '.number_format((float)$assessmentSummary[$competency]->loa, 2, '.', ''));

            foreach ($ProjectParticipantResult as $key => $result){
                $keys = collect($keyBehaviorResults)->keys();
                $series = [];
                foreach($keyBehaviorResults[$keys[$keybehavior]] as $seriesValue){
                    array_push($series, $seriesValue['value']);
                }

                $table3->addRow(Converter::pointToTwip(140), ['cantSplit' => true]);
                $table3->addCell(Converter::cmToTwip(5.72), ['vMerge' => 'restart'])->addText($no.". ".$result->description, ['bold' => true]);
                $cell = $table3->addCell(Converter::cmToTwip(0.92));
                $cell->addText(' ', ['size' => 9], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);
                $cell->addText(($projectParticipant->superior_number + $projectParticipant->collegue_number +  $projectParticipant->subordinate_number), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
                $cell->addText(($projectParticipant->superior_number + $projectParticipant->collegue_number +  $projectParticipant->subordinate_number), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
                $cell->addText(($projectParticipant->collegue_number +  $projectParticipant->subordinate_number), ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
                $cell->addText($projectParticipant->superior_number, ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
                $cell->addText(1, ['size' => 8], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spacing' => Converter::cmToTwip(0.426)]);
                $chart = $table3->addCell(Converter::cmToTwip(7.3), ['valign' => 'top', 'vMerge' => 'restart'])->addChart('bar', array_reverse($categories), array_reverse($series));
                $chart->getStyle()->setWidth(Converter::pointToEmu(190))->setHeight(Converter::pointToEmu(140));
                $chart->getStyle()->setShowGridX(false);
                $chart->getStyle()->setShowGridY(true);
                $chart->getStyle()->setShowLegend(false);
                $chart->getStyle()->setShowAxisLabels(true);
                $chart->getStyle()->setColors($colors);
                $chart->getStyle()->setDataLabelOptions(['showVal' => false, 'showCatName' => false, 'showLegendKey' => true]);
                $textRun = $table3->addCell(Converter::cmToTwip(2.03), ['vMerge' => 'restart'])->addTextRun();
                $textRun->addText('Gap ', ['italic' => true]);
                $textRun->addText('= '.number_format((float)$result->gap, 2, '.', ''));
                // $textRun->addTextBreak();
                // $textRun->addText('LOA ', ['italic' => true]);
                // $textRun->addText('= '.number_format((float)$assessmentSummary[$competency]->loa, 2, '.', ''));

                $keybehavior++;
                $no++;
            }

            $competency++;
            $no = 1;
        }

        $section2->addPageBreak();
        $section2->addText('3.        Summary', ['bold' => true, "italic" => true, 'size' => 11]);
        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Berdasarkan pengukuran 360 degree terlihat kompetensi ');

        if($projectParticipantResults->count() == 1){
            $textRun->addText($projectParticipantResults->first()[0]->name.' ');
        }else{
            $i = 0;
            foreach ($projectParticipantResults as $projectParticipantResult){
                if($projectParticipantResult == $projectParticipantResults->last()){
                    $textRun->addText('dan ' . $ProjectParticipantResult[0]->name . ' ');
                }else if($i == $projectParticipantResults->count() - 2){
                    $textRun->addText($projectParticipantResult[0]->name.' ');
                }else {
                    $textRun->addText($projectParticipantResult[0]->name.', ');
                }
                $i++;
            }
        }

        $textRun->addText('Sdr. ' . $projectParticipant->participant->name . ' ');
        $textRun->addText($request->summary);

        $section2->addTextBreak(2);
        $section2->addText('4.        Saran', ['bold' => true, 'size' => 11]);
        $textRun = $section2->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::BOTH]);
        $textRun->addText('Berdasarkan hasil monitoring dan hasil 360 degree, ');
        $textRun->addText($request->suggestion);

        $section2->addTextBreak(2);
        $table4 = $section2->addTable(['borderSize' => 0, 'borderColor' => 'ffffff']);
        $table4->addRow();
        $textRun = $table4->addCell(Converter::cmToTwip(6.75))->addTextRun(['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER]);
        $textRun->addText('Bandung ');
        $textRun->addText(\Carbon\Carbon::now()->translatedFormat('d F Y'));

        $table4->addRow(1500, ['exactHeight' => true]);
        $table4->addCell(Converter::cmToTwip(6.75));

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(6.75))->addText('Sri Chandrawati', ['bold' => true, 'size' => 11], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        $table4->addRow();
        $table4->addCell(Converter::cmToTwip(6.75))->addText('Talent Acquisition and Development Director', ['italic' => true], ['alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'spaceBefore' => 0, 'spaceAfter' => 0]);

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(storage_path('Laporan Assessment 360 - '. $projectParticipant->participant->name .' .docx'));
        } catch (Exception $e) {

        }

        Session::flash('download.in.the.next.request', '/assessment/report-assessment-document/'.'Laporan Assessment 360 - '. $projectParticipant->participant->name .' .docx');

        return redirect('/assessment/report-assessment');
    }

    public function getAssssmentDocument($document){
        return response()->download(storage_path($document));
    }

    public function getLoAResult($project_participant_id, $competency_id) {
        $types = ['CPR', 'CP All', 'CP Other', 'CP Super', 'Self'];
        $values = [];

        foreach ($types as $type) {
            $values[] = $this->getAverageResult($project_participant_id, $competency_id, $type);
        }

        // Menghitung standar deviasi LoA
        $mean = array_sum($values) / count($values);
        $variance = array_sum(array_map(fn($x) => pow($x - $mean, 2), $values)) / count($values);
        $stdDev = sqrt($variance);

        return $stdDev;
    }

    public function getAssessmentCompetencyResult($projectParticipantId){
        $projectParticipant =  ProjectParticipant::find($projectParticipantId);

        $results = DB::table('project_participant_results')
        ->join('key_behaviors', 'project_participant_results.key_behavior_id', '=', 'key_behaviors.id')
        ->join('competencies', 'key_behaviors.competence_id', '=', 'competencies.id')
        ->select('competencies.id as competency_id', 'competencies.name as competency_name', 'key_behaviors.id as key_behavior_id', 'project_participant_results.type', 'project_participant_results.value')
        ->where('project_participant_id', $projectParticipantId)
        ->orderBy('competency_name')
        ->get();

        $competency_keys = $results->pluck('competency_id', 'competency_name')->unique();
        $competencies = [];

        switch($projectParticipant->project->type){
            case '1':
                foreach($competency_keys as $competency_name => $competency_key){
                    array_push($competencies, [
                        ['type' => 'CPR', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CPR")],
                        ['type' => 'CP All', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP All")],
                        ['type' => 'CP Other', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP Other")],
                        ['type' => 'CP Super', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP Super")],
                        ['type' => 'Self', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "Self")],
                    ]);
                }

                return $competencies;
            case '2':
                foreach($competency_keys as $competency_name => $competency_key){
                    array_push($competencies, [
                        ['type' => 'FPR', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "FPR")],
                        ['type' => 'CP All', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP All")],
                        ['type' => 'CP Other', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP Other")],
                        ['type' => 'CP Super', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CP Super")],
                        ['type' => 'Self', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "Self")],
                    ]);
                }

                return $competencies;
            case '3':
                foreach($competency_keys as $competency_name => $competency_key){
                    array_push($competencies, [
                        ['type' => 'CFR', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CFR")],
                        ['type' => 'CF All', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF All")],
                        ['type' => 'CF Other', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF Other")],
                        ['type' => 'CF Super', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF Super")],
                        ['type' => 'Self', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "Self")],
                    ]);
                }

                return $competencies;
            case '4':
                foreach($competency_keys as $competency_name => $competency_key){
                    array_push($competencies, [
                        ['type' => 'FFR', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "FFR")],
                        ['type' => 'CF All', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF All")],
                        ['type' => 'CF Other', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF Other")],
                        ['type' => 'CF Super', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "CF Super")],
                        ['type' => 'Self', 'name' => $competency_name, 'value' => $this->getAverageResult($projectParticipantId, $competency_key, "Self")],
                    ]);
                }

                return $competencies;
        }
    }

    public function getAssessmentKeyBehaviorResult($projectParticipantId){
        $results = ProjectParticipantResult::where('project_participant_id', $projectParticipantId)->get();

        return $results->groupBy('key_behavior_id');
    }

    public function getAverageResult($projectParticipantId, $competencyId, $type){
        $results = DB::table('project_participant_results')
        ->join('key_behaviors', 'project_participant_results.key_behavior_id', '=', 'key_behaviors.id')
        ->selectRaw('avg(project_participant_results.value) as value')
        ->where(['project_participant_id' => $projectParticipantId, 'competence_id' => $competencyId, 'type' => $type])
        ->first()->value;

        return $results;
    }

    public function getCalculate($project_participant_id, $key_behavior_id, $type){
        $average = DB::table('project_participants')
            ->join('project_participant_repondents', 'project_participants.id', '=', 'project_participant_repondents.project_participant_id')
            ->join('project_participant_repondent_answers', 'project_participant_repondents.id', '=', 'project_participant_repondent_answers.project_participant_repondent_id')
            ->where([
                'project_participants.id' => $project_participant_id,
                ['project_participant_repondents.type', '!=', 'Diri Sendiri'],
                'project_participant_repondent_answers.type' => $type,
                'project_participant_repondent_answers.key_behavior_id' => $key_behavior_id])
            ->avg('project_participant_repondent_answers.answer');
        return $average == null ? 0 : $average;
    }

    public function getCalculateAll($project_participant_id, $key_behavior_id, $type){
        $average = DB::table('project_participants')
            ->join('project_participant_repondents', 'project_participants.id', '=', 'project_participant_repondents.project_participant_id')
            ->join('project_participant_repondent_answers', 'project_participant_repondents.id', '=', 'project_participant_repondent_answers.project_participant_repondent_id')
            ->where([
                'project_participants.id' => $project_participant_id,
                ['project_participant_repondents.type', '!=', 'Diri Sendiri'],
                'project_participant_repondent_answers.type' => $type,
                'project_participant_repondent_answers.key_behavior_id' => $key_behavior_id])
            ->avg('project_participant_repondent_answers.answer');
        return $average == null ? 0 : $average;
    }

    public function getCalculateSuperior($project_participant_id, $key_behavior_id, $type){
        $average = DB::table('project_participants')
            ->join('project_participant_repondents', 'project_participants.id', '=', 'project_participant_repondents.project_participant_id')
            ->join('project_participant_repondent_answers', 'project_participant_repondents.id', '=', 'project_participant_repondent_answers.project_participant_repondent_id')
            ->where([
                'project_participants.id' => $project_participant_id,
                'project_participant_repondents.type' => 'Atasan',
                'project_participant_repondent_answers.type' => $type,
                'project_participant_repondent_answers.key_behavior_id' => $key_behavior_id])
            ->avg('project_participant_repondent_answers.answer');
        return $average == null ? 0 : $average;
    }

    public function getCalculateSelf($project_participant_id, $key_behavior_id, $type){
        $average = DB::table('project_participants')
            ->join('project_participant_repondents', 'project_participants.id', '=', 'project_participant_repondents.project_participant_id')
            ->join('project_participant_repondent_answers', 'project_participant_repondents.id', '=', 'project_participant_repondent_answers.project_participant_repondent_id')
            ->where([
                'project_participants.id' => $project_participant_id,
                'project_participant_repondents.type' => 'Diri Sendiri',
                'project_participant_repondent_answers.type' => $type,
                'project_participant_repondent_answers.key_behavior_id' => $key_behavior_id])
            ->avg('project_participant_repondent_answers.answer');
            return $average == null ? 0 : $average;
    }

    public function getCalculateOther($project_participant_id, $key_behavior_id, $type){
        $average = DB::table('project_participants')
            ->join('project_participant_repondents', 'project_participants.id', '=', 'project_participant_repondents.project_participant_id')
            ->join('project_participant_repondent_answers', 'project_participant_repondents.id', '=', 'project_participant_repondent_answers.project_participant_repondent_id')
            ->where([
                'project_participants.id' => $project_participant_id,
                'project_participant_repondent_answers.type' => $type,
                'project_participant_repondent_answers.key_behavior_id' => $key_behavior_id])
            ->where(function ($query){
                $query->where('project_participant_repondents.type', 'Rekan Kerja')
                    ->orWhere('project_participant_repondents.type', 'Bawahan');
                })
            ->avg('project_participant_repondent_answers.answer');
        return $average == null ? 0 : $average;
    }

    public function saveProjectParticipantResult($project_participant_id, $key_behavior_id, $type, $value, $gap, $loa){
        return ProjectParticipantResult::create([
            'project_participant_id' => $project_participant_id,
            'key_behavior_id' => $key_behavior_id,
            'type' => $type,
            'value' => $value,
            'gap' => $gap,
            'loa' => $loa
        ]);
    }

    private function getStandardDeviation($values) {
        $mean = array_sum($values) / count($values);
        $sumSquaredDiffs = array_reduce($values, function ($carry, $value) use ($mean) {
            return $carry + pow($value - $mean, 2);
        }, 0);
        return sqrt($sumSquaredDiffs / count($values));
    }


    public function getProjectByCompanyName($companyId){
        return Project::where(['company_id' => $companyId])->orderBy('name')->get();
    }

    public function getParticipantByProjectName($projectId){
        return ProjectParticipantResource::collection(ProjectParticipant::where(['project_id' => $projectId, 'status' => 'Selesai'])->get())->sortBy('name');
    }
}
