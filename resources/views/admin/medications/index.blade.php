@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Medications</h1>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <a href="{{ route('medications.create') }}" class="btn btn-primary mb-3">Add Medication</a>

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
                        <a href="{{ route('medications.edit', $med) }}" class="btn btn-sm btn-warning">Edit</a>
                        <form action="{{ route('medications.destroy', $med) }}" method="POST" style="display:inline-block;">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Delete this medication?')">Delete</button>
                        </form>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
