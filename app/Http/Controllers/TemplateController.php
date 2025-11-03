<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ScheduleTemplate;

class TemplateController extends Controller
{
    public function index()
    {
        $templates = ScheduleTemplate::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
        return response()->json($templates);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
            'template_data' => 'required|array',
        ]);

        $template = ScheduleTemplate::create([
            'name' => $request->name,
            'description' => $request->description,
            'template_data' => $request->template_data,
            'user_id' => auth()->id(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Template saved successfully',
            'template' => $template
        ]);
    }

    public function show($id)
    {
        $template = ScheduleTemplate::where('user_id', auth()->id())
            ->findOrFail($id);
        return response()->json($template);
    }

    public function destroy($id)
    {
        $template = ScheduleTemplate::where('user_id', auth()->id())
            ->findOrFail($id);
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template deleted successfully'
        ]);
    }
}
