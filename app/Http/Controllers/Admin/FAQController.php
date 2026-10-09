<?php

namespace App\Http\Controllers\Admin;

use App\Models\FAQ;
use App\Models\User;
use App\Models\Webinars;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class FAQController
{
    public function index()
    {
        return view('admin.faqs.index', ['title' => __('FAQ'), 'breadcrumb' => breadcrumb([__('FAQ') => route('admin.faqs')])]);
    }

    public function datatable(Request $request)
    {
        $query = FAQ::query();
        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('que', 'LIKE', "%$search%");
            });
        }
        if ($request->has('order')) {
            $columns = $request->columns;
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $columns[$columnIndex]['data'];
                $direction = $order['dir'];

                $dbColumn = match ($columnName) {
                    'que' => 'que',
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
        $faq = $id ? FAQ::findOrFail($id) : new FAQ();

        $response = [
            'faq' => $faq,
            'title' => __('FAQ'),
            'breadcrumb' => breadcrumb([__('FAQ') => route('admin.faqs'), ($id ? 'Edit' : 'Add' . ' FAQ') => '']),
        ];
        return view('admin.faqs.add_edit', $response);
    }

    public function save(Request $request, $id = null)
    {
        $faq = $id ? FAQ::findOrFail($id) : new FAQ();
        $request->validate([
            'que' => ['required'],
            'ans' => ['required']
        ]);
        $faq->que = $request->que ?? null;
        $faq->ans = $request->ans ?? null;
        $faq->save();

        return redirect()->route('admin.faqs')
            ->with('success', 'FAQ Saved Successfully');
    }

    public function statusChange($id)
    {
        try {
            $faq = FAQ::findOrFail($id);

            $faq->status = $faq->status == 'active' ? 'inactive' : 'active';
            $faq->save();

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
            $faq = FAQ::findOrFail($id);
            $faq->delete();
            return response()->json([
                'success' => true,
                'message' => 'FAQ deleted successfully'
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
            FAQ::whereIn('id', $ids)->delete();

            return response()->json(['success' => true, 'message' => 'FAQ deleted successfully']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error deleting FAQ'], 500);
        }
    }
}
