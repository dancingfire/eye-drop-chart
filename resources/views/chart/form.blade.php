@extends('layouts.app')
@section('title','Eye Drop Chart Generator')
@section('content')
<div class="container py-5">
  <h1 class="mb-4">Eye Drop Chart Generator</h1>

  <!-- Template Section -->
  <div class="card mb-4">
    <div class="card-body">
      <h5 class="card-title">Templates</h5>
      <div class="row">
        <div class="col-md-8">
          <label class="form-label">Load Template</label>
          <div class="input-group">
            <select id="template-select" class="form-select">
              <option value="">-- Select a template --</option>
            </select>
            <button type="button" class="btn btn-outline-danger" id="delete-template-btn" style="display: none;" title="Delete Template">
              <i class="bi bi-trash"></i> Delete
            </button>
          </div>
        </div>
        <div class="col-md-4 d-flex align-items-end">
          <button type="button" class="btn btn-outline-success" id="save-template-btn">Save as Template</button>
        </div>
      </div>
    </div>
  </div>

  <form method="post" action="{{ route('chart.generate') }}">
    @csrf
    <div class="row mb-3">
      <div class="col-md-4">
        <label class="form-label">Start date</label>
        <input type="date" name="start_date" class="form-control" value="{{ old('start_date', \Carbon\Carbon::now()->toDateString()) }}" required>
      </div>
      <div class="col-md-4">
        <label class="form-label">Surgery date (optional)</label>
        <input type="date" name="surgery_date" class="form-control" value="{{ old('surgery_date') }}">
      </div>
    </div>

    <p>Build per-medication schedule:</p>

    <div id="med-container"></div>

    <div class="text-end mb-3">
      <button type="button" class="btn btn-success" id="add-med">+ Add Medication</button>
    </div>
    <div id="generate-buttons" style="display: none;">
      <button class="btn btn-primary" type="submit">Generate PDF chart</button>
    </div>
  </form>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Save as Template</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>
      <div class="modal-body">
        <div class="mb-3">
          <label class="form-label">Template Name</label>
          <input type="text" class="form-control" id="template-name" required>
        </div>
        <div class="mb-3">
          <label class="form-label">Description (optional)</label>
          <textarea class="form-control" id="template-description" rows="3"></textarea>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
        <button type="button" class="btn btn-primary" id="save-template-confirm">Save Template</button>
      </div>
    </div>
  </div>
</div>

@push('scripts')
<script>
let medCount = 0;
const maxMeds = 4;

// Function to update Generate button visibility
function updateGenerateButtonVisibility() {
    const medContainer = document.getElementById('med-container');
    const generateButtons = document.getElementById('generate-buttons');
    const hasMeds = medContainer.children.length > 0;
    
    generateButtons.style.display = hasMeds ? 'block' : 'none';
}

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
            <div class="col-md-6 text-end">
                <button type="button" class="btn btn-danger btn-sm remove-med">Remove Medication</button>
            </div>
        </div>
        <div class="schedule-blocks" data-med-index="${medCount-1}"></div>
        <button type="button" class="btn btn-outline-primary add-schedule">And then...</button>
    `;

    document.getElementById('med-container').appendChild(medDiv);

    addScheduleBlock(medDiv.querySelector('.schedule-blocks'));
    
    // Add remove medication handler
    medDiv.querySelector('.remove-med').addEventListener('click', function(){
        medDiv.remove();
        updateGenerateButtonVisibility();
    });
    
    updateGenerateButtonVisibility();
});

function addScheduleBlock(container){
    const medIndex = container.dataset.medIndex;
    const blockCount = container.children.length;

    const blockDiv = document.createElement('div');
    blockDiv.className = 'row mb-2 align-items-center schedule-block';
    blockDiv.innerHTML = `
        <div class="col-md-3">
            <input type="number" name="medications[${medIndex}][blocks][${blockCount}][days]" min="1" max="70" class="form-control" placeholder="Days" required>
        </div>
        <div class="col-md-3">
            <select name="medications[${medIndex}][blocks][${blockCount}][doses]" class="form-select" required>
                <option value="">Times/day</option>
                <option value="0">0x daily (Not taking)</option>
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

// Template Management
let templates = [];
let saveTemplateModal;

document.addEventListener('DOMContentLoaded', function(){
    // Initialize Bootstrap modal
    saveTemplateModal = new bootstrap.Modal(document.getElementById('saveTemplateModal'));
    
    // Load templates
    loadTemplates();
});

function loadTemplates() {
    fetch('/templates')
        .then(response => response.json())
        .then(data => {
            templates = data;
            const select = document.getElementById('template-select');
            select.innerHTML = '<option value="">-- Select a template --</option>';
            templates.forEach(template => {
                const option = document.createElement('option');
                option.value = template.id;
                option.textContent = template.name + (template.description ? ' - ' + template.description : '');
                select.appendChild(option);
            });
        });
}

// Save Template Button
document.getElementById('save-template-btn').addEventListener('click', function(){
    const scheduleData = getCurrentScheduleData();
    if (!scheduleData || scheduleData.length === 0) {
        alert('Please add at least one medication schedule before saving a template.');
        return;
    }
    saveTemplateModal.show();
});

// Save Template Confirm
document.getElementById('save-template-confirm').addEventListener('click', function(){
    const name = document.getElementById('template-name').value.trim();
    const description = document.getElementById('template-description').value.trim();
    
    if (!name) {
        alert('Please enter a template name.');
        return;
    }
    
    const scheduleData = getCurrentScheduleData();
    
    fetch('/templates', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            name: name,
            description: description,
            template_data: scheduleData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template saved successfully!');
            saveTemplateModal.hide();
            document.getElementById('template-name').value = '';
            document.getElementById('template-description').value = '';
            loadTemplates();
        }
    })
    .catch(error => {
        alert('Error saving template: ' + error.message);
    });
});

// Auto-load template on selection
document.getElementById('template-select').addEventListener('change', function(){
    const templateId = this.value;
    const deleteBtn = document.getElementById('delete-template-btn');
    
    if (!templateId) {
        // Hide delete button when no template selected
        deleteBtn.style.display = 'none';
        return;
    }
    
    // Show delete button
    deleteBtn.style.display = 'block';
    
    // Auto-load the selected template
    fetch(`/templates/${templateId}`)
        .then(response => response.json())
        .then(template => {
            loadScheduleData(template.template_data);
        })
        .catch(error => {
            alert('Error loading template: ' + error.message);
        });
});

// Delete Template Button
document.getElementById('delete-template-btn').addEventListener('click', function(){
    const templateId = document.getElementById('template-select').value;
    if (!templateId) {
        return;
    }
    
    const selectedOption = document.getElementById('template-select').selectedOptions[0];
    const templateName = selectedOption ? selectedOption.textContent : 'this template';
    
    if (!confirm(`Are you sure you want to delete "${templateName}"?`)) {
        return;
    }
    
    fetch(`/templates/${templateId}`, {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            alert('Template deleted successfully!');
            loadTemplates();
        }
    })
    .catch(error => {
        alert('Error deleting template: ' + error.message);
    });
});

// Helper: Get current schedule data from form
function getCurrentScheduleData() {
    const startDate = document.querySelector('input[name="start_date"]')?.value || '';
    const surgeryDate = document.querySelector('input[name="surgery_date"]')?.value || '';
    const medications = [];
    const medDivs = document.querySelectorAll('.med-schedule');
    
    medDivs.forEach((medDiv, index) => {
        const medSelect = medDiv.querySelector('select[name^="medications"][name$="[id]"]');
        const blocks = [];
        
        const blockDivs = medDiv.querySelectorAll('.schedule-block');
        blockDivs.forEach(blockDiv => {
            const daysInput = blockDiv.querySelector('input[name*="[days]"]');
            const dosesSelect = blockDiv.querySelector('select[name*="[doses]"]');
            
            if (daysInput && dosesSelect && daysInput.value && dosesSelect.value) {
                blocks.push({
                    days: parseInt(daysInput.value),
                    doses: parseInt(dosesSelect.value)
                });
            }
        });
        
        if (medSelect && medSelect.value && blocks.length > 0) {
            medications.push({
                id: parseInt(medSelect.value),
                blocks: blocks
            });
        }
    });
    
    return {
        start_date: startDate,
        surgery_date: surgeryDate,
        medications: medications
    };
}

// Helper: Load schedule data into form
function loadScheduleData(templateData) {
    // Load dates if present
    if (templateData.start_date) {
        document.querySelector('input[name="start_date"]').value = templateData.start_date;
    }
    if (templateData.surgery_date) {
        document.querySelector('input[name="surgery_date"]').value = templateData.surgery_date;
    }
    
    // Clear existing medications
    document.getElementById('med-container').innerHTML = '';
    medCount = 0;
    
    // Support both old format (array of meds) and new format (object with medications array)
    const medications = Array.isArray(templateData) ? templateData : (templateData.medications || []);
    
    // Load each medication from template
    medications.forEach(medData => {
        // Add medication
        document.getElementById('add-med').click();
        
        const medDiv = document.querySelector(`.med-schedule[data-med-index="${medCount-1}"]`);
        const medSelect = medDiv.querySelector('select[name^="medications"][name$="[id]"]');
        medSelect.value = medData.id;
        
        // Clear default block
        const scheduleBlocks = medDiv.querySelector('.schedule-blocks');
        scheduleBlocks.innerHTML = '';
        
        // Add blocks
        medData.blocks.forEach(block => {
            addScheduleBlock(scheduleBlocks);
            const blockDivs = scheduleBlocks.querySelectorAll('.schedule-block');
            const lastBlock = blockDivs[blockDivs.length - 1];
            
            const daysInput = lastBlock.querySelector('input[name*="[days]"]');
            const dosesSelect = lastBlock.querySelector('select[name*="[doses]"]');
            
            daysInput.value = block.days;
            dosesSelect.value = block.doses;
        });
    });
    
    // Update generate button visibility after loading template
    updateGenerateButtonVisibility();
}
</script>
@endpush
@endsection
