<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Assessment\Company;
use Yajra\DataTables\Facades\DataTables;
use DB;
use App\Models\Assessment\BusinessField;

class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.company.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $business = BusinessField::orderBy('name', 'ASC')->get();

        return view('assessment.company.create' , compact('business'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            'namaPerusahaan' => 'required|unique:companies,name',
            'bidangIndustri' => 'required',
            'alamatPerusahaan' => 'required',
            'kotaPerusahaan' => 'required',
        ], [
            'namaPerusahaan.unique' => 'Nama Perusahaan sudah ada',
            'namaPerusahan.required' => 'Nama Perusahan wajib diisi',
            'bidangIndustri.required' => 'Bidang Usaha wajib diisi',
            'alamatPerusahaan.required' => 'Alamat Perusahaan wajib diisi',
            'kotaPerusahaan' => 'Kota Perusahaan wajib diisi'
        ]);

        $company = Company::create([
            'name' => $request->namaPerusahaan,
            'business_field_id' => $request->bidangIndustri,
            'address' => $request->alamatPerusahaan,
            'city' => $request->kotaPerusahaan,
            'phone' => $request->noTelpPerusahaan,
            'pic_name' => $request->namaPIC,
            'pic_phone' => $request->noTelpPIC,
            'pic_mail' => $request->emailPIC
        ]);

        return redirect('assessment/companies');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // $company = Company::find($id);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $company = Company::find($id);
        $business = BusinessField::all();

        return view('assessment.company.edit',compact('company','business'));
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
        $validatedData = $request->validate([
            'namaPerusahaan' => 'required|unique:companies,name,'.$id,
            'bidangIndustri' => 'required',
            'alamatPerusahaan' => 'required',
            'kotaPerusahaan' => 'required',
        ], [
            'namaPerusahaan.unique' => 'Nama Perusahaan sudah ada',
            'namaPerusahan.required' => 'Nama Perusahan wajib diisi',
            'bidangIndustri.required' => 'Bidang Usaha wajib diisi',
            'alamatPerusahaan.required' => 'Alamat Perusahaan wajib diisi',
            'kotaPerusahaan' => 'Kota Perusahaan wajib diisi'
        ]);

        $company = Company::where('id',$id)->update([
            'name' => $request->namaPerusahaan,
            'business_field_id' => $request->bidangIndustri,
            'address' => $request->alamatPerusahaan,
            'city' => $request->kotaPerusahaan,
            'phone' => $request->noTelpPerusahaan,
            'pic_name' => $request->namaPIC,
            'pic_phone' => $request->noTelpPIC,
            'pic_mail' => $request->emailPIC
        ]);

        return redirect('assessment/companies');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $company = Company::destroy($id);

        return redirect('assessment/companies');
    }

    public function companyData(){
        return DataTables::of(Company::with('businessField')->get())
        ->addIndexColumn()
        ->addColumn('action', function($company){
            $csrf = csrf_token();
            return '
                <a href="" class="btn btn-sm btn-default show" id="'.$company->id.'" data-toggle="modal" data-target="#show-modal" data-nama="'.$company->name.'" data-bf="'.$company->businessField->name.'" data-address="'.$company->address.'" data-city="'.$company->city.'" data-phone="'.$company->phone.'" data-picname="'.$company->pic_name.'" data-picphone="'.$company->pic_phone.'" data-picmail="'.$company->pic_mail.'">
                    <i class="fas fa-eye"></i> Lihat
                </a>

                <a href="/assessment/companies/'.$company->id.'/edit" class="btn btn-sm btn-default">
                    <i class="far fa-edit"></i>Ubah
                </a>

            ';
        })
        ->make(true);
    }
}
