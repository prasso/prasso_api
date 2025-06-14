// AI Image Generation functionality
document.addEventListener('DOMContentLoaded', initializeAiImageGeneration);
document.addEventListener('livewire:load', () => {
    Livewire.hook('message.processed', (message, component) => {
        initializeAiImageGeneration();
    });
});
document.addEventListener('turbo:load', initializeAiImageGeneration);

function initializeAiImageGeneration() {
    const generateAiImageBtn = document.getElementById('generateAiImageBtn');
    const aiImageModal = document.getElementById('aiImageModal');
    const aiImageForm = document.getElementById('aiImageForm');
    const aiGenerationStatus = document.getElementById('aiGenerationStatus');
    const aiGenerationResult = document.getElementById('aiGenerationResult');
    const aiGenerationSuccess = document.getElementById('aiGenerationSuccess');
    const aiGenerationError = document.getElementById('aiGenerationError');
    const generatedImage = document.getElementById('generatedImage');
    
    if (!generateAiImageBtn) return; // Exit if button not found
    
    // Get CSRF token from meta tag
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Open modal when button is clicked
    generateAiImageBtn.addEventListener('click', function() {
        openAiImageModal();
    });
    
    // Handle form submission
    if (aiImageForm) {
        aiImageForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const prompt = document.getElementById('imagePrompt').value.trim();
            const siteId = document.getElementById('ai_site_id').value;
            
            if (!prompt) {
                showAiGenerationError('Please enter a description for the image you want to generate.');
                return;
            }
            
            // Show loading state
            showAiGenerationLoading();
            
            try {
                const response = await fetch('/images/generate-ai', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        prompt: prompt,
                        site_id: siteId
                    }),
                    credentials: 'same-origin'
                });
                
                const result = await response.json();
                
                if (result.success) {
                    showAiGenerationSuccess(result.imageUrl);
                    
                    // Refresh the page after a short delay to show the new image
                    setTimeout(() => {
                        window.location.reload();
                    }, 3000);
                } else {
                    showAiGenerationError(result.error || 'Failed to generate image. Please try again.');
                }
            } catch (error) {
                console.error('AI image generation error:', error);
                showAiGenerationError('An error occurred while generating the image. Please try again.');
            }
        });
    }
}

function openAiImageModal() {
    const aiImageModal = document.getElementById('aiImageModal');
    const aiImageForm = document.getElementById('aiImageForm');
    const aiGenerationStatus = document.getElementById('aiGenerationStatus');
    const aiGenerationResult = document.getElementById('aiGenerationResult');
    
    if (aiImageModal) {
        // Reset form and hide status/result sections
        if (aiImageForm) aiImageForm.reset();
        if (aiGenerationStatus) aiGenerationStatus.classList.add('hidden');
        if (aiGenerationResult) aiGenerationResult.classList.add('hidden');
        
        // Show modal
        aiImageModal.classList.remove('hidden');
        document.body.classList.add('overflow-hidden'); // Prevent scrolling
        
        // Focus on prompt textarea
        const promptTextarea = document.getElementById('imagePrompt');
        if (promptTextarea) promptTextarea.focus();
    }
}

function closeAiImageModal() {
    const aiImageModal = document.getElementById('aiImageModal');
    
    if (aiImageModal) {
        aiImageModal.classList.add('hidden');
        document.body.classList.remove('overflow-hidden'); // Re-enable scrolling
    }
}

function showAiGenerationLoading() {
    const aiImageForm = document.getElementById('aiImageForm');
    const aiGenerationStatus = document.getElementById('aiGenerationStatus');
    const aiGenerationResult = document.getElementById('aiGenerationResult');
    const generateImageBtn = document.getElementById('generateImageBtn');
    
    // Hide form and result, show loading
    if (aiImageForm) {
        const inputs = aiImageForm.querySelectorAll('button, textarea, input');
        inputs.forEach(input => input.disabled = true);
        if (generateImageBtn) {
            generateImageBtn.innerHTML = '<span class="spinner mr-2"></span> Generating...';
        }
    }
    
    if (aiGenerationResult) aiGenerationResult.classList.add('hidden');
    if (aiGenerationStatus) aiGenerationStatus.classList.remove('hidden');
}

function showAiGenerationSuccess(imageUrl) {
    const aiImageForm = document.getElementById('aiImageForm');
    const aiGenerationStatus = document.getElementById('aiGenerationStatus');
    const aiGenerationResult = document.getElementById('aiGenerationResult');
    const aiGenerationSuccess = document.getElementById('aiGenerationSuccess');
    const aiGenerationError = document.getElementById('aiGenerationError');
    const generatedImage = document.getElementById('generatedImage');
    const generateImageBtn = document.getElementById('generateImageBtn');
    
    // Re-enable form controls
    if (aiImageForm) {
        const inputs = aiImageForm.querySelectorAll('button, textarea, input');
        inputs.forEach(input => input.disabled = false);
        if (generateImageBtn) {
            generateImageBtn.innerHTML = 'Generate Image';
        }
    }
    
    // Hide loading, show result with success
    if (aiGenerationStatus) aiGenerationStatus.classList.add('hidden');
    if (aiGenerationResult) aiGenerationResult.classList.remove('hidden');
    if (aiGenerationError) aiGenerationError.classList.add('hidden');
    if (aiGenerationSuccess) {
        aiGenerationSuccess.classList.remove('hidden');
        if (generatedImage) {
            generatedImage.src = imageUrl;
            generatedImage.alt = 'AI Generated Image';
        }
    }
}

function showAiGenerationError(errorMessage) {
    const aiImageForm = document.getElementById('aiImageForm');
    const aiGenerationStatus = document.getElementById('aiGenerationStatus');
    const aiGenerationResult = document.getElementById('aiGenerationResult');
    const aiGenerationSuccess = document.getElementById('aiGenerationSuccess');
    const aiGenerationError = document.getElementById('aiGenerationError');
    const generateImageBtn = document.getElementById('generateImageBtn');
    
    // Re-enable form controls
    if (aiImageForm) {
        const inputs = aiImageForm.querySelectorAll('button, textarea, input');
        inputs.forEach(input => input.disabled = false);
        if (generateImageBtn) {
            generateImageBtn.innerHTML = 'Generate Image';
        }
    }
    
    // Hide loading, show result with error
    if (aiGenerationStatus) aiGenerationStatus.classList.add('hidden');
    if (aiGenerationResult) aiGenerationResult.classList.remove('hidden');
    if (aiGenerationSuccess) aiGenerationSuccess.classList.add('hidden');
    if (aiGenerationError) {
        aiGenerationError.classList.remove('hidden');
        aiGenerationError.textContent = errorMessage;
    }
}
