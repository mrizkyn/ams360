<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use File;
use DB;
use Illuminate\Support\Facades\Storage;
use App\Models\Assessment\Participant;
use App\Models\Assessment\Company;
use App\Models\Assessment\Departement;
use App\Models\Assessment\Division;
use App\Models\Assessment\Position;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.participant.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::all()->sortBy('name');
        $departements = Departement::all()->sortBy('name');
        $positions = Position::all()->sortBy('name');
        $divisions = Division::all()->sortBy('name');

        return view('assessment.participant.create' , [
            'companies' => $companies ,
            'departements' => $departements ,
            'positions' => $positions ,
            'divisions' => $divisions
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
        $validate = $this->validate($request , [
            'name' => 'required' ,
            'identity' => 'required' ,
            'company' => 'required' ,
            'division' => 'required' ,
            'departement' => 'required' ,
            'position' => 'required'
        ]);

        $participant = new Participant;
        $participant->name = $request->name;
        $participant->identity_number = $request->identity;
        $participant->project_date = $request->project_date;
        $participant->birth = $request->birth;
        $participant->phone = $request->phone;
        $participant->email = $request->email;
        if($request->hasFile('image')){
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time().'.'.$extension;
            $path = 'public/picture/';
            $request->file('image')->storeAs($path,$filename);
            $participant->picture = $filename;
        }else{
            $participant->picture = 'no_foto.png';
        }
        $participant->company_id = $request->company;
        $participant->division_id = $request->division;
        $participant->departement_id = $request->departement;
        $participant->position_id = $request->position;
        $participant->save();

        return redirect('assessment/participants');
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
        $companies = Company::all()->sortBy('name');
        $departements = Departement::all()->sortBy('name');
        $positions = Position::all()->sortBy('name');
        $divisions = Division::all()->sortBy('name');
        $participant = Participant::find($id);

        $name = $participant->name;
        $nik = $participant->identity_number;
        $projectDate = $participant->project_date;
        $birth = $participant->birth;
        $phone = $participant->phone;
        $email = $participant->email;
        $picture = $participant->picture;
        $company = $participant->company_id;
        $division = $participant->division_id;
        $departement = $participant->departement_id;
        $position = $participant->position_id;


        return view('assessment.participant.edit' , [
            'companies' => $companies ,
            'departements' => $departements ,
            'positions' => $positions ,
            'divisions' => $divisions ,
            'id' => $id ,
            'name' => $name ,
            'nik' => $nik ,
            'projectDate' => $projectDate ,
            'birth' => $birth ,
            'phone' => $phone ,
            'email' => $email ,
            'picture' => $picture ,
            'company_id' => $company ,
            'division_id' => $division ,
            'departement_id' => $departement ,
            'position_id' => $position
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
        $participant = Participant::find($id);
        $participant->name = $request->name;
        $participant->identity_number = $request->identity;
        $participant->project_date = $request->project_date;
        $participant->birth = $request->birth;
        $participant->phone = $request->phone;
        $participant->email = $request->email;
        if($request->hasFile('image')){
            $image_path = 'public/picture/'.$participant->picture;
            if(Storage::exists($image_path)){
                Storage::delete($image_path);
            }
            $file = $request->file('image');
            $extension = $file->getClientOriginalExtension();
            $filename = time().'.'.$extension;
            $path = 'public/picture/';
            $request->file('image')->storeAs($path,$filename);
            $participant->picture = $filename;
        }else{
            $participant->picture = $participant->picture;
        }
        $participant->company_id = $request->company;
        $participant->division_id = $request->division;
        $participant->departement_id = $request->departement;
        $participant->position_id = $request->position;
        $participant->save();

        return redirect('assessment/participants');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $participant = Participant::find($id);
        $participant->delete();

        $image_path = 'uploads/participants/'.$participant->picture;
        if(File::exists($image_path) && $participant->picture != 'no_foto.png' ){
            File::delete($image_path);
        }

        return redirect('assessment/participants');
    }

    public function participantData(){
        return DataTables::of(Participant::with('company' , 'departement' , 'division' , 'position')->get())
        ->addIndexColumn()
        ->addColumn('action', function($participant){
            $csrf = csrf_token();
            return '
            <button type="button" class="btn btn-sm btn-default detail" id="'.$participant->id.'">
                <i class="fas fa-plus"></i> Detail
            </button>';
        })
        ->make(true);
    }

    public function participantDetail($id){
        $participant = Participant::find($id);
        $path = resource_path() . '/uploads/participants'.$participant->picture;

        $html  = '<div class="row">
                    <div class="col-12">
                        <div class="card">
                        <div class="card-header">
                            <div class="row">
                            <div class="col-sm-4" style ="text-align:center">
                                <div class="card-title"> Detail Asesi </div>
                            </div>
                            <div class="col-sm-8" style="text-align: right">
                                <a href="/assessment/participants/'.$participant->id.'/edit" class="btn btn-sm btn-info">Ubah Asesi</a>

                            </div>
                        </div>
                        </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-sm-3" style ="text-align:center">
                                        <div class="card" style="width:200px ; margin: auto; margin-top: 10px;">
                                            <img src="'.url('/picture/'.$participant->picture.'').'")}}" alt="your image" style="width:200px;height:200px; object-fit: cover;">
                                        </div>
                                    </div>
                                    <div class="col-sm-9">
                                        <ul class="list-group list-group-flush">
                                            <li class="list-group-item"><label>Tanggal Lahir&nbsp;&nbsp;&nbsp;&nbsp; : &nbsp;</label>'.$participant->birth.'</li>
                                            <li class="list-group-item"><label>No. Telp&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp; : &nbsp;</label>'.$participant->phone.'</li>
                                            <li class="list-group-item"><label>Email&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&emsp;&emsp;&emsp; : &nbsp;</label>'.$participant->email.'</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>';
        return $html;
    }
}
