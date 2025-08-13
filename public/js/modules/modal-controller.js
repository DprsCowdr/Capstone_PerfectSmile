/**
 * Modal Controller - Handles all modal operations
 * Manages modal state, animations, resizing, and fullscreen
 */

class ModalController {
    constructor() {
        this.modal = null;
        this.modalDialog = null;
        this.modalContent = null;
        this.init();
    }

    init() {
        this.modal = document.getElementById('patientRecordsModal');
        // Support legacy (resizable-modal) and new minimalist (modal-panel) markup
        this.modalDialog = this.modal?.querySelector('.resizable-modal') || this.modal?.querySelector('.modal-panel');
        this.modalContent = document.getElementById('modalContent');
    }

    setupModalEventListeners() {
        if (!this.modal) return;

        // Handle escape key and outside clicks
        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && !this.modal.classList.contains('hidden')) {
                this.closeModal();
            }
        });
        
        // Handle clicks outside modal
        this.modal.addEventListener('click', (event) => {
            if (event.target === this.modal) {
                this.closeModal();
            }
        });
        
        // Initialize modal resize functionality
        this.initializeResize();
    }

    // ==================== MODAL OPERATIONS ====================

    openModal() {
        if (!this.modal) return;
        // Re-query in case DOM changed
        if (!this.modalDialog) {
            this.modalDialog = this.modal.querySelector('.resizable-modal') || this.modal.querySelector('.modal-panel');
        }
        if (!this.modalDialog) return;
        this.modal.classList.remove('hidden');
        this.modal.classList.add('flex');
        requestAnimationFrame(() => {
            this.modalDialog.style.opacity = '1';
            this.modalDialog.style.transform = 'scale(1)';
        });
    }

    closeModal() {
        if (!this.modal || !this.modalDialog) return;
        this.modalDialog.style.opacity = '0';
        this.modalDialog.style.transform = 'scale(0.95)';
        setTimeout(() => {
            this.modal.classList.add('hidden');
            this.modal.classList.remove('flex');
            this.clearContent();
        }, 200);
    }

    clearContent() {
        if (this.modalContent) {
            this.modalContent.innerHTML = `
                <div class="flex items-center justify-center h-32">
                    <div class="text-center">
                        <i class="fas fa-spinner fa-spin text-2xl text-blue-500 mb-2"></i>
                        <p class="text-gray-600">Loading patient information...</p>
                    </div>
                </div>
            `;
        }
    }

    // ==================== TAB MANAGEMENT ====================

    setActiveTab(tabType) {
        // Add loading transition
        if (this.modalContent) {
            this.modalContent.classList.add('modal-content-loading');
        }
        
        // Update active tab with smooth transitions
        document.querySelectorAll('.record-tab').forEach(tab => {
            tab.classList.remove('bg-blue-600', 'text-white');
            tab.classList.add('bg-gray-100', 'text-gray-700');
        });
        
        const activeTab = document.getElementById(`${tabType}-tab`);
        if (activeTab) {
            activeTab.classList.remove('bg-gray-100', 'text-gray-700');
            activeTab.classList.add('bg-blue-600', 'text-white');
        }
        
        // Remove loading state after content loads
        setTimeout(() => {
            if (this.modalContent) {
                this.modalContent.classList.remove('modal-content-loading');
            }
        }, 200);
    }

    // ==================== LOADING STATES ====================

    setLoadingState(message) {
        if (this.modalContent) {
            this.modalContent.innerHTML = `
                <div class="text-center py-4">
                    <i class="fas fa-spinner fa-spin"></i> ${message}
                </div>
            `;
        }
    }

    setErrorState(title, message, retryCallback) {
        if (this.modalContent) {
            this.modalContent.innerHTML = `
                <div class="text-center py-8">
                    <i class="fas fa-exclamation-triangle text-red-500 text-3xl mb-4"></i>
                    <h3 class="text-lg font-semibold text-red-600 mb-2">${title}</h3>
                    <p class="text-gray-600 mb-4">Error: ${message}</p>
                    <button onclick="(${retryCallback.toString()})()" 
                            class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded">
                        <i class="fas fa-redo mr-2"></i>Retry
                    </button>
                </div>
            `;
        }
    }

    // ==================== RESIZE FUNCTIONALITY ====================
    
    initializeResize() {
        // No-op in minimalist version (kept for backward compatibility)
    }
    
    makeDraggable(element, handle) {
        let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
        
        handle.addEventListener('mousedown', (e) => {
            e.preventDefault();
            
            // Don't allow dragging in fullscreen mode
            if (element.classList.contains('fullscreen')) {
                return;
            }
            
            // Switch to fixed positioning for dragging
            const rect = element.getBoundingClientRect();
            element.style.position = 'fixed';
            element.style.top = rect.top + 'px';
            element.style.left = rect.left + 'px';
            element.style.margin = '0';
            
            pos3 = e.clientX;
            pos4 = e.clientY;
            
            const dragMouseMove = (e) => {
                e.preventDefault();
                pos1 = pos3 - e.clientX;
                pos2 = pos4 - e.clientY;
                pos3 = e.clientX;
                pos4 = e.clientY;
                
                const newTop = element.offsetTop - pos2;
                const newLeft = element.offsetLeft - pos1;
                
                // Keep modal within viewport bounds
                const maxTop = window.innerHeight - element.offsetHeight;
                const maxLeft = window.innerWidth - element.offsetWidth;
                
                element.style.top = Math.max(0, Math.min(newTop, maxTop)) + 'px';
                element.style.left = Math.max(0, Math.min(newLeft, maxLeft)) + 'px';
            };
            
            const dragMouseUp = () => {
                document.removeEventListener('mousemove', dragMouseMove);
                document.removeEventListener('mouseup', dragMouseUp);
            };
            
            document.addEventListener('mousemove', dragMouseMove);
            document.addEventListener('mouseup', dragMouseUp);
        });
    }
    
    toggleFullscreen() { /* removed in minimalist version */ }
    
    centerModal() { /* not needed */ }

    addCenteringHelper() { /* not needed */ }
    
    resetSize() { /* not needed */ }
}

// Export for use
window.ModalController = ModalController;
