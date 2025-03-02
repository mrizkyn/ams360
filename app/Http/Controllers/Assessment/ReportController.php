<?php

namespace App\Http\Controllers\Assessment;

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\DB;
use PhpOffice\PhpWord\Shared\Converter;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\IOFactory;

use App\Models\Assessment\Competency;
use App\Models\Assessment\KeyBehavior;
use App\Models\Assessment\Company;
use App\Models\Assessment\Departement;
use App\Models\Assessment\Division;
use App\Models\Assessment\ProjectQuestion;
use App\Models\Assessment\Project;
use App\Models\Assessment\Position;
use App\Models\Assessment\Participant;

class ReportController extends Controller
{
    public function index(){
        $departements = Departement::orderBy('name' , 'ASC')->get();
        $divisions = Division::orderBy('name' , 'ASC')->get();
        $positions = Position::orderBy('name' , 'ASC')->get();
        $companies = DB::table('projects')
                        ->select('projects.company_id as id' , 'companies.name')
                        ->join('companies' , 'projects.company_id' , '=' , 'companies.id')
                        ->groupBy('projects.company_id','companies.name')
                        ->orderBy('companies.name')
                        ->get();

        return view('assessment.report.competence' , compact('departements' ,'divisions' ,'companies' , 'positions' ));
    }

    public function competencePDF(Request $request)
    {
        if ($request->competence == 'all') {
            $competencies = Competency::with('behavior')->get();
            $flag = $request->competence;
            $pdf = PDF::loadView('assessment.report.competence-pdf', [
                'competencies' => $competencies,
                'counter' => 0,
                'flag' => $flag
            ]);
            $pdf->setPaper('a4');
            return $pdf->stream('Laporan-kompetensi-semua.PDF');
        } else {
            $request->validate([
                'company' => 'required',
                'division' => 'required',
                'departement' => 'required',
                'position' => 'required'
            ]);

            $flag = 'perusahaan';
            $company = Company::findOrFail($request->company)->name;

            $results = DB::table('participants')
                ->select(
                    'competencies.id AS competence_id',
                    'companies.name AS company',
                    'competencies.name AS competence',
                    'competencies.definition AS definition',
                    'key_behaviors.description AS key_behavior'
                )
                ->join('projects', 'participants.company_id', '=', 'projects.company_id')
                ->join('project_questions', 'projects.id', '=', 'project_questions.project_id')
                ->join('key_behaviors', 'project_questions.key_behavior_id', '=', 'key_behaviors.id')
                ->join('competencies', 'key_behaviors.competence_id', '=', 'competencies.id')
                ->join('companies', 'participants.company_id', '=', 'companies.id')
                ->where([
                    'projects.company_id' => $request->company,
                    'participants.division_id' => $request->division,
                    'participants.departement_id' => $request->departement,
                    'projects.position_id' => $request->position
                ])
                ->groupBy('competence_id', 'company', 'competence', 'definition', 'key_behavior')
                ->get();

            if ($results->isEmpty()) {
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }

            $key_behavior = $results->pluck('key_behavior')->toArray();

            $values = [
                'company' => $company,
                'competence' => $results->first()->competence,
                'definition' => $results->first()->definition,
                'key_behavior' => $key_behavior
            ];

            $pdf = PDF::loadView('assessment.report.competence-pdf', $values);
            $pdf->setPaper('a4');

            return $pdf->stream('Laporan-kompetensi-' . $company . '.PDF');
        }
    }

    public function competenceDOC(Request $request)
    {
        $request->validate([
            'competence' => 'required',
        ]);

        $counter = 0;
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        $styleCell = [
            'borderSize' => 6,
            'borderColor' => '999999',
            'cellMargin' => 50
        ];

        $styleFont = [
            'bold' => true,
            'size' => 12,
        ];

        $indentParagraph = ['indentation' => ['left' => Converter::cmToTwip(0.5)]];
        $indentParagraphBullet = ['indentation' => ['left' => Converter::cmToTwip(1.6)]];

        // Create header
        $header = $section->createHeader();
        if ($header) {
            $tableH = $header->addTable();
            $tableH->addRow();
            $tableH->addCell(9000, $styleCell)->addText('Kamus Kompetensi');
        }

        // Create footer
        $footer = $section->createFooter();
        if ($footer) {
            $tableF = $footer->addTable();
            $tableF->addRow();
            $tableF->addCell(4500)->addText('© Bina Potensia Indonesia');
            $tableF->addCell(4500)->addPreserveText('{PAGE}');
        }

        if ($request->competence == 'company') {
            $request->validate([
                'company' => 'required',
                'division' => 'required',
                'departement' => 'required',
                'position' => 'required'
            ]);

            $results = DB::table('participants')
            ->select(
                'competencies.id AS competence_id',
                'companies.name AS company',
                'competencies.name AS competence',
                'competencies.definition AS definition',
                'key_behaviors.description AS key_behavior',
                'projects.company_id',
                'participants.division_id',
                'participants.departement_id',
                'projects.position_id'
            )
            ->join('projects', 'participants.company_id', '=', 'projects.company_id')
            ->join('project_questions', 'projects.id', '=', 'project_questions.project_id')
            ->join('key_behaviors', 'project_questions.key_behavior_id', '=', 'key_behaviors.id')
            ->join('competencies', 'key_behaviors.competence_id', '=', 'competencies.id')
            ->join('companies', 'participants.company_id', '=', 'companies.id')
            ->where([
                'projects.company_id' => $request->company,
                'participants.division_id' => $request->division,
                'participants.departement_id' => $request->departement,
                'projects.position_id' => $request->position
            ])
            ->groupBy(
                'competencies.id',
                'companies.name',
                'competencies.name',
                'competencies.definition',
                'key_behaviors.description',
                'projects.company_id',
                'participants.division_id',
                'participants.departement_id',
                'projects.position_id'
            )
            ->get();

            if ($results->isEmpty()) {
                return redirect()->back()->with('error', 'Data tidak ditemukan.');
            }

            $companyName = $results->first()->company;
            $key_behavior = $results->pluck('key_behavior')->toArray();

            $section->addText("Nama Perusahaan : " . $companyName);
            $section->addText(($counter += 1) . '. ' . $results->first()->competence, $styleFont);
            $section->addText("Definisi : ", $styleFont, $indentParagraph);
            $section->addText($results->first()->definition, null, $indentParagraph);
            $section->addText("Key Behavior : ", $styleFont, $indentParagraph);

            foreach ($key_behavior as $value) {
                $listItemRun = $section->addListItemRun(0, null, $indentParagraphBullet);
                $listItemRun->addText("\n" . $value);
            }

            $file = 'laporan-kompetensi-' . $companyName . '.docx';
        } else {
            $competencies = Competency::with('behavior')->orderBy('name', 'ASC')->get();

            foreach ($competencies as $value) {
                $counter++;
                $section->addText($counter . '. ' . $value->name, $styleFont);
                $section->addText("Definisi : ", $styleFont, $indentParagraph);
                $section->addText($value->definition, null, $indentParagraph);
                $section->addText("Key Behavior : ", $styleFont, $indentParagraph);

                foreach ($value->behavior as $behavior) {
                    $listItemRun = $section->addListItemRun(0, null, $indentParagraphBullet);
                    $listItemRun->addText("\n" . $behavior->description);
                }

                $section->addTextBreak();
            }

            $file = 'laporan-kompetensi-semua.docx';
        }

        try {
            $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
            $objWriter->save(storage_path($file));
            return response()->download(storage_path($file));
        } catch (Exception $e) {
            return redirect()->back()->with('error', 'Gagal menyimpan file.');
        }
    }


    public function getDivision(Request $request){
        $participant = Participant::select('division_id')->with('division')
                        ->where('company_id' , $request->company_id)
                        ->groupBy('division_id')
                        ->get();

        if($participant->isEmpty()){
            $data = [
                'status' => 0 ,
                'message' => 'Data divisi tidak ada untuk perusahaan ini'
            ];
            return response()->json($data);
        }else{
            return response()->json($participant);
        }
    }

    public function getDepartement(Request $request){
        $participant = Participant::select('departement_id')->with('departement')
                        ->where(['company_id' => $request->company_id , 'division_id' => $request->division_id])
                        ->groupBy('departement_id')
                        ->get();

        if($participant->isEmpty()){
            $data = [
                'status' => 0 ,
                'message' => 'Data divisi tidak ada untuk perusahaan ini'
            ];
            return response()->json($data);
        }else{
            return response()->json($participant);
        }
    }

    public function getPosition(Request $request){
        $participant = DB::table('participants')
        ->select('positions.id as position_id', 'positions.name as position')
        ->join('projects', 'participants.company_id', '=', 'projects.company_id')
        ->join('positions', 'projects.position_id', '=', 'positions.id')
        ->where('participants.company_id', $request->company_id)
        ->where('participants.division_id', $request->division_id)
        ->where('participants.departement_id', $request->departement_id)
        ->groupBy('positions.id', 'positions.name')
        ->get();

        if($participant->isEmpty()){
            $data = [
                'status' => 0 ,
                'message' => 'Data divisi tidak ada untuk perusahaan ini'
            ];
            return response()->json($data);
        }else{
            return response()->json($participant);
        }
    }
}
