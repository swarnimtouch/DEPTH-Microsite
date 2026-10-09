<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comments;
use App\Models\Module;
use Illuminate\Http\Request;

class CommentController
{
    public function index()
    {
        return view('admin.comments.index', [
            'title' => __('Comments'),
            'modules' => Module::orderBy('sort_order')->orderBy('title')->get(),
            'breadcrumb' => breadcrumb([__('Comments') => route('admin.comments')]),
        ]);
    }

    public function datatable(Request $request)
    {
        $query = Comments::with(['module', 'user']);

        if ($request->module_id) {
            $query->where('episode_id', $request->module_id);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('comment', 'LIKE', "%$search%");
                $q->orWhereHas('module', fn($module) => $module->where('title', 'LIKE', "%$search%"));
                $q->orWhereHas('user', fn($user) => $user
                    ->where('name', 'LIKE', "%$search%")
                    ->orWhere('email', 'LIKE', "%$search%"));
            });
        }

        $recordsTotal = $query->count();
        $data = $query->latest()
            ->skip($request->start)
            ->take($request->length)
            ->get();

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        ]);
    }

    public function statusChange($id)
    {
        $comment = Comments::findOrFail($id);
        $comment->status = $comment->status === 'publish' ? 'unpublish' : 'publish';
        $comment->save();

        return response()->json([
            'success' => true,
            'status' => $comment->status,
        ]);
    }

    public function delete($id)
    {
        Comments::findOrFail($id)->delete();

        return response()->json(['success' => true]);
    }
}
