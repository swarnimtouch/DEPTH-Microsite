<?php

namespace App\Http\Controllers\Admin;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SubAdminController extends Controller
{
    public function index()
    {
        return view('admin.sub_admins.index', [
            'title' => __('Sub Admins'),
            'subAdmins' => User::where('type', 'employee')->where('role', 'sub_admin')->latest()->get(),
            'breadcrumb' => breadcrumb([__('Sub Admins') => route('admin.sub_admins')]),
        ]);
    }

    public function form(?int $id = null)
    {
        $subAdmin = $id
            ? User::where('type', 'employee')->where('role', 'sub_admin')->findOrFail($id)
            : new User(['status' => 'active']);

        return view('admin.sub_admins.form', [
            'title' => $id ? __('Edit Sub Admin') : __('Add Sub Admin'),
            'subAdmin' => $subAdmin,
            'breadcrumb' => breadcrumb([
                __('Sub Admins') => route('admin.sub_admins'),
                ($id ? __('Edit') : __('Add')) => '',
            ]),
        ]);
    }

    public function save(Request $request, ?int $id = null)
    {
        $subAdmin = $id
            ? User::where('type', 'employee')->where('role', 'sub_admin')->findOrFail($id)
            : new User();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($id)],
            'password' => [$id ? 'nullable' : 'required', 'confirmed', 'min:8'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        $subAdmin->fill([
            'type' => 'employee',
            'role' => 'sub_admin',
            'name' => $validated['name'],
            'email' => $validated['email'],
            'username' => $subAdmin->username ?: $validated['email'],
            'status' => $validated['status'],
        ]);

        if (!empty($validated['password'])) {
            $subAdmin->password = $validated['password'];
        }

        $subAdmin->save();

        return redirect()->route('admin.sub_admins')->with('success', 'Sub admin saved successfully.');
    }

    public function delete(int $id)
    {
        User::where('type', 'employee')->where('role', 'sub_admin')->findOrFail($id)->delete();

        return redirect()->route('admin.sub_admins')->with('success', 'Sub admin deleted successfully.');
    }
}
