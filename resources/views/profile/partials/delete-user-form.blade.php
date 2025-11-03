<section>
    <h4 class="mb-3 text-danger">Delete Account</h4>
    <p class="text-muted mb-4">
        Once your account is deleted, all of its resources and data will be permanently deleted. 
        Before deleting your account, please download any data or information that you wish to retain.
    </p>

    <button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#confirmDeleteModal">
        Delete Account
    </button>

    <!-- Delete Account Modal -->
    <div class="modal fade" id="confirmDeleteModal" tabindex="-1" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="{{ route('profile.destroy') }}">
                    @csrf
                    @method('DELETE')
                    
                    <div class="modal-header">
                        <h5 class="modal-title" id="confirmDeleteModalLabel">Are you sure you want to delete your account?</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    
                    <div class="modal-body">
                        <p class="text-muted mb-3">
                            Once your account is deleted, all of its resources and data will be permanently deleted. 
                            Please enter your password to confirm you would like to permanently delete your account.
                        </p>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">Password</label>
                            <input type="password" class="form-control @error('password', 'userDeletion') is-invalid @enderror" id="password" name="password" placeholder="Password" required>
                            @error('password', 'userDeletion')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-danger">Delete Account</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</section>

@if($errors->userDeletion->isNotEmpty())
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var modal = new bootstrap.Modal(document.getElementById('confirmDeleteModal'));
        modal.show();
    });
</script>
@endif
