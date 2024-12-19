document.addEventListener('DOMContentLoaded', function() {
    const uploadForm = document.getElementById('uploadForm');
    const submitButton = uploadForm.querySelector('button[type="submit"]');
    const fileInput = document.getElementById('image');
    const alertContainer = document.getElementById('alertContainer');
    const alertContent = document.getElementById('alertContent');
    const resizeDialog = document.getElementById('resizeDialog');
    const resizeMessage = document.getElementById('resizeMessage');
    let originalImage = null;

    // Set max file size to 8MB (slightly less than server's 8388608 bytes limit)
    const MAX_FILE_SIZE = 8 * 1024 * 1024; // 8MB in bytes

    function setLoading(isLoading) {
        if (isLoading) {
            submitButton.disabled = true;
            submitButton.innerHTML = '<span class="spinner"></span> Uploading...';
            document.body.style.cursor = 'wait';
        } else {
            submitButton.disabled = false;
            submitButton.innerHTML = 'Upload Image';
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
        const file = this.files[0];
        if (file) {
            if (file.size > MAX_FILE_SIZE) {
                showAlert('File is too large. Maximum size allowed is 8MB.', 'error');
                this.value = ''; // Clear the file input
                submitButton.disabled = true;
            } else {
                submitButton.disabled = false;
                alertContainer.classList.add('hidden');
            }
        } else {
            submitButton.disabled = true;
        }
    });

    function showResizeDialog(message) {
        resizeMessage.textContent = message;
        resizeDialog.classList.remove('hidden');
    }

    function hideResizeDialog() {
        resizeDialog.classList.add('hidden');
        originalImage = null;
    }

    window.confirmResize = async function() {
        if (!originalImage) return;
        
        setLoading(true);
        const formData = new FormData();
        formData.append('image', originalImage);
        
        try {
            const response = await fetch('/images/confirm-resize', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });
            
            const result = await response.json();
            
            if (response.ok) {
                showAlert('Image successfully resized and uploaded!', 'success');
                location.reload(); // Refresh to show new image
            } else {
                showAlert(result.error || 'Failed to resize image', 'error');
            }
        } catch (error) {
            console.error('Resize error:', error);
            showAlert('An error occurred while resizing the image', 'error');
        } finally {
            hideResizeDialog();
            setLoading(false);
        }
    };

    window.cancelResize = function() {
        hideResizeDialog();
        showAlert('Upload cancelled. Please try with a smaller image.', 'warning');
    };

    uploadForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const fileInput = document.getElementById('image');
        const file = fileInput.files[0];
        
        if (!file) {
            showAlert('Please select an image to upload', 'error');
            return;
        }

        // Check file type
        if (!file.type.startsWith('image/')) {
            showAlert('Please select a valid image file', 'error');
            return;
        }

        setLoading(true);
        const formData = new FormData();
        formData.append('image', file);

        try {
            const response = await fetch('/images/upload', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: formData
            });

            const result = await response.json();
            console.log('Server response:', result);

            if (response.ok) {
                if (result.warning && result.show_resize_options) {
                    originalImage = file;
                    showResizeDialog(`Current file size: ${result.file_size}. ${result.warning}`);
                } else {
                    showAlert('Image uploaded successfully!', 'success');
                    location.reload(); // Refresh to show new image
                }
            } else {
                showAlert(result.error || 'Failed to upload image: ' + response.statusText, 'error');
            }
        } catch (error) {
            console.error('Upload error:', error);
            showAlert(`Upload error: ${error.message}`, 'error');
        } finally {
            setLoading(false);
        }
    });
});
