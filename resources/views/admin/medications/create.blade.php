@extends('layouts.app')

@section('content')
<div class="container">
    <h1>Add Medication</h1>
    <form method="POST" action="{{ route('medications.store') }}">
        @csrf
        <div class="mb-3">
            <label class="form-label">Name</label>
            <input type="text" name="name" class="form-control" value="{{ old('name') }}" required>
        </div>
        <div class="mb-3">
            <label class="form-label">Notes</label>
            <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
        </div>
        <button type="submit" class="btn btn-success">Add</button>
        <a href="{{ route('medications.index') }}" class="btn btn-secondary">Cancel</a>
    </form>
</div>
@endsection
