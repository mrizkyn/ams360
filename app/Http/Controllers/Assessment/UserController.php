<?php

namespace App\Http\Controllers\Assessment;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
use DB;

use App\User;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        return view('assessment.users.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('assessment.users.create');
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
            'email' => 'required|unique:users' ,
            'password' => 'required|min:8' ,
            'confirm_password' => 'required|same:password' ,
            'role' => 'required'
        ],
        [
            'name.required' => 'Harap isi field nama' ,
            'email.required' => 'Harap isi field email' ,
            'password.required' => 'Harap isi field password' ,
            'password.min' => 'Harap isi field password dengan 8 karakter' ,
            'confirm_password.required' => 'Harap isi field confirm password' ,
            'confirm_password.same' => 'Password konfirmasi tidak cocok',
            'role.required' => 'Harap pilih role user'
        ]);

        $email = User::where('email' , $request->email)->get();
        $user = new User;
        $user->name = $request->name ;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->role = $request->role;
        $user->save();

        return redirect('assessment/users');
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
        $user = User::find($id);

        if($request->flag == "reset"){
            if($request->pass == $request->pass_conf){
                $user->password = bcrypt($request->pass);
                $user->save();
            }
            return redirect('assessment/users')->with('success-reset' , $user->email);
        }
        $user->name = $request->name;
        $user->email = $request->email;
        $user->role = $request->role;
        $user->save();

        return redirect('assessment/users');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user = User::find($id);
        $user->delete();
        return redirect('assessment/users');
    }

    public function usersData(){
        return DataTables::of(User::get())
        ->addIndexColumn()
        ->addColumn('action', function($user){
            $csrf = csrf_token();
            return '
            <button type="button" class="btn btn-sm btn-default ubah" id="'.$user->id.'">
                <i class="far fa-edit"></i>Ubah
            </button>
            <button type="button" class="btn btn-sm btn-default ubah-pass" id="'.$user->id.'">
                <i class="fas fa-user-cog"></i> Ubah Password
            </button>
            </a>
            <button type="button" class="btn btn-sm btn-default hapus" id="'.$user->id.'">
                <i class="fas fa-trash"></i> Hapus
            </button>
            ';
        })
        ->make(true);
    }

    public function userDetail($id){
        $user = User::find($id);

        return $user;
    }
}
