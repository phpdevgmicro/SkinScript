/**
 * SkinCraft App - Modern Step-by-Step Skincare Formulation
 */

class SkinCraftApp {
    constructor() {
        this.currentStep = 1;
        this.totalSteps = 6;
        this.maxActives = 3;
        this.selectedActives = 0;
        
        this.formData = {
            skinType: [],
            baseFormat: 'mist',
            keyActives: [],
            extracts: [],
            boosters: [],
            contact: {}
        };
        
        this.init();
    }
    
    init() {
        this.bindEvents();
        this.updateStepDisplay();
        this.updateProgress();
        this.updateSidebar();
        this.updateNavigation();
        console.log('SkinCraft App initialized');
    }
    
    bindEvents() {
        // Form submission
        const form = document.getElementById('formulationForm');
        form?.addEventListener('submit', (e) => this.handleSubmit(e));
        
        // Navigation buttons
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        nextBtn?.addEventListener('click', () => this.nextStep());
        prevBtn?.addEventListener('click', () => this.prevStep());
        
        // Form inputs
        this.bindFormInputs();
        
        
    }
    
    bindFormInputs() {
        // Skin type checkboxes
        document.querySelectorAll('input[name="skinType"]').forEach(input => {
            input.addEventListener('change', () => this.handleSkinTypeChange());
        });
        
        // Base format radios
        document.querySelectorAll('input[name="baseFormat"]').forEach(input => {
            input.addEventListener('change', () => this.handleBaseFormatChange());
        });
        
        // Key actives checkboxes
        document.querySelectorAll('input[name="keyActives"]').forEach(input => {
            input.addEventListener('change', () => this.handleKeyActivesChange());
        });
        
        // Extracts checkboxes
        document.querySelectorAll('input[name="extracts"]').forEach(input => {
            input.addEventListener('change', () => this.handleExtractsChange());
        });
        
        // Boosters checkboxes
        document.querySelectorAll('input[name="boosters"]').forEach(input => {
            input.addEventListener('change', () => this.handleBoostersChange());
        });
        
        // Contact inputs
        document.querySelectorAll('#step6 input, #step6 textarea').forEach(input => {
            input.addEventListener('blur', () => this.validateContactField(input));
            input.addEventListener('input', () => this.handleContactChange());
        });
    }
    
    // Event Handlers
    handleSkinTypeChange() {
        this.formData.skinType = this.getCheckedValues('skinType');
        this.updateSidebar();
        this.updateNavigation();
        this.clearError('skinTypeError');
    }
    
    handleBaseFormatChange() {
        const selected = document.querySelector('input[name="baseFormat"]:checked');
        this.formData.baseFormat = selected ? selected.value : 'mist';
        this.updateSidebar();
        this.updateNavigation();
    }
    
    handleKeyActivesChange() {
        this.formData.keyActives = this.getCheckedValues('keyActives');
        this.selectedActives = this.formData.keyActives.length;
        this.updateActivesUI();
        this.updateSidebar();
        this.updateNavigation();
        this.clearError('keyActivesError');
    }
    
    handleExtractsChange() {
        this.formData.extracts = this.getCheckedValues('extracts');
        this.updateSidebar();
    }
    
    handleBoostersChange() {
        this.formData.boosters = this.getCheckedValues('boosters');
        this.updateSidebar();
    }
    
    handleContactChange() {
        this.formData.contact = {
            fullName: document.getElementById('fullName')?.value || '',
            email: document.getElementById('email')?.value || '',
            skinConcerns: document.getElementById('skinConcerns')?.value || ''
        };
        this.updateUI();
    }
    
    // Navigation
    nextStep() {
        if (!this.validateCurrentStep()) {
            return;
        }
        
        if (this.currentStep < this.totalSteps) {
            this.currentStep++;
            this.updateStepDisplay();
            this.updateProgress();
            this.updateNavigation();
        }
    }
    
    prevStep() {
        if (this.currentStep > 1) {
            this.currentStep--;
            this.updateStepDisplay();
            this.updateProgress();
            this.updateNavigation();
        }
    }
    
    updateStepDisplay() {
        // Hide all steps
        document.querySelectorAll('.form-step').forEach(step => {
            step.classList.remove('active');
        });
        
        // Show current step
        const currentStepEl = document.getElementById(`step${this.currentStep}`);
        currentStepEl?.classList.add('active');
        
        // Update step counter
        const stepCounter = document.getElementById('stepCounter');
        if (stepCounter) {
            stepCounter.textContent = `Step ${this.currentStep} of ${this.totalSteps}`;
        }
    }
    
    updateProgress() {
        const progressFill = document.getElementById('progressFill');
        if (progressFill) {
            const percentage = (this.currentStep / this.totalSteps) * 100;
            progressFill.style.width = `${percentage}%`;
        }
    }
    
    updateNavigation() {
        const nextBtn = document.getElementById('nextBtn');
        const prevBtn = document.getElementById('prevBtn');
        
        if (prevBtn) {
            prevBtn.disabled = this.currentStep === 1;
        }
        
        if (nextBtn) {
            if (this.currentStep === this.totalSteps) {
                nextBtn.style.display = 'none';
            } else {
                nextBtn.style.display = 'flex';
                // Only disable Next button if current step has validation requirements that aren't met
                if (this.currentStep === 1) {
                    nextBtn.disabled = this.formData.skinType.length === 0;
                } else if (this.currentStep === 3) {
                    nextBtn.disabled = this.formData.keyActives.length === 0;
                } else {
                    nextBtn.disabled = false; // Steps 2, 4, 5 have no validation requirements
                }
            }
        }
    }
    
    // Validation
    validateCurrentStep() {
        switch (this.currentStep) {
            case 1:
                return this.validateSkinType();
            case 2:
                return true; // Base format is always valid (has default)
            case 3:
                return this.validateKeyActives();
            case 4:
                return true; // Extracts are optional
            case 5:
                return true; // Boosters are optional
            case 6:
                return this.validateContact();
            default:
                return true;
        }
    }
    
    validateSkinType() {
        if (this.formData.skinType.length === 0) {
            this.showError('skinTypeError', 'Please select at least one skin type');
            return false;
        }
        return true;
    }
    
    validateKeyActives() {
        if (this.formData.keyActives.length === 0) {
            this.showError('keyActivesError', 'Please select at least one active ingredient');
            return false;
        }
        return true;
    }
    
    validateContact() {
        const nameField = document.getElementById('fullName');
        const emailField = document.getElementById('email');
        
        let isValid = true;
        
        if (!this.validateContactField(nameField)) {
            isValid = false;
        }
        
        if (!this.validateContactField(emailField)) {
            isValid = false;
        }
        
        return isValid;
    }
    
    validateContactField(field) {
        if (!field) return false;
        
        const value = field.value.trim();
        const validators = {
            fullName: (val) => ({
                valid: val.length >= 2,
                message: val.length === 0 ? 'Full name is required' : 'Name must be at least 2 characters'
            }),
            email: (val) => ({
                valid: val.length > 0 && /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(val),
                message: val.length === 0 ? 'Email address is required' : 'Please enter a valid email address'
            })
        };
        
        const validator = validators[field.name];
        if (!validator) return true;
        
        const result = validator(value);
        this.displayFieldError(field.name, result.message, !result.valid);
        return result.valid;
    }
    
    canProceedToNextStep() {
        switch (this.currentStep) {
            case 1:
                return this.formData.skinType.length > 0;
            case 2:
                return true;
            case 3:
                return this.formData.keyActives.length > 0;
            case 4:
            case 5:
                return true;
            case 6:
                return false; // Last step, no next button
            default:
                return true;
        }
    }
    
    // UI Updates
    updateUI() {
        this.updateNavigation();
        this.updateSubmitButton();
    }
    
    updateActivesUI() {
        // Update counter
        const counter = document.getElementById('activesCounter');
        if (counter) {
            counter.textContent = `${this.selectedActives}/${this.maxActives}`;
        }
        
        // Enable/disable options based on limit
        const activeInputs = document.querySelectorAll('input[name="keyActives"]');
        activeInputs.forEach(input => {
            input.disabled = !input.checked && this.selectedActives >= this.maxActives;
        });
    }
    
    updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;
        
        const hasRequired = this.hasRequiredSelections();
        submitBtn.disabled = !hasRequired;
    }
    
    
    
    updateSidebar() {
        // Update base format
        const formatEl = document.getElementById('sidebarFormat');
        if (formatEl) {
            formatEl.textContent = this.formatDisplayName(this.formData.baseFormat) || 'Not selected';
        }
        
        // Update active ingredients
        const activesEl = document.getElementById('sidebarActives');
        if (activesEl) {
            activesEl.textContent = this.formData.keyActives.length > 0 
                ? this.formData.keyActives.map(a => this.formatDisplayName(a)).join(', ')
                : 'Not selected';
        }
        
        // Update botanical extracts
        const extractsEl = document.getElementById('sidebarExtracts');
        if (extractsEl) {
            extractsEl.textContent = this.formData.extracts.length > 0
                ? this.formData.extracts.map(e => this.formatDisplayName(e)).join(', ')
                : 'Not selected';
        }
        
        // Update hydrators
        const hydratorsEl = document.getElementById('sidebarHydrators');
        if (hydratorsEl) {
            hydratorsEl.textContent = this.formData.boosters.length > 0
                ? this.formData.boosters.map(b => this.formatDisplayName(b)).join(', ')
                : 'Not selected';
        }
    }
    
    // Form Submission
    async handleSubmit(event) {
        event.preventDefault();
        
        if (!this.validateContact()) {
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        const statusDiv = document.getElementById('submissionStatus');
        
        // Show loading state
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Formula...';
        submitBtn.disabled = true;
        
        try {
            const submissionData = this.prepareSubmissionData();
            const response = await this.submitToAPI(submissionData);
            
            if (response.success) {
                this.displayResults(response);
            } else {
                this.displayError('Failed to generate formulation. Please try again.');
            }
            
        } catch (error) {
            console.error('Submission error:', error);
            this.displayError('An error occurred. Please try again.');
        } finally {
            submitBtn.innerHTML = '<i class="fas fa-robot"></i> Get AI Formulation Suggestions';
            submitBtn.disabled = false;
        }
    }
    
    prepareSubmissionData() {
        return {
            timestamp: new Date().toISOString(),
            formulation: {
                skinType: this.formData.skinType,
                baseFormat: this.formData.baseFormat,
                keyActives: this.formData.keyActives,
                extracts: this.formData.extracts,
                boosters: this.formData.boosters
            },
            contact: this.formData.contact,
            userAgent: navigator.userAgent,
            formVersion: '3.0'
        };
    }
    
    async submitToAPI(data) {
        const response = await fetch('/api/submit_formulation.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    }
    
    // Results Display
    displayResults(response) {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;
        
        statusDiv.className = 'status-message formulation-results';
        statusDiv.innerHTML = this.buildResultsHTML(response);
    }
    
    buildResultsHTML(response) {
        let html = '<div class="success-message">';
        html += '<h3><i class="fas fa-check-circle"></i> AI Formulation Created Successfully!</h3>';
        html += `<p><strong>Formulation ID:</strong> ${response.formulation_id}</p>`;
        
        if (response.suggestions) {
            const suggestions = response.suggestions;
            
            // AI-generated formulation name
            if (suggestions.formulation_name) {
                html += `<h4>${suggestions.formulation_name}</h4>`;
            }
            
            // Recommended percentages
            if (suggestions.recommended_percentages) {
                html += '<div class="formulation-section">';
                html += '<h4><i class="fas fa-calculator"></i> AI-Recommended Concentrations</h4>';
                html += '<div class="percentage-grid">';
                Object.entries(suggestions.recommended_percentages).forEach(([ingredient, range]) => {
                    html += `<div class="percentage-item">
                        <strong>${this.formatDisplayName(ingredient)}:</strong> 
                        ${range.recommended || 1.0}% 
                        <small>(Safe range: ${range.min || 0.1}% - ${range.max || 5.0}%)</small>
                    </div>`;
                });
                html += '</div></div>';
            }
            
            // Expected benefits
            if (suggestions.expected_benefits) {
                html += '<div class="formulation-section">';
                html += '<h4><i class="fas fa-star"></i> Expected Benefits</h4>';
                html += '<ul class="benefits-list">';
                suggestions.expected_benefits.forEach(benefit => {
                    html += `<li>${benefit}</li>`;
                });
                html += '</ul></div>';
            }
            
            // Application instructions
            if (suggestions.application_instructions) {
                html += '<div class="formulation-section">';
                html += '<h4><i class="fas fa-info-circle"></i> Usage Instructions</h4>';
                html += `<p>${suggestions.application_instructions}</p>`;
                html += '</div>';
            }
            
            // Warnings
            if (suggestions.warnings && suggestions.warnings.length > 0) {
                html += '<div class="formulation-section warning">';
                html += '<h4><i class="fas fa-exclamation-triangle"></i> Important Notes</h4>';
                html += '<ul>';
                suggestions.warnings.forEach(warning => {
                    html += `<li>${warning}</li>`;
                });
                html += '</ul></div>';
            }
        }
        
        html += '</div>';
        return html;
    }
    
    displayError(message) {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;
        
        statusDiv.className = 'status-message error';
        statusDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Error:</strong> ${message}
        `;
    }
    
    // Preview functionality
    async showPreview() {
        if (!this.hasRequiredSelections()) {
            alert('Please select your skin type and at least one active ingredient to generate a preview.');
            return;
        }
        
        const previewBtn = document.getElementById('previewBtn');
        const originalText = previewBtn.innerHTML;
        
        // Show loading state
        previewBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Generating Preview...';
        previewBtn.disabled = true;
        
        try {
            const previewData = this.preparePreviewData();
            const response = await this.generatePDFPreview(previewData);
            
            if (response.success) {
                this.displayPDFPreview(response);
            } else {
                this.displayError('Failed to generate PDF preview. Please try again.');
            }
            
        } catch (error) {
            console.error('Preview error:', error);
            this.displayError('An error occurred while generating the preview.');
        } finally {
            previewBtn.innerHTML = originalText;
            previewBtn.disabled = false;
        }
    }
    
    preparePreviewData() {
        return {
            formulation: {
                skinType: this.formData.skinType,
                baseFormat: this.formData.baseFormat,
                keyActives: this.formData.keyActives,
                extracts: this.formData.extracts,
                boosters: this.formData.boosters
            },
            contact: {
                fullName: 'Preview User',
                email: 'preview@example.com'
            }
        };
    }
    
    async generatePDFPreview(data) {
        const response = await fetch('/api/preview_pdf.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        return await response.json();
    }
    
    displayPDFPreview(response) {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;
        
        statusDiv.className = 'status-message formulation-results';
        statusDiv.innerHTML = this.buildPDFPreviewHTML(response);
        
        // Scroll to the preview
        statusDiv.scrollIntoView({ behavior: 'smooth' });
    }
    
    buildPDFPreviewHTML(response) {
        let html = '<div class="pdf-preview-section">';
        html += '<h3><i class="fas fa-file-pdf"></i> PDF Formulation Preview</h3>';
        
        // AI Suggestions Summary
        if (response.ai_suggestions) {
            const suggestions = response.ai_suggestions;
            
            if (suggestions.formulation_name) {
                html += `<h4 class="formulation-name">${suggestions.formulation_name}</h4>`;
            }
            
            // Expected benefits
            if (suggestions.expected_benefits) {
                html += '<div class="preview-section">';
                html += '<h5><i class="fas fa-star"></i> Expected Benefits</h5>';
                html += '<ul class="benefits-preview">';
                suggestions.expected_benefits.forEach(benefit => {
                    html += `<li>${benefit}</li>`;
                });
                html += '</ul></div>';
            }
            
            // Recommended percentages
            if (suggestions.recommended_percentages) {
                html += '<div class="preview-section">';
                html += '<h5><i class="fas fa-calculator"></i> AI-Recommended Concentrations</h5>';
                html += '<div class="percentage-preview">';
                Object.entries(suggestions.recommended_percentages).forEach(([ingredient, range]) => {
                    html += `<div class="percentage-item-preview">
                        <strong>${this.formatDisplayName(ingredient)}:</strong> 
                        ${range.recommended || 1.0}% 
                        <small>(Range: ${range.min || 0.1}% - ${range.max || 5.0}%)</small>
                    </div>`;
                });
                html += '</div></div>';
            }
        }
        
        // Ingredient descriptions
        if (response.ingredient_descriptions) {
            html += '<div class="preview-section">';
            html += '<h5><i class="fas fa-info-circle"></i> Ingredient Descriptions</h5>';
            
            const descriptions = response.ingredient_descriptions;
            
            if (descriptions.actives && Object.keys(descriptions.actives).length > 0) {
                html += '<div class="ingredient-category">';
                html += '<h6>Active Ingredients</h6>';
                Object.entries(descriptions.actives).forEach(([ingredient, description]) => {
                    html += `<div class="ingredient-desc">
                        <strong>${this.formatDisplayName(ingredient)}:</strong> ${description}
                    </div>`;
                });
                html += '</div>';
            }
            
            if (descriptions.extracts && Object.keys(descriptions.extracts).length > 0) {
                html += '<div class="ingredient-category">';
                html += '<h6>Botanical Extracts</h6>';
                Object.entries(descriptions.extracts).forEach(([ingredient, description]) => {
                    html += `<div class="ingredient-desc">
                        <strong>${this.formatDisplayName(ingredient)}:</strong> ${description}
                    </div>`;
                });
                html += '</div>';
            }
            
            if (descriptions.hydrators && Object.keys(descriptions.hydrators).length > 0) {
                html += '<div class="ingredient-category">';
                html += '<h6>Hydrators & Boosters</h6>';
                Object.entries(descriptions.hydrators).forEach(([ingredient, description]) => {
                    html += `<div class="ingredient-desc">
                        <strong>${this.formatDisplayName(ingredient)}:</strong> ${description}
                    </div>`;
                });
                html += '</div>';
            }
            
            html += '</div>';
        }
        
        // PDF Download Button
        if (response.pdf_preview && response.pdf_preview.available) {
            html += '<div class="preview-actions">';
            html += `<button class="pdf-download-btn" onclick="window.skincraftApp.downloadPDFPreview('${response.pdf_preview.content_base64}', '${response.pdf_preview.filename}')">
                <i class="fas fa-download"></i> Download PDF Preview
            </button>`;
            html += '</div>';
        }
        
        html += '<div class="preview-note">';
        html += '<p><i class="fas fa-info-circle"></i> This is a preview of your formulation. To receive the final formulation and complete analysis, please fill in your contact details and submit the form.</p>';
        html += '</div>';
        
        html += '</div>';
        return html;
    }
    
    downloadPDFPreview(base64Content, filename) {
        try {
            const binaryString = atob(base64Content);
            const bytes = new Uint8Array(binaryString.length);
            for (let i = 0; i < binaryString.length; i++) {
                bytes[i] = binaryString.charCodeAt(i);
            }
            
            const blob = new Blob([bytes], { type: 'application/pdf' });
            const url = URL.createObjectURL(blob);
            
            const a = document.createElement('a');
            a.href = url;
            a.download = filename || 'formulation_preview.pdf';
            document.body.appendChild(a);
            a.click();
            document.body.removeChild(a);
            URL.revokeObjectURL(url);
        } catch (error) {
            console.error('Download error:', error);
            alert('Failed to download PDF. Please try again.');
        }
    }
    
    // Utility functions
    getCheckedValues(name) {
        return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
            .map(input => input.value);
    }
    
    formatDisplayName(value) {
        if (!value) return '';
        return value.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }
    
    hasRequiredSelections() {
        return this.formData.skinType.length > 0 && 
               this.formData.keyActives.length > 0 &&
               this.formData.contact.fullName &&
               this.formData.contact.email;
    }
    
    showError(elementId, message) {
        const errorEl = document.getElementById(elementId);
        if (errorEl) {
            errorEl.textContent = message;
            errorEl.classList.add('show');
        }
    }
    
    clearError(elementId) {
        const errorEl = document.getElementById(elementId);
        if (errorEl) {
            errorEl.classList.remove('show');
        }
    }
    
    displayFieldError(fieldName, message, show) {
        const errorEl = document.getElementById(`${fieldName}Error`);
        if (errorEl) {
            errorEl.textContent = message;
            if (show) {
                errorEl.classList.add('show');
            } else {
                errorEl.classList.remove('show');
            }
        }
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    window.skincraftApp = new SkinCraftApp();
});