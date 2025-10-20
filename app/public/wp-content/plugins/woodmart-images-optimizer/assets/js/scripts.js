/**
 * WoodMart Images Optimizer
 * Modern, modular image optimization JavaScript
 */
(function($) {
    'use strict';

         // Configuration constants
     const CONFIG = {
         SELECTORS: {
             optimizeButton: '.xts-optimizer-button',
             restoreButton: '.xts-restore-button',
             bulkProgress: '#bulk-progress',
             bulkProgressBar: '#bulk-progress-bar',
             bulkProgressContainer: '#bulk-progress-bar'
         },
         CLASSES: {
             optimizationError: 'optimization-error',
             optimizationSuccess: 'optimization-success',
             optimizationCompleted: 'optimization-completed',
             processing: 'processing',
             restoreSuccess: 'restore-success',
             restoreError: 'restore-error'
         },
         TIMEOUTS: {
             buttonRenable: 2000,
             bulkDelay: 1000,
             pageReload: 2000,
             restoreTransition: 800
         },
         MESSAGES: {
             optimizing: 'Optimizing...',
             restoring: 'Restoring...',
             optimized: 'Optimized!',
             restored: 'Restored!',
             optimize: 'Optimize',
             error: 'Error',
             timeout: 'Timeout',
             requestFailed: 'Request failed',
             connectionError: 'Connection error',
             restoreFailed: 'Restore failed'
         },
         OPTIONS: {
             preventReOptimization: true, // Set to false to allow re-optimization
             debugMode: false // Set to true for console debugging
         }
     };

    /**
     * Base AJAX handler class
     */
    class AjaxHandler {
        constructor() {
            this.activeRequests = new Set();
        }

        /**
         * Make AJAX request with consistent error handling
         */
        async request(options) {
            const requestId = Math.random().toString(36).substr(2, 9);
            this.activeRequests.add(requestId);

            const defaultOptions = {
                url: xts_optimizer.ajax_url,
                type: 'POST',
                data: {
                    nonce: xts_optimizer.nonce
                }
            };

            const mergedOptions = { ...defaultOptions, ...options };
            
            return new Promise((resolve, reject) => {
                $.ajax({
                    ...mergedOptions,
                    success: (response) => {
                        this.activeRequests.delete(requestId);
                        resolve(response);
                    },
                    error: (xhr, status, error) => {
                        this.activeRequests.delete(requestId);
                        reject({ xhr, status, error });
                    }
                });
            });
        }

        /**
         * Cancel all active requests
         */
        cancelAllRequests() {
            this.activeRequests.clear();
        }
    }

    /**
     * Button state manager
     */
    class ButtonStateManager {
        constructor(button) {
            this.button = $(button);
            this.originalText = this.button.text();
            this.timeoutId = null;
        }

        setLoading(loadingText) {
            this.button.prop('disabled', true).text(loadingText);
            return this;
        }

        setSuccess(successText, cssClass) {
            this.button.text(successText).addClass(cssClass);
            return this;
        }

        setError(errorText, cssClass, tooltipText = null) {
            this.button.text(errorText).addClass(cssClass);
            if (tooltipText) {
                this.button.attr('title', tooltipText);
            }
            return this;
        }

                 enableAfterDelay(delay = CONFIG.TIMEOUTS.buttonRenable) {
             if (this.timeoutId) {
                 clearTimeout(this.timeoutId);
             }
             
             this.timeoutId = setTimeout(() => {
                 // Don't re-enable if optimization was completed successfully or still processing
                 if (!this.button.hasClass(CONFIG.CLASSES.optimizationCompleted) && 
                     !this.button.hasClass(CONFIG.CLASSES.processing)) {
                     this.button.prop('disabled', false);
                 }
                 this.timeoutId = null;
             }, delay);
             return this;
         }

        destroy() {
            if (this.timeoutId) {
                clearTimeout(this.timeoutId);
            }
        }
    }

    /**
     * Individual image optimizer
     */
    class ImageOptimizer extends AjaxHandler {
        constructor() {
            super();
            this.bindEvents();
        }

        bindEvents() {
            $(document).on('click', CONFIG.SELECTORS.optimizeButton, this.handleOptimize.bind(this));
        }

                 async handleOptimize(event) {
             event.preventDefault();
             
             const button = $(event.currentTarget);
             const imageId = button.data('id');

             // Prevent optimization if button is disabled or already processing
             if (button.prop('disabled')) {
                 return;
             }

             // Prevent optimization if already completed (if option is enabled)
             if (CONFIG.OPTIONS.preventReOptimization && button.hasClass(CONFIG.CLASSES.optimizationCompleted)) {
                 return;
             }

             // Prevent multiple simultaneous optimizations of the same image
             if (button.hasClass(CONFIG.CLASSES.processing)) {
                 return;
             }

             const buttonManager = new ButtonStateManager(button);

             if (!imageId) {
                 buttonManager.setError(CONFIG.MESSAGES.error, CONFIG.CLASSES.optimizationError, 'Invalid image ID');
                 return;
             }

             // Mark as processing and disable immediately
             button.addClass(CONFIG.CLASSES.processing);
             buttonManager.setLoading(CONFIG.MESSAGES.optimizing);

                         try {
                 const response = await this.request({
                     data: {
                         action: 'xts_optimizer_run',
                         image_id: imageId,
                         nonce: xts_optimizer.nonce
                     }
                 });

                 this.handleOptimizeResponse(response, buttonManager, imageId);
             } catch (error) {
                 this.handleOptimizeError(error, buttonManager);
             } finally {
                 // Remove processing class
                 button.removeClass(CONFIG.CLASSES.processing);
                 
                 // Only re-enable if optimization didn't complete successfully
                 if (!CONFIG.OPTIONS.preventReOptimization || !button.hasClass(CONFIG.CLASSES.optimizationCompleted)) {
                     buttonManager.enableAfterDelay();
                 }
             }
        }

        handleOptimizeResponse(response, buttonManager, imageId) {
            if (!response.success) {
                buttonManager.setError(CONFIG.MESSAGES.requestFailed, CONFIG.CLASSES.optimizationError);
                return;
            }

            const { data } = response;
            const { result } = data;

            if (result.error) {
                this.handleApiError(result, buttonManager);
            } else {
                this.handleOptimizeSuccess(result, buttonManager, imageId);
            }
        }

        handleApiError(result, buttonManager) {
            const errorText = result.message?.includes('timed out') 
                ? CONFIG.MESSAGES.timeout 
                : CONFIG.MESSAGES.error;
            
            const fullErrorText = `${errorText}: ${result.message || 'Optimization failed'}`;
            const tooltipText = `Error: ${result.message || 'Unknown error'}`;
            
            buttonManager.setError(fullErrorText, CONFIG.CLASSES.optimizationError, tooltipText);
        }

                 handleOptimizeSuccess(result, buttonManager, imageId) {
             let displayText = CONFIG.MESSAGES.optimized;

             // Add compression info
             const compressionPercentage = result.compression_percentage || 
                 result.optimization?.compression_ratio;
             
             if (compressionPercentage) {
                 displayText += ` (${compressionPercentage}% smaller)`;
             }

             // Add replacement status
             if (result.replacement_error === false) {
                 displayText += ' & Replaced';
             } else if (result.replacement_error === true) {
                 displayText = 'Optimized (Replace Failed)';
             }

             buttonManager.setSuccess(displayText, CONFIG.CLASSES.optimizationSuccess);

             // Disable the button permanently after successful optimization (if option is enabled)
             if (CONFIG.OPTIONS.preventReOptimization) {
                 buttonManager.button.prop('disabled', true).addClass(CONFIG.CLASSES.optimizationCompleted);
             }

             // Add restore button if backup was created
             if (result.backup_created) {
                 this.addRestoreButton(buttonManager.button, imageId, result.backup_filename);
             }
         }

        addRestoreButton(button, imageId, backupFilename) {
            const restoreButton = $(`
                <br>
                <a href="#" class="xts-restore-button" 
                   data-id="${imageId}" 
                   title="Restore from: ${backupFilename || 'backup'}">
                   Restore Backup
                </a>
            `);
            button.parent().append(restoreButton);
        }

        handleOptimizeError(error, buttonManager) {
            const errorMessage = error.error || 'Connection error';
            const tooltipText = `Connection error: ${errorMessage}`;
            buttonManager.setError(CONFIG.MESSAGES.connectionError, CONFIG.CLASSES.optimizationError, tooltipText);
        }
    }

    /**
     * Image restore handler
     */
    class ImageRestorer extends AjaxHandler {
        constructor() {
            super();
            this.bindEvents();
        }

        bindEvents() {
            $(document).on('click', CONFIG.SELECTORS.restoreButton, this.handleRestore.bind(this));
        }

                 async handleRestore(event) {
             event.preventDefault();
             
             const button = $(event.currentTarget);
             const imageId = button.data('id');

             // Prevent restore if button is disabled or already processing
             if (button.prop('disabled') || button.hasClass(CONFIG.CLASSES.processing)) {
                 return;
             }

             if (!this.confirmRestore()) {
                 return;
             }

             if (!imageId) {
                 this.showRestoreError(button, 'Invalid image ID');
                 return;
             }

             // Mark as processing and disable immediately
             button.addClass(CONFIG.CLASSES.processing);
             
             const buttonManager = new ButtonStateManager(button);
             buttonManager.setLoading(CONFIG.MESSAGES.restoring);

            try {
                const response = await this.request({
                    data: {
                        action: 'xts_optimizer_restore',
                        image_id: imageId,
                        nonce: xts_optimizer.nonce
                    }
                });

                                 this.handleRestoreResponse(response, buttonManager, imageId);
             } catch (error) {
                 this.handleRestoreError(error, buttonManager);
             } finally {
                 // Remove processing class
                 button.removeClass(CONFIG.CLASSES.processing);
                 buttonManager.enableAfterDelay();
             }
        }

        confirmRestore() {
            return confirm('Are you sure you want to restore the original image? This will replace the optimized version.');
        }

                 handleRestoreResponse(response, buttonManager, imageId) {
             if (response.success) {
                 buttonManager.setSuccess(CONFIG.MESSAGES.restored, CONFIG.CLASSES.restoreSuccess);
                 
                 // Delay the UI updates slightly for better user feedback
                 setTimeout(() => {
                     this.resetOptimizeButton(imageId);
                     this.removeOptimizationInfo(imageId);
                     this.removeRestoreButton(buttonManager.button);
                 }, CONFIG.TIMEOUTS.restoreTransition);
             } else {
                 const errorMessage = response.data || 'Unknown error';
                 buttonManager.setError(CONFIG.MESSAGES.restoreFailed, CONFIG.CLASSES.restoreError, `Error: ${errorMessage}`);
             }
         }

        handleRestoreError(error, buttonManager) {
            const errorMessage = error.error || 'Connection error';
            buttonManager.setError(CONFIG.MESSAGES.connectionError, CONFIG.CLASSES.restoreError, `Connection error: ${errorMessage}`);
        }

                 schedulePageReload() {
             // Legacy method - no longer used since we reset buttons instead of reloading
             setTimeout(() => {
                 window.location.reload();
             }, CONFIG.TIMEOUTS.pageReload);
         }

         showRestoreError(button, message) {
             const buttonManager = new ButtonStateManager(button);
             buttonManager.setError(CONFIG.MESSAGES.restoreFailed, CONFIG.CLASSES.restoreError, message);
         }

         resetOptimizeButton(imageId) {
             if (CONFIG.OPTIONS.debugMode) {
                 console.log('Resetting optimize button for image ID:', imageId);
             }
             
             // Find the optimize button for this image
             let optimizeButton = $(CONFIG.SELECTORS.optimizeButton).filter(`[data-id="${imageId}"]`);
             
             if (optimizeButton.length) {
                 if (CONFIG.OPTIONS.debugMode) {
                     console.log('Found existing optimize button, resetting state');
                 }
                 // Reset existing button
                 optimizeButton.fadeOut(200, function() {
                     // Reset button to initial state
                     $(this)
                         .prop('disabled', false)
                         .removeClass([
                             CONFIG.CLASSES.optimizationSuccess,
                             CONFIG.CLASSES.optimizationError,
                             CONFIG.CLASSES.optimizationCompleted,
                             CONFIG.CLASSES.processing
                         ].join(' '))
                         .attr('title', '') // Clear any error tooltips
                         .text(CONFIG.MESSAGES.optimize) // Reset to original text
                         .fadeIn(200); // Fade back in with new state
                 });
             } else {
                 if (CONFIG.OPTIONS.debugMode) {
                     console.log('No existing optimize button found, creating new one');
                 }
                 
                 // No optimize button exists, create a new one
                 // Find the container for this image's optimizer buttons
                 const buttonsContainer = $(`.xts-optimizer-buttons`).has(`[data-id="${imageId}"]`);
                 
                 if (buttonsContainer.length) {
                     if (CONFIG.OPTIONS.debugMode) {
                         console.log('Found buttons container, adding new optimize button');
                     }
                     // Create a new optimize button
                     const newOptimizeButton = $(`
                         <a href="#" class="xts-optimizer-button" data-id="${imageId}">
                             ${CONFIG.MESSAGES.optimize}
                         </a>
                     `);
                     
                     // Add it to the beginning of the container with animation
                     newOptimizeButton.hide().prependTo(buttonsContainer).fadeIn(400);
                     
                     // Note: Event binding is handled by event delegation in bindEvents(), so no need to rebind
                 }
             }
         }

         removeRestoreButton(restoreButton) {
             // Remove the restore button and its preceding <br> tag with animation
             const $restoreButton = $(restoreButton);
             const $prevBr = $restoreButton.prev('br');
             
             $restoreButton.fadeOut(300, function() {
                 if ($prevBr.length) {
                     $prevBr.remove();
                 }
                 $restoreButton.remove();
             });
         }

         removeOptimizationInfo(imageId) {
             // Find and remove the optimization info display for this image
             const buttonsContainer = $(`.xts-optimizer-buttons`).has(`[data-id="${imageId}"]`);
             
             if (buttonsContainer.length) {
                 // Find and remove optimization info within the same parent container
                 const optimizationInfo = buttonsContainer.siblings('.xts-optimization-info').add(
                     buttonsContainer.parent().find('.xts-optimization-info')
                 );
                 
                 if (optimizationInfo.length) {
                     optimizationInfo.fadeOut(300, function() {
                         $(this).remove();
                     });
                 }
             }
         }
    }

    /**
     * Bulk optimization handler
     */
    class BulkOptimizer extends AjaxHandler {
        constructor() {
            super();
            this.isProcessing = false;
            this.processed = 0;
            this.errors = 0;
            this.successes = 0;
        }

        initialize() {
            if (typeof window.woodmartBulkOptimize === 'undefined') {
                return;
            }

            this.batchId = window.woodmartBulkOptimize.batch_id;
            this.total = window.woodmartBulkOptimize.total;
            this.resetCounters();
            this.startProcessing();
        }

        resetCounters() {
            this.processed = 0;
            this.errors = 0;
            this.successes = 0;
        }

        async startProcessing() {
            if (this.isProcessing) {
                return;
            }

            this.isProcessing = true;
            await this.processBatch(0);
        }

        async processBatch(offset) {
            try {
                const response = await this.request({
                    data: {
                        action: 'xts_optimizer_bulk',
                        batch_id: this.batchId,
                        offset: offset,
                        nonce: xts_optimizer.nonce
                    }
                });

                this.handleBatchResponse(response);
            } catch (error) {
                this.handleBatchError();
            }
        }

        handleBatchResponse(response) {
            if (!response.success) {
                this.showBatchError(response.data || 'Unknown error');
                return;
            }

            const { data } = response;
            this.processed = data.processed;

            // Count results
            data.results.forEach(result => {
                if (result.success) {
                    this.successes++;
                } else {
                    this.errors++;
                }
            });

            this.updateProgress(data.progress_percentage);

            if (data.complete) {
                this.handleBatchCompletion();
            } else {
                this.scheduleContinuation();
            }
        }

        updateProgress(progressPercentage) {
            $(CONFIG.SELECTORS.bulkProgress).text(this.processed);
            $(`${CONFIG.SELECTORS.bulkProgressBar} div`).css('width', `${progressPercentage}%`);
        }

        handleBatchCompletion() {
            const message = this.buildCompletionMessage();
            const progressContainer = $(CONFIG.SELECTORS.bulkProgressBar).parent();
            progressContainer.html(`<p style="color: green;"><strong>${message}</strong></p>`);
            this.schedulePageRedirect();
        }

        buildCompletionMessage() {
            let message = `Bulk optimization complete! ${this.successes} images optimized`;
            if (this.errors > 0) {
                message += `, ${this.errors} errors occurred`;
            }
            return message;
        }

        scheduleContinuation() {
            setTimeout(() => {
                this.processBatch(this.processed);
            }, CONFIG.TIMEOUTS.bulkDelay);
        }

        schedulePageRedirect() {
            setTimeout(() => {
                const url = window.location.href.replace(/[?&](bulk_optimize|image_count)=[^&]*/g, '');
                window.location.href = url;
            }, CONFIG.TIMEOUTS.pageReload);
        }

        showBatchError(errorMessage) {
            const progressContainer = $(CONFIG.SELECTORS.bulkProgressBar).parent();
            progressContainer.html(`<p style="color: red;"><strong>Error: ${errorMessage}</strong></p>`);
        }

        handleBatchError() {
            const progressContainer = $(CONFIG.SELECTORS.bulkProgressBar).parent();
            progressContainer.html('<p style="color: red;"><strong>Network error occurred during bulk optimization.</strong></p>');
        }
    }

    /**
     * Main application controller
     */
    class WoodMartOptimizer {
        constructor() {
            this.optimizer = new ImageOptimizer();
            this.restorer = new ImageRestorer();
            this.bulkOptimizer = new BulkOptimizer();
        }

        initialize() {
            this.bulkOptimizer.initialize();
        }

        destroy() {
            // Clean up any resources
            this.optimizer.cancelAllRequests();
            this.restorer.cancelAllRequests();
            this.bulkOptimizer.cancelAllRequests();
        }
    }

    // Initialize when DOM is ready
    $(document).ready(() => {
        const app = new WoodMartOptimizer();
        app.initialize();

        // Clean up on page unload
        $(window).on('beforeunload', () => {
            app.destroy();
        });
    });

})(jQuery);