@extends('layouts.app')
@section('title','DropTrack')
@section('content')
<div class="container py-5" style="max-width: 900px;">

  <!-- Template Section -->
  <div class="card mb-4">
    <div class="card-body">
      <div class="row">
        <div class="col-md-12">
          <div class="d-flex gap-2 align-items-center">
            <div class="flex-grow-1">
              <select id="template-select" class="form-select">
                <option value="">-- Select a template --</option>
              </select>
            </div>
            <button type="button" class="btn btn-outline-secondary" id="save-template-btn">Save template</button>
            <button type="button" class="btn btn-outline-secondary" id="update-template-btn" style="display: none;">Update template</button>
            <button type="button" class="btn btn-link text-danger p-2" id="delete-template-btn" style="display: none;" title="Delete Template">
              <i class="bi bi-trash fs-5"></i>
            </button>
          </div>
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
      <button type="button" class="btn btn-outline-secondary" id="add-med">+ Add Medication</button>
    </div>
    <div id="generate-buttons" style="display: none;">
      <button class="btn btn-dark" type="submit">Generate PDF chart</button>
    </div>
  </form>
</div>

<!-- Save Template Modal -->
<div class="modal fade" id="saveTemplateModal" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Save template</h5>
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
        <button type="button" class="btn btn-dark" id="save-template-confirm">Save Template</button>
      </div>
    </div>
  </div>
</div>

@push('styles')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
@endpush

@push('scripts')
<!-- jQuery (required for Select2) -->
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
let medCount = 0;

// Function to initialize Select2 on a medication select element
function initSelect2(selectElement) {
    $(selectElement).select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select medication --',
        allowClear: true,
        width: '100%'
    });
}

// Function to update Generate button visibility
function updateGenerateButtonVisibility() {
    const medContainer = document.getElementById('med-container');
    const generateButtons = document.getElementById('generate-buttons');
    const hasMeds = medContainer.children.length > 0;
    
    generateButtons.style.display = hasMeds ? 'block' : 'none';
}

// Function to reindex medications after reordering
function reindexMedications() {
    const medContainer = document.getElementById('med-container');
    const medDivs = medContainer.querySelectorAll('.med-schedule');
    
    medDivs.forEach((medDiv, index) => {
        medDiv.dataset.medIndex = index;
        
        // Update medication select name
        const medSelect = medDiv.querySelector('select[name^="medications"][name$="[id]"]');
        if (medSelect) {
            medSelect.name = `medications[${index}][id]`;
        }
        
        // Update schedule blocks
        const scheduleBlocks = medDiv.querySelector('.schedule-blocks');
        if (scheduleBlocks) {
            scheduleBlocks.dataset.medIndex = index;
            
            const blockDivs = scheduleBlocks.querySelectorAll('.schedule-block');
            blockDivs.forEach((blockDiv, blockIndex) => {
                const daysInput = blockDiv.querySelector('input[name*="[days]"]');
                const dosesSelect = blockDiv.querySelector('select[name*="[doses]"]');
                
                if (daysInput) {
                    daysInput.name = `medications[${index}][blocks][${blockIndex}][days]`;
                }
                if (dosesSelect) {
                    dosesSelect.name = `medications[${index}][blocks][${blockIndex}][doses]`;
                }
            });
        }
    });
}

// Function to enable/disable move up/down buttons based on position
function updateMoveButtonsState() {
    const medContainer = document.getElementById('med-container');
    const medDivs = medContainer.querySelectorAll('.med-schedule');
    
    medDivs.forEach((medDiv, index) => {
        const moveUpBtn = medDiv.querySelector('.move-up');
        const moveDownBtn = medDiv.querySelector('.move-down');
        
        if (moveUpBtn) {
            moveUpBtn.disabled = (index === 0);
        }
        if (moveDownBtn) {
            moveDownBtn.disabled = (index === medDivs.length - 1);
        }
    });
}

document.getElementById('add-med').addEventListener('click', function(){
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
                <button type="button" class="btn btn-outline-secondary btn-sm move-up" title="Move Up">
                    <i class="bi bi-arrow-up"></i>
                </button>
                <button type="button" class="btn btn-outline-secondary btn-sm move-down" title="Move Down">
                    <i class="bi bi-arrow-down"></i>
                </button>
                <button type="button" class="btn btn-outline-danger btn-sm remove-med">Remove</button>
            </div>
        </div>
        <div class="schedule-blocks" data-med-index="${medCount-1}"></div>
        <button type="button" class="btn btn-outline-secondary btn-sm add-schedule">And then...</button>
    `;

    document.getElementById('med-container').appendChild(medDiv);

    // Initialize Select2 on the medication dropdown
    const medSelect = medDiv.querySelector('select[name*="[id]"]');
    initSelect2(medSelect);

    addScheduleBlock(medDiv.querySelector('.schedule-blocks'));
    
    // Add remove medication handler
    medDiv.querySelector('.remove-med').addEventListener('click', function(){
        medDiv.remove();
        updateGenerateButtonVisibility();
        updateMoveButtonsState();
    });
    
    // Add move up handler
    medDiv.querySelector('.move-up').addEventListener('click', function(){
        const container = document.getElementById('med-container');
        const prevSibling = medDiv.previousElementSibling;
        if (prevSibling) {
            container.insertBefore(medDiv, prevSibling);
            reindexMedications();
            updateMoveButtonsState();
        }
    });
    
    // Add move down handler
    medDiv.querySelector('.move-down').addEventListener('click', function(){
        const container = document.getElementById('med-container');
        const nextSibling = medDiv.nextElementSibling;
        if (nextSibling) {
            container.insertBefore(nextSibling, medDiv);
            reindexMedications();
            updateMoveButtonsState();
        }
    });
    
    updateGenerateButtonVisibility();
    updateMoveButtonsState();
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
            <button type="button" class="btn btn-outline-danger btn-sm remove-block">Remove</button>
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
let currentTemplateId = null; // Track currently loaded template

document.addEventListener('DOMContentLoaded', function(){
    // Initialize Bootstrap modal
    saveTemplateModal = new bootstrap.Modal(document.getElementById('saveTemplateModal'));
    
    // Initialize Select2 on template dropdown
    $('#template-select').select2({
        theme: 'bootstrap-5',
        placeholder: '-- Select a template --',
        allowClear: true,
        width: '100%'
    });
    
    // Setup template change event listener (after Select2 is initialized)
    $('#template-select').on('change', function(){
        const templateId = this.value;
        const deleteBtn = document.getElementById('delete-template-btn');
        const updateBtn = document.getElementById('update-template-btn');
        
        if (!templateId) {
            // Hide delete and update buttons when no template selected
            deleteBtn.style.display = 'none';
            updateBtn.style.display = 'none';
            currentTemplateId = null;
            return;
        }
        
        // Show delete and update buttons
        deleteBtn.style.display = 'block';
        updateBtn.style.display = 'block';
        currentTemplateId = templateId;
        
        // Auto-load the selected template
        fetch(`/templates/${templateId}`)
            .then(response => response.json())
            .then(template => {
                loadScheduleData(template.template_data);
            })
            .catch(error => {
                console.error('Error loading template:', error);
                alert('Failed to load template');
            });
    });
    
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
            // Trigger Select2 to update with new options
            $('#template-select').trigger('change.select2');
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

// Update Template Button
document.getElementById('update-template-btn').addEventListener('click', function(){
    if (!currentTemplateId) {
        alert('No template selected.');
        return;
    }
    
    const scheduleData = getCurrentScheduleData();
    if (!scheduleData || scheduleData.medications.length === 0) {
        alert('Please add at least one medication schedule before updating the template.');
        return;
    }
    
    // Get current template details
    const selectedOption = document.getElementById('template-select').selectedOptions[0];
    const currentTemplate = templates.find(t => t.id == currentTemplateId);
    
    if (!currentTemplate) {
        alert('Template not found.');
        return;
    }
    
    if (!confirm(`Update template "${currentTemplate.name}" with the current schedule?`)) {
        return;
    }
    
    fetch(`/templates/${currentTemplateId}`, {
        method: 'PUT',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '{{ csrf_token() }}'
        },
        body: JSON.stringify({
            name: currentTemplate.name,
            description: currentTemplate.description,
            template_data: scheduleData
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadTemplates();
        }
    })
    .catch(error => {
        alert('Error updating template: ' + error.message);
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
            // Clear the form
            document.getElementById('med-container').innerHTML = '';
            medCount = 0;
            updateGenerateButtonVisibility();
            
            // Reset template selection
            $('#template-select').val('').trigger('change');
            document.getElementById('delete-template-btn').style.display = 'none';
            document.getElementById('update-template-btn').style.display = 'none';
            currentTemplateId = null;
            
            // Reload template list
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
        $(medSelect).trigger('change'); // Trigger Select2 to update
        
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
    updateMoveButtonsState();
}
</script>
@endpush
@endsection
