<?php

namespace App\Http\Controllers\Db;

use App\Http\Controllers\Controller;
use App\Models\DB\Company;
use App\Models\DB\Participant;
use App\Models\DB\DbPosition;
use App\Models\DB\Departement;
use App\Models\DB\Division;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Facades\DataTables;

class ParticipantController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {

        return view('db_assessment.participant.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $companies = Company::orderBy('name' , 'ASC')->get();
        $positions = DBPosition::orderBy('name' , 'ASC')->get();
        $divisions = Division::orderBy('name' , 'ASC')->get();
        $departements = Departement::orderBy('name' , 'ASC')->get();
        return view('db_assessment.participant.create', compact('companies', 'positions', 'divisions', 'departements'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate(
            [
                'name' => 'required',
                'city' => 'required',
                'company_id' => 'required',
                'identity_number' => 'required | max:18,',
                'division' => 'required',
                'departement' => 'required',
                'position' => 'required',
            ],
            [
                'name.required' => 'Harap isi field Nama',
                'city.required' => 'Harap isi field Tempat Lahir',
                'company_id.required' => 'Harap pilih item pada field Nama Perusahaan',
                'identity_number.required' => 'Harap isi field NIK',
                'division.required' => 'Harap isi field Divisi',
                'departement.max' => 'Harap isi field Departemen',
                'position.required' => 'Harap isi field Jabatan',
                'identity_number.max' => 'Jumlah digit NIK melebihi dari 18 digit',
            ]
        );

        $participants = new Participant;
        $participants->name = $request->name;
        $participants->city = $request->city;
        $participants->birth = $request->birth;
        $participants->phone = $request->phone;
        $participants->email = $request->email;
        $participants->company_id = $request->company_id;
        $participants->identity_number = $request->identity_number;
        $participants->division_id = $request->division;
        $participants->departement_id = $request->departement;
        $participants->position_id = $request->position;
        $participants->save();

        return redirect('db-assessment/participants');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $participant = Participant::find($id);
        $companies = Company::all();
        $positions = DBPosition::all();
        $divisions = Division::all();
        $departements = Departement::all();
        return view('db_assessment.participant.show', compact('companies', 'participant', 'positions', 'divisions', 'departements'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $participant = Participant::find($id);
        $companies = Company::orderBy('name' , 'ASC')->get();
        $positions = DBPosition::orderBy('name' , 'ASC')->get();
        $divisions = Division::orderBy('name' , 'ASC')->get();
        $departements = Departement::orderBy('name' , 'ASC')->get();
        return view('db_assessment.participant.edit', compact('companies', 'participant', 'positions', 'divisions', 'departements'));
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
        $validatedData = $request->validate(
            [
                'name' => 'required',
                'city' => 'required',
                'birth' => 'required',
                'company_id' => 'required',
                'identity_number' => 'required | max:18,',
                'division' => 'required',
                'departement' => 'required',
                'position' => 'required',
            ],
            [
                'name.required' => 'Harap isi field Nama',
                'city.required' => 'Harap pilih field Tempat Lahir',
                'birth.required' => 'Harap isi field Tanggal Lahir',
                'company_id.required' => 'Harap pilih item pada field Nama Perusahaan',
                'identity_number.required' => 'Harap isi field NIK',
                'division.required' => 'Harap isi field Divisi',
                'departement.max' => 'Harap isi field Departemen',
                'position.required' => 'Harap isi field Jabatan',
                'identity_number.max' => 'Jumlah digit NIk melebihi dari 18 digit',
                'phone.max' => 'Jumlah digit melebihi dari 13 digit',
            ]
        );

        $participants = Participant::find($id);
        $participants->name = $request->name;
        $participants->city = $request->city;
        $participants->birth = $request->birth;
        $participants->phone = $request->phone;
        $participants->email = $request->email;
        $participants->company_id = $request->company_id;
        $participants->identity_number = $request->identity_number;
        $participants->division_id = $request->division;
        $participants->departement_id = $request->departement;
        $participants->position_id = $request->position;
        $participants->save();

        return redirect('db-assessment/participants');
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

        return redirect('db-assessment/participants');
    }

    public function participantData()
    {
        return Datatables::of(Participant::with('company')->get([
            env("DB_DATABASE2", "db_assessment") . '.participants_db.*'
        ]))
            ->addColumn('action', function ($participant) {
                $csrf = csrf_token();
                return '
            <a href="/db-assessment/participants/' . $participant->id . '/edit" class="btn btn-sm btn-default">
                <i class="far fa-edit"></i>Ubah
            </a>
            <a href="/db-assessment/participants/' . $participant->id . '" class="btn btn-sm btn-default">
                <i class="far fa-eye"></i>Lihat
            </a>
        ';
            })
            ->addIndexColumn()
            ->make(true);
    }
}
