<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Medication;

class MedicationController extends Controller
{
    public function index()
    {
        $medications = Medication::orderBy('name')->get();
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

        Medication::create($request->only('name', 'notes'));
        return redirect()->route('medications.index')->with('success', 'Medication added.');
    }

    public function edit(Medication $medication)
    {
        return view('admin.medications.edit', compact('medication'));
    }

    public function update(Request $request, Medication $medication)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'notes' => 'nullable|string|max:255',
        ]);

        $medication->update($request->only('name', 'notes'));
        return redirect()->route('medications.index')->with('success', 'Medication updated.');
    }

    public function destroy(Medication $medication)
    {
        $medication->delete();
        return redirect()->route('medications.index')->with('success', 'Medication deleted.');
    }
}
