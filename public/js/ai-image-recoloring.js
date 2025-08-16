/**
 * AI Image Recoloring JavaScript
 * Handles the UI interactions for the AI image recoloring feature
 */

document.addEventListener('DOMContentLoaded', function() {
    // Get DOM elements
    const recolorButton = document.getElementById('recolorAiImageBtn');
    const recolorModal = document.getElementById('aiRecolorModal');
    const recolorForm = document.getElementById('aiRecolorForm');
    const imageSelector = document.getElementById('imageSelector');
    const selectedImagePreview = document.getElementById('selectedImagePreview');
    const previewImage = document.getElementById('previewImage');
    const imageCanvas = document.getElementById('imageCanvas');
    const oldColorPicker = document.getElementById('oldColorPicker');
    const oldColorInput = document.getElementById('oldColor');
    const oldColorEyedropper = document.getElementById('oldColorEyedropper');
    const newColorPicker = document.getElementById('newColorPicker');
    const newColorInput = document.getElementById('newColor');
    const oldColorPreview = document.getElementById('oldColorPreview');
    const newColorPreview = document.getElementById('newColorPreview');
    const eyedropperCursor = document.getElementById('eyedropperCursor');
    const recoloringStatus = document.getElementById('aiRecoloringStatus');
    const recoloringResult = document.getElementById('aiRecoloringResult');
    const recoloringSuccess = document.getElementById('aiRecoloringSuccess');
    const recoloringError = document.getElementById('aiRecoloringError');
    const recoloredImage = document.getElementById('recoloredImage');

    // Canvas context for eyedropper
    let ctx = null;
    let isEyedropperActive = false;
    let currentEyedropperTarget = null;

    // Set initial values for color pickers
    oldColorPicker.value = '#000000';
    oldColorInput.value = '#000000';
    newColorPicker.value = '#FF5733';
    newColorInput.value = '#FF5733';
    
    // Set initial color previews
    updateColorPreview('oldColorPreview', '#000000');
    updateColorPreview('newColorPreview', '#FF5733');

    // Open modal when button is clicked
    if (recolorButton) {
        recolorButton.addEventListener('click', function() {
            openAiRecolorModal();
        });
    }

    // Handle image selection change
    if (imageSelector) {
        imageSelector.addEventListener('change', function() {
            const selectedOption = imageSelector.options[imageSelector.selectedIndex];
            if (selectedOption.value) {
                const imageSrc = selectedOption.getAttribute('data-src');
                previewImage.src = imageSrc;
                selectedImagePreview.classList.remove('hidden');
                
                // Load image into canvas for eyedropper functionality
                previewImage.onload = function() {
                    imageCanvas.width = previewImage.naturalWidth;
                    imageCanvas.height = previewImage.naturalHeight;
                    ctx = imageCanvas.getContext('2d');
                    ctx.drawImage(previewImage, 0, 0);
                };
            } else {
                selectedImagePreview.classList.add('hidden');
            }
        });
    }
    
    // Eyedropper functionality
    if (oldColorEyedropper && previewImage) {
        oldColorEyedropper.addEventListener('click', function() {
            if (!imageSelector.value) {
                alert('Please select an image first.');
                return;
            }
            
            toggleEyedropper('old');
        });
        
        previewImage.addEventListener('click', function(e) {
            if (!isEyedropperActive) return;
            
            const color = getColorFromImage(e);
            if (color && currentEyedropperTarget === 'old') {
                oldColorPicker.value = color;
                oldColorInput.value = color;
                updateColorPreview('oldColorPreview', color);
                toggleEyedropper(null); // Deactivate eyedropper
            }
        });
        
        previewImage.addEventListener('mousemove', function(e) {
            if (!isEyedropperActive) return;
            
            // Update eyedropper cursor position
            const rect = previewImage.getBoundingClientRect();
            const x = e.clientX - rect.left;
            const y = e.clientY - rect.top;
            
            eyedropperCursor.style.left = x + 'px';
            eyedropperCursor.style.top = y + 'px';
            
            // Show current color in cursor
            const color = getColorFromImage(e);
            if (color) {
                eyedropperCursor.style.backgroundColor = color;
            }
        });
    }

    // Sync color picker with text input for old color
    if (oldColorPicker && oldColorInput) {
        oldColorPicker.addEventListener('input', function() {
            const color = oldColorPicker.value;
            oldColorInput.value = color;
            updateColorPreview('oldColorPreview', color);
        });
        
        oldColorInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(oldColorInput.value)) {
                const color = oldColorInput.value;
                oldColorPicker.value = color;
                updateColorPreview('oldColorPreview', color);
            }
        });
    }

    // Sync color picker with text input for new color
    if (newColorPicker && newColorInput) {
        newColorPicker.addEventListener('input', function() {
            const color = newColorPicker.value;
            newColorInput.value = color;
            updateColorPreview('newColorPreview', color);
        });
        
        newColorInput.addEventListener('input', function() {
            if (/^#[0-9A-Fa-f]{6}$/.test(newColorInput.value)) {
                const color = newColorInput.value;
                newColorPicker.value = color;
                updateColorPreview('newColorPreview', color);
            }
        });
    }

    // Handle form submission
    if (recolorForm) {
        recolorForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            // Validate form
            if (!validateRecolorForm()) {
                return;
            }
            
            // Show loading state
            recoloringStatus.classList.remove('hidden');
            recoloringResult.classList.add('hidden');
            
            // Disable form submission
            document.getElementById('recolorImageBtn').disabled = true;
            
            // Get form data
            const formData = new FormData(recolorForm);
            
            // Send AJAX request
            fetch('/images/recolor-ai', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => response.json())
            .then(data => {
                // Hide loading state
                recoloringStatus.classList.add('hidden');
                recoloringResult.classList.remove('hidden');
                
                if (data.success) {
                    // Show success message and image
                    recoloringSuccess.classList.remove('hidden');
                    recoloringError.classList.add('hidden');
                    recoloredImage.src = data.imageUrl;
                    
                    // Refresh the image library after a delay
                    setTimeout(function() {
                        window.location.reload();
                    }, 3000);
                } else {
                    // Show error message
                    recoloringSuccess.classList.add('hidden');
                    recoloringError.classList.remove('hidden');
                    recoloringError.textContent = data.error || 'An error occurred while recoloring the image.';
                }
                
                // Re-enable form submission
                document.getElementById('recolorImageBtn').disabled = false;
            })
            .catch(error => {
                // Hide loading state
                recoloringStatus.classList.add('hidden');
                recoloringResult.classList.remove('hidden');
                
                // Show error message
                recoloringSuccess.classList.add('hidden');
                recoloringError.classList.remove('hidden');
                recoloringError.textContent = 'Network error: ' + error.message;
                
                // Re-enable form submission
                document.getElementById('recolorImageBtn').disabled = false;
            });
        });
    }
});

/**
 * Open the AI recolor modal
 */
function openAiRecolorModal() {
    const modal = document.getElementById('aiRecolorModal');
    if (modal) {
        modal.classList.remove('hidden');
    }
}

/**
 * Close the AI recolor modal
 */
function closeAiRecolorModal() {
    const modal = document.getElementById('aiRecolorModal');
    if (modal) {
        modal.classList.add('hidden');
        
        // Reset form
        document.getElementById('aiRecolorForm').reset();
        document.getElementById('selectedImagePreview').classList.add('hidden');
        document.getElementById('aiRecoloringStatus').classList.add('hidden');
        document.getElementById('aiRecoloringResult').classList.add('hidden');
    }
}

/**
 * Update the color preview element with the specified color
 * @param {string} elementId - ID of the preview element
 * @param {string} color - Hex color code
 */
function updateColorPreview(elementId, color) {
    const previewElement = document.getElementById(elementId);
    if (previewElement) {
        previewElement.style.backgroundColor = color;
    }
}

/**
 * Toggle the eyedropper tool on/off
 * @param {string|null} target - 'old' for old color, null to deactivate
 */
function toggleEyedropper(target) {
    const eyedropperCursor = document.getElementById('eyedropperCursor');
    const previewImage = document.getElementById('previewImage');
    
    if (target) {
        // Activate eyedropper
        isEyedropperActive = true;
        currentEyedropperTarget = target;
        eyedropperCursor.classList.remove('hidden');
        previewImage.style.cursor = 'none';
        
        // Highlight the active eyedropper button
        if (target === 'old') {
            document.getElementById('oldColorEyedropper').classList.add('bg-blue-200');
        }
    } else {
        // Deactivate eyedropper
        isEyedropperActive = false;
        currentEyedropperTarget = null;
        eyedropperCursor.classList.add('hidden');
        previewImage.style.cursor = 'default';
        
        // Remove highlight from all eyedropper buttons
        document.getElementById('oldColorEyedropper').classList.remove('bg-blue-200');
    }
}

/**
 * Get the color from the image at the clicked position
 * @param {MouseEvent} event - Mouse event
 * @returns {string} Hex color code
 */
function getColorFromImage(event) {
    if (!ctx) return null;
    
    const rect = previewImage.getBoundingClientRect();
    const scaleX = previewImage.naturalWidth / previewImage.width;
    const scaleY = previewImage.naturalHeight / previewImage.height;
    
    const x = Math.floor((event.clientX - rect.left) * scaleX);
    const y = Math.floor((event.clientY - rect.top) * scaleY);
    
    // Get pixel data
    const pixel = ctx.getImageData(x, y, 1, 1).data;
    
    // Convert RGB to hex
    const hex = rgbToHex(pixel[0], pixel[1], pixel[2]);
    return hex;
}

/**
 * Convert RGB values to hex color code
 * @param {number} r - Red (0-255)
 * @param {number} g - Green (0-255)
 * @param {number} b - Blue (0-255)
 * @returns {string} Hex color code
 */
function rgbToHex(r, g, b) {
    return '#' + [r, g, b].map(x => {
        const hex = x.toString(16);
        return hex.length === 1 ? '0' + hex : hex;
    }).join('');
}

/**
 * Validate the recolor form
 * @returns {boolean} True if form is valid, false otherwise
 */
function validateRecolorForm() {
    const imageSelector = document.getElementById('imageSelector');
    const oldColorInput = document.getElementById('oldColor');
    const newColorInput = document.getElementById('newColor');
    let isValid = true;
    
    // Validate image selection
    if (!imageSelector.value) {
        alert('Please select an image to recolor.');
        isValid = false;
    }
    
    // Validate old color format
    if (!oldColorInput.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        alert('Please enter a valid hex color code for the old color (e.g., #000000).');
        isValid = false;
    }
    
    // Validate new color format
    if (!newColorInput.value.match(/^#[0-9A-Fa-f]{6}$/)) {
        alert('Please enter a valid hex color code for the new color (e.g., #FF5733).');
        isValid = false;
    }
    
    return isValid;
}
