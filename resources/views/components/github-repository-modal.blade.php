{{-- GitHub Repository Modal Component --}}
<div x-data="githubRepoModal()" @keydown.escape="closeModal()">
@push('scripts')
<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('githubRepoModal', () => ({
            isOpen: false,
            siteId: null,
            folderPath: '',
            repositoryName: '',
            githubUsername: '',
            githubToken: '',
            isLoading: false,
            errorMessage: '',
            successMessage: '',
            
            init() {
                window.addEventListener('open-github-repo-modal', event => {
                    this.siteId = event.detail.siteId;
                    this.isOpen = true;
                });
            },
            
            closeModal() {
                this.isOpen = false;
                this.resetForm();
            },
            
            resetForm() {
                this.folderPath = '';
                this.repositoryName = '';
                this.githubUsername = '';
                this.githubToken = '';
                this.isLoading = false;
                this.errorMessage = '';
                this.successMessage = '';
            },
            
            selectedFiles: [],
           // Fixed handleFolderSelection method
handleFolderSelection(event) {
    const files = event.target.files;
    if (files && files.length > 0) {
        this.selectedFiles = Array.from(files);
        
        // Get the common folder path from the first file's webkitRelativePath
        const firstFilePath = files[0].webkitRelativePath;
        const folderName = firstFilePath.split('/')[0];
        
        // Set the folder path to the folder name
        this.folderPath = folderName;
        
        // Set the repository name if empty
        if (!this.repositoryName) {
            this.repositoryName = folderName;
        }
        
        // Show success message with file count
        this.successMessage = `Selected folder: ${folderName} (${this.selectedFiles.length} files)\n\nFiles will be temporarily uploaded when creating the repository.`;
        
        // Clear success message after 5 seconds
        setTimeout(() => {
            if (this.successMessage && this.successMessage.includes('Selected folder:')) {
                this.successMessage = '';
            }
        }, 5000);
    }
},

// Fixed createRepository method
async createRepository() {
    if (!this.folderPath || !this.repositoryName) {
        this.errorMessage = 'All fields are required';
        return;
    }

    this.isLoading = true;
    this.errorMessage = '';
    this.successMessage = 'Preparing files for upload...';

    try {
        let response;
        
        // If we have selected files, use FormData to upload them
        if (this.selectedFiles && this.selectedFiles.length > 0) {
            const formData = new FormData();
            formData.append('site_id', this.siteId);
            formData.append('repository_name', this.repositoryName);
            formData.append('folder_path', this.folderPath);
            
            // Show progress message
            this.successMessage = `Uploading ${this.selectedFiles.length} files...`;
            
            // Add all files to the form data, preserving folder structure
            for (let i = 0; i < this.selectedFiles.length; i++) {
                const file = this.selectedFiles[i];
                
                // Use the webkitRelativePath to maintain folder structure
                const relativePath = file.webkitRelativePath;
                
                // Create a unique field name that includes the relative path
                // This ensures each file has its path preserved
                formData.append(`files[${i}]`, file);
                formData.append(`paths[${i}]`, relativePath);
                
                // Update progress message for large uploads
                if (i % 20 === 0 && i > 0) {
                    this.successMessage = `Uploading files... (${i}/${this.selectedFiles.length})`;
                    // Allow UI to update
                    await new Promise(resolve => setTimeout(resolve, 0));
                }
            }
            
            this.successMessage = 'Sending files to server...';
            
            response = await fetch('/sites/create-github-repository', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: formData
            });
        } else {
            // If no files are selected, use the traditional JSON approach
            response = await fetch('/sites/create-github-repository', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    site_id: this.siteId,
                    folder_path: this.folderPath,
                    repository_name: this.repositoryName
                })
            });
        }

        const result = await response.json();
        
        if (result.success) {
            this.successMessage = result.message;
            
            // Emit event to update the github_repository field in the parent component
            window.dispatchEvent(new CustomEvent('github-repo-created', {
                detail: {
                    repositoryPath: result.repository_path
                }
            }));
            
            // Close modal after 3 seconds
            setTimeout(() => {
                this.closeModal();
                // Reload the page to reflect changes
                window.location.reload();
            }, 3000);
        } else {
            this.errorMessage = result.message || 'An error occurred while creating the repository';
        }
    } catch (error) {
        this.errorMessage = 'An error occurred while creating the repository';
        console.error(error);
    } finally {
        this.isLoading = false;
    }
}
        }))
    })
</script>
@endpush
    <!-- GitHub Repository Creation Modal -->
    <div x-show="isOpen" class="fixed inset-0 z-50 overflow-y-auto" aria-labelledby="modal-title" x-cloak>
        <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <!-- Background overlay -->
            <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" @click="closeModal()" aria-hidden="true"></div>

            <!-- Modal panel -->
            <div x-show="isOpen" x-transition:enter="ease-out duration-300" x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100" x-transition:leave="ease-in duration-200" x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100" x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95" class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                    <div class="sm:flex sm:items-start">
                        <div class="mx-auto flex-shrink-0 flex items-center justify-center h-12 w-12 rounded-full bg-blue-100 sm:mx-0 sm:h-10 sm:w-10">
                            <svg class="h-6 w-6 text-blue-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                        <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left">
                            <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                Create GitHub Repository
                            </h3>
                            <div class="mt-2">
                                <p class="text-sm text-gray-500">
                                    Create a new GitHub repository from a local folder and link it to this site.
                                </p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Error message -->
                    <div x-show="errorMessage" class="mt-4 bg-red-100 border-l-4 border-red-500 text-red-700 p-4" role="alert">
                        <p x-text="errorMessage"></p>
                    </div>
                    
                    <!-- Success message -->
                    <div x-show="successMessage" class="mt-4 bg-green-100 border-l-4 border-green-500 text-green-700 p-4" role="alert">
                        <p class="whitespace-pre-wrap" x-text="successMessage"></p>
                    </div>
                    
                    <!-- Form -->
                    <div class="mt-4">
                        <div class="mb-4">
                            <label for="folderPath" class="block text-gray-700 text-sm font-bold mb-2">Local Folder Path:</label>
                            <div class="mb-2">
                                <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="folderPath" placeholder="Selected folder will appear here" x-model="folderPath" readonly>
                            </div>
                            
                            <div class="mb-4">
                                <label for="folderSelector" class="cursor-pointer bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded border border-blue-600 flex items-center inline-block">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7v10a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-6l-2-2H5a2 2 0 00-2 2z" />
                                    </svg>
                                    Browse for Folder
                                </label>
                                <input type="file" id="folderSelector" class="hidden" webkitdirectory directory multiple @change="handleFolderSelection($event)">
                                <p class="text-xs text-gray-500 mt-2">Select a folder from your computer. Files will be temporarily uploaded to create the repository.</p>
                            </div>
                            
                            <!-- Selected Files Info -->
                            <div x-show="selectedFiles.length > 0" class="mb-4 p-3 bg-gray-50 border border-gray-200 rounded text-sm">
                                <div class="flex items-center text-gray-700">
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2 text-blue-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    <span x-text="`${selectedFiles.length} files selected from folder '${folderPath}'`"></span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="repositoryName" class="block text-gray-700 text-sm font-bold mb-2">Repository Name:</label>
                            <input type="text" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline" id="repositoryName" placeholder="Enter repository name" x-model="repositoryName">
                            <p class="text-xs text-gray-500 mt-1">Example: my-website</p>
                        </div>
                        
                        <p class="text-sm text-gray-600 mt-4">
                            Using GitHub credentials from environment variables. Make sure GITHUB_TOKEN and GITHUB_USERNAME are set in your .env file.
                        </p>
                    </div>
                </div>
                
                <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                    <button type="button" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm" 
                        @click="createRepository()" 
                        x-bind:disabled="isLoading">
                        <span x-show="isLoading" class="inline-block animate-spin mr-2">
                            <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                        <span x-text="isLoading ? 'Creating...' : 'Create Repository'"></span>
                    </button>
                    <button type="button" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm" @click="closeModal()">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
