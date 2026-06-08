{{-- resources/views/partials/modal/success-modal.blade.php --}}

@push('scripts')
<script>
// Register success modal component
document.addEventListener('alpine:init', () => {
    console.log('Registering successModal component');
    
    Alpine.data('successModal', () => ({
        showSuccessModal: false,
        title: 'Success!',
        message: 'Operation completed successfully.',
        details: '',
        timer: 3,
        timerPercent: 100,
        timerInterval: null,
        showSecondaryButton: false,
        secondaryButtonText: 'View Details',
        onSecondaryAction: null,
        onClose: null,
        
        open(options = {}) {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            
            this.title = options.title || 'Success!';
            this.message = options.message || 'Operation completed successfully.';
            this.details = options.details || '';
            this.timer = options.timer || 3;
            this.timerPercent = 100;
            this.showSecondaryButton = options.showSecondaryButton || false;
            this.secondaryButtonText = options.secondaryButtonText || 'View Details';
            this.onSecondaryAction = options.onSecondaryAction || null;
            this.onClose = options.onClose || null;
            
            this.showSuccessModal = true;
            document.body.style.overflow = 'hidden';
            this.startTimer();
        },
        
        startTimer() {
            const interval = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                    this.timerPercent = (this.timer / 3) * 100;
                } else {
                    clearInterval(interval);
                    this.closeModal();
                }
            }, 1000);
            this.timerInterval = interval;
        },
        
        closeModal() {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            this.showSuccessModal = false;
            document.body.style.overflow = '';
            if (typeof this.onClose === 'function') {
                this.onClose();
            }
        },
        
        secondaryAction() {
            if (this.onSecondaryAction && typeof this.onSecondaryAction === 'function') {
                this.onSecondaryAction();
            }
            this.closeModal();
        }
    }));
});

// Make showSuccessModal globally available
window.showSuccessModal = function(options) {
    const modalEl = document.querySelector('[x-data="successModal()"]');
    if (modalEl && modalEl.__x) {
        modalEl.__x.$data.open(options);
    } else {
        console.error('Success modal not found');
        alert(options.message || 'Success!');
    }
};
</script>
@endpush

<!-- Modal HTML -->
<div x-data="successModal()" 
     x-show="showSuccessModal" 
     x-cloak
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300" 
         x-show="showSuccessModal"
         x-transition:enter="ease-out duration-300"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         x-transition:leave="ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         @click="closeModal()">
    </div>

    <!-- Modal Container -->
    <div class="flex min-h-full items-center justify-center p-4 text-center sm:p-0">
        <div class="relative transform overflow-hidden rounded-2xl bg-white shadow-2xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
             x-show="showSuccessModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Animated Success Icon -->
            <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
                <div class="relative">
                    <div class="w-24 h-24 bg-gradient-to-br from-green-400 to-green-600 rounded-full flex items-center justify-center shadow-lg animate-bounce">
                        <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path>
                        </svg>
                    </div>
                    <div class="absolute inset-0 w-24 h-24 bg-green-400 rounded-full animate-ping opacity-75"></div>
                </div>
            </div>

            <!-- Modal Content -->
            <div class="pt-16 pb-6">
                <div class="text-center px-6">
                    <h3 class="text-2xl font-bold text-gray-900 mb-2" x-text="title"></h3>
                    <div class="mt-3">
                        <p class="text-sm text-gray-600" x-html="message"></p>
                    </div>

                    <div x-show="details" x-transition
                         class="mt-4 p-4 bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl border border-green-200">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <svg class="w-5 h-5 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium text-green-800 mb-1">Details:</p>
                                <div class="text-xs text-green-700 space-y-1" x-html="details"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto-close Timer Bar -->
                    <div class="mt-6">
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block text-green-600">
                                        Closing in <span x-text="timer"></span> seconds
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block text-green-600">
                                        <button @click="closeModal()" class="hover:underline">Close now</button>
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 text-xs flex rounded bg-green-200">
                                <div class="animate-progress-bar" :style="{ width: timerPercent + '%' }"
                                     class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-green-500 transition-all duration-1000">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" @click="closeModal()"
                                class="inline-flex justify-center items-center px-5 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200 transform hover:scale-105">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            Got it!
                        </button>
                        
                        <button x-show="showSecondaryButton" type="button" @click="secondaryAction()"
                                class="inline-flex justify-center items-center px-5 py-2.5 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 9l3 3m0 0l-3 3m3-3H8m13 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            <span x-text="secondaryButtonText"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Close button top right -->
            <button @click="closeModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition-colors">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
</div>

<style>
    @keyframes progressBar {
        from { width: 100%; }
        to { width: 0%; }
    }
    .animate-progress-bar {
        animation: progressBar 3s linear forwards;
    }
    @keyframes bounce {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-10px); }
    }
    .animate-bounce {
        animation: bounce 0.5s ease-in-out;
    }
</style>