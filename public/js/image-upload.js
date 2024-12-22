document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    const fileInput = document.getElementById('image');
    const resizeCheckbox = document.getElementById('resize');
    const alertContainer = document.getElementById('alertContainer');
    const alertContent = document.getElementById('alertContent');
    const resizeDialog = document.getElementById('resizeDialog');
    const resizeMessage = document.getElementById('resizeMessage');
    let originalFiles = null;

    // Set max file size to 8MB (slightly less than server's 8388608 bytes limit)
    const MAX_FILE_SIZE = 8 * 1024 * 1024; // 8MB in bytes

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

        // Calculate total size and check for large files
        files.forEach(file => {
            totalSize += file.size;
            if (file.size > MAX_FILE_SIZE) {
                hasLargeFiles = true;
            }
        });

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

    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const files = Array.from(fileInput.files);
        if (files.length === 0) {
            showAlert('Please select at least one image to upload.', 'error');
            return;
        }

        setLoading(true);

        try {
            const formData = new FormData();
            formData.append('_token', document.querySelector('input[name="_token"]').value);
            formData.append('resize', resizeCheckbox.checked);
            
            // Append all files to the FormData
            files.forEach((file, index) => {
                formData.append(`image[${index}]`, file);
            });

            const response = await fetch('/upload', {
                method: 'POST',
                body: formData
            });

            const result = await response.json();

            if (response.ok) {
                showAlert(result.message || 'Images uploaded successfully.', 'success');
                fileInput.value = ''; // Clear the file input
                // Refresh the page after a short delay to show the new images
                setTimeout(() => {
                    window.location.reload();
                }, 1500);
            } else {
                throw new Error(result.error || 'Upload failed');
            }
        } catch (error) {
            showAlert(error.message, 'error');
        } finally {
            setLoading(false);
        }
    });
});
