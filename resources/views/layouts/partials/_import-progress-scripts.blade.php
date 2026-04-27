<script>
document.addEventListener('DOMContentLoaded', function() {
    // We use jQuery here because it's already loaded and handles AJAX headers/FormData more robustly in this environment
    $(document).on('submit', 'form[action*="import"]', function(e) {
        const $form = $(this);
        const fileInput = $form.find('input[type="file"]')[0];
        
        // Only handle if it's an Excel import modal
        if (!fileInput || $form.data('handling-progress') === true) return;

        e.preventDefault();

        // Validate form
        if (this.checkValidity() === false) {
            $form.addClass('was-validated');
            return;
        }

        const $submitBtn = $form.find('button[type="submit"]');
        const originalBtnContent = $submitBtn.html();
        const $modalBody = $form.find('.modal-body');
        const $modalFooter = $form.find('.modal-footer');

        // UI Feedback
        $submitBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-1"></span> Uploading...');
        
        const formData = new FormData(this);

        $.ajax({
            url: $form.attr('action'),
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(data) {
                if (data.success && data.task_id) {
                    startProgressPolling(data.task_id, $modalBody, $modalFooter, $submitBtn, originalBtnContent, $form);
                } else {
                    showError(data.message || 'Import failed to start');
                    resetUI();
                }
            },
            error: function(xhr) {
                let message = 'Something went wrong while starting the import.';
                try {
                    const response = JSON.parse(xhr.responseText);
                    message = response.message || message;
                    if (response.errors) {
                        const firstError = Object.values(response.errors)[0][0];
                        message = firstError;
                    }
                } catch (e) {
                    // If not JSON, show a snippet of the HTML or status text
                    console.error('Non-JSON error response:', xhr.responseText);
                    message = "Server Error (" + xhr.status + "): " + (xhr.statusText || 'Internal Server Error');
                }
                showError(message);
                resetUI();
            }
        });

        function resetUI() {
            $submitBtn.prop('disabled', false).html(originalBtnContent);
        }

        function showError(msg) {
            Swal.fire({
                icon: 'error',
                title: 'Import Issue',
                text: msg,
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 6000
            });
        }
    });

    function startProgressPolling(taskId, $container, $footer, $btn, originalContent, $form) {
        // Create Progress UI
        const progressHtml = `
            <div id="import-progress-container" class="mt-4 p-3 bg-light rounded border">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <h6 class="mb-0 fw-bold text-primary">
                        <i class="mdi mdi-sync mdi-spin me-1"></i> Processing Import...
                    </h6>
                    <span id="progress-percentage" class="badge bg-primary">0%</span>
                </div>
                <div class="progress mb-2" style="height: 12px;">
                    <div id="progress-bar" class="progress-bar progress-bar-striped progress-bar-animated bg-primary" 
                         role="progressbar" style="width: 0%;" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"></div>
                </div>
                <div class="d-flex justify-content-between small text-muted">
                    <span id="progress-status">Initializing...</span>
                    <span id="progress-count">0 / 0 rows</span>
                </div>
            </div>
        `;

        // Hide form fields
        const $formElements = $container.find('.mb-3, .alert, .d-flex');
        $formElements.hide();
        $container.append(progressHtml);
        $footer.hide();

        const $bar = $('#progress-bar');
        const $percentageText = $('#progress-percentage');
        const $statusText = $('#progress-status');
        const $countText = $('#progress-count');

        let pollInterval = setInterval(() => {
            $.ajax({
                url: `/warehouse/imports/progress/${taskId}`,
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' },
                success: function(task) {
                    const percent = task.percentage || 0;
                    $bar.css('width', percent + '%').attr('aria-valuenow', percent);
                    $percentageText.text(percent + '%');
                    $countText.text(`${task.processed_rows} / ${task.total_rows} rows`);
                    $statusText.text(task.status_message || 'Processing...');

                    if (task.status === 'completed') {
                        clearInterval(pollInterval);
                        $statusText.text('Finalizing...');
                        
                        setTimeout(() => {
                            Swal.fire({
                                icon: 'success',
                                title: 'Import Successful',
                                text: 'All records have been imported successfully.',
                                confirmButtonText: 'Great!'
                            }).then(() => {
                                window.location.reload();
                            });
                        }, 500);
                    } else if (task.status === 'failed') {
                        clearInterval(pollInterval);
                        $statusText.text('Failed');
                        $bar.removeClass('bg-primary').addClass('bg-danger');
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Import Failed',
                            text: task.error_message || 'An error occurred during processing.',
                        });

                        setTimeout(() => {
                            $('#import-progress-container').remove();
                            $formElements.show();
                            $footer.show();
                            $btn.prop('disabled', false).html(originalContent);
                        }, 4000);
                    }
                },
                error: function() {
                    console.error('Polling error');
                }
            });
        }, 2000);
    }
});
</script>
