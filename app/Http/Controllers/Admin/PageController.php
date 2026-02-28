<?php

namespace App\Http\Controllers\Admin;

use App\Models\Page;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class PageController extends Controller
{
    # Display a listing of pages
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $pages = Page::with('creator')->latest();

            return DataTables::of($pages)
                ->addIndexColumn()
                ->addColumn('link', fn($page) => '<a href="/page/'.$page->slug.'" target="_blank"><i class="fa fa-external-link"></i> Lihat</a>')
                ->editColumn('status', fn($page) => $page->status === 'active' 
                    ? '<span class="label label-success">Active</span>' 
                    : '<span class="label label-warning">Inactive</span>')
                ->addColumn('creator', fn($page) => $page->creator?->name ?? '-')
                ->addColumn('action', function ($page) {
                    $edit = auth()->user()->can('edit pages')
                        ? '<a href="'.route('pages.edit', $page->id).'" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>'
                        : '';

                    $delete = auth()->user()->can('delete pages')
                        ? '<form action="'.route('pages.destroy', $page->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Yakin hapus?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>'
                        : '';

                    return '<div class="text-center">'.$edit.' '.$delete.'</div>';
                })
                ->rawColumns(['link', 'status', 'action'])
                ->make(true);
        }

        return view('pages.admin.page.index')->with('page', 'Halaman');
    }

    # Show the form for creating a new page
    public function create()
    {
        return view('pages.admin.page.create')->with('page', 'Halaman');
    }

    # Store a newly created page in storage
    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        $data['slug'] = slugVerified($request->title);
        $data['created_by'] = auth()->id(); 

        Page::create($data);

        return redirect()->route('pages.index')->with('success', 'Halaman berhasil ditambahkan.');
    }

    # Show the form for editing the specified page
    public function edit($page)
    {
        $edit = Page::find($page);
        return view('pages.admin.page.edit', compact('edit'))->with('page', 'Halaman');
    }

    # Update the specified page in storage
    public function update(Request $request, Page $page)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $data = $request->all();
        // $data['slug'] = slugVerified($request->title); 

        $page->update($data);

        return redirect()->route('pages.index')->with('success', 'Halaman berhasil diperbarui.');
    }

    # Remove the specified page from storage
    public function destroy(Page $page)
    {
        $page->delete();
        return redirect()->route('pages.index')->with('success', 'Halaman berhasil dihapus.');
    }
}