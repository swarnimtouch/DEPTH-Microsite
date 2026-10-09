<?php

namespace App\Http\Controllers\Admin;

use App\Models\Content;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        // Pagination
        return view('admin.content.index', [
            'title' => __('Contents'),
            'breadcrumb' => breadcrumb([
                __('Contents') => route('admin.content')
            ])
        ]);
    }


     /**
     * Display the specified resource.
     */
    public function show($slug)
    {
        $content = Content::where('slug', $slug)->firstOrFail();

        return view('admin.content.show', compact('content'));
    }


    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $content = Content::findOrFail($id);

        // 🔹 Edit page pe bhi same breadcrumb & title
        $response = [
            'content' => $content,
            'title' => __('Content'),
            'breadcrumb' => breadcrumb([__('Contents') => route('admin.content'), ($id ? 'Edit' : 'Add' . ' Content') => '']),
        ];
        return view('admin.content.edit', $response);



    }


    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'title'   => 'required|string|max:255',
            'slug'    => 'required|string|max:255|unique:contents,slug,' . $id,
            'content' => 'required|string',
        ]);

        $content = Content::findOrFail($id);

        $content->update($validated);

        return redirect()
            ->route('admin.content')
            ->with('success', 'Content updated successfully!');
    }

    public function datatable(Request $request)
    {
        $query = Content::query();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ;
            });
        }


        $total = $query->count();

        if ($request->has('order')) {
            $columns = $request->columns;
            foreach ($request->order as $order) {
                $columnIndex = $order['column'];
                $columnName = $columns[$columnIndex]['data'];
                $direction = $order['dir'];

                $dbColumn = match ($columnName) {
                    'title' => 'title',
                    'slug' => 'slug',
                    default => 'id'
                };

                $query->orderBy($dbColumn, $direction);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $length = $request->input('length', 10);
        $start = $request->input('start', 0);
        $contents = $query->skip($start)->take($length)->get();

        $data = $contents->map(function ($content) {
            return [
                'id' => $content->id,
                'title' => $content->title,
                'slug' => $content->slug,
                'content' => $content->content,
                'created_at' => $content->created_at->format('d M Y'),
                'actions' => '',
            ];
        });

        return response()->json([
            'draw' => $request->input('draw', 1),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $data
        ]);
    }
}
