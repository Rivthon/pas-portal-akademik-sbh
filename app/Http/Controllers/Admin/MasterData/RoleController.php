<?php

namespace App\Http\Controllers\Admin\MasterData;

use App\Http\Controllers\Controller;
use App\Support\PermissionCatalog;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function __construct()
    {
        $this->middleware('permission:role-list|role-create|role-edit|role-delete', ['only' => ['index', 'show']]);
        $this->middleware('permission:role-create', ['only' => ['create', 'store']]);
        $this->middleware('permission:role-edit', ['only' => ['edit', 'update']]);
        $this->middleware('permission:role-delete', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request): View
    {
        $roles = Role::withCount('permissions')->orderByDesc('id')->paginate(10);

        return view('admin.master-data.roles.index', compact('roles'))
            ->with('i', ($request->input('page', 1) - 1) * 5);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create(): View
    {
        $permissionGroups = PermissionCatalog::groups(
            Permission::where('guard_name', 'web')->orderBy('name')->get()
        );
        $selectedPermissionIds = array_map('intval', old('permission', []));

        return view('admin.master-data.roles.create', compact('permissionGroups', 'selectedPermissionIds'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @return Response
     */
    public function store(Request $request): RedirectResponse
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name'],
            'permission' => ['nullable', 'array'],
            'permission.*' => ['integer', 'exists:permissions,id'],
        ]);

        $permissionsID = array_map(
            function ($value) {
                return (int) $value;
            },
            $request->input('permission', [])
        );

        $role = Role::create(['name' => $request->input('name')]);
        $role->syncPermissions($permissionsID);

        activity_log('tambah_role', 'Admin menambah role baru: '.$role->name);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role created successfully');
    }
    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id): View
    {
        $role = Role::findOrFail($id);
        $permissionGroups = PermissionCatalog::groups(
            Permission::where('guard_name', $role->guard_name)->orderBy('name')->get()
        );
        $selectedPermissionIds = array_map(
            'intval',
            old('permission', $role->permissions->pluck('id')->all())
        );

        return view('admin.master-data.roles.edit', compact('role', 'permissionGroups', 'selectedPermissionIds'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update(Request $request, $id): RedirectResponse
    {
        $this->validate($request, [
            'name' => ['required', 'string', 'max:255', 'unique:roles,name,'.$id],
            'permission' => ['nullable', 'array'],
            'permission.*' => ['integer', 'exists:permissions,id'],
        ]);

        $role = Role::findOrFail($id);
        $role->name = $request->input('name');
        $role->save();

        $permissionsID = array_map(
            function ($value) {
                return (int) $value;
            },
            $request->input('permission', [])
        );

        $role->syncPermissions($permissionsID);

        activity_log('update_role', 'Admin memperbarui role: '.$role->name);

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role updated successfully');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function destroy($id): RedirectResponse
    {
        activity_log('hapus_role', 'Admin menghapus role ID: '.$id);
        Role::findOrFail($id)->delete();

        return redirect()->route('admin.roles.index')
            ->with('success', 'Role deleted successfully');
    }
}
