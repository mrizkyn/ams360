<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use App\Models\Assessment\Project;
use App\Models\Assessment\Company;
use App\Models\Assessment\Position;
use App\Models\Assessment\Participant;
use App\Models\Assessment\KeyBehavior;
use App\Http\Resources\Participant as ProjectParticipantResource;
use App\Models\Assessment\ProjectParticipant;
use App\Models\Assessment\ProjectParticipantRepondent;
use App\Models\Assessment\ProjectParticipantRepondentAnswer;
use App\Models\Assessment\ProjectParticipantStatus;
use App\Models\Assessment\ProjectQuestion;
use App\Models\DB\ProjectParticipantValue;
use PDF;
use DB;
use Illuminate\Support\Facades\Storage;
use PhpOffice\PhpWord\Shared\Converter;
// use PhpOffice\PhpWord\Style\Alignment;

class ProjectController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.project.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.project.create', [
            'companies' => Company::all()->sortBy('name'),
            'positions' => Position::all()->sortBy('name'),
            'keyBehaviors' => KeyBehavior::all()->sortBy('competence.name')
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $this->validate($request , [
            'name' => 'required|unique:projects,name'
        ],[
            'name.unique' => 'Nama project yang diinputkan sudah terdaftar sebelumnya'
        ]);

        $selectedParticipants = json_decode($request->selected_participants);
        $selectedKeyBehaviors = json_decode($request->selected_key_behaviors);

        if (empty($selectedParticipants)) {
            return redirect('assessment/projects/create')->withInput()->with('errorAsesi', 'Asesi belum dipilih');
        }


        if (empty($selectedKeyBehaviors)) {
            return redirect('assessment/projects/create')->withInput()->with('errorKeyBehaviour', 'Perilaku Kunci Belum dipilih belum dipilih');
        }
        // return $selectedParticipants;

        $project =  Project::create([
            'company_id' => $request->company_id,
            'position_id' => $request->position_id,
            'name' => $request->name,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'participant_number' => count($selectedParticipants),
            'type' => $request->type,
            'scale' => $request->scale,
            'status' => 'Draft'
        ]);

        foreach ($selectedParticipants as $participant) {
            $projectParticipant = ProjectParticipant::create([
                'project_id' => $project->id,
                'participant_id' => $participant->id,
                'superior_number' => $participant->superior_number,
                'collegue_number' => $participant->collegue_number,
                'subordinate_number' => $participant->subordinate_number,
                'status' => 'Belum Selesai'
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Diri Sendiri',
                'difference' => 1
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Atasan',
                'difference' => $participant->superior_number
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Rekan Kerja',
                'difference' => $participant->collegue_number
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Bawahan',
                'difference' => $participant->subordinate_number
            ]);
        }

        foreach ($selectedKeyBehaviors as $keyBehavior) {
            ProjectQuestion::create([
                'project_id' => $project->id,
                'key_behavior_id' => $keyBehavior->id
            ]);
        }

        return redirect(url('assessment/projects'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        return view('assessment/project/show', [
            'project' => Project::find($id),
            'projectParticipants' => ProjectParticipant::where('project_id', $id)->get(),
            'projectQuestions' => ProjectQuestion::where('project_id', $id)->get()
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        return view('assessment.project.edit', [
            'project' => Project::find($id),
            'companies' => Company::all(),
            'positions' => Position::all(),
            'keyBehaviors' => KeyBehavior::orderBy('competence_id')->get(),
            'projectParticipants' => ProjectParticipant::where('project_id', $id)->get(),
            'projectQuestions' => ProjectQuestion::where('project_id', $id)->get()
        ]);
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
        $project = Project::find($id);
        $selectedParticipants = json_decode($request->selected_participants);
        $selectedKeyBehaviors = json_decode($request->selected_key_behaviors);

        ProjectQuestion::where('project_id', $id)->delete();
        $projectParticipants = ProjectParticipant::where('project_id', $id)->get();
        foreach($projectParticipants as $projectParticipant){
            ProjectParticipantStatus::where('project_participant_id', $projectParticipant->id)->delete();
            $projectParticipant->delete();
        }

        $project->company_id = $request->company_id;
        $project->position_id = $request->position_id;
        $project->name = $request->name;
        $project->start_date = $request->start_date;
        $project->end_date = $request->end_date;
        $project->participant_number = count($selectedParticipants);
        $project->type = $request->type;
        $project->scale = $request->scale;
        $project->status = 'Draft';
        $project->save();

        foreach ($selectedParticipants as $participant) {
            $projectParticipant = ProjectParticipant::create([
                'project_id' => $project->id,
                'participant_id' => $participant->id,
                'superior_number' => $participant->superior_number,
                'collegue_number' => $participant->collegue_number,
                'subordinate_number' => $participant->subordinate_number,
                'status' => 'Belum Selesai'
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Diri Sendiri',
                'difference' => 1
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Atasan',
                'difference' => $projectParticipant->superior_number
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Rekan Kerja',
                'difference' => $projectParticipant->collegue_number
            ]);
            ProjectParticipantStatus::create([
                'project_participant_id' => $projectParticipant->id,
                'type' => 'Bawahan',
                'difference' => $projectParticipant->subordinate_number
            ]);
        }

        foreach ($selectedKeyBehaviors as $keyBehaviorId) {
            ProjectQuestion::create([
                'project_id' => $project->id,
                'key_behavior_id' => $keyBehaviorId
            ]);
        }

        return redirect(url('assessment/projects'));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        Project::destroy($id);
        ProjectQuestion::where('project_id', $id)->delete();
        $projectParticipants = ProjectParticipant::where('project_id', $id)->get();
        $projectParticipants->each(function ($projectParticipant){
            $projectParticipant->delete();
            ProjectParticipantStatus::where('project_participant_id', $projectParticipant->id);
            $projectParticipantRespondents = ProjectParticipantRepondent::where('project_participant_id', $projectParticipant->id)->get();
            $projectParticipantRespondents->each(function ($projectParticipantRespondent){
                $projectParticipantRespondent->delete();
                ProjectParticipantRepondentAnswer::where('project_participant_respondent_id', $projectParticipantRespondent->id)->delete();
            });

        });

        return redirect('assessment/projects');
    }

    public function projectData(){
        return DataTables::of(Project::with('company')->get())
        ->addIndexColumn()
        ->addColumn('action', function($project){
            $csrf = csrf_token();
            return '
            <a href="/assessment/projects/'.$project->id.'" id="'.$project->id.'" class="btn btn-sm btn-default">
            <i class="far fa-eye"></i>Detail
            </a>
            <button type="button" class="btn btn-sm btn-default update" id="'.$project->id.'" data-status="'.$project->status.'">
            <i class="fas fa-edit"></i> Ubah
            </button>';
        })
        ->make(true);
    }

    public function getParticipantByCompany($companyId){
        return ProjectParticipantResource::collection(Participant::where('company_id', $companyId)->get());
    }

    public function printQuestionWord($id)
    {
        $project = Project::find($id);
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
        // $styleFont = [
        //     'bold' => true ,
        //     'size' => 12,
        // ];

        $indentParagraph = ['indentation' => ['first' => Converter::cmToTwip(0.5)], 'align' => 'both'];
        // $indentParagraphBullet = ['indentation' => ['left' => Converter::cmToTwip(1.6)]];

        // Add header for all other pages
        $subsequent = $section->addHeader();
        $subsequent->addImage(Storage::disk('public')->get("BPI.png"), array('width' => 180, 'height' => 70));

        // create footer
        $footer = $section->createFooter();
        $footer->addText('', [], ['borderBottomSize' => 6]);
        $tableF = $footer->addTable();
        $tableF->addRow();
        $tableF->addCell(4500)->addText('© Bina Potensia Indonesia');
        $tableF->addCell(4500)->addPreserveText('{PAGE}', array('bold' => false), array('alignment' => 'right'));

        $paragraphCenter = 'pStyle';
        $phpWord->addParagraphStyle($paragraphCenter, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER));

        $paragraphBoth = 'pStyle1';
        $phpWord->addParagraphStyle($paragraphBoth, array('align' => 'both'));

        // $paragraphLeft = 'pStyleLeft';
        // $phpWord->addParagraphStyle($paragraphLeft, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'bold' => true));

        // Define styles
        // $paragraphTextRun = 'pStyle';
        // $phpWord->addParagraphStyle($paragraphTextRun, array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT));

        $section->addText('EVALUASI KOMPETENSI HASIL', array('name' => 'calibri','size' => 14, 'bold' => true), $paragraphCenter);
        $section->addText('DEVELOPMENT PROGRAM - ' . strtoupper($project->position->name) . '', array('name' => 'calibri','size' => 14, 'bold' => true), $paragraphCenter);

        $fancyTableStyle = array('borderSize' => 6, 'borderColor' => '999999', 'cellMargin' => 80, 'alignment' => \PhpOffice\PhpWord\SimpleType\JcTable::CENTER);
        $spanTableStyleName = 'Colspan Rowspan';
        $phpWord->addTableStyle($spanTableStyleName, $fancyTableStyle);
        $table = $section->addTable($spanTableStyleName);

        $cellColSpan = array('gridSpan' => 4, 'valign' => 'center');
        $cellRowContinue = array('vMerge' => 'continue');
        $cellHCentered = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::CENTER, 'valign' => 'center');
        $cellHLeft = array('alignment' => \PhpOffice\PhpWord\SimpleType\Jc::LEFT, 'valign' => 'center');
        $cellVCentered = array('valign' => 'center');

        $table->addRow();
        $cell2 = $table->addCell(10000, $cellColSpan);
        $textrun2 = $cell2->addTextRun($cellHCentered);
        $textrun2->addText($project->company->name, array('name' => 'calibri','bold' => true,'size' => 14));

        $table->addRow();
        $cell2 = $table->addCell(10000, array('gridSpan' => 4, 'valign' => 'center','bgColor' => '#d9d9d9'));
        $textrun2 = $cell2->addTextRun($cellHLeft);
        $textrun2->addText('IDENTITAS YANG DINILAI', array('name' => 'calibri','size' => 10,'bold' => true));

        $table->addRow();
        $table->addCell(3000, [
            'borderTopColor' =>'ffffff',
            'borderTopSize' => 1,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'999999',
            'borderLeftSize' => 6,
        ])->addText('Nama yang dinilai', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(3000, [
            'borderTopColor' =>'ffffff',
            'borderTopSize' => 1,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'ffffff',
            'borderTopSize' => 1,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText('Divisi', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'ffffff',
            'borderTopSize' => 1,
            'borderRightColor' =>'999999',
            'borderRightSize' => 6,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);

        $table->addRow();
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'999999',
            'borderLeftSize' => 6,
        ])->addText('Jabatan', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText('Departemen', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'999999',
            'borderRightSize' => 6,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);

        $table->addRow();
        $cell2 = $table->addCell(10000, array('gridSpan' => 4, 'valign' => 'center','bgColor' => '#d9d9d9'));
        $textrun2 = $cell2->addTextRun($cellHLeft);
        $textrun2->addText('IDENTITAS PENILAI', array('bold' => true,'name' => 'calibri','size' => 10));

        $table->addRow();
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'999999',
            'borderLeftSize' => 6,
        ])->addText('Nama penilai', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText('Divisi', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'999999',
            'borderRightSize' => 6,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);

        $table->addRow();
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'999999',
            'borderLeftSize' => 6,
        ])->addText('Jabatan', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText('Departemen', array('name' => 'calibri','size' => 10), $cellHLeft);
        $table->addCell(2000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'999999',
            'borderRightSize' => 6,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1,
        ])->addText(':', array('name' => 'calibri','size' => 10), $cellHLeft);

        $table->addRow();
        $table->addCell(3000, [
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'ffffff',
            'borderRightSize' => 1,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'999999',
            'borderLeftSize' => 6,
        ])->addText('Kode Rater', array('name' => 'calibri','size' => 10), $cellHLeft);
        $colSpanCheckbox = $table->addCell(7000, array('gridSpan' => 3,
            'borderTopColor' =>'999999',
            'borderTopSize' => 6,
            'borderRightColor' =>'999999',
            'borderRightSize' => 6,
            'borderBottomColor' =>'999999',
            'borderBottomSize' => 6,
            'borderLeftColor' =>'ffffff',
            'borderLeftSize' => 1
        ));
        $textRun = $colSpanCheckbox->addTextRun($cellHLeft);
        $textRun->addText(': ', array('name' => 'calibri','size' => 10), $cellHLeft);
        // $colSpanCheckbox->addText(': [ ] Atasan   [ ] Rekan   [ ] Bawahan   [ ] Diri Sendiri', null, $cellHLeft);
        $textRun->addCheckBox('chkBox1',' Atasan   ',array('name' => 'calibri','size' => 10));
        $textRun->addCheckBox('chkBox2',' Rekan   ',array('name' => 'calibri','size' => 10));
        $textRun->addCheckBox('chkBox3',' Bawahan   ',array('name' => 'calibri','size' => 10));
        $textRun->addCheckBox('chkBox4',' Diri Sendiri   ',array('name' => 'calibri','size' => 10));

        $section->addTextBreak();
        $section->addText('Pengantar', array('name' => 'calibri','size' => 11,'bold' => true));
        // $textrun2 = $cell2->addTextRun($cellHLeft);
        // $textrun2->addText('IDENTITAS YANG DINILAI');
        $textRun = $section->addTextRun($paragraphBoth);
        $textRun->addText(htmlspecialchars('Evaluasi kompetensi hasil dari program pengembangan dilakukan dengan menggunakan metode 3-'),array('name' => 'calibri','size' => 11));
        $textRun->addText(htmlspecialchars('Sixty Degree. '), array('name' => 'calibri','size' => 11,'italic' => true));
        $textRun->addText(htmlspecialchars('Tujuannya adalah sebagai bagian dari upaya yang dilakukan perusahaan dalam rangka mengembangkan '),array('name' => 'calibri','size' => 11));
        $textRun->addText(htmlspecialchars('Kompetensi Karyawan. '), array('name' => 'calibri','size' => 11,'bold' => true));
        $textRun->addText(htmlspecialchars('Melalui hasil evaluasi, diharapkan karyawan mendapatkan '),array('name' => 'calibri','size' => 11));
        $textRun->addText(htmlspecialchars('feedback '), array('name' => 'calibri','size' => 11,'italic' => true));
        $textRun->addText(htmlspecialchars('tentang efektivitas perilaku (kompetensi) yang dikembangkan di tempat kerja dan tampil dalam pekerjaan sehari-hari.'),array('name' => 'calibri','size' => 11));
        $textRun1 = $section->addTextRun($paragraphBoth);
        $textRun1->addText(htmlspecialchars('Dalam eveluasi ini, Bapak/Ibu/Sdr/i diminta untuk memberikan penilaian terhadap pernyataan-pernyataan yang mencerminkan perilaku kerja '),array('name' => 'calibri','size' => 11));
        $textRun1->addText(htmlspecialchars('karyawan (sesuai Nama yang dinilai). '), array('name' => 'calibri','size' => 11,'bold' => true));
        $textRun1->addText(htmlspecialchars('Hasil jawaban Bapak/Ibu/Sdr/i akan dianalisa berdasarkan kombinasi hasil penilaian dari atasan, rekan, diri sendiri dan bawahan. Tidak ada jawaban yang salah. Silakan mengisi sesuai dengan instruksi yang diberikan. Hal yang perlu diperhatikan dalam memberikan penilaian adalah '),array('name' => 'calibri','size' => 11));
        $textRun1->addText(htmlspecialchars('berikanlah penilaian seobjektif mungkin berdasarkan hasil pengamatan perilaku yang ditampilkan dalam pekerjaan kesehariannya.'), array('name' => 'calibri','size' => 11,'bold' => true));
        $textRun1 = $section->addTextRun($paragraphBoth);
        $textRun1->addText(htmlspecialchars('Terima kasih atas partisipasi dan kerja sama Bapak/Ibu/Sdr/i dalam kegiatan evaluasi hasil pengembangan kompetensi dengan menggunakan metode 3-'),array('name' => 'calibri','size' => 11));
        $textRun1->addText(htmlspecialchars('Sixty Degree.'), array('name' => 'calibri','size' => 11,'italic' => true));
        $section->addTextBreak();
        $section->addText(htmlspecialchars('Selamat bekerja!'), array('name' => 'calibri','size' => 11), array('align' => 'center'));

        $section->addPageBreak();
        $section->addText('Instruksi Pengisian', array('name' => 'calibri','size' => 11,'bold' => true));
        $section->addText(htmlspecialchars('Dihadapan Bapak/Ibu/Sdr/i terdapat sejumlah pernyataan yang menggambarkan perilaku-perilaku kerja pada posisi tertentu yang menjadi target pengembangan. Silakan memberikan nilai terhadap pernyataan-pernyataan berdasarkan kesesuaian perilaku yang ditampilkan dalam menjalankan pekerjaannya sehari-hari. Untuk setiap pernyataan, Bapak/Ibu/Sdr/i diminta memberikan penilaian dari dua sudut pandang.'), array('name' => 'calibri','size' => 11), array('align' => 'both'));

        if ($project->type == 1) {
            $section->addTextBreak();
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('1. CPR = '), array('name' => 'calibri','size' => 11,'bold' => true));
            $textrun->addText(htmlspecialchars('Current Proficiency Required'), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Current Proficiency Required '), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun->addText(htmlspecialchars('adalah tingkat efektivitas yang '), array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DIPERSYARATKAN '), array('name' => 'calibri','size' => 11,'bold' => true));
            $textrun->addText(htmlspecialchars('untuk dapat menjalankan pekerjaan '), array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('SAAT INI.'), array('name' => 'calibri','size' => 11,'bold' => true));
            $section->addText(htmlspecialchars('Tingkat efektivitas dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SANGAT EFEKTIF (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        } elseif ($project->type == 2) {
            $section->addTextBreak();
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('1. FPR = '), array('name' => 'calibri','size' => 11,'bold' => true));
            $textrun->addText(htmlspecialchars('Future Proficiency Required'), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Future Proficiency Required '), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun->addText(htmlspecialchars('adalah tingkat efektivitas yang '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DIPERSYARATKAN '), array('name' => 'calibri','size' => 11,'bold' => true));
            $textrun->addText(htmlspecialchars('untuk dapat menjalankan pekerjaan '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DI MASA YANG AKAN DATANG.'), array('name' => 'calibri','size' => 11,'bold' => true));
            $section->addText(htmlspecialchars('Tingkat efektivitas dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SANGAT EFEKTIF (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        } elseif ($project->type == 3) {
            $section->addTextBreak();
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('1. CFR = '), array('name' => 'calibri','size' => 11,'bold' => true));
            $textrun->addText(htmlspecialchars('Current Frequency Required'), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Current Frequency Required '), array('name' => 'calibri','size' => 11,'bold' => true, 'italic' => true));
            $textrun->addText(htmlspecialchars('adalah tingkat frekuensi (kekerapan) munculnya suatu perilaku, yang '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DIPERSYARATKAN '), array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('untuk dapat menjalankan pekerjaan '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('SAAT INI.'), array('bold' => true,'name' => 'calibri','size' => 11));
            $section->addText(htmlspecialchars('Tingkat frekuensi dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SELALU (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        } else {
            $section->addTextBreak();
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText('1. FFR = ', array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText('Future Frequency Required', array('bold' => true,'name' => 'calibri','size' => 11, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Future Frequency Required '), array('bold' => true, 'italic' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('adalah tingkat frekuensi (kekerapan) munculnya suatu perilaku, yang '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DIPERSYARATKAN '), array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('untuk dapat menjalankan pekerjaan '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DI MASA YANG AKAN DATANG.'), array('bold' => true,'name' => 'calibri','size' => 11));
            $section->addText(htmlspecialchars('Tingkat frekuensi dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SELALU (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        }

        $table = $section->addTable($spanTableStyleName);
        $table->addRow();
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('N', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('1       2', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('3       4', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('5       6', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('7       8', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('9       10', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));

        if ($project->type == 1 || $project->type == 2) {
            $table->addRow();
            $table->addCell(2000, $cellVCentered)->addText('Tidak ditampilkan', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Tidak Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Tidak Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Cukup Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        } else {
            $table->addRow();
            $table->addCell(2000, $cellVCentered)->addText('Tidak pernah ditampilkan', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Jarang', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Jarang', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Cukup Sering', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sering', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Selalu', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        }
        $section->addTextBreak();
        $textrun = $section->addTextRun($paragraphBoth);
        $textrun->addText(htmlspecialchars('Silakan Bapak/Ibu/Sdr/i '), array('name' => 'calibri','size' => 11));
        $textrun->addText(htmlspecialchars('melingkari (atau memberi tanda lain seperti mengganti warna) '), array('bold' => true,'name' => 'calibri','size' => 11));
        if ($project->type == 1 || $project->type == 2) {
            $textrun->addText(htmlspecialchars('salah satu nomor pilihan yang tersedia berdasarkan pertimbangan efektivitas tampilan perilaku yang bersangkutan pada setiap pernyataan.'),array('name' => 'calibri','size' => 11));
        } else {
            $textrun->addText(htmlspecialchars('salah satu nomor pilihan yang tersedia berdasarkan pertimbangan frekuensi tampilan perilaku yang bersangkutan pada setiap pernyataan.'),array('name' => 'calibri','size' => 11));
        }

        $section->addTextBreak();
        if ($project->type == 1 || $project->type == 2) {
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText('2. CP = ', array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText('Current Proficiency', array('bold' => true,'name' => 'calibri','size' => 11, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Current Proficiency '), array('bold' => true, 'italic' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('adalah tingkat efektivitas perilaku yang '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('DITAMPILKAN '), array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('dalam menjalankan pekerjaannya '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('SAAT INI.'), array('bold' => true,'name' => 'calibri','size' => 11));
            $section->addText(htmlspecialchars('Tingkat efektivitas dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SANGAT EFEKTIF (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        } else {
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText('2. CF = ', array('bold' => true,'name' => 'calibri','size' => 11));
            $textrun->addText('Current Frequency', array('bold' => true,'name' => 'calibri','size' => 11, 'italic' => true));
            $textrun = $section->addTextRun($paragraphBoth);
            $textrun->addText(htmlspecialchars('Current Frequency '), array('bold' => true, 'italic' => true,'name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('adalah tingkat frekuensi (kekerapan) munculnya suatu perilaku dalam menjalankan pekerjaannya '),array('name' => 'calibri','size' => 11));
            $textrun->addText(htmlspecialchars('SAAT INI.'), array('bold' => true,'name' => 'calibri','size' => 11));
            $section->addText(htmlspecialchars('Tingkat frekuensi dimulai dari rentang TIDAK DITAMPILKAN (N) sampai SELALU (9-10). Secara terperinci, rentang yang tergambar sebagai berikut :'), array('name' => 'calibri','size' => 11), array('align' => 'both'));
        }

        $table = $section->addTable($spanTableStyleName);
        $table->addRow();
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('N', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('1       2', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('3       4', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('5       6', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('7       8', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        $table->addCell(2000, array('valign' => 'center', 'bgColor' => '#d9d9d9'))->addText('9       10', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));

        if ($project->type == 1 || $project->type == 2) {
            $table->addRow();
            $table->addCell(2000, $cellVCentered)->addText('Tidak ditampilkan', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Tidak Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Tidak Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Cukup Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Efektif', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        } else {
            $table->addRow();
            $table->addCell(2000, $cellVCentered)->addText('Tidak pernah ditampilkan', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sangat Jarang', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Jarang', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Cukup Sering', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Sering', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
            $table->addCell(2000, $cellVCentered)->addText('Selalu', array('bold' => true,'name' => 'calibri','size' => 11), array('alignment' => 'center'));
        }

        $section->addTextBreak();
        $textrun = $section->addTextRun($paragraphBoth);
        $textrun->addText(htmlspecialchars('Silakan Bapak/Ibu/Sdr/i '),array('name' => 'calibri','size' => 11));
        $textrun->addText(htmlspecialchars('melingkari (atau memberi tanda lain seperti mengganti warna) '), array('bold' => true,'name' => 'calibri','size' => 11));
        if ($project->type == 1 || $project->type == 2) {
            $textrun->addText(htmlspecialchars('salah satu nomor pilihan yang tersedia berdasarkan pertimbangan efektivitas tampilan perilaku yang bersangkutan pada setiap pernyataan.'),array('name' => 'calibri','size' => 11));
        } else {
            $textrun->addText(htmlspecialchars('salah satu nomor pilihan yang tersedia berdasarkan pertimbangan frekuensi tampilan perilaku yang bersangkutan pada setiap pernyataan.'),array('name' => 'calibri','size' => 11));
        }


        $section->addPageBreak();
        $section->addText('EVALUASI KOMPETENSI HASIL', array('size' => 14, 'bold' => true,'name' => 'calibri'), $paragraphCenter);
        $section->addText('DEVELOPMENT PROGRAM - ' . strtoupper($project->position->name) . '', array('size' => 14, 'bold' => true,'name' => 'calibri'), $paragraphCenter);

        $table = $section->addTable($spanTableStyleName);
        $table->addRow();
        $table->addCell(500, array('valign' => 'center','bgColor' => '#d9d9d9'))->addText('No', array('bold' => true,'name' => 'calibri','size' => 10), array('alignment' => 'center'));
        $table->addCell(3500, array('valign' => 'center','bgColor' => '#d9d9d9'))->addText('Pernyataan', array('bold' => true,'name' => 'calibri','size' => 10), array('alignment' => 'center'));
        $table->addCell(6000, array('gridSpan' => ($project->scale + 2), 'valign' => 'center','bgColor' => '#d9d9d9'))->addText('Rating', array('bold' => true,'name' => 'calibri','size' => 10), array('alignment' => 'center'));

        $type1 = '';
        $type2 = '';
        if ($project->type == 1) {
            $type1 = 'Current Proficiency';
            $type2 = 'Current Proficiency Required';
        } elseif ($project->type == 2) {
            $type1 = 'Current Proficiency';
            $type2 = 'Future Proficiency Required';
        } elseif ($project->type == 3) {
            $type1 = 'Current Frequency';
            $type2 = 'Current Frequency Required';
        } else {
            $type1 = 'Current Frequency';
            $type2 = 'Future Frequency Required';
        }
        foreach ($project->projectQuestions as $key => $value) {
            $table->addRow();
            $table->addCell(500, array('vMerge' => 'restart', 'valign' => 'center'))->addText(($key+1), array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
            $table->addCell(3500, array('vMerge' => 'restart', 'valign' => 'center'))->addText($value->keyBehavior->description, array('name' => 'calibri','size' => 10), array('alignment' => 'left'));
            $table->addCell(2000, array('vMerge' => 'restart', 'valign' => 'center'))->addText($type2, array('name' => 'calibri','size' => 10), array('alignment' => 'center', 'indentation' => ['first' => Converter::cmToTwip(0.5)]));
            $table->addCell((4000/($project->scale + 1)), [
                'borderTopColor' =>'999999',
                'borderTopSize' => 6,
                'borderRightColor' =>'ffffff',
                'borderRightSize' => 1,
                'borderBottomColor' =>'999999',
                'borderBottomSize' => 6,
                'borderLeftColor' =>'999999',
                'borderLeftSize' => 6,
                'valign' => 'center',
            ])->addText('N', array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
            for ($i=1; $i <= $project->scale; $i++) {
                if ($i==$project->scale) {
                    $table->addCell((4000/($project->scale + 1)), [
                        'borderTopColor' =>'999999',
                        'borderTopSize' => 6,
                        'borderRightColor' =>'999999',
                        'borderRightSize' => 6,
                        'borderBottomColor' =>'999999',
                        'borderBottomSize' => 6,
                        'borderLeftColor' =>'ffffff',
                        'borderLeftSize' => 1,
                        'valign' => 'center',
                    ])->addText($i, array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
                } else {
                    $table->addCell((4000/($project->scale + 1)), [
                        'borderTopColor' =>'999999',
                        'borderTopSize' => 6,
                        'borderRightColor' =>'ffffff',
                        'borderRightSize' => 1,
                        'borderBottomColor' =>'999999',
                        'borderBottomSize' => 6,
                        'borderLeftColor' =>'ffffff',
                        'borderLeftSize' => 1,
                        'valign' => 'center',
                    ])->addText($i, array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
                }
            }

            $table->addRow();
            $table->addCell(null, array('vMerge' => 'continue', 'valign' => 'center'));
            $table->addCell(null, array('vMerge' => 'continue', 'valign' => 'center'));
            $table->addCell(2000, array('valign' => 'center'))->addText($type1, array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
            $table->addCell((4000/($project->scale + 1)), [
                'borderTopColor' =>'999999',
                'borderTopSize' => 6,
                'borderRightColor' =>'ffffff',
                'borderRightSize' => 1,
                'borderBottomColor' =>'999999',
                'borderBottomSize' => 6,
                'borderLeftColor' =>'999999',
                'borderLeftSize' => 6,
                'valign' => 'center',
            ])->addText('N', array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
            for ($i=1; $i <= $project->scale; $i++) {
                if ($i==$project->scale) {
                    $table->addCell((4000/($project->scale + 1)), [
                        'borderTopColor' =>'999999',
                        'borderTopSize' => 6,
                        'borderRightColor' =>'999999',
                        'borderRightSize' => 6,
                        'borderBottomColor' =>'999999',
                        'borderBottomSize' => 6,
                        'borderLeftColor' =>'ffffff',
                        'borderLeftSize' => 1,
                        'valign' => 'center',
                    ])->addText($i, array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
                } else {
                    $table->addCell((4000/($project->scale + 1)), [
                        'borderTopColor' =>'999999',
                        'borderTopSize' => 6,
                        'borderRightColor' =>'ffffff',
                        'borderRightSize' => 1,
                        'borderBottomColor' =>'999999',
                        'borderBottomSize' => 6,
                        'borderLeftColor' =>'ffffff',
                        'borderLeftSize' => 1,
                        'valign' => 'center',
                    ])->addText($i, array('name' => 'calibri','size' => 10), array('alignment' => 'center'));
                }
            }
        }

        // download
        $file = 'Kuesioner '.$project->company->name.'.docx';
        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        try {

            $objWriter->save(storage_path($file));

        } catch (Exception $e) {

        }
        return response()->download(storage_path($file));
    }

    public function printQuestionPDF($id){
        $project = Project::find($id);
        switch($project->type){
            case 1:
            $pdf = PDF::loadView('assessment.report.CP-CPR',[
                'project' => $project
            ]);
            return $pdf->download();
            case 2:
            $pdf = PDF::loadView('assessment.report.CP-FPR',[
                'project' => $project
            ]);
            return $pdf->download();
            case 3:
            $pdf = PDF::loadView('assessment.report.CF-CFR',[
                'project' => $project
            ]);
            return $pdf->download();
            case 4:
            $pdf = PDF::loadView('assessment.report.CF-FFR',[
                'project' => $project
            ]);
            return $pdf->download();
        }
    }

    public function printQuestionDOCX($id){

    }
}
