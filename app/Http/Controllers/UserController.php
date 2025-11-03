<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Medication;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rules;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::orderBy('name')->paginate(15);
        return view('admin.users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('admin.users.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'], // 2MB max
            'is_superuser' => ['boolean'],
        ]);

        $logoPath = null;
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('logos', 'public');
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'company_name' => $request->company_name,
            'phone_number' => $request->phone_number,
            'logo_path' => $logoPath,
            'is_superuser' => $request->boolean('is_superuser'),
        ]);

        // Seed default medications for new user
        $this->seedMedicationsForUser($user);

        return redirect()->route('users.index')->with('success', 'User created successfully.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'password' => ['nullable', 'confirmed', Rules\Password::defaults()],
            'company_name' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:20'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'is_superuser' => ['boolean'],
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'company_name' => $request->company_name,
            'phone_number' => $request->phone_number,
            'is_superuser' => $request->boolean('is_superuser'),
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        if ($request->hasFile('logo')) {
            // Delete old logo
            if ($user->logo_path) {
                Storage::disk('public')->delete($user->logo_path);
            }
            $data['logo_path'] = $request->file('logo')->store('logos', 'public');
        }

        $user->update($data);

        return redirect()->route('users.index')->with('success', 'User updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        // Prevent deleting yourself or the last superuser
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->is_superuser && User::where('is_superuser', true)->count() === 1) {
            return back()->with('error', 'Cannot delete the only superuser account.');
        }

        // Delete logo if exists
        if ($user->logo_path) {
            Storage::disk('public')->delete($user->logo_path);
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'User deleted successfully.');
    }

    /**
     * Seed default medications for a new user.
     */
    private function seedMedicationsForUser(User $user)
    {
        $defaultMedications = [
            ['name' => 'Latanoprost (Xalatan)', 'notes' => 'Evening'],
            ['name' => 'Timolol 0.5% (Timoptic)', 'notes' => 'Morning/Evening'],
            ['name' => 'Dorzolamide (Trusopt)', 'notes' => 'TID'],
            ['name' => 'Brimonidine (Alphagan)', 'notes' => 'BID'],
            ['name' => 'Tobramycin', 'notes' => 'Antibiotic'],
            ['name' => 'Moxifloxacin', 'notes' => 'Antibiotic'],
            ['name' => 'Artificial tears', 'notes' => 'PRN'],
            ['name' => 'Prednisolone (steroid)', 'notes' => 'Per Rx'],
        ];

        foreach ($defaultMedications as $med) {
            Medication::create([
                'name' => $med['name'],
                'notes' => $med['notes'],
                'user_id' => $user->id,
            ]);
        }
    }
}
