{{-- Reusable Confirmation Modal --}}
<div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content confirm-modal-content">
            <div class="modal-body confirm-modal-body">
                <div class="confirm-icon-wrapper">
                    <i class="fas fa-exclamation-circle confirm-icon" id="confirmModalIcon"></i>
                </div>
                <h4 class="confirm-title" id="confirmModalTitle">Confirm Action</h4>
                <p class="confirm-message" id="confirmModalMessage">Are you sure you want to proceed?</p>
                <div class="confirm-actions">
                    <button type="button" class="btn btn-light confirm-btn-cancel" data-dismiss="modal">
                        Cancel
                    </button>
                    <button type="button" class="btn btn-danger confirm-btn-confirm" id="confirmModalBtn">
                        Confirm
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Global confirmation modal handler
window.confirmAction = function(options) {
    const defaults = {
        title: 'Confirm Action',
        message: 'Are you sure you want to proceed?',
        confirmText: 'Confirm',
        confirmClass: 'btn-danger',
        onConfirm: null
    };
    
    const settings = Object.assign({}, defaults, options);
    
    // Set modal content
    document.getElementById('confirmModalTitle').textContent = settings.title;
    document.getElementById('confirmModalMessage').textContent = settings.message;
    
    const confirmBtn = document.getElementById('confirmModalBtn');
    confirmBtn.innerHTML = '<i class="fas fa-check mr-1"></i> ' + settings.confirmText;
    
    // Reset button classes
    confirmBtn.className = 'btn ' + settings.confirmClass;
    
    // Remove old event listeners by cloning
    const newConfirmBtn = confirmBtn.cloneNode(true);
    confirmBtn.parentNode.replaceChild(newConfirmBtn, confirmBtn);
    
    // Add new event listener
    newConfirmBtn.addEventListener('click', function() {
        $('#confirmModal').modal('hide');
        if (settings.onConfirm && typeof settings.onConfirm === 'function') {
            settings.onConfirm();
        }
    });
    
    // Show modal
    $('#confirmModal').modal('show');
};

// Helper function for form confirmation
window.confirmFormSubmit = function(form, options) {
    confirmAction(Object.assign({}, options, {
        onConfirm: function() {
            form.submit();
        }
    }));
    return false; // Prevent default form submission
};
</script>
