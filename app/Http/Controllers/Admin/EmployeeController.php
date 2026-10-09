<?php

namespace App\Http\Controllers\Admin;

use App\Models\Area;
use App\Models\Division;
use App\Models\Headquarter;
use App\Models\Region;
use App\Models\Speciality;
use App\Models\User;
use App\Models\Zone;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EmployeeController
{
    public function index()
    {
        return view('admin.employee.index', ['title' => __('Employee'), 'breadcrumb' => breadcrumb([__('Employee') => route('admin.employee')])]);
    }

    public function datatable(Request $request)
    {
        $query = User::where('type', 'employee');
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
                $q->orWhere('email', 'LIKE', "%$search%");
                $q->orWhere('emp_id', 'LIKE', "%$search%");
                $q->orWhereHas('division', function ($sq) use ($search) {
                    $sq->where('name', 'LIKE', "%$search%");
                });
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
                    'emp_id' => 'emp_id',
                    'email' => 'email',
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

    public function addEditForm($id = null)
    {
        $employee = $id ? User::findOrFail($id) : new User();
        $response = [
            'employee' => $employee,
            'zones' => Zone::Active()->get(),
            'headquarters' => ($employee)?Headquarter::Active()->where('zone_id',$employee->zone_id)->get():[],
            'regions' => ($employee)?Region::Active()->where('headquarter_id',$employee->headquarter_id)->get():[],
            'areas' => ($employee)?Area::Active()->where('region_id',$employee->region_id)->get():[],
            'divisions' => Division::Active()->get(),
            'title' => __('Employee'),
            'breadcrumb' => breadcrumb([__('Employee') => route('admin.employee'), ($id ? 'Edit' : 'Add' . ' Employee') => '']),
        ];
        return view('admin.employee.add_edit', $response);
    }

    public function save(Request $request, $id = null)
    {
        $employee = $id ? User::findOrFail($id) : new User();
        $request->validate([
            'zone_id' => ['required', Rule::exists('zones', 'id')],
            'headquarter_id' => ['required', Rule::exists('headquarters', 'id')],
            'region_id' => ['required', Rule::exists('regions', 'id')],
            'area_id' => ['required', Rule::exists('areas', 'id')],
            'division_id' => ['required', Rule::exists('divisions', 'id')],
            'emp_id' => ['required', Rule::unique('users', 'emp_id')->ignore($id)],
            'emp_chair_id' => ['required'],
            'name' => ['required'],
            'email' => ['required', Rule::unique('users', 'email')->ignore($id)],
        ]);
        $employee->type = 'employee';
        $employee->zone_id = $request->zone_id ?? null;
        $employee->headquarter_id = $request->headquarter_id ?? null;
        $employee->region_id = $request->region_id ?? null;
        $employee->area_id = $request->area_id ?? null;
        $employee->division_id = $request->division_id ?? null;
        $employee->name = $request->name ?? null;
        $employee->email = $request->email ?? null;
        $employee->emp_id = $request->emp_id ?? null;
        $employee->emp_chair_id = $request->emp_chair_id ?? null;

        $employee->save();

        return redirect()->route('admin.employee')
            ->with('success', 'Employee Saved Successfully');
    }

    public function statusChange($id)
    {
        try {
            $employee = User::findOrFail($id);

            $employee->status = $employee->status == 'active' ? 'inactive' : 'active';
            $employee->save();

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
            $employee = User::findOrFail($id);
            $employee->delete();
            return response()->json([
                'success' => true,
                'message' => 'Employee deleted successfully'
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

            return response()->json(['success' => true, 'message' => 'Employee deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting Employee'], 500);
        }
    }
}
