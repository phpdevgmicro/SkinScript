/**
 * Skincare Formulation App - Modern JavaScript Implementation
 * Clean, modular architecture for better maintainability
 */

class SkincareApp {
    constructor() {
        // App configuration
        this.config = {
            maxKeyActives: 3,
            apiEndpoint: '/api/submit_formulation.php',
            storageKey: 'skincareFormData'
        };

        // App state
        this.state = {
            formData: {
                skinType: [],
                baseFormat: 'mist',
                keyActives: [],
                extracts: [],
                boosters: [],
                contact: {}
            },
            selectedKeyActives: 0,
            isSubmitting: false
        };

        // Ingredient compatibility rules
        this.incompatibleCombinations = [
            ['retinol', 'vitamin-c'],
            ['retinol', 'niacinamide'],
            ['vitamin-c', 'niacinamide']
        ];

        this.init();
    }

    // Initialization
    init() {
        this.bindEvents();
        this.loadSavedData();
        this.updateUI();
        console.log('Skincare Formulation App initialized');
    }

    // Event Binding
    bindEvents() {
        this.bindFormEvents();
        this.bindSectionEvents();
        this.bindInputEvents();
    }

    bindFormEvents() {
        const form = document.getElementById('formulationForm');
        form?.addEventListener('submit', (e) => this.handleFormSubmit(e));

        const previewBtn = document.getElementById('previewBtn');
        previewBtn?.addEventListener('click', () => this.previewFormulation());
    }

    bindSectionEvents() {
        // Bind events for each form section
        this.bindSkinTypeEvents();
        this.bindBaseFormatEvents();
        this.bindKeyActivesEvents();
        this.bindExtractsEvents();
        this.bindBoostersEvents();
        this.bindContactEvents();
    }

    bindSkinTypeEvents() {
        const inputs = document.querySelectorAll('input[name="skinType"]');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.handleSkinTypeChange());
        });
    }

    bindBaseFormatEvents() {
        const inputs = document.querySelectorAll('input[name="baseFormat"]');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.handleBaseFormatChange());
        });
    }

    bindKeyActivesEvents() {
        const inputs = document.querySelectorAll('input[name="keyActives"]');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.handleKeyActivesChange());
        });
    }

    bindExtractsEvents() {
        const inputs = document.querySelectorAll('input[name="extracts"]');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.handleExtractsChange());
        });
    }

    bindBoostersEvents() {
        const inputs = document.querySelectorAll('input[name="boosters"]');
        inputs.forEach(input => {
            input.addEventListener('change', () => this.handleBoostersChange());
        });
    }

    bindContactEvents() {
        const inputs = document.querySelectorAll('#contactSection input, #contactSection textarea');
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateContactField(input));
            input.addEventListener('input', () => this.handleContactChange());
        });
    }

    bindInputEvents() {
        // Global form change listener
        document.addEventListener('change', () => {
            this.updateUI();
            this.saveFormData();
        });
    }

    // Event Handlers
    handleSkinTypeChange() {
        const selectedTypes = this.getSelectedValues('skinType');
        this.state.formData.skinType = selectedTypes;
        
        this.updateSidebar('skinTypeItems', selectedTypes, 'Select your skin type');
        this.clearError('skinTypeError');
        this.addVisualFeedback('skinTypeSection');
    }

    handleBaseFormatChange() {
        const selected = document.querySelector('input[name="baseFormat"]:checked');
        this.state.formData.baseFormat = selected ? selected.value : '';
        
        const formatArray = selected ? [selected.value] : [];
        this.updateSidebar('baseFormatItems', formatArray, 'Choose format');
        this.addVisualFeedback('baseFormatSection');
    }

    handleKeyActivesChange() {
        const selectedActives = this.getSelectedValues('keyActives');
        this.state.formData.keyActives = selectedActives;
        this.state.selectedKeyActives = selectedActives.length;
        
        this.updateKeyActivesUI();
        this.updateSidebar('keyActivesItems', selectedActives, 'Select up to 3 actives');
        this.checkCompatibility();
        this.clearError('keyActivesError');
        this.addVisualFeedback('keyActivesSection');
    }

    handleExtractsChange() {
        const selectedExtracts = this.getSelectedValues('extracts');
        this.state.formData.extracts = selectedExtracts;
        
        this.updateSidebar('extractsItems', selectedExtracts, 'Add botanical extracts');
        this.addVisualFeedback('extractsSection');
    }

    handleBoostersChange() {
        const selectedBoosters = this.getSelectedValues('boosters');
        this.state.formData.boosters = selectedBoosters;
        
        this.updateSidebar('boostersItems', selectedBoosters, 'Add hydrating boosters');
        this.addVisualFeedback('boostersSection');
    }

    handleContactChange() {
        this.state.formData.contact = {
            fullName: document.getElementById('fullName')?.value || '',
            email: document.getElementById('email')?.value || '',
            skinConcerns: document.getElementById('skinConcerns')?.value || ''
        };
        this.updateUI();
    }

    // UI Updates
    updateUI() {
        this.updateSubmitButton();
        this.updatePreviewButton();
    }

    updateKeyActivesUI() {
        this.updateActivesCounter();
        this.toggleActivesAvailability();
    }

    updateActivesCounter() {
        const counter = document.getElementById('activesCounter');
        const sidebarCounter = document.getElementById('activesCounterSidebar');
        const count = `${this.state.selectedKeyActives}/${this.config.maxKeyActives}`;
        
        if (counter) counter.textContent = count;
        if (sidebarCounter) sidebarCounter.textContent = count;
    }

    toggleActivesAvailability() {
        const inputs = document.querySelectorAll('input[name="keyActives"]');
        inputs.forEach(input => {
            input.disabled = !input.checked && this.state.selectedKeyActives >= this.config.maxKeyActives;
        });
    }

    updateSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;
        
        const hasRequired = this.hasRequiredSelections();
        submitBtn.disabled = !hasRequired || this.state.isSubmitting;
    }

    updatePreviewButton() {
        const previewBtn = document.getElementById('previewBtn');
        if (!previewBtn) return;
        
        previewBtn.disabled = !this.hasValidSelections();
    }

    updateSidebar(containerId, items, placeholder) {
        const container = document.getElementById(containerId);
        if (!container) return;

        if (items.length === 0) {
            container.innerHTML = `<span class="placeholder">${placeholder}</span>`;
        } else {
            const tags = items.map(item => {
                const displayName = this.formatDisplayName(item);
                return `<span class="ingredient-tag" data-ingredient="${item}" title="Remove ${displayName}">
                    ${displayName}
                    <button class="remove-ingredient" onclick="skincareApp.removeIngredient('${containerId}', '${item}')">&times;</button>
                </span>`;
            }).join('');
            container.innerHTML = tags;
        }
    }

    removeIngredient(containerId, ingredient) {
        // Determine which form section this belongs to
        const sectionMap = {
            'skinTypeItems': 'skinType',
            'keyActivesItems': 'keyActives', 
            'extractsItems': 'extracts',
            'boostersItems': 'boosters'
        };
        
        const sectionName = sectionMap[containerId];
        if (!sectionName) return;
        
        // Uncheck the corresponding form element
        const checkbox = document.getElementById(ingredient);
        if (checkbox) {
            checkbox.checked = false;
            // Trigger the appropriate handler
            checkbox.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    addVisualFeedback(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        
        section.classList.add('fade-in');
        setTimeout(() => section.classList.remove('fade-in'), 500);
    }

    // Validation
    validateForm() {
        let isValid = true;
        const errors = [];

        // Validate required selections
        if (this.state.formData.skinType.length === 0) {
            this.showError('skinTypeError', 'Please select at least one skin type');
            isValid = false;
        }

        if (this.state.formData.keyActives.length === 0) {
            this.showError('keyActivesError', 'Please select at least one key ingredient');
            isValid = false;
        }

        // Validate contact fields
        const nameField = document.getElementById('fullName');
        const emailField = document.getElementById('email');
        
        if (!this.validateContactField(nameField) || !this.validateContactField(emailField)) {
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

    // Compatibility Checking
    checkCompatibility() {
        const warnings = this.getCompatibilityWarnings();
        this.displayCompatibilityWarnings(warnings);
    }

    getCompatibilityWarnings() {
        const selectedActives = this.state.formData.keyActives;
        return this.incompatibleCombinations
            .filter(combo => combo.every(ingredient => selectedActives.includes(ingredient)))
            .map(combo => `${this.formatDisplayName(combo[0])} and ${this.formatDisplayName(combo[1])} may not be compatible when used together.`);
    }

    displayCompatibilityWarnings(warnings) {
        // Remove existing warnings
        document.querySelectorAll('.compatibility-warning').forEach(el => el.remove());
        
        if (warnings.length === 0) return;

        const section = document.getElementById('keyActivesSection');
        if (!section) return;

        const warningDiv = document.createElement('div');
        warningDiv.className = 'compatibility-warning show';
        warningDiv.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <strong>Compatibility Notice:</strong>
            <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                ${warnings.map(warning => `<li>${warning}</li>`).join('')}
            </ul>
        `;
        section.appendChild(warningDiv);
    }

    // Form Submission
    async handleFormSubmit(event) {
        event.preventDefault();
        
        if (!this.validateForm()) {
            this.scrollToFirstError();
            return;
        }
        
        this.state.isSubmitting = true;
        this.showSubmissionState(true);
        
        try {
            const submissionData = this.prepareSubmissionData();
            const response = await this.submitToBackend(submissionData);
            
            if (response.formulation) {
                this.displayResults(response.formulation, response.pdf);
            } else {
                this.displaySuccessMessage();
            }
            
            this.clearSavedData();
            this.scrollToStatus();
            
        } catch (error) {
            console.error('Submission error:', error);
            this.displayErrorMessage();
        } finally {
            this.state.isSubmitting = false;
            this.showSubmissionState(false);
        }
    }

    showSubmissionState(isSubmitting) {
        const submitBtn = document.getElementById('submitBtn');
        if (!submitBtn) return;
        
        if (isSubmitting) {
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        } else {
            submitBtn.innerHTML = 'Submit Request';
        }
        
        this.updateSubmitButton();
    }

    prepareSubmissionData() {
        return {
            timestamp: new Date().toISOString(),
            formulation: {
                skinType: this.state.formData.skinType,
                baseFormat: this.state.formData.baseFormat,
                keyActives: this.state.formData.keyActives,
                extracts: this.state.formData.extracts,
                boosters: this.state.formData.boosters
            },
            contact: this.state.formData.contact,
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`,
            formVersion: '2.0'
        };
    }

    async submitToBackend(data) {
        const response = await fetch(this.config.apiEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(data)
        });
        
        const responseData = await response.json();
        
        if (!response.ok) {
            throw new Error(responseData.error || 'Network response was not ok');
        }
        
        return responseData;
    }

    // Display Results
    displayResults(formulation, pdfInfo) {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;

        statusDiv.className = 'status-message formulation-results';
        statusDiv.innerHTML = this.buildResultsHTML(formulation, pdfInfo);
    }

    buildResultsHTML(formulation, pdfInfo) {
        const formulaRows = Object.entries(formulation.formula || {})
            .map(([ingredient, percentage]) => 
                `<tr><td>${this.formatDisplayName(ingredient)}</td><td>${percentage}%</td></tr>`
            ).join('');

        const recommendations = formulation.recommendations?.length > 0 ? 
            `<div class="recommendations-section">
                <h4><i class="fas fa-lightbulb"></i> Usage Recommendations:</h4>
                <ul>${formulation.recommendations.map(rec => `<li>${rec}</li>`).join('')}</ul>
            </div>` : '';

        const pdfSection = pdfInfo?.success ? 
            `<div class="pdf-section">
                <h4><i class="fas fa-file-pdf"></i> Your Formulation Document</h4>
                <p>Download your personalized formulation guide:</p>
                <a href="${pdfInfo.download_url}" class="pdf-download-btn" target="_blank">
                    <i class="fas fa-download"></i> Download PDF${pdfInfo.fallback ? ' (HTML Format)' : ''}
                </a>
            </div>` : '';

        return `
            <div class="formulation-header">
                <i class="fas fa-flask"></i>
                <h3>${formulation.title}</h3>
                <div class="profile-badge">${formulation.profile}</div>
            </div>
            <div class="formulation-description">
                <p>${formulation.description}</p>
            </div>
            <div class="formulation-breakdown">
                <h4><i class="fas fa-list-ul"></i> Formulation Breakdown (% w/w)</h4>
                <table class="formula-table">
                    <thead><tr><th>Ingredient</th><th>Percentage</th></tr></thead>
                    <tbody>${formulaRows}</tbody>
                </table>
            </div>
            ${recommendations}
            ${pdfSection}
            <div class="formulation-footer">
                <p><i class="fas fa-info-circle"></i> Formulation ID: ${formulation.formulation_id}</p>
                <p>Generated on ${formulation.generated_at}</p>
            </div>
        `;
    }

    displaySuccessMessage() {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;

        statusDiv.className = 'status-message success';
        statusDiv.innerHTML = `
            <i class="fas fa-check-circle"></i>
            <strong>Request Submitted Successfully!</strong><br>
            We'll review your custom formulation and get back to you within 24 hours at ${this.state.formData.contact.email}.
        `;
    }

    displayErrorMessage() {
        const statusDiv = document.getElementById('submissionStatus');
        if (!statusDiv) return;

        statusDiv.className = 'status-message error';
        statusDiv.innerHTML = `
            <i class="fas fa-exclamation-circle"></i>
            <strong>Submission Failed</strong><br>
            Please try again. If the problem persists, contact support.
        `;
    }

    // Data Management
    saveFormData() {
        try {
            localStorage.setItem(this.config.storageKey, JSON.stringify(this.state.formData));
        } catch (error) {
            console.warn('Failed to save form data:', error);
        }
    }

    loadSavedData() {
        try {
            const saved = localStorage.getItem(this.config.storageKey);
            if (saved) {
                const data = JSON.parse(saved);
                this.restoreFormState(data);
            }
        } catch (error) {
            console.warn('Failed to load saved data:', error);
        }
    }

    restoreFormState(data) {
        // Restore form selections
        this.restoreSelections('skinType', data.skinType);
        this.restoreRadioSelection('baseFormat', data.baseFormat);
        this.restoreSelections('keyActives', data.keyActives);
        this.restoreSelections('extracts', data.extracts);
        this.restoreSelections('boosters', data.boosters);
        this.restoreContactInfo(data.contact);

        // Update state
        this.state.formData = { ...this.state.formData, ...data };
        this.state.selectedKeyActives = data.keyActives?.length || 0;

        // Update sidebar to reflect restored data
        this.updateAllSidebarSections();
        
        // Update UI
        this.updateUI();
        this.updateKeyActivesUI();
        
        // Check compatibility for restored actives
        this.checkCompatibility();
    }

    updateAllSidebarSections() {
        // Update all sidebar sections with current form data
        this.updateSidebar('skinTypeItems', this.state.formData.skinType, 'Select your skin type');
        
        const formatArray = this.state.formData.baseFormat ? [this.state.formData.baseFormat] : [];
        this.updateSidebar('baseFormatItems', formatArray, 'Choose format');
        
        this.updateSidebar('keyActivesItems', this.state.formData.keyActives, 'Select up to 3 actives');
        this.updateSidebar('extractsItems', this.state.formData.extracts, 'Add botanical extracts');
        this.updateSidebar('boostersItems', this.state.formData.boosters, 'Add hydrating boosters');
        
        // Update counters
        const count = `${this.state.selectedKeyActives}/${this.config.maxKeyActives}`;
        const sidebarCounter = document.getElementById('activesCounterSidebar');
        if (sidebarCounter) sidebarCounter.textContent = count;
    }

    restoreSelections(name, values) {
        if (!Array.isArray(values)) return;
        values.forEach(value => {
            const checkbox = document.getElementById(value);
            if (checkbox) checkbox.checked = true;
        });
    }

    restoreRadioSelection(name, value) {
        if (!value) return;
        const radio = document.getElementById(value);
        if (radio) radio.checked = true;
    }

    restoreContactInfo(contact) {
        if (!contact) return;
        Object.entries(contact).forEach(([field, value]) => {
            const input = document.getElementById(field);
            if (input) input.value = value;
        });
    }

    clearSavedData() {
        try {
            localStorage.removeItem(this.config.storageKey);
        } catch (error) {
            console.warn('Failed to clear saved data:', error);
        }
    }

    // Utility Functions
    getSelectedValues(name) {
        return Array.from(document.querySelectorAll(`input[name="${name}"]:checked`))
            .map(input => input.value);
    }

    hasRequiredSelections() {
        return this.state.formData.skinType.length > 0 && 
               this.state.formData.keyActives.length > 0 &&
               this.state.formData.contact.fullName &&
               this.state.formData.contact.email;
    }

    hasValidSelections() {
        return this.state.formData.skinType.length > 0 || 
               this.state.formData.baseFormat || 
               this.state.formData.keyActives.length > 0 || 
               this.state.formData.extracts.length > 0 || 
               this.state.formData.boosters.length > 0;
    }

    formatDisplayName(ingredient) {
        let formatted = ingredient.replace(/-/g, ' ');
        formatted = formatted.replace(/\b\w/g, l => l.toUpperCase());
        
        const replacements = {
            'L Carnitine': 'L-Carnitine',
            'Beta Vulgaris': 'Beta Vulgaris (Beet Root)',
            'Avena Sativa': 'Avena Sativa (Oat)',
            'Green Tea': 'Green Tea Extract',
            'Sodium Pca': 'Sodium PCA',
            'Copper Peptides': 'Copper Peptides'
        };
        
        return replacements[formatted] || formatted;
    }

    showError(elementId, message) {
        const element = document.getElementById(elementId);
        if (element) {
            element.textContent = message;
            element.classList.add('show');
        }
    }

    clearError(elementId) {
        const element = document.getElementById(elementId);
        if (element) {
            element.classList.remove('show');
            element.textContent = '';
        }
    }

    displayFieldError(fieldName, message, show) {
        const errorElement = document.getElementById(`${fieldName}Error`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.toggle('show', show);
        }
    }

    scrollToFirstError() {
        const firstError = document.querySelector('.error-message.show');
        firstError?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    scrollToStatus() {
        const statusDiv = document.getElementById('submissionStatus');
        statusDiv?.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    // Preview functionality
    previewFormulation() {
        console.log('Preview formulation:', this.state.formData);
        // This can be expanded later for preview functionality
    }
}

// Initialize the app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.skincareApp = new SkincareApp();
});