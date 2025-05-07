document.addEventListener('DOMContentLoaded', function() {
    // Toggle disability details
    const disabilitySelect = document.getElementById('disability');
    if (disabilitySelect) {
        disabilitySelect.addEventListener('change', function() {
            const detailsField = document.getElementById('disability_details');
            detailsField.style.display = this.value === 'Yes' ? 'block' : 'none';
            if (this.value !== 'Yes') detailsField.value = '';
        });
    }

    // Toggle parent details and death cert section
    const parentalStatusRadios = document.querySelectorAll('input[name="parental_status"]');
    parentalStatusRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const showParents = this.value !== 'Orphan';
            document.getElementById('parent_details').style.display = 
                showParents ? 'block' : 'none';
            document.getElementById('death_cert_section').style.display = 
                !showParents ? 'block' : 'none';
            
            // Make parent fields required if showing
            const parentFields = document.querySelectorAll('#parent_details input, #parent_details select');
            parentFields.forEach(field => {
                if (showParents && field.name.match(/father_|mother_/)) {
                    field.required = true;
                } else {
                    field.required = false;
                }
            });
        });
    });

    // Toggle external sponsor details
    const externalSupportSelect = document.querySelector('select[name="external_sponsor"]');
    if (externalSupportSelect) {
        externalSupportSelect.addEventListener('change', function() {
            const sponsorDetails = document.getElementById('sponsor_details');
            sponsorDetails.style.display = this.value === 'Yes' ? 'block' : 'none';
            if (this.value === 'Yes') {
                sponsorDetails.querySelector('textarea').required = true;
            } else {
                sponsorDetails.querySelector('textarea').required = false;
            }
        });
    }

    // Toggle fee balance field
    const feePaymentSelect = document.querySelector('select[name="fee_payment_status"]');
    if (feePaymentSelect) {
        feePaymentSelect.addEventListener('change', function() {
            const feeBalanceContainer = document.getElementById('fee_balance_container');
            feeBalanceContainer.style.display = this.value === 'No' ? 'block' : 'none';
            if (this.value === 'No') {
                feeBalanceContainer.querySelector('input').required = true;
            } else {
                feeBalanceContainer.querySelector('input').required = false;
            }
        });
    }

    // Toggle deferral reasons
    const deferredStudiesSelect = document.querySelector('select[name="deferred_studies"]');
    if (deferredStudiesSelect) {
        deferredStudiesSelect.addEventListener('change', function() {
            const deferralReasons = document.getElementById('deferral_reasons');
            deferralReasons.style.display = this.value === 'Yes' ? 'block' : 'none';
        });
    }

    // File upload handling with previews
    function setupFileUpload(inputName, previewId) {
        const input = document.querySelector(`input[name="${inputName}"]`);
        const preview = document.getElementById(previewId);
        
        if (input && preview) {
            input.addEventListener('change', function() {
                preview.innerHTML = '';
                
                if (this.files) {
                    Array.from(this.files).forEach(file => {
                        const reader = new FileReader();
                        
                        reader.onload = function(e) {
                            const filePreview = document.createElement('div');
                            filePreview.className = 'file-preview';
                            
                            if (file.type.match('image.*')) {
                                filePreview.innerHTML = `
                                    <img src="${e.target.result}" alt="Preview">
                                    <div class="file-info">
                                        <div class="file-name">${file.name}</div>
                                        <div class="file-size">${formatFileSize(file.size)}</div>
                                    </div>
                                    <div class="remove-file" data-file="${file.name}">×</div>
                                `;
                            } else {
                                filePreview.innerHTML = `
                                    <div class="file-icon">
                                        <svg viewBox="0 0 24 24">
                                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8l-6-6z"/>
                                            <path d="M14 2v6h6"/>
                                        </svg>
                                    </div>
                                    <div class="file-info">
                                        <div class="file-name">${file.name}</div>
                                        <div class="file-size">${formatFileSize(file.size)}</div>
                                    </div>
                                    <div class="remove-file" data-file="${file.name}">×</div>
                                `;
                            }
                            
                            preview.appendChild(filePreview);
                        };
                        
                        reader.readAsDataURL(file);
                    });
                }
            });
            
            // Handle file removal
            preview.addEventListener('click', function(e) {
                if (e.target.classList.contains('remove-file')) {
                    const fileName = e.target.getAttribute('data-file');
                    const files = Array.from(input.files);
                    const updatedFiles = files.filter(f => f.name !== fileName);
                    
                    // Create new DataTransfer object for updated file list
                    const dataTransfer = new DataTransfer();
                    updatedFiles.forEach(file => dataTransfer.items.add(file));
                    input.files = dataTransfer.files;
                    
                    // Remove preview
                    e.target.parentElement.remove();
                }
            });
        }
    }

    // Initialize file upload handlers
    setupFileUpload('death_certificates[]', 'death_certificates_preview');
    setupFileUpload('secondary_fee_proof', 'secondary_fee_preview');
    setupFileUpload('work_study_proof', 'work_study_proof_preview');
    setupFileUpload('fee_statement', 'fee_statement_preview');
    setupFileUpload('other_documents[]', 'other_documents_preview');

    // Helper function to format file size
    function formatFileSize(bytes) {
        if (bytes === 0) return '0 Bytes';
        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
    }

    // Form validation before submission
    const form = document.getElementById('applicationForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            let isValid = true;
            
            // Check required fields
            const requiredFields = this.querySelectorAll('[required]');
            requiredFields.forEach(field => {
                if (!field.value) {
                    field.classList.add('error');
                    isValid = false;
                } else {
                    field.classList.remove('error');
                }
            });
            
            // Check file uploads
            const requiredFiles = this.querySelectorAll('input[type="file"][required]');
            requiredFiles.forEach(fileInput => {
                if (!fileInput.files || fileInput.files.length === 0) {
                    fileInput.classList.add('error');
                    isValid = false;
                } else {
                    fileInput.classList.remove('error');
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill all required fields and upload all required documents');
                // Scroll to first error
                const firstError = this.querySelector('.error');
                if (firstError) {
                    firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }
});

function addDeathCertificate() {
    const wrapper = document.getElementById('death_certificates_wrapper');

    const inputDiv = document.createElement('div');
    inputDiv.className = 'death-cert-input';

    const newInput = document.createElement('input');
    newInput.type = 'file';
    newInput.name = 'death_certificates[]';
    newInput.className = 'form-control mb-2';
    newInput.accept = '.pdf,.jpg,.jpeg,.png';

    newInput.onchange = function () {
        const file = newInput.files[0];
        if (file) {
            const preview = document.getElementById('death_certificates_preview');
            const p = document.createElement('p');
            p.textContent = file.name;
            preview.appendChild(p);
        }
    };

    inputDiv.appendChild(newInput);
    wrapper.appendChild(inputDiv);
}