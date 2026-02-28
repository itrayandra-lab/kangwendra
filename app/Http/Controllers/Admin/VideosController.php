<?php

namespace App\Http\Controllers\Admin;

use App\Models\Video;
use App\Helpers\FileHelper;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

class VideosController extends Controller
{
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $videos = Video::with('createdBy')->latest();

            return DataTables::of($videos)
                ->addIndexColumn()
                ->addColumn('link', fn($video) => '<a href="/video/'.$video->slug.'" target="_blank"><i class="fa fa-external-link"></i> Lihat</a>')
                ->addColumn('image', function ($video) {
                    return $video->image
                        ? '<img src="'.getFile($video->image).'" class="img-thumbnail" style="width:70px;height:50px;object-fit:cover;">'
                        : '<span class="text-muted">Tidak ada</span>';
                })
                ->addColumn('created_by', fn($video) => $video->createdBy?->name ?? '-')
                ->editColumn('created_at', fn($video) => $video->created_at->translatedFormat('d M Y H:i'))
                ->addColumn('action', function ($video) {
                    $edit = auth()->user()->can('edit video')
                        ? '<a href="'.route('video.edit', $video->id).'" class="btn btn-primary btn-xs"><i class="fa fa-edit"></i></a>'
                        : '';

                    $delete = auth()->user()->can('delete video')
                        ? '<form action="'.route('video.destroy', $video->id).'" method="POST" style="display:inline" onsubmit="return confirm(\'Yakin hapus?\')">
                                '.csrf_field().method_field('DELETE').'
                                <button type="submit" class="btn btn-danger btn-xs"><i class="fa fa-trash"></i></button>
                        </form>'
                        : '';

                    return '<div class="text-center">'.$edit.' '.$delete.'</div>';
                })
                ->rawColumns(['link', 'image', 'action'])
                ->make(true);
        }

        return view('pages.admin.video.index')->with('page', 'Video');
    }

    public function create()
    {
        return view('pages.admin.video.create')->with('page', 'Video');
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link_yt' => 'required|string|max:255',
            'image' => 'nullable|image|max:4096',
            'description' => 'nullable|string',
        ]);

        $videoData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'link_yt' => $request->link_yt,
            'description' => $request->description,
            'created_by' => auth()->user()->id,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            $videoData['image'] = FileHelper::saveFile($request->file('image'), 'videos', 'image');
        } else {
            $videoData['image'] = 'default.jpg';
        }

        Video::create($videoData);

        return redirect()->route('video.index')->with('success', 'Video berhasil dibuat');
    }

    public function edit($id)
    {
        $video = Video::find($id);
        return view('pages.admin.video.edit', compact('video'))->with('page', 'Video');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'link_yt' => 'required|string|max:255',
            'image' => 'nullable|image|max:4096',
            'description' => 'nullable|string',
        ]);

        $video = Video::find($id);
        
        $videoData = [
            'title' => $request->title,
            'slug' => Str::slug($request->title),
            'link_yt' => $request->link_yt,
            'description' => $request->description,
        ];

        if ($request->hasFile('image') && $request->file('image')->isValid()) {
            if ($video->image && $video->image !== 'default.jpg') {
                Storage::disk('public')->delete('videos/' . $video->image);
                FileHelper::deleteFile($video->image);
            }
            $videoData['image'] = FileHelper::saveFile($request->file('image'), 'videos', 'image');
        }

        $video->update($videoData);

        return redirect()->route('video.index')->with('success', 'Video berhasil diperbarui');
    }

    public function destroy($id)
    {
        $video = Video::find($id);
        if ($video->image && $video->image !== 'default.jpg') {
            Storage::disk('public')->delete('videos/' . $video->image);
        }

        $video->delete();

        return redirect()->route('video.index')->with('success', 'Video deleted successfully');
    }
}
