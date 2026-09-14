(function ($) {
    if (!$('.bdt-setup-wizard').length) {
        return;
    }

    $(document).ready(function () {
        const wizard = {
            currentStep: 0,
            steps: document.querySelectorAll('.bdt-wizard-step'),
            nextButtons: document.querySelectorAll('.bdt-wizard-next'),
            prevButtons: document.querySelectorAll('.bdt-wizard-prev'),
            progressItems: document.querySelectorAll('.bdt-wizard-progress-item'),
            progressBar: document.getElementById('plugin-install-progress'),
            categorySelect: document.getElementById('category-select'),
            searchInput: document.querySelector('.widget-search'),
            activateAllButton: document.querySelector('.bulk-action.activate'),
            deactivateAllButton: document.querySelector('.bulk-action.deactivate'),
            saveButton: document.getElementById('save-and-continue'),
            widgetList: document.querySelector('.widget-list'),
            installButton: document.getElementById('pg-install-plugins-btn'),
            filterButtons: document.querySelectorAll('.filter-button'),

            init: function () {
                this.setupStepAttributes();
                this.showStep(this.currentStep);
                this.updateProgress(this.currentStep);
                this.bindEvents();
                this.initAnimations();
            },

            setupStepAttributes: function() {
                const stepNames = ['welcome', 'features', 'integration', 'finish'];
                this.steps.forEach((step, index) => {
                    if (!step.hasAttribute('data-step') && index < stepNames.length) {
                        step.setAttribute('data-step', stepNames[index]);
                    }
                });
            },

            initAnimations: function() {
                const currentStep = this.steps[this.currentStep];
                if (currentStep) {
                    setTimeout(() => {
                        currentStep.classList.add('active');
                        currentStep.style.display = 'block'; // Ensure display is set to block
                    }, 100);
                }
            },

            bindEvents: function () {
                const self = this;
                
                this.progressItems.forEach((item, index) => {
                    item.addEventListener('click', () => {
                        const stepName = item.getAttribute('data-step');
                        if (index <= this.getCompletedStepIndex()) {
                            this.goToStep(index);
                        }
                    });
                });

                $(document).on('click', '.bdt-wizard-next', function(e) {
                    e.preventDefault();
                    const targetStep = $(this).data('step');
                    
                    // No loader or timeout - immediate transition
                    if (targetStep) {
                        for (let i = 0; i < self.steps.length; i++) {
                            if ($(self.steps[i]).data('step') === targetStep) {
                                self.goToStep(i);
                                return;
                            }
                        }
                        
                        const stepIndex = self.getStepIndexByName(targetStep);
                        if (stepIndex !== -1) {
                            self.goToStep(stepIndex);
                            return;
                        }
                    }
                    
                    self.goToStep(self.currentStep + 1);
                });

                this.prevButtons.forEach(button => {
                    button.addEventListener('click', () => {
                        // No loader or timeout - immediate transition
                        const targetStep = button.getAttribute('data-step');
                        if (targetStep) {
                            const stepIndex = this.getStepIndexByName(targetStep);
                            if (stepIndex !== -1) {
                                this.goToStep(stepIndex);
                            }
                        } else if (this.currentStep > 0) {
                            this.goToStep(this.currentStep - 1);
                        }
                    });
                });

                if (this.filterButtons) {
                    this.filterButtons.forEach(button => {
                        button.addEventListener('click', () => {
                            this.filterButtons.forEach(btn => btn.classList.remove('active'));
                            button.classList.add('active');
                            this.filterItemsByType(button.dataset.filter);
                        });
                    });
                }

                if (this.categorySelect) {
                    this.categorySelect.addEventListener('change', this.filterWidgets.bind(this));
                }

                if (this.searchInput) {
                    this.searchInput.addEventListener('input', this.searchWidgets.bind(this));
                }

                if (this.activateAllButton) {
                    this.activateAllButton.addEventListener('click', this.activateAllWidgets.bind(this));
                }
                if (this.deactivateAllButton) {
                    this.deactivateAllButton.addEventListener('click', this.deactivateAllWidgets.bind(this));
                }

                this.saveSettingsSubmit();
                this.installPlugins();
                this.onChangedPluginSliderCheckbox();
                
                const featureItems = document.querySelectorAll('.bdt-feature-item');
                featureItems.forEach(item => {
                    item.addEventListener('mouseenter', () => {
                        item.style.transform = 'translateY(-8px)';
                    });
                    item.addEventListener('mouseleave', () => {
                        item.style.transform = 'translateY(-3px)';
                    });
                });
            },

            getStepIndexByName: function(stepName) {
                for (let i = 0; i < this.steps.length; i++) {
                    if (this.steps[i].getAttribute('data-step') === stepName) {
                        return i;
                    }
                }
                
                const stepMap = {
                    'welcome': 0,
                    'features': 1,
                    'integration': 2,
                    'finish': 3
                };
                
                return stepName in stepMap ? stepMap[stepName] : -1;
            },

            getCompletedStepIndex: function() {
                return this.currentStep;
            },

            goToStep: function(stepIndex) {
                if (stepIndex >= 0 && stepIndex < this.steps.length) {
                    this.currentStep = stepIndex;
                    this.showStep(this.currentStep);
                    this.updateProgress(this.currentStep);
                }
            },

            getSelectedPluginSlugs: function () {
                return $('#pg-install-plugins .plugin-slider-checkbox:checked')
                    .map(function () { return $(this).val(); })
                    .get()
                    .filter(Boolean);
            },

            pluginSliderCheckbox: function(){
                const pluginSlugs = this.getSelectedPluginSlugs();

                if(pluginSlugs.length){
                    $("#pg-install-plugins-btn").removeClass('d-none').addClass('pulse-animation');
                    $("#pg-activate-consent-wrap").removeClass('d-none');
                }else{
                    $("#pg-install-plugins-btn").addClass('d-none').removeClass('pulse-animation');
                    $("#pg-activate-consent-wrap").addClass('d-none');
                    // Activation consent never carries over to an empty selection.
                    $("#pg-activate-after-install").prop('checked', false);
                }
            },

            onChangedPluginSliderCheckbox: function(){
                const vm = this;
                $('#pg-install-plugins').on('change', '.plugin-slider-checkbox', function (e) {
                    vm.pluginSliderCheckbox();
                    
                    const pluginItem = $(this).closest('.plugin-item');
                    pluginItem.addClass('item-highlight');
                    setTimeout(() => {
                        pluginItem.removeClass('item-highlight');
                    }, 600);
                });
            },

            showStep: function (step) {
                // Hide all steps first
                this.steps.forEach((stepElement) => {
                    stepElement.classList.remove('active');
                    stepElement.style.display = 'none'; // Ensure inactive steps are completely hidden
                });

                // Immediately show the current step without delay
                const currentStep = this.steps[step];
                if (currentStep) {
                    currentStep.classList.add('active');
                    currentStep.style.display = 'block'; // Make sure active step is visible
                }

                this.pluginSliderCheckbox();
            },

            filterItemsByType: function(type) {
                const items = document.querySelectorAll('.feature-item, .plugin-item');
                
                items.forEach(item => {
                    if (type === 'all' || item.dataset.type === type) {
                        item.style.display = 'flex';
                        setTimeout(() => {
                            item.style.opacity = '1';
                            item.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        item.style.opacity = '0';
                        item.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            item.style.display = 'none';
                        }, 300);
                    }
                });
            },

            filterWidgets: function () {
                const selectedCategory = this.categorySelect.value;
                const widgets = this.widgetList.querySelectorAll('li');

                widgets.forEach(widget => {
                    const types = widget.dataset.type.split(/\s+/);
                    if (selectedCategory === 'all' || types.includes(selectedCategory)) {
                        widget.style.display = 'block';
                        setTimeout(() => {
                            widget.style.opacity = '1';
                            widget.style.transform = 'translateY(0)';
                        }, 50);
                    } else {
                        widget.style.opacity = '0';
                        widget.style.transform = 'translateY(10px)';
                        setTimeout(() => {
                            widget.style.display = 'none';
                        }, 300);
                    }
                });
            },

            updateProgress: function (step) {
                this.progressItems.forEach((item, index) => {
                    if (index < step) {
                        item.classList.remove('active');
                        item.classList.add('completed');
                    } else if (index === step) {
                        item.classList.add('active');
                        item.classList.remove('completed');
                    } else {
                        item.classList.remove('active', 'completed');
                    }
                });
            },

            searchWidgets: function () {
                const searchTerm = this.searchInput.value.toLowerCase();
                const widgets = this.widgetList.querySelectorAll('li');

                widgets.forEach(widget => {
                    if (widget.dataset.label.toLowerCase().includes(searchTerm)) {
                        widget.style.display = 'block';
                        setTimeout(() => {
                            widget.style.opacity = '1';
                        }, 50);
                    } else {
                        widget.style.opacity = '0';
                        setTimeout(() => {
                            widget.style.display = 'none';
                        }, 300);
                    }
                });
            },

            activateAllWidgets: function (event) {
                event.preventDefault();
                const checkboxes = this.widgetList.querySelectorAll('input[type="checkbox"]');
                
                this.activateAllButton.classList.add('button-pulse');
                setTimeout(() => {
                    this.activateAllButton.classList.remove('button-pulse');
                }, 500);
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = true;
                    const widgetItem = checkbox.closest('li');
                    widgetItem.classList.add('item-highlight');
                    setTimeout(() => {
                        widgetItem.classList.remove('item-highlight');
                    }, 600);
                });
            },

            deactivateAllWidgets: function (event) {
                event.preventDefault();
                const checkboxes = this.widgetList.querySelectorAll('input[type="checkbox"]');
                
                this.deactivateAllButton.classList.add('button-pulse');
                setTimeout(() => {
                    this.deactivateAllButton.classList.remove('button-pulse');
                }, 500);
                
                checkboxes.forEach(checkbox => {
                    checkbox.checked = false;
                    const widgetItem = checkbox.closest('li');
                    widgetItem.classList.add('item-highlight');
                    setTimeout(() => {
                        widgetItem.classList.remove('item-highlight');
                    }, 600);
                });
            },

            saveSettingsSubmit: function () {
                const vm = this;
                $('#pg_setup_wizard_modules').submit(function (e) {
                    e.preventDefault();
                    var data = $(this).serialize();
                    
                    // Get the button and store original text
                    const saveBtn = $(this).find('#save-and-continue');
                    const originalText = saveBtn.html().trim();
                    
                    // Show loading state
                    saveBtn.prop('disabled', true)
                         .html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Saving...');
                    
                    $.ajax({
                        url: BDT_SetupWizard.ajax_url,
                        type: 'POST',
                        data: data,
                        success: function (response) {
                            if (response.success) {
                                saveBtn.html('<i class="dashicons dashicons-yes-alt"></i> Saved!');
                                setTimeout(() => {
                                    vm.goToStep(vm.currentStep + 1);
                                    // Reset button state
                                    saveBtn.prop('disabled', false).html(originalText);
                                }, 1000);
                            } else {
                                saveBtn.html('<i class="dashicons dashicons-no"></i> Failed');
                                setTimeout(() => {
                                    saveBtn.prop('disabled', false).html(originalText);
                                }, 1000);
                                alert('Failed to save settings');
                            }
                        },
                        error: function (error) {
                            saveBtn.html('<i class="dashicons dashicons-no"></i> Error');
                            setTimeout(() => {
                                saveBtn.prop('disabled', false).html(originalText);
                            }, 1000);
                            console.error('Error:', error);
                        }
                    });
                });
            },

            installPlugins: function () {
                const vm = this;
                $('#pg-install-plugins').submit(function (e) {
                    e.preventDefault();
                    
                    vm.installButton.disabled = true;
                    vm.installButton.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Installing...';
                    
                    const pluginSlugs = vm.getSelectedPluginSlugs();

                    // Activation is a second, separate opt-in. When it is not
                    // ticked the plugins are only downloaded, never switched on.
                    const activateAfterInstall = $('#pg-activate-after-install').is(':checked') ? '1' : '0';

                    let installedPlugins = 0;
                    const totalPluginsSlug = pluginSlugs.length;
                    
                    let progressContainer = document.querySelector('.install-progress-container');
                    if (!progressContainer) {
                        progressContainer = document.createElement('div');
                        progressContainer.className = 'install-progress-container';
                        progressContainer.innerHTML = `
                            <div class="progress-bar-wrapper">
                                <div id="plugin-install-progress" class="progress-bar" style="width: 0%">0%</div>
                            </div>
                            <div class="install-status">Preparing to install plugins...</div>
                        `;
                        vm.installButton.parentNode.appendChild(progressContainer);
                    }
                    
                    const progressBar = document.getElementById('plugin-install-progress');
                    const statusText = document.querySelector('.install-status');

                    const updateProgressBar = () => {
                        const progress = (installedPlugins / totalPluginsSlug) * 100;
                        progressBar.style.width = `${progress}%`;
                        progressBar.textContent = `${Math.round(progress)}%`;
                        statusText.textContent = `Installing plugin ${installedPlugins} of ${totalPluginsSlug}...`;
                    };

                    const installNextPlugin = () => {
                        if (installedPlugins < totalPluginsSlug) {
                            const slug = pluginSlugs[installedPlugins];
                            const pluginName = slug.split('/')[0].split('-').map(word => word.charAt(0).toUpperCase() + word.slice(1)).join(' ');

                            statusText.textContent = `Installing ${pluginName}...`;
                            
                            jQuery.ajax({
                                url: BDT_SetupWizard.ajax_url,
                                method: 'POST',
                                data: {
                                    action: 'bdtpg_setup_wizard_install_plugins',
                                    nonce: BDT_SetupWizard.nonce,
                                    plugins: [slug],
                                    activate: activateAfterInstall
                                },
                                success: (response) => {
                                    if (response.success) {
                                        installedPlugins++;
                                        updateProgressBar();
                                        
                                        statusText.innerHTML += ` <span class="success-indicator"><i class="dashicons dashicons-yes-alt"></i></span>`;
                                        
                                        const pluginItem = document.querySelector(`[data-slug="${slug}"]`);
                                        if (pluginItem) {
                                            pluginItem.classList.add('plugin-installed');
                                        }
                                        
                                        setTimeout(() => {
                                            installNextPlugin();
                                        }, 500);
                                    } else {
                                        statusText.innerHTML += ` <span class="error-indicator"><i class="dashicons dashicons-no"></i> Failed</span>`;
                                        installedPlugins++;
                                        updateProgressBar();
                                        setTimeout(() => {
                                            installNextPlugin();
                                        }, 500);
                                    }
                                },
                                error: (error) => {
                                    statusText.innerHTML += ` <span class="error-indicator"><i class="dashicons dashicons-no"></i> Error</span>`;
                                    installedPlugins++;
                                    updateProgressBar();
                                    setTimeout(() => {
                                        installNextPlugin();
                                    }, 500);
                                }
                            });
                        } else {
                            statusText.textContent = activateAfterInstall === '1'
                                ? 'All selected plugins were installed and activated.'
                                : 'All selected plugins were installed. Activate them from the Plugins screen whenever you are ready.';
                            statusText.innerHTML += ' <span class="success-indicator"><i class="dashicons dashicons-yes-alt"></i></span>';
                            
                            setTimeout(() => {
                                vm.installButton.disabled = false;
                                vm.installButton.innerHTML = 'Installation Complete';
                                vm.goToStep(vm.currentStep + 1);
                            }, 1500);
                        }
                    };

                    installNextPlugin();
                });
            }
        };

        wizard.init();
    });


})(jQuery);

document.addEventListener('DOMContentLoaded', function() {
    const style = document.createElement('style');
    style.textContent = `
        .bdt-wizard-step {
            transition: opacity 0.3s ease, transform 0.3s ease;
            display: none; /* Hide all steps by default */
        }
        
        .bdt-wizard-step.active {
            display: block; /* Show only active step */
            opacity: 1;
            transform: translateY(0);
        }
        
        .item-highlight {
            transition: all 0.3s ease;
            box-shadow: 0 0 0 2px var(--pg-primary);
            transform: translateY(-3px);
        }
        
        .button-pulse {
            animation: buttonPulse 0.5s ease;
        }
        
        .pulse-animation {
            animation: pulse 1.5s infinite;
        }
        
        /* Spinner for plugin installation and template importing */
        .spinner {
            display: inline-block;
            width: 16px;
            height: 16px;
            border: 2px solid rgba(255,255,255,0.3);
            border-radius: 50%;
            border-top-color: #fff;
            animation: spin 1s linear infinite;
            margin-right: 8px;
        }
        
        /* Plugin installation progress styles */
        .install-progress-container {
            margin-top: 20px;
            padding: 15px;
            background: var(--pg-gray-light);
            border-radius: var(--pg-border-radius);
        }
        
        .progress-bar-wrapper {
            height: 8px;
            background: var(--pg-gray-medium);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 10px;
        }
        
        .progress-bar {
            height: 100%;
            background: var(--pg-primary);
            border-radius: 4px;
            transition: width 0.3s ease;
            color: transparent;
            font-size: 0;
        }
        
        .install-status {
            font-size: 14px;
            color: var(--pg-text-light);
        }
        
        .success-indicator {
            color: var(--pg-success);
        }
        
        .error-indicator {
            color: var(--pg-danger);
        }
        
        .plugin-installed {
            border-color: var(--pg-success) !important;
        }
        
        /* Animations */
        @keyframes buttonPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.05); }
        }
        
        @keyframes pulse {
            0% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(108, 92, 231, 0); }
            100% { box-shadow: 0 0 0 0 rgba(108, 92, 231, 0); }
        }
        
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
    `;
    document.head.appendChild(style);
});
