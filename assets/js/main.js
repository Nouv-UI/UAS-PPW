// assets/js/main.js

document.addEventListener('DOMContentLoaded', function() {
    console.log("Jajan Pasar An-NaHL script loaded.");

    // Event Listener 1: Form Validation for Forms with class .needs-validation
    const validationForms = document.querySelectorAll('.needs-validation');
    validationForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }
            form.classList.add('was-validated');
        }, false);
    });

    // Event Listener 2: Confirm Dialog before deleting objects
    const deleteButtons = document.querySelectorAll('.confirm-delete');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function(event) {
            const itemName = this.getAttribute('data-item-name') || 'data ini';
            const message = `Apakah Anda yakin ingin menghapus ${itemName}? Tindakan ini tidak dapat dibatalkan.`;
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Event Listener 3: Confirm Dialog before saving edits
    const editForms = document.querySelectorAll('.confirm-save-edit');
    editForms.forEach(form => {
        form.addEventListener('submit', function(event) {
            const message = "Apakah Anda yakin ingin menyimpan perubahan data ini?";
            if (!confirm(message)) {
                event.preventDefault();
            }
        });
    });

    // Event Listener 4: Real-time validation for product prices (harga jual vs harga supplier)
    const supplierPriceInput = document.getElementById('harga_supplier');
    const sellingPriceInput = document.getElementById('harga_jual');

    if (supplierPriceInput && sellingPriceInput) {
        const validatePrices = () => {
            const supplierVal = parseFloat(supplierPriceInput.value) || 0;
            const sellingVal = parseFloat(sellingPriceInput.value) || 0;

            if (sellingVal <= supplierVal && sellingVal > 0) {
                sellingPriceInput.setCustomValidity("Harga jual harus lebih besar dari harga supplier.");
                // Create or show feedback
                const feedback = document.getElementById('harga_jual_feedback');
                if (feedback) {
                    feedback.textContent = "Harga jual harus lebih besar dari harga supplier.";
                }
            } else {
                sellingPriceInput.setCustomValidity("");
            }
        };

        supplierPriceInput.addEventListener('input', validatePrices);
        sellingPriceInput.addEventListener('input', validatePrices);
    }
});
