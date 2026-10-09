<?php

namespace App\Http\Controllers\Admin;

use App\Models\Country;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class DoctorController
{
    public function index()
    {
        return view('admin.doctors.index', ['title' => __('Doctors'), 'breadcrumb' => breadcrumb([__('Doctors') => route('admin.doctors')])]);
    }

    public function datatable(Request $request)
    {
        $query = User::where('type', 'doctor');
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
                $q->orWhere('email', 'LIKE', "%$search%");
                $q->orWhere('mobile', 'LIKE', "%$search%");
                $q->orWhere('country', 'LIKE', "%$search%");
                $q->orWhere('hospital', 'LIKE', "%$search%");
            });
        }
        if ($request->has('order')) {
            $columns = $request->columns;
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $columns[$columnIndex]['data'];
                $direction = $order['dir'];

                $dbColumn = match ($columnName) {
                    'name' => 'name',
                    'mobile' => 'mobile',
                    'email' => 'email',
                    'country' => 'country',
                    'hospital' => 'hospital',
                    default => 'id'
                };

                $query->orderBy($dbColumn, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }
        $recordsTotal = $query->count();
        $data = $query
            ->skip($request->start)
            ->take($request->length)
            ->get();
        return response()->json([
            "draw" => intval($request->draw),
            "recordsTotal" => $recordsTotal,
            "recordsFiltered" => $recordsTotal,
            "data" => $data
        ]);
    }

    public function export(Request $request): StreamedResponse
    {
        $doctors = User::where('type', 'doctor')
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%$search%");
                    $q->orWhere('email', 'LIKE', "%$search%");
                    $q->orWhere('mobile', 'LIKE', "%$search%");
                    $q->orWhere('country', 'LIKE', "%$search%");
                    $q->orWhere('hospital', 'LIKE', "%$search%");
                });
            })
            ->orderBy('name')
            ->get();

        return response()->streamDownload(function () use ($doctors) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, [
                'No.',
                'Name',
                'Hospital',
                'Country',
                'Mobile Number',
                'Email Id',
                'Last Login',
                'Created At',
            ]);

            foreach ($doctors as $index => $doctor) {
                $lastLoginAt = $doctor->last_login_at
                    ? '="' . $doctor->last_login_at->format('d-m-Y h:i A') . '"'
                    : '="' . $doctor->updated_at->format('d-m-Y h:i A') . '"';
                $createdAt = $doctor->created_at
                    ? '="' . $doctor->created_at->format('d-m-Y h:i A') . '"'
                    : '';

                fputcsv($handle, [
                    $index + 1,
                    $doctor->name,
                    $doctor->hospital,
                    $doctor->country,
                    $doctor->mobile,
                    $doctor->email,
                    $lastLoginAt,
                    $createdAt,
                ]);
            }

            fclose($handle);
        }, 'doctors-' . now()->format('Y-m-d-His') . '.csv', [
            'Content-Type' => 'text/csv',
        ]);
    }

    public function addEditForm($id = null)
    {
        $doctor = $id ? User::findOrFail($id) : new User();

        $response = [
            'doctor' => $doctor,
            'country' => Country::orderBy('name')->get(),
            'title' => __('Doctors'),
            'breadcrumb' => breadcrumb([__('Doctors') => route('admin.doctors'), ($id ? 'Edit' : 'Add' . ' Doctor') => '']),
        ];
        return view('admin.doctors.add_edit', $response);
    }

    public function save(Request $request, $id = null)
    {
        $doctor = $id ? User::findOrFail($id) : new User();
        $request->validate([
            'name' => ['required'],
            'email' => ['required', Rule::unique('users', 'email')->ignore($id)],
            'mobile' => ['required', Rule::unique('users', 'mobile')->ignore($id)],
            'country' => ['required'],
            'hospital' => ['required'],
        ]);
        $doctor->type = 'doctor';
        $doctor->name = $request->name ?? null;
        $doctor->email = $request->email ?? null;
        $doctor->mobile = $request->mobile ?? null;
        $doctor->country = $request->country ?? null;
        $doctor->hospital = $request->hospital ?? null;
        if ($request->profile_image_remove == 1) {
            if ($doctor->profile_image && Storage::disk('public')->exists($doctor->profile_image)) {
                Storage::disk('public')->delete($doctor->profile_image);
            }
            $doctor->profile_image = null;
        }
        if ($request->hasFile('profile_image')) {
            $doctor->profile_image = $request->file('profile_image')->store('uploads/doctors', 'public');
        }
        $doctor->save();
        return redirect()->route('admin.doctors')
            ->with('success', 'Doctor Saved Successfully');
    }

    public function statusChange($id)
    {
        try {
            $doctor = User::findOrFail($id);

            $doctor->status = $doctor->status == 'active' ? 'inactive' : 'active';
            $doctor->save();

            return response()->json([
                'success' => true,
                'message' => 'Status updated successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error updating status'
            ], 500);
        }
    }

    public function delete($id)
    {
        try {
            $doctor = User::findOrFail($id);
            $doctor->delete();
            return response()->json([
                'success' => true,
                'message' => 'Doctor deleted successfully'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => "You can't delete this record"
            ], 500);
        }
    }

    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids', []);

            if (empty($ids)) {
                return response()->json(['success' => false, 'message' => 'No data selected'], 400);
            }
            User::whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'message' => 'Doctor deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting Doctor'], 500);
        }
    }
}
