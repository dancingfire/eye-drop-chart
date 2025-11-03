<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;

class MedicationController extends Controller
{
    public function index()
    {
        $medications = Medication::where('user_id', auth()->id())
            ->orderBy('name')
            ->get();
        return view('admin.medications.index', compact('medications'));
    }

    public function create()
    {
        return view('admin.medications.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        Medication::create([
            'name' => $request->name,
            'notes' => $request->notes,
            'user_id' => auth()->id(),
        ]);
        
        return redirect()->route('medications.index')->with('success', 'Medication added.');
    }

    public function edit(Medication $medication)
    {
        // Ensure user can only edit their own medications
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'You can only edit your own medications.');
        }
        
        return view('admin.medications.edit', compact('medication'));
    }

    public function update(Request $request, Medication $medication)
    {
        // Ensure user can only update their own medications
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'You can only update your own medications.');
        }
        
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $medication->update($request->only('name', 'notes'));
        return redirect()->route('medications.index')->with('success', 'Medication updated.');
    }

    public function destroy(Medication $medication)
    {
        // Ensure user can only delete their own medications
        if ($medication->user_id !== auth()->id()) {
            abort(403, 'You can only delete your own medications.');
        }
        
        $medication->delete();
        return redirect()->route('medications.index')->with('success', 'Medication deleted.');
    }
}
