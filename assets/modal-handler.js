/**
 * Custom Modal Handler
 * Intercepts native confirm() dialogs and replaces them with custom styled modals
 */

// Store original confirm
const originalConfirm = window.confirm;

// Create custom confirm modal
window.customConfirm = function(message, callback) {
    // Create overlay
    const overlay = document.createElement('div');
    overlay.className = 'delete-modal-overlay';
    overlay.style.display = 'flex';
    
    // Create modal content
    overlay.innerHTML = `
        <div class="delete-modal-content">
            <div class="delete-modal-header">
                <h3><i class="fas fa-exclamation-triangle"></i> Confirmer la suppression</h3>
                <button class="close-modal" onclick="this.closest('.delete-modal-overlay').remove()">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            <div class="delete-modal-body">
                <p>${message}</p>
                <p class="warning-text">Cette action est irréversible.</p>
            </div>
            <div class="delete-modal-footer">
                <button class="btn-cancel-modal" onclick="this.closest('.delete-modal-overlay').remove()">
                    Annuler
                </button>
                <button class="btn-delete-confirm" id="confirmBtn">
                    <i class="fas fa-trash"></i> Supprimer
                </button>
            </div>
        </div>
    `;
    
    // Add to body
    document.body.appendChild(overlay);
    
    // Close on background click
    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) {
            overlay.remove();
        }
    });
    
    // Handle confirm button
    const confirmBtn = overlay.querySelector('#confirmBtn');
    confirmBtn.addEventListener('click', function() {
        overlay.remove();
        if (callback) callback(true);
    });
    
    // Handle cancel buttons
    const cancelBtns = overlay.querySelectorAll('.btn-cancel-modal, .close-modal');
    cancelBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            overlay.remove();
            if (callback) callback(false);
        });
    });
};

// Override native confirm for forms with onsubmit
document.addEventListener('DOMContentLoaded', function() {
    // Find all forms with confirm in onsubmit
    const forms = document.querySelectorAll('form[onsubmit*="confirm"]');
    
    forms.forEach(form => {
        const originalOnsubmit = form.onsubmit;
        form.onsubmit = null;
        
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Extract message from original confirm
            const onsubmitStr = form.getAttribute('onsubmit');
            const match = onsubmitStr.match(/confirm\(['"](.+?)['"]\)/);
            const message = match ? match[1] : 'Êtes-vous sûr de vouloir supprimer cet élément ?';
            
            // Show custom confirm
            window.customConfirm(message, function(confirmed) {
                if (confirmed) {
                    // Remove the onsubmit to avoid loop
                    form.removeAttribute('onsubmit');
                    form.submit();
                }
            });
        });
    });
});

console.log('Custom modal handler loaded');
