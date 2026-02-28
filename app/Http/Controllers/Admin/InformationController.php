<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\FileHelper;
use App\Models\Information;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class InformationController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $query = Information::with('createdBy')->latest();

            if ($request->has('type') && in_array($request->type, ['banner', 'text'])) {
                $query->where('type', $request->type);
            }

            return DataTables::of($query)
                ->addIndexColumn()
                ->addColumn('link', function ($info) {
                    $prefix = $info->type === 'banner' ? 'banner' : 'info';
                    return '<a href="/'.$prefix.'/'.$info->slug.'" target="_blank"><i class="fa fa-external-link"></i> Lihat</a>';
                })
                ->addColumn('image', function ($info) {
                    return $info->image
                        ? '<img src="'.getFile($info->image).'" class="img-thumbnail" style="width:70px;height:50px;object-fit:cover;">'
                        : '<span class="text-muted">Tidak ada</span>';
                })
                ->editColumn('type', fn($info) => $info->type === 'banner'
                    ? '<span class="label label-info">Banner</span>'
                    : '<span class="label label-default">Text</span>')
                ->addColumn('created_by', fn($info) => $info->createdBy?->name ?? '-')
                ->editColumn('created_at', fn($info) => $info->created_at->translatedFormat('d M Y H:i'))
                ->addColumn('action', function ($info) {
                    $edit = auth()->user()->can('edit information')
                        ? '<a href="'.route('information.edit', $info->id).'" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>'
                        : '';

                    $delete = auth()->user()->can('delete information')
                        ? '<form action="'.route('information.destroy', $info->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Yakin hapus?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>'
                        : '';

                    return '<div class="text-center">'.$edit.' '.$delete.'</div>';
                })
                ->rawColumns(['link', 'image', 'type', 'action'])
                ->make(true);
        }

        return view('pages.admin.information.index')->with('page', 'Informasi');
    }

    public function create()
    {
        return view('pages.admin.information.create')->with('page', 'Informasi');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => [
                'nullable',
                'image',
                'max:4096',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'banner' && $request->hasFile('image')) {
                        $image = $request->file('image');
                        if ($image->isValid()) {
                            [$width, $height] = getimagesize($image->path());
                            if ($width <= $height) {
                                $fail('Banner harus berukuran landscape (lebar lebih besar dari tinggi).');
                            }
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'type' => 'required|string|in:banner,text',
        ]);

        $informationData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'type' => $request->type,
            'description' => $request->description,
            'created_by' => auth()->user()->id,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $informationData['image'] = FileHelper::saveFile($request->file('image'), 'banners', 'image');
        }

        Information::create($informationData);

        return redirect()->route('information.index')->with('success', 'Information berhasil dibuat');
    }

    public function edit($id)
    {
        $information = Information::find($id);
        return view('pages.admin.information.edit', compact('information'))->with('page', 'Informasi');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'image' => [
                'nullable',
                'image',
                'max:4096',
                function ($attribute, $value, $fail) use ($request) {
                    if ($request->type === 'banner' && $request->hasFile('image')) {
                        $image = $request->file('image');
                        if ($image->isValid()) {
                            [$width, $height] = getimagesize($image->path());
                            if ($width <= $height) {
                                $fail('Banner harus berukuran landscape (lebar lebih besar dari tinggi).');
                            }
                        }
                    }
                },
            ],
            'description' => 'nullable|string',
            'type' => 'required|string|in:banner,text',
        ]);

        $information = Information::find($id);

        $informationData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'type' => $request->type,
            'description' => $request->description,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($information->image) {
                FileHelper::deleteFile($information->image);
            }
            $informationData['image'] = FileHelper::saveFile($request->file('image'), 'banners', 'image');
        }

        $information->update($informationData);

        return redirect()->route('information.index')->with('success', 'Information berhasil diperbarui');
    }

    public function destroy($id)
    {
        $information = Information::find($id);
        if ($information->image) {
            FileHelper::deleteFile($information->image);
        }

        $information->delete();

        return redirect()->route('information.index')->with('success', 'Information deleted successfully');
    }
}
