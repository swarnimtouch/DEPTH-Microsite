<?php

namespace App\Http\Controllers\Admin;

use App\Models\Speciality;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SpecialitiesController
{
    public function index()
    {
        return view('admin.specialities.index', ['title' => __('Specialities'), 'breadcrumb' => breadcrumb([__('Specialities') => route('admin.specialities')])]);
    }

    public function datatable(Request $request)
    {
        $query = Speciality::query();
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'LIKE', "%$search%");
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
        $speciality = $id ? Speciality::findOrFail($id) : new Speciality();
        $response = [
            'speciality' => $speciality,
            'title' => __('Speciality'),
            'breadcrumb' => breadcrumb([__('Specialities') => route('admin.specialities'), ($id ? 'Edit' : 'Add' . ' Speciality') => '']),
        ];
        return view('admin.specialities.add_edit', $response);
    }

    public function save(Request $request, $id = null)
    {
        $speciality = $id ? Speciality::findOrFail($id) : new Speciality();
        $request->validate([
            'name' => ['required'],
            'image' => 'file|max:2048'
        ]);
        $speciality->name = $request->name ?? null;
        if($request->hasFile('image')) {
            $speciality->image = $request->file('image')->store('uploads/specialities', 'public');
        }
        $speciality->save();

        return redirect()->route('admin.specialities')
            ->with('success', 'Speciality Saved Successfully');
    }

    public function statusChange($id)
    {
        try {
            $speciality = Speciality::findOrFail($id);

            $speciality->status = $speciality->status == 'active' ? 'inactive' : 'active';
            $speciality->save();

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
            $speciality = Speciality::findOrFail($id);
            $speciality->delete();
            return response()->json([
                'success' => true,
                'message' => 'Speciality deleted successfully'
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
            Speciality::whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'message' => 'Speciality deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting speciality'], 500);
        }
    }
}
