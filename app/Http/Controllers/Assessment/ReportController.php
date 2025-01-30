<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use PDF;
use App;
use DB;

use App\Models\Assessment\Competency;
use App\Models\Assessment\KeyBehavior;
use App\Models\Assessment\Company;
use App\Models\Assessment\Departement;
use App\Models\Assessment\Division;
use App\Models\Assessment\ProjectQuestion;
use App\Models\Assessment\ProjectParticipant;
use App\Models\Assessment\Project;
use App\Models\Assessment\Position;
use App\Models\Assessment\Participant;
use PhpOffice\PhpWord\Shared\Converter;

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

    public function competencePDF(Request $request){
        if($request->competence == 'all'){
            $competencies = Competency::with('behavior')->get();
            $flag = $request->competence;
            $pdf = PDF::loadView('assessment.report.competence-pdf' , ['competencies' => $competencies , 'counter' => 0 , 'flag' => $flag]);
            $pdf->setPaper('a4');
            return $pdf->stream('Laporan-kompetensi-semua.PDF');
        }else{
            $validate = $this->validate($request , [
                'company' => 'required' ,
                'division' => 'required' ,
            ]);
            $flag = 'perusahaan';
            $companies = Company::find($request->company);
            $company = $companies->name;

            $results = DB::table('participants')
                        ->select('competencies.id AS competence_id' , 'companies.name AS company' ,  'competencies.name AS competence' , 'competencies.definition AS definition' , 'key_behaviors.description AS key_behavior' ,  'projects.company_id' , 'participants.division_id' , 'participants.departement_id' , 'projects.position_id')
                        ->join('projects' , 'participants.company_id' , '=' , 'projects.company_id')
                        ->join('project_questions' ,'projects.id' ,'=' ,'project_questions.project_id')
                        ->join('key_behaviors' , 'project_questions.key_behavior_id' , '=' , 'key_behaviors.id')
                        ->join('competencies' , 'key_behaviors.competence_id' , '=' , 'competencies.id')
                        ->join('companies' , 'participants.company_id' , '=' , 'companies.id')
                        ->where(['projects.company_id' => $request->company , 'participants.division_id' => $request->division , 'participants.departement_id' => $request->departement , 'projects.position_id' => $request->position])
                        ->groupBy('competence_id' , 'company' , 'competence' , 'definition' , 'key_behavior' , 'company_id' , 'division_id' , 'departement_id' , 'position_id')
                        ->get();
            // return $results;
                
            if($results->isEmpty()){
                return redirect()->back();
            }else{
                $key_behavior = array();

                foreach ($results as $key => $value) {
                    array_push($key_behavior , $value->key_behavior);            
                }
            
                $values = [
                    'company' => $results[0]->company,
                    'competence' => $value->competence,
                    'definition' => $value->definition,
                    'key_behavior' => $key_behavior
                ];

                $pdf = PDF::loadView('assessment.report.competence-pdf' , [ 'competence' => $value->competence , 'definition' => $value->definition , 'key_behavior' => $key_behavior  ,'counter' => 0 , 'flag' => $flag , 'company' => $company]);
                $pdf->setPaper('a4');
            }
        }
        return $pdf->stream('Laporan-kompetensi-'.$company.'.PDF');
    }

    public function competenceDOC(Request $request){
        $validatedData = $request->validate([
            'competence' => 'required',
        ]);
        $counter = 0;
        $phpWord = new \PhpOffice\PhpWord\PhpWord();
        $section = $phpWord->addSection();

        // style table
        $styleCell =
        [
            'borderTopColor' =>'ffffff',
            'borderTopSize' => 1,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'0000000',
            'borderBottomSize' => 2,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ];

        // style font
        $styleFont = [
            'bold' => true ,
            'size' => 12,
        ];

        $indentParagraph = ['indentation' => ['left' => Converter::cmToTwip(0.5)]];
        $indentParagraphBullet = ['indentation' => ['left' => Converter::cmToTwip(1.6)]];

        // create header
        $header = $section->createHeader();
        $tableH = $header->addTable();
        $tableH->addRow();
        $tableH->addCell(9000 , $styleCell)->addText('Kamus Kompetensi');
        // $tableH->addCell(4500, $styleCell)->addText('| Kamus Kompetensi Generic', array('bold' => false), array('alignment' => 'right'));

        // create footer
        $footer = $section->createFooter();
        $tableF = $footer->addTable();
        $tableF->addRow();
        $tableF->addCell(4500)->addText('© Bina Potensia Indonesia');
        $tableF->addCell(4500)->addPreserveText('{PAGE}', array('bold' => false), array('alignment' => 'right'));

        if($request->competence == 'company'){
            $validate = $this->validate($request , [
                'company' => 'required' ,
                'division' => 'required' ,
                'departement' => 'required',
                'position' => 'required'
            ]);
            
            
            $results = DB::table('participants')
                        ->select('competencies.id AS competence_id' , 'companies.name AS company' ,  'competencies.name AS competence' , 'competencies.definition AS definition' , 'key_behaviors.description AS key_behavior' ,  'projects.company_id' , 'participants.division_id' , 'participants.departement_id' , 'projects.position_id')
                        ->join('projects' , 'participants.company_id' , '=' , 'projects.company_id')
                        ->join('project_questions' ,'projects.id' ,'=' ,'project_questions.project_id')
                        ->join('key_behaviors' , 'project_questions.key_behavior_id' , '=' , 'key_behaviors.id')
                        ->join('competencies' , 'key_behaviors.competence_id' , '=' , 'competencies.id')
                        ->join('companies' , 'participants.company_id' , '=' , 'companies.id')
                        ->where(['projects.company_id' => $request->company , 'participants.division_id' => $request->division , 'participants.departement_id' => $request->departement , 'projects.position_id' => $request->position])
                        ->groupBy('competence_id' , 'company' , 'competence' , 'definition' , 'key_behavior' , 'company_id' , 'division_id' , 'departement_id' , 'position_id')
                        ->get();
            // return $results;
            
            if($results->isEmpty()){
                return redirect()->back();
            }else{
                $key_behavior = array();

                foreach ($results as $key => $value) {
                    array_push($key_behavior , $value->key_behavior);
                    
                }

                $values = [
                    'company' => $results[0]->company,
                    'competence' => $value->competence,
                    'definition' => $value->definition,
                    'key_behavior' => $key_behavior
                ];

                
                $section->addText("Nama Perusahaan : ".$values['company']);
                $section->addText(($counter += 1).'. '.$values['competence'] , $styleFont);
                $section->addText(htmlspecialchars("Definisi : ") , $styleFont, $indentParagraph);
                $section->addText(htmlspecialchars($values['definition']), null, $indentParagraph);
                $section->addText(htmlspecialchars("Key Behavior : ") , $styleFont, $indentParagraph);
                foreach ($values['key_behavior'] as $key => $value) {
                    $listItemRun = $section->addListItemRun(0, null, $indentParagraphBullet);
                    $listItemRun->addText(htmlspecialchars("\n".$value));
                }
                $section->addTextBreak();
                $file = 'laporan-kompetensi-'.$values['company'].'.docx';


            }
            
            
        }else{
            // content
            $competencies = Competency::with('behavior')->orderBy('name' , 'ASC')->get();
            foreach ($competencies as $key => $value) {
                $counter += 1;
                $section->addText($counter.'. '.$value->name , $styleFont);
                $section->addText(htmlspecialchars("Definisi : ") , $styleFont, $indentParagraph);
                $section->addText(htmlspecialchars($value->definition), null, $indentParagraph);
                $section->addText(htmlspecialchars("Key Behavior : ") , $styleFont, $indentParagraph);
                for ($i=0; $i < count($value->behavior) ; $i++) {
                    $listItemRun = $section->addListItemRun(0, null, $indentParagraphBullet);
                    $listItemRun->addText(htmlspecialchars("\n".$value->behavior[$i]->description));
                }
                $section->addTextBreak();
            }
            $file = 'laporan-kompetensi-semua.docx';
        }
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {
            $objWriter->save(storage_path($file));
        } catch (Exception $e) {

        }
        return response()->download(storage_path($file));
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
                        ->select('projects.company_id' , 'companies.name AS company' , 'participants.division_id' , 'divisions.name AS division' , 'participants.departement_id' , 'departements.name AS departement' , 'projects.position_id' , 'positions.name AS position')
                        ->join('projects' , 'projects.company_id' , '=' , 'participants.company_id')
                        ->join('companies' , 'projects.company_id' , '=' , 'companies.id')
                        ->join('divisions' , 'participants.division_id' , '=' , 'divisions.id')
                        ->join('departements', 'participants.departement_id', '=' , 'departements.id')
                        ->join('positions', 'projects.position_id' , '=' , 'positions.id')
                        ->groupBy('projects.company_id', 'participants.division_id' , 'participants.departement_id' ,'projects.position_id' )
                        ->where(['projects.company_id' => $request->company_id , 'participants.division_id' => $request->division_id , 'participants.departement_id' => $request->departement_id])
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