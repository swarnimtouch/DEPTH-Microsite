<?php

namespace App\Http\Controllers\Admin;

use App\Models\ReminderLog;
use App\Models\ReminderTemplate;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReminderTemplateController extends Controller
{
    public function index()
    {
        return view('admin.reminder_templates.index', [
            'title' => __('Reminder Templates'),
            'breadcrumb' => breadcrumb([__('Reminder Templates') => route('admin.reminder_templates')]),
            'channels' => ReminderTemplate::CHANNELS,
            'timings' => ReminderTemplate::TIMINGS,
        ]);
    }

    public function datatable(Request $request)
    {
        $query = ReminderTemplate::query();

        if ($request->channel) {
            $query->where('channel', $request->channel);
        }

        if ($request->timing) {
            $query->where('timing', $request->timing);
        }

        if ($request->search) {
            $search = $request->search;
            $query->where(fn($q) => $q
                ->where('name', 'LIKE', "%$search%")
                ->orWhere('subject', 'LIKE', "%$search%")
                ->orWhere('body', 'LIKE', "%$search%"));
        }

        $recordsTotal = $query->count();
        $data = $query->orderBy('channel')->orderBy('timing')
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

    public function addEditForm($id = null)
    {
        $template = $id ? ReminderTemplate::findOrFail($id) : new ReminderTemplate([
            'channel' => 'email',
            'timing' => 'one_hour_prior',
            'status' => 'active',
        ]);

        return view('admin.reminder_templates.add_edit', [
            'title' => __('Reminder Templates'),
            'template' => $template,
            'channels' => ReminderTemplate::CHANNELS,
            'timings' => ReminderTemplate::TIMINGS,
            'breadcrumb' => breadcrumb([__('Reminder Templates') => route('admin.reminder_templates'), ($id ? 'Edit' : 'Add Template') => '']),
        ]);
    }

    public function save(Request $request, $id = null)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'channel' => ['required', Rule::in(ReminderTemplate::CHANNELS)],
            'timing' => ['required', Rule::in(array_keys(ReminderTemplate::TIMINGS))],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'status' => ['required', Rule::in(['active', 'inactive'])],
        ]);

        if ($validated['channel'] === 'email' && blank($validated['subject'])) {
            return back()->withInput()->withErrors(['subject' => 'Subject is required for email templates.']);
        }

        $exists = ReminderTemplate::where('channel', $validated['channel'])
            ->where('timing', $validated['timing'])
            ->when($id, fn($query) => $query->where('id', '!=', $id))
            ->exists();

        if ($exists) {
            return back()->withInput()->withErrors(['timing' => 'A template already exists for this channel and timing.']);
        }

        ReminderTemplate::updateOrCreate(['id' => $id], $validated);

        return redirect()->route('admin.reminder_templates')->with('success', 'Reminder template saved successfully');
    }

    public function statusChange($id)
    {
        $template = ReminderTemplate::findOrFail($id);
        $template->status = $template->status === 'active' ? 'inactive' : 'active';
        $template->save();

        return response()->json(['success' => true]);
    }

    public function logs()
    {
        return view('admin.reminder_templates.logs', [
            'title' => __('Reminder Logs'),
            'breadcrumb' => breadcrumb([__('Reminder Templates') => route('admin.reminder_templates'), __('Logs') => route('admin.reminder_templates.logs')]),
            'channels' => ReminderTemplate::CHANNELS,
        ]);
    }

    public function logsDatatable(Request $request)
    {
        $query = ReminderLog::with(['module', 'user', 'template']);

        if ($request->channel) {
            $query->where('channel', $request->channel);
        }

        if ($request->status) {
            $query->where('status', $request->status);
        }

        $recordsTotal = $query->count();
        $data = $query->latest()
            ->skip($request->start)
            ->take($request->length)
            ->get()
            ->map(fn($log) => [
                'id' => $log->id,
                'module' => ['title' => $log->module?->title ?: '-'],
                'user' => ['name' => $log->user?->name ?: '-', 'email' => $log->user?->email ?: '-'],
                'channel' => $log->channel,
                'timing' => ReminderTemplate::TIMINGS[$log->timing] ?? $log->timing,
                'recipient' => $log->recipient ?: '-',
                'status' => $log->status,
                'sent_at' => $log->sent_at?->format('d M Y, h:i A') ?: '-',
                'response' => $log->response ?: '-',
            ]);

        return response()->json([
            'draw' => intval($request->draw),
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsTotal,
            'data' => $data,
        ]);
    }
}
