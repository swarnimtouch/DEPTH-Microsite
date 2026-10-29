<?php

namespace App\Http\Controllers\Admin;

use App\Models\Comments;
use App\Models\Module;
use App\Support\ExcelExport;
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
                    ->orWhere('email', 'LIKE', "%$search%")
                    ->orWhere('country', 'LIKE', "%$search%"));
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

    public function export(Request $request)
    {
        $comments = Comments::with(['module', 'user'])
            ->when($request->module_id, fn($query, $moduleId) => $query->where('episode_id', $moduleId))
            ->when($request->status, fn($query, $status) => $query->where('status', $status))
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('comment', 'LIKE', "%$search%")
                        ->orWhereHas('module', fn($module) => $module->where('title', 'LIKE', "%$search%"))
                        ->orWhereHas('user', fn($user) => $user
                            ->where('name', 'LIKE', "%$search%")
                            ->orWhere('email', 'LIKE', "%$search%")
                            ->orWhere('country', 'LIKE', "%$search%"));
                });
            })
            ->latest()
            ->get();

        $rows = $comments->map(fn($comment, $index) => [
            $index + 1,
            $comment->module?->title ?: '-',
            $comment->user?->name ?: 'Delegate',
            $comment->user?->email ?: '-',
            $comment->user?->country ?: '-',
            $comment->comment,
            $comment->created_at?->format('d M Y, h:i A') ?: '-',
            ucfirst($comment->status),
        ]);

        return ExcelExport::download(
            'comments-' . now()->format('Y-m-d-His'),
            ['No.', 'Chapter', 'User', 'Email', 'Country', 'Comment', 'Posted At', 'Status'],
            $rows
        );
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
