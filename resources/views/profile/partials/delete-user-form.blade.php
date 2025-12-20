<section class="d-grid gap-4">
    <header>
        <h2 class="h5 fw-bold text-danger mb-1">
            {{ __('Delete Account') }}
        </h2>

        <p class="text-muted small mb-0">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <button type="button" class="btn btn-danger fw-medium w-auto me-auto" 
            data-bs-toggle="modal" 
            data-bs-target="#confirmUserDeletionModal">
        {{ __('Delete Account') }}
    </button>

    <div class="modal fade" id="confirmUserDeletionModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                
                <form method="post" action="{{ route('profile.destroy') }}" class="p-4">
                    @csrf
                    @method('delete')

                    <div class="text-center mb-4">
                        <div class="mx-auto bg-danger-subtle text-danger rounded-circle d-flex align-items-center justify-content-center mb-3" style="width: 48px; height: 48px;">
                            <svg width="24" height="24" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        </div>
                        
                        <h2 class="h5 fw-bold text-danger">Delete Account</h2>
                        
                        <p class="text-muted small text-center px-2">
                            Are you sure you want to delete your account? This action is irreversible.
                        </p>
                    </div>

                    <div class="mb-4">
                        <label for="password" class="form-label visually-hidden">Password</label>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               class="form-control bg-light border-0 py-2" 
                               placeholder="Enter your password to confirm"
                               required
                        >
                        @if($errors->userDeletion->has('password'))
                            <div class="text-danger small mt-2 fw-bold">
                                {{ $errors->userDeletion->first('password') }}
                            </div>
                        @endif
                    </div>

                    {{-- Tombol Action --}}
                    <div class="d-flex justify-content-end gap-2">
                        <button type="button" class="btn btn-light fw-medium" data-bs-dismiss="modal">
                            Cancel
                        </button>

                        <button type="submit" class="btn btn-danger fw-medium">
                            Delete Account
                        </button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    @if($errors->userDeletion->isNotEmpty())
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('confirmUserDeletionModal'));
                myModal.show();
            });
        </script>
    @endif
</section>