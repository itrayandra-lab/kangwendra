<?php

namespace App\Http\Controllers\Admin;

use App\Models\PublishSchedule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;

class PublishScheduleController extends Controller
{
    /**
     * Display a listing of the publish schedules.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            $schedules = PublishSchedule::orderBy('time')->get();

            return DataTables::of($schedules)
                ->addIndexColumn()
                ->addColumn('time_display', function ($schedule) {
                    return $schedule->formatted_time;
                })
                ->addColumn('day_display', function ($schedule) {
                    return $schedule->day_name;
                })
                ->editColumn('is_active', function ($schedule) {
                    $checked = $schedule->is_active ? 'checked' : '';
                    return '
                        <form action="'.route('schedules.toggle', $schedule->id).'" method="POST" style="display:inline">
                            '.csrf_field().'
                            <label class="switch switch-sm">
                                <input type="checkbox" '.$checked.' onchange="this.form.submit()">
                                <span class="slider round"></span>
                            </label>
                        </form>
                    ';
                })
                ->editColumn('max_posts', function ($schedule) {
                    return '<span class="badge badge-info">'.$schedule->max_posts.'</span>';
                })
                ->addColumn('action', function ($schedule) {
                    return '<button type="button" class="btn btn-primary btn-xs" onclick="editSchedule('.$schedule->id.', \''.$schedule->time.'\', \''.($schedule->day_of_week ?? '').'\', '.$schedule->max_posts.', '.($schedule->is_active ? 1 : 0).')"><i class="fa fa-edit"></i></button>';
                })
                ->rawColumns(['is_active', 'max_posts', 'action'])
                ->make(true);
        }

        return view('pages.admin.schedules.index')->with('page', 'Jadwal Publish');
    }

    /**
     * Store a newly created schedule in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'time' => 'required|date_format:H:i',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'max_posts' => 'required|integer|min:1|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        PublishSchedule::create([
            'time' => $request->time.':00',
            'day_of_week' => $request->filled('day_of_week') ? $request->day_of_week : null,
            'max_posts' => $request->max_posts,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('schedules.index')->with('success', 'Jadwal publish berhasil ditambahkan');
    }

    /**
     * Update the specified schedule in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'time' => 'required|date_format:H:i',
            'day_of_week' => 'nullable|integer|min:0|max:6',
            'max_posts' => 'required|integer|min:1|max:10',
            'is_active' => 'sometimes|boolean',
        ]);

        $schedule = PublishSchedule::findOrFail($id);

        $schedule->update([
            'time' => $request->time.':00',
            'day_of_week' => $request->filled('day_of_week') ? $request->day_of_week : null,
            'max_posts' => $request->max_posts,
            'is_active' => $request->boolean('is_active', true),
        ]);

        return redirect()->route('schedules.index')->with('success', 'Jadwal publish berhasil diperbarui');
    }

    /**
     * Remove the specified schedule from storage.
     */
    public function destroy($id)
    {
        $schedule = PublishSchedule::findOrFail($id);
        $schedule->delete();

        return redirect()->route('schedules.index')->with('success', 'Jadwal publish berhasil dihapus');
    }

    /**
     * Toggle the active status of a schedule.
     */
    public function toggle($id)
    {
        $schedule = PublishSchedule::findOrFail($id);
        $schedule->update(['is_active' => !$schedule->is_active]);

        return back()->with('success', 'Status jadwal diperbarui');
    }
}
