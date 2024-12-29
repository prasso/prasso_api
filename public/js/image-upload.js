// Initialize the upload form functionality
function initializeUploadForm() {
    const uploadForm = document.getElementById('uploadForm');
    if (!uploadForm) return; // Exit if form not found

    const submitButton = uploadForm.querySelector('button[type="submit"]');
    const fileInput = document.getElementById('image');
    const resizeCheckbox = document.getElementById('resize');
    const alertContainer = document.getElementById('alertContainer');
    const alertContent = document.getElementById('alertContent');
    const resizeDialog = document.getElementById('resizeDialog');
    const resizeMessage = document.getElementById('resizeMessage');
    let originalFiles = null;

    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // Set max file size to 8MB (slightly less than server's 8388608 bytes limit)
    const MAX_FILE_SIZE = 8 * 1024 * 1024; // 8MB in bytes
    const MAX_TOTAL_SIZE = 40 * 1024 * 1024; // 40MB total upload size
    const MAX_FILES = 10; // Maximum number of files that can be uploaded at once
    const TARGET_FILE_SIZE = 2 * 1024 * 1024; // 2MB target size for resized images
    const MAX_WIDTH = 1920; // Maximum width for resized images
    const MAX_HEIGHT = 1080; // Maximum height for resized images

    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Uploading...';
            document.body.style.cursor = 'wait';
        } else {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Upload Images';
            document.body.style.cursor = 'default';
        }
    }

    function showAlert(message, type) {
        alertContainer.classList.remove('hidden');
        alertContent.className = 'p-4 rounded-md ' + 
            (type === 'error' ? 'bg-red-50 text-red-700 border-l-4 border-red-500' :
             type === 'warning' ? 'bg-yellow-50 text-yellow-700 border-l-4 border-yellow-500' :
             'bg-green-50 text-green-700 border-l-4 border-green-500');
        alertContent.textContent = message;

        // Auto-hide success messages after 5 seconds
        if (type === 'success') {
            setTimeout(() => {
                alertContainer.classList.add('hidden');
            }, 5000);
        }
    }

    // Add file input change handler
    fileInput.addEventListener('change', function() {
        const files = Array.from(this.files);
        let totalSize = 0;
        let hasLargeFiles = false;

        if (files.length > MAX_FILES) {
            showAlert(`You can only upload up to ${MAX_FILES} files at once.`, 'error');
            this.value = ''; // Clear the file input
            submitButton.disabled = true;
            return;
        }

        // Calculate total size and check for large files
        files.forEach(file => {
            totalSize += file.size;
            if (file.size > MAX_FILE_SIZE) {
                hasLargeFiles = true;
            }
        });

        if (totalSize > MAX_TOTAL_SIZE) {
            showAlert(`Total file size (${(totalSize / (1024 * 1024)).toFixed(2)}MB) exceeds the maximum allowed size of ${MAX_TOTAL_SIZE / (1024 * 1024)}MB.`, 'error');
            this.value = ''; // Clear the file input
            submitButton.disabled = true;
            return;
        }

        if (files.length === 0) {
            submitButton.disabled = true;
            return;
        }

        if (hasLargeFiles && !resizeCheckbox.checked) {
            showAlert('One or more files are too large. Please enable resize option or select smaller files.', 'error');
            submitButton.disabled = true;
        } else {
            submitButton.disabled = false;
            if (hasLargeFiles && resizeCheckbox.checked) {
                showAlert('Large images will be automatically resized before upload.', 'warning');
            }
        }
    });

    // Add resize checkbox change handler
    resizeCheckbox.addEventListener('change', function() {
        if (fileInput.files.length > 0) {
            const files = Array.from(fileInput.files);
            const hasLargeFiles = files.some(file => file.size > MAX_FILE_SIZE);
            
            if (hasLargeFiles && !this.checked) {
                showAlert('One or more files are too large. Please enable resize option or select smaller files.', 'error');
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
                if (hasLargeFiles && this.checked) {
                    showAlert('Large images will be automatically resized before upload.', 'warning');
                }
            }
        }
    });

    async function resizeImage(file) {
        return new Promise((resolve) => {
            const reader = new FileReader();
            reader.onload = (e) => {
                const img = new Image();
                img.onload = () => {
                    let width = img.width;
                    let height = img.height;
                    
                    // Calculate new dimensions while maintaining aspect ratio
                    if (width > MAX_WIDTH || height > MAX_HEIGHT) {
                        if (width / height > MAX_WIDTH / MAX_HEIGHT) {
                            height = Math.round(height * (MAX_WIDTH / width));
                            width = MAX_WIDTH;
                        } else {
                            width = Math.round(width * (MAX_HEIGHT / height));
                            height = MAX_HEIGHT;
                        }
                    }

                    const canvas = document.createElement('canvas');
                    canvas.width = width;
                    canvas.height = height;
                    
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);
                    
                    // Determine output format based on input
                    let outputType = file.type;
                    let outputQuality = 0.9;
                    let outputExt = file.name.split('.').pop().toLowerCase();
                    
                    // Default to JPEG for unsupported formats
                    if (!['image/jpeg', 'image/png', 'image/webp'].includes(outputType)) {
                        outputType = 'image/jpeg';
                        outputExt = 'jpg';
                    }
                    
                    // Start with high quality
                    let dataUrl = canvas.toDataURL(outputType, outputQuality);
                    let currentSize = Math.round((dataUrl.length - `data:${outputType};base64,`.length) * 0.75);
                    
                    // Reduce quality until file size is under target size
                    while (currentSize > TARGET_FILE_SIZE && outputQuality > 0.1) {
                        outputQuality -= 0.1;
                        dataUrl = canvas.toDataURL(outputType, outputQuality);
                        currentSize = Math.round((dataUrl.length - `data:${outputType};base64,`.length) * 0.75);
                    }

                    // Convert base64 to blob
                    fetch(dataUrl)
                        .then(res => res.blob())
                        .then(blob => {
                            // Create a new file with the same name but resized
                            const fileName = file.name.replace(/\.[^/.]+$/, '') + '_resized.' + outputExt;
                            const resizedFile = new File([blob], fileName, {
                                type: outputType,
                                lastModified: new Date().getTime()
                            });
                            resolve(resizedFile);
                        });
                };
                img.src = e.target.result;
            };
            reader.readAsDataURL(file);
        });
    }

    async function uploadFiles(files) {
        const batchSize = 2; // Number of files to upload in each batch
        const results = {
            success: [],
            errors: []
        };

        setLoading(true);
        showAlert('Processing and uploading images...', 'warning');

        // Resize images if checkbox is checked
        let processedFiles = files;
        if (resizeCheckbox.checked) {
            try {
                processedFiles = await Promise.all(
                    Array.from(files).map(file => resizeImage(file))
                );
                showAlert('Images have been resized and are being uploaded...', 'warning');
            } catch (error) {
                console.error('Error resizing images:', error);
                showAlert('Error resizing images. Please try again.', 'error');
                setLoading(false);
                return;
            }
        }

        // Split files into batches
        for (let i = 0; i < processedFiles.length; i += batchSize) {
            const batch = processedFiles.slice(i, i + batchSize);
            
            // Process each file in the batch
            for (const file of batch) {
                const formData = new FormData();
                formData.append('resize', 'false'); // We've already resized on client if needed
                
                // Add site_id from form if present
                const siteIdInput = document.getElementById('site_id');
                const uploadForm = document.getElementById('uploadForm');
                let siteId = null;
                
                // Try to get site_id from hidden input first
                if (siteIdInput && siteIdInput.value) {
                    siteId = siteIdInput.value;
                }
                // If not found, try to get from data attribute
                else if (uploadForm && uploadForm.dataset.siteId) {
                    siteId = uploadForm.dataset.siteId;
                }
                
                if (siteId) {
                    formData.append('site_id', siteId);
                    console.log('Uploading with site_id:', siteId);
                }
                
                formData.append('image', file);

                // Debug log the FormData contents
                console.log('Uploading file:', {
                    name: file.name,
                    type: file.type,
                    size: file.size,
                    siteId: siteId || 'Not provided'
                });

                try {
                    const response = await fetch('/upload', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': csrfToken,
                            'Accept': 'application/json'
                        },
                        body: formData,
                        credentials: 'same-origin'
                    });

                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }

                    const result = await response.json();
                    
                    if (result.success) {
                        results.success.push(file.name);
                        showAlert(`Successfully uploaded ${file.name}`, 'success');
                    } else {
                        results.errors.push(result.error || 'Unknown error occurred');
                        showAlert(result.error || 'Failed to upload image', 'error');
                    }
                } catch (error) {
                    console.error('Upload error:', error);
                    results.errors.push(`Failed to upload ${file.name}: ${error.message}`);
                    showAlert(`Error uploading ${file.name}: ${error.message}`, 'error');
                }
            }
        }
        return results;
    }

    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const files = Array.from(fileInput.files);
        if (files.length === 0) {
            showAlert('Please select at least one image to upload.', 'error');
            return;
        }

        setLoading(true);

        try {
            const results = await uploadFiles(files);
            
            if (results.success.length > 0) {
                showAlert(`Successfully uploaded ${results.success.length} image(s)${results.errors.length > 0 ? ` with ${results.errors.length} error(s)` : ''}.`, 'success');
                fileInput.value = ''; // Clear the file input
                
                // Refresh the page after a short delay to show the new images
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                showAlert('Failed to upload any images. Please try again.', 'error');
            }

            if (results.errors.length > 0) {
                console.error('Upload errors:', results.errors);
            }
        } catch (error) {
            showAlert(`Upload error: ${error.message}`, 'error');
        } finally {
            setLoading(false);
        }
    });
}

// Initialize on DOMContentLoaded for initial page load
document.addEventListener('DOMContentLoaded', initializeUploadForm);

// Initialize when Livewire updates the DOM
document.addEventListener('livewire:load', () => {
    Livewire.hook('message.processed', (message, component) => {
        initializeUploadForm();
    });
});

// Also initialize when turbo:load event occurs (if using Turbo)
document.addEventListener('turbo:load', initializeUploadForm);
