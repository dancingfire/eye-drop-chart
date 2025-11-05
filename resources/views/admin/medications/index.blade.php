@extends('layouts.app')
@section('title','My Medications')

@section('content')
<div class="container py-5">
    <h1>My Medications</h1>
    <p class="text-muted">Manage your personal medication library for building eye drop charts.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('medications.create') }}" class="btn btn-dark mb-3">Add Medication</a>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Name</th>
                <th>Notes</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($medications as $med)
                <tr>
                    <td>{{ $med->name }}</td>
                    <td>{{ $med->notes }}</td>
                    <td>
                        <a href="{{ route('medications.edit', $med) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                        <form action="{{ route('medications.destroy', $med) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Delete this medication?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
