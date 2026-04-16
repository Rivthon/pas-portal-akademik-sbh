<?php

namespace App\Http\Controllers\Admin\MasterData;

use DB;
use Hash;
use App\Models\User;
use Illuminate\View\View;
use Illuminate\Support\Arr;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use RealRashid\SweetAlert\Facades\Alert;

class UserController extends Controller
{
     function __construct()
    {
        $this->middleware('permission:users-list|users-create|users-edit|users-delete', ['only' => ['index']]);
        $this->middleware('permission:users-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:users-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:users-delete', ['only' => ['destroy']]);
    }
    public function index(Request $request): View
    {
        $data = User::latest()->paginate(5);
        $roles = Role::pluck('name','name')->all();
        return view('admin.master-data.users.index',compact('data','roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(): View
    {
        $roles = Role::pluck('name','name')->all();
        return view('admin.master-data.users.create',compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        $input['password'] = Hash::make($input['password']);

        $user = User::create($input);
        $user->assignRole($request->input('roles'));
        Alert::success('Berhasil', 'Data pengguna berhasil dibuat');
        return redirect()->route('admin.users.index')
                        ->with('success','User created successfully');
    }


    public function edit($id): View
    {
        // Mencari user berdasarkan ID
        $user = User::find($id);

        // Jika user tidak ditemukan, redirect ke daftar pengguna dengan pesan error
        if (!$user) {
            return redirect()->route('admin.users.index')->with('error', 'User tidak ditemukan');
        }

        // Mendapatkan daftar role
        $roles = Role::pluck('name', 'name')->all();

        // Mendapatkan role yang terkait dengan user
        $userRoles = $user->roles->pluck('name', 'name')->all();

        // Menampilkan halaman edit dengan data user dan roles
        return view('admin.master-data.users.edit', compact('user', 'roles', 'userRoles'));
    }


    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email|unique:users,email,'.$id,
            'password' => 'same:confirm-password',
            'roles' => 'required'
        ]);

        $input = $request->all();
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        }else{
            $input = Arr::except($input, array('password'));
        }

        $user = User::find($id);
        $user->update($input);
        DB::table('model_has_roles')->where('model_id',$id)->delete();

        $user->assignRole($request->input('roles'));
        Alert::success('Berhasil', 'Data pengguna berhasil diperbarui');
        return redirect()->route('admin.users.index')

                        ->with('success','User updated successfully');
    }

    public function destroy($id): RedirectResponse
    {
        User::find($id)->delete();
        return redirect()->route('admin.users.index')
                        ->with('success','User deleted successfully');
    }
}