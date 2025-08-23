/**
 * Skincare Formulation App - Main JavaScript File
 * Handles form validation, ingredient compatibility, and user interactions
 */

class SkincareFormulationApp {
    constructor() {
        this.maxKeyActives = 3;
        this.selectedKeyActives = 0;
        this.formData = {
            skinType: [],
            baseFormat: '',
            keyActives: [],
            extracts: [],
            boosters: [],
            contact: {}
        };
        
        // Ingredient compatibility rules
        this.incompatibleCombinations = [
            ['retinol', 'vitamin-c'],
            ['retinol', 'niacinamide'],
            ['vitamin-c', 'niacinamide']
        ];
        
        this.init();
    }

    init() {
        this.bindEventListeners();
        this.loadSavedData();
        this.updateFormState();
        console.log('Skincare Formulation App initialized');
    }

    bindEventListeners() {
        // Form submission
        const form = document.getElementById('formulationForm');
        form.addEventListener('submit', (e) => this.handleFormSubmit(e));

        // Skin type checkboxes (multiple selection allowed)
        const skinTypeInputs = document.querySelectorAll('input[name="skinType"]');
        skinTypeInputs.forEach(input => {
            input.addEventListener('change', () => this.handleSkinTypeChange());
        });

        // Base format radio buttons
        const baseFormatInputs = document.querySelectorAll('input[name="baseFormat"]');
        baseFormatInputs.forEach(input => {
            input.addEventListener('change', () => this.handleBaseFormatChange());
        });

        // Key actives checkboxes with limit
        const keyActiveInputs = document.querySelectorAll('input[name="keyActives"]');
        keyActiveInputs.forEach(input => {
            input.addEventListener('change', () => this.handleKeyActivesChange());
        });

        // Extract checkboxes
        const extractInputs = document.querySelectorAll('input[name="extracts"]');
        extractInputs.forEach(input => {
            input.addEventListener('change', () => this.handleExtractsChange());
        });

        // Booster checkboxes
        const boosterInputs = document.querySelectorAll('input[name="boosters"]');
        boosterInputs.forEach(input => {
            input.addEventListener('change', () => this.handleBoostersChange());
        });

        // Contact form inputs
        const contactInputs = document.querySelectorAll('#contactSection input, #contactSection textarea');
        contactInputs.forEach(input => {
            input.addEventListener('blur', () => this.validateContactField(input));
            input.addEventListener('input', () => this.handleContactChange());
        });

        // Real-time form validation
        document.addEventListener('change', () => {
            this.updateFormState();
            this.saveFormData();
        });
    }

    handleSkinTypeChange() {
        const selectedTypes = Array.from(document.querySelectorAll('input[name="skinType"]:checked'))
            .map(input => input.value);
        
        this.formData.skinType = selectedTypes;
        this.clearError('skinTypeError');
        this.updatePreview();
        
        // Add visual feedback
        this.addSelectionFeedback('skinTypeSection');
    }

    handleBaseFormatChange() {
        const selectedFormat = document.querySelector('input[name="baseFormat"]:checked');
        this.formData.baseFormat = selectedFormat ? selectedFormat.value : '';
        this.updatePreview();
        
        this.addSelectionFeedback('baseFormatSection');
    }

    handleKeyActivesChange() {
        const selectedActives = Array.from(document.querySelectorAll('input[name="keyActives"]:checked'))
            .map(input => input.value);
        
        this.selectedKeyActives = selectedActives.length;
        this.formData.keyActives = selectedActives;
        
        // Update counter
        const counter = document.getElementById('activesCounter');
        counter.textContent = `(${this.selectedKeyActives}/${this.maxKeyActives} selected)`;
        
        // Disable/enable checkboxes based on limit
        this.toggleKeyActivesAvailability();
        
        // Check for incompatible combinations
        this.checkIngredientCompatibility();
        
        this.clearError('keyActivesError');
        this.updatePreview();
        this.addSelectionFeedback('keyActivesSection');
    }

    handleExtractsChange() {
        const selectedExtracts = Array.from(document.querySelectorAll('input[name="extracts"]:checked'))
            .map(input => input.value);
        
        this.formData.extracts = selectedExtracts;
        this.updatePreview();
        this.addSelectionFeedback('extractsSection');
    }

    handleBoostersChange() {
        const selectedBoosters = Array.from(document.querySelectorAll('input[name="boosters"]:checked'))
            .map(input => input.value);
        
        this.formData.boosters = selectedBoosters;
        this.updatePreview();
        this.addSelectionFeedback('boostersSection');
    }

    handleContactChange() {
        const fullName = document.getElementById('fullName').value;
        const email = document.getElementById('email').value;
        const skinConcerns = document.getElementById('skinConcerns').value;
        
        this.formData.contact = {
            fullName,
            email,
            skinConcerns
        };
        
        this.updateFormState();
    }

    toggleKeyActivesAvailability() {
        const keyActiveInputs = document.querySelectorAll('input[name="keyActives"]');
        const keyActiveCards = document.querySelectorAll('[data-type="key-active"]');
        
        keyActiveInputs.forEach((input, index) => {
            const card = keyActiveCards[index];
            
            if (!input.checked && this.selectedKeyActives >= this.maxKeyActives) {
                card.classList.add('disabled');
                input.disabled = true;
            } else {
                card.classList.remove('disabled');
                input.disabled = false;
            }
        });
    }

    checkIngredientCompatibility() {
        const selectedActives = this.formData.keyActives;
        const warnings = [];
        
        this.incompatibleCombinations.forEach(combination => {
            if (combination.every(ingredient => selectedActives.includes(ingredient))) {
                warnings.push(`${this.formatIngredientName(combination[0])} and ${this.formatIngredientName(combination[1])} may not be compatible when used together.`);
            }
        });
        
        this.displayCompatibilityWarnings(warnings);
    }

    displayCompatibilityWarnings(warnings) {
        // Remove existing warnings
        const existingWarnings = document.querySelectorAll('.compatibility-warning');
        existingWarnings.forEach(warning => warning.remove());
        
        if (warnings.length > 0) {
            const keyActivesSection = document.getElementById('keyActivesSection');
            const warningDiv = document.createElement('div');
            warningDiv.className = 'compatibility-warning show';
            warningDiv.innerHTML = `
                <i class="fas fa-exclamation-triangle"></i>
                <strong>Compatibility Notice:</strong>
                <ul style="margin-top: 0.5rem; margin-left: 1.5rem;">
                    ${warnings.map(warning => `<li>${warning}</li>`).join('')}
                </ul>
            `;
            keyActivesSection.appendChild(warningDiv);
        }
    }

    formatIngredientName(ingredient) {
        return ingredient.split('-').map(word => 
            word.charAt(0).toUpperCase() + word.slice(1)
        ).join(' ');
    }

    validateContactField(field) {
        const value = field.value.trim();
        const fieldName = field.name;
        let isValid = true;
        let errorMessage = '';
        
        switch (fieldName) {
            case 'fullName':
                if (!value) {
                    isValid = false;
                    errorMessage = 'Full name is required';
                } else if (value.length < 2) {
                    isValid = false;
                    errorMessage = 'Name must be at least 2 characters long';
                }
                break;
                
            case 'email':
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!value) {
                    isValid = false;
                    errorMessage = 'Email address is required';
                } else if (!emailRegex.test(value)) {
                    isValid = false;
                    errorMessage = 'Please enter a valid email address';
                }
                break;
        }
        
        this.displayFieldError(fieldName, errorMessage, !isValid);
        return isValid;
    }

    displayFieldError(fieldName, message, show) {
        const errorElement = document.getElementById(`${fieldName}Error`);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.toggle('show', show);
        }
    }

    validateForm() {
        let isValid = true;
        const errors = [];
        
        // Validate skin type selection
        if (this.formData.skinType.length === 0) {
            errors.push('Please select at least one skin type');
            this.showError('skinTypeError', 'Please select at least one skin type');
            isValid = false;
        }
        
        // Validate key actives selection
        if (this.formData.keyActives.length === 0) {
            errors.push('Please select at least one key active ingredient');
            this.showError('keyActivesError', 'Please select at least one key active ingredient');
            isValid = false;
        }
        
        // Validate contact information
        const fullNameValid = this.validateContactField(document.getElementById('fullName'));
        const emailValid = this.validateContactField(document.getElementById('email'));
        
        if (!fullNameValid || !emailValid) {
            isValid = false;
        }
        
        return isValid;
    }

    showError(elementId, message) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.classList.add('show');
        }
    }

    clearError(elementId) {
        const errorElement = document.getElementById(elementId);
        if (errorElement) {
            errorElement.classList.remove('show');
            errorElement.textContent = '';
        }
    }

    updatePreview() {
        const previewContent = document.getElementById('previewContent');
        
        if (this.hasAnySelections()) {
            const previewHTML = this.generatePreviewHTML();
            previewContent.innerHTML = previewHTML;
        } else {
            previewContent.innerHTML = '<p class="empty-state">Make your selections above to see your custom formulation preview</p>';
        }
    }

    hasAnySelections() {
        return this.formData.skinType.length > 0 || 
               this.formData.baseFormat || 
               this.formData.keyActives.length > 0 || 
               this.formData.extracts.length > 0 || 
               this.formData.boosters.length > 0;
    }

    generatePreviewHTML() {
        let html = '';
        
        if (this.formData.skinType.length > 0) {
            html += `
                <div class="preview-section">
                    <h4>Skin Type:</h4>
                    <div class="preview-list">
                        ${this.formData.skinType.map(type => 
                            `<span class="preview-item">${this.formatIngredientName(type)}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        if (this.formData.baseFormat) {
            html += `
                <div class="preview-section">
                    <h4>Base Format:</h4>
                    <div class="preview-list">
                        <span class="preview-item">${this.formatIngredientName(this.formData.baseFormat)}</span>
                    </div>
                </div>
            `;
        }
        
        if (this.formData.keyActives.length > 0) {
            html += `
                <div class="preview-section">
                    <h4>Key Active Ingredients:</h4>
                    <div class="preview-list">
                        ${this.formData.keyActives.map(active => 
                            `<span class="preview-item">${this.formatIngredientName(active)}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        if (this.formData.extracts.length > 0) {
            html += `
                <div class="preview-section">
                    <h4>Functional Extracts:</h4>
                    <div class="preview-list">
                        ${this.formData.extracts.map(extract => 
                            `<span class="preview-item">${this.formatIngredientName(extract)}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        if (this.formData.boosters.length > 0) {
            html += `
                <div class="preview-section">
                    <h4>Boosters & Hydrators:</h4>
                    <div class="preview-list">
                        ${this.formData.boosters.map(booster => 
                            `<span class="preview-item">${this.formatIngredientName(booster)}</span>`
                        ).join('')}
                    </div>
                </div>
            `;
        }
        
        return html;
    }

    updateFormState() {
        const submitBtn = document.getElementById('submitBtn');
        const hasMinimumSelections = this.formData.skinType.length > 0 && 
                                    this.formData.keyActives.length > 0 &&
                                    this.formData.contact.fullName &&
                                    this.formData.contact.email;
        
        submitBtn.disabled = !hasMinimumSelections;
    }

    addSelectionFeedback(sectionId) {
        const section = document.getElementById(sectionId);
        section.classList.add('fade-in');
        
        setTimeout(() => {
            section.classList.remove('fade-in');
        }, 500);
    }

    saveFormData() {
        try {
            localStorage.setItem('skincareFormData', JSON.stringify(this.formData));
        } catch (error) {
            console.warn('Failed to save form data to localStorage:', error);
        }
    }

    loadSavedData() {
        try {
            const savedData = localStorage.getItem('skincareFormData');
            if (savedData) {
                const parsedData = JSON.parse(savedData);
                this.restoreFormState(parsedData);
            }
        } catch (error) {
            console.warn('Failed to load saved form data:', error);
        }
    }

    restoreFormState(data) {
        // Restore skin type selections
        if (data.skinType) {
            data.skinType.forEach(type => {
                const checkbox = document.getElementById(type);
                if (checkbox) checkbox.checked = true;
            });
            this.formData.skinType = data.skinType;
        }
        
        // Restore base format selection
        if (data.baseFormat) {
            const radio = document.getElementById(data.baseFormat);
            if (radio) radio.checked = true;
            this.formData.baseFormat = data.baseFormat;
        }
        
        // Restore key actives selections
        if (data.keyActives) {
            data.keyActives.forEach(active => {
                const checkbox = document.getElementById(active);
                if (checkbox) checkbox.checked = true;
            });
            this.formData.keyActives = data.keyActives;
            this.selectedKeyActives = data.keyActives.length;
            this.toggleKeyActivesAvailability();
        }
        
        // Restore extracts selections
        if (data.extracts) {
            data.extracts.forEach(extract => {
                const checkbox = document.getElementById(extract);
                if (checkbox) checkbox.checked = true;
            });
            this.formData.extracts = data.extracts;
        }
        
        // Restore boosters selections
        if (data.boosters) {
            data.boosters.forEach(booster => {
                const checkbox = document.getElementById(booster);
                if (checkbox) checkbox.checked = true;
            });
            this.formData.boosters = data.boosters;
        }
        
        // Restore contact information
        if (data.contact) {
            if (data.contact.fullName) {
                document.getElementById('fullName').value = data.contact.fullName;
            }
            if (data.contact.email) {
                document.getElementById('email').value = data.contact.email;
            }
            if (data.contact.skinConcerns) {
                document.getElementById('skinConcerns').value = data.contact.skinConcerns;
            }
            this.formData.contact = data.contact;
        }
        
        // Update UI state
        this.updatePreview();
        this.updateFormState();
        
        // Update actives counter
        const counter = document.getElementById('activesCounter');
        counter.textContent = `(${this.selectedKeyActives}/${this.maxKeyActives} selected)`;
    }

    async handleFormSubmit(event) {
        event.preventDefault();
        
        if (!this.validateForm()) {
            this.scrollToFirstError();
            return;
        }
        
        const submitBtn = document.getElementById('submitBtn');
        const statusDiv = document.getElementById('submissionStatus');
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Submitting...';
        
        try {
            // Prepare submission data
            const submissionData = this.prepareSubmissionData();
            
            // Simulate API call (replace with actual backend integration)
            await this.simulateFormSubmission(submissionData);
            
            // Show success message
            statusDiv.className = 'submission-status success';
            statusDiv.innerHTML = `
                <i class="fas fa-check-circle"></i>
                <strong>Request Submitted Successfully!</strong><br>
                We'll review your custom formulation and get back to you within 24 hours at ${this.formData.contact.email}.
            `;
            
            // Clear form data from localStorage
            localStorage.removeItem('skincareFormData');
            
            // Scroll to success message
            statusDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
            
        } catch (error) {
            console.error('Form submission error:', error);
            statusDiv.className = 'submission-status error';
            statusDiv.innerHTML = `
                <i class="fas fa-exclamation-circle"></i>
                <strong>Submission Failed</strong><br>
                Please try again. If the problem persists, contact support.
            `;
        } finally {
            // Reset button state
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i> Submit Request';
        }
    }

    prepareSubmissionData() {
        const timestamp = new Date().toISOString();
        
        return {
            timestamp,
            formulation: {
                skinType: this.formData.skinType,
                baseFormat: this.formData.baseFormat,
                keyActives: this.formData.keyActives,
                extracts: this.formData.extracts,
                boosters: this.formData.boosters
            },
            contact: this.formData.contact,
            userAgent: navigator.userAgent,
            screenResolution: `${screen.width}x${screen.height}`,
            formVersion: '1.0'
        };
    }

    async simulateFormSubmission(data) {
        // Simulate network delay
        await new Promise(resolve => setTimeout(resolve, 2000));
        
        // Log submission data for development
        console.log('Form submission data:', data);
        
        // In a real application, this would be an actual API call:
        // const response = await fetch('/api/formulations', {
        //     method: 'POST',
        //     headers: {
        //         'Content-Type': 'application/json',
        //     },
        //     body: JSON.stringify(data)
        // });
        // 
        // if (!response.ok) {
        //     throw new Error('Network response was not ok');
        // }
        // 
        // return response.json();
        
        return { success: true, id: 'FORM_' + Date.now() };
    }

    scrollToFirstError() {
        const firstError = document.querySelector('.error-message.show');
        if (firstError) {
            firstError.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }
    }
}

// Initialize the application when the DOM is fully loaded
document.addEventListener('DOMContentLoaded', () => {
    window.skincareApp = new SkincareFormulationApp();
});

// Add some utility functions for enhanced user experience
document.addEventListener('DOMContentLoaded', () => {
    // Add smooth scrolling for anchor links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function (e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
    
    // Add keyboard navigation support
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Enter' && e.target.type === 'checkbox') {
            e.target.click();
        }
    });
    
    // Add focus management for accessibility
    const checkboxCards = document.querySelectorAll('.checkbox-card');
    checkboxCards.forEach(card => {
        card.addEventListener('keydown', (e) => {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                const input = card.querySelector('input');
                if (input && !input.disabled) {
                    input.click();
                }
            }
        });
        
        // Make cards focusable
        card.setAttribute('tabindex', '0');
    });
});

// Export for potential testing or external usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = SkincareFormulationApp;
}
