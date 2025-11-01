@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Edit Medication</h1>
    <form method="POST" action="{{ route('medications.update', $medication) }}">
        @csrf
        @method('PUT')
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name', $medication->name) }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" value="{{ old('notes', $medication->notes) }}">
        </div>
        <button type="submit" class="btn btn-success">Update</button>
        <a href="{{ route('medications.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
