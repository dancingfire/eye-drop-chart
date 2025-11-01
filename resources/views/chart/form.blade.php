@extends('layouts.app')
@section('title','Eye Drop Chart Generator')
@section('content')
<div class="container py-5">
  <h1 class="mb-4">Eye Drop Chart Generator</h1>

  <form method="post" action="{{ route('chart.generate') }}">
    @csrf
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Start date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', \Carbon\Carbon::now()->toDateString()) }}" required>
      </div>
    </div>

    <p>Build per-medication schedule:</p>

    <div id="med-container"></div>

    <button type="button" class="btn btn-success mb-3" id="add-med">+ Add Medication</button>
    <div>
      <button class="btn btn-primary" type="submit">Generate PDF chart</button>
 
    </div>
  </form>
</div>

@push('scripts')
<script>
let medCount = 0;
const maxMeds = 4;

document.getElementById('add-med').addEventListener('click', function(){
    if(medCount >= maxMeds) return alert('Max 4 medications');
    medCount++;

    const medDiv = document.createElement('div');
    medDiv.className = 'med-schedule mb-4 border p-3';
    medDiv.dataset.medIndex = medCount-1;

    medDiv.innerHTML = `
        <div class="row mb-2">
            <div class="col-md-6">
                <select name="medications[${medCount-1}][id]" class="form-select" required>
                    <option value="">-- Select medication --</option>
                    @foreach($medications as $med)
                        <option value="{{ $med->id }}">{{ $med->name }} {{ $med->notes ? ' — '.$med->notes : '' }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="schedule-blocks" data-med-index="${medCount-1}"></div>
        <button type="button" class="btn btn-outline-primary add-schedule">And then...</button>
    `;

    document.getElementById('med-container').appendChild(medDiv);

    addScheduleBlock(medDiv.querySelector('.schedule-blocks'));
});

function addScheduleBlock(container){
    const medIndex = container.dataset.medIndex;
    const blockCount = container.children.length;

    const blockDiv = document.createElement('div');
    blockDiv.className = 'row mb-2 align-items-center schedule-block';
    blockDiv.innerHTML = `
        <div class="col-md-3">
            <input type="number" name="medications[${medIndex}][blocks][${blockCount}][weeks]" min="1" class="form-control" placeholder="Weeks" required>
        </div>
        <div class="col-md-3">
            <select name="medications[${medIndex}][blocks][${blockCount}][doses]" class="form-select" required>
                <option value="">Times/day</option>
                <option value="1">1x daily (Morning)</option>
                <option value="2">2x daily (Morning/Bedtime)</option>
                <option value="3">3x daily (Morning/Supper/Bedtime)</option>
                <option value="4">4x daily (Morning/Midday/Supper/Bedtime)</option>
            </select>
        </div>
        <div class="col-md-3">
            <button type="button" class="btn btn-danger remove-block">Remove</button>
        </div>
    `;
    container.appendChild(blockDiv);

    blockDiv.querySelector('.remove-block').addEventListener('click', function(){
        blockDiv.remove();
    });
}

// Add schedule block when clicking 'And then...'
document.addEventListener('click', function(e){
    if(e.target.classList.contains('add-schedule')){
        const container = e.target.previousElementSibling; // .schedule-blocks
        addScheduleBlock(container);
    }
});
</script>
@endpush
@endsection
