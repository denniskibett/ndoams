{{-- resources/views/partials/modal/alert-modal.blade.php --}}

@push('scripts')
<script>
// Register alert modal component
document.addEventListener('alpine:init', () => {
    console.log('Registering alertModal component');
    
    Alpine.data('alertModal', () => ({
        showAlertModal: false,
        type: 'info',
        title: 'Alert',
        message: '',
        details: '',
        timer: 5,
        timerPercent: 100,
        timerInterval: null,
        autoClose: false,
        showCancelButton: false,
        confirmButtonText: 'OK',
        cancelButtonText: 'Cancel',
        onConfirm: null,
        onCancel: null,
        
        open(options = {}) {
            if (this.timerInterval) {
                clearInterval(this.timerInterval);
            }
            
            this.type = options.type || 'info';
            this.title = options.title || this.getDefaultTitle();
            this.message = options.message || '';
            this.details = options.details || '';
            this.timer = options.timer || (this.type === 'success' ? 3 : 5);
            this.timerPercent = 100;
            this.autoClose = options.autoClose !== undefined ? options.autoClose : (this.type === 'success');
            this.showCancelButton = options.showCancelButton || false;
            this.confirmButtonText = options.confirmButtonText || 'OK';
            this.cancelButtonText = options.cancelButtonText || 'Cancel';
            this.onConfirm = options.onConfirm || null;
            this.onCancel = options.onCancel || null;
            
            this.showAlertModal = true;
            document.body.style.overflow = 'hidden';
            
            if (this.autoClose) {
                this.startTimer();
            }
        },
        
        getDefaultTitle() {
            const titles = {
                success: 'Success! ✅',
                error: 'Error! ❌',
                warning: 'Warning! ⚠️',
                info: 'Information ℹ️'
            };
            return titles[this.type] || 'Alert';
        },
        
        startTimer() {
            const interval = setInterval(() => {
                if (this.timer > 0) {
                    this.timer--;
                    this.timerPercent = (this.timer / (this.type === 'success' ? 3 : 5)) * 100;
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
            this.showAlertModal = false;
            document.body.style.overflow = '';
        },
        
        confirmAction() {
            if (this.onConfirm && typeof this.onConfirm === 'function') {
                this.onConfirm();
            }
            this.closeModal();
        },
        
        cancelAction() {
            if (this.onCancel && typeof this.onCancel === 'function') {
                this.onCancel();
            }
            this.closeModal();
        }
    }));
});

// Make showAlertModal globally available
window.showAlertModal = function(options) {
    const modalEl = document.querySelector('[x-data="alertModal()"]');
    if (modalEl && modalEl.__x) {
        modalEl.__x.$data.open(options);
    } else {
        console.error('Alert modal not found - falling back to browser alert');
        alert(options.message || 'Alert');
    }
};
</script>
@endpush

<!-- Modal HTML -->
<div x-data="alertModal()" 
     x-show="showAlertModal" 
     x-cloak
     @keydown.escape.window="closeModal()"
     class="fixed inset-0 z-[99999] overflow-y-auto"
     style="display: none;">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-all duration-300" 
         x-show="showAlertModal"
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
        <div class="relative transform overflow-hidden rounded-2xl bg-white text-left shadow-xl transition-all sm:my-8 sm:w-full sm:max-w-lg"
             x-show="showAlertModal"
             x-transition:enter="ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave="ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
             x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95">
            
            <!-- Animated Icon based on type -->
            <div class="absolute -top-12 left-1/2 transform -translate-x-1/2">
                <div class="relative">
                    <div class="w-24 h-24 rounded-full flex items-center justify-center shadow-lg animate-bounce"
                         :class="{
                             'bg-gradient-to-br from-green-400 to-green-600': type === 'success',
                             'bg-gradient-to-br from-red-400 to-red-600': type === 'error',
                             'bg-gradient-to-br from-yellow-400 to-yellow-600': type === 'warning',
                             'bg-gradient-to-br from-blue-400 to-blue-600': type === 'info'
                         }">
                        <i class="fas text-white text-4xl"
                           :class="{
                               'fa-check-circle': type === 'success',
                               'fa-exclamation-circle': type === 'error',
                               'fa-exclamation-triangle': type === 'warning',
                               'fa-info-circle': type === 'info'
                           }"></i>
                    </div>
                    <div class="absolute inset-0 w-24 h-24 rounded-full animate-ping opacity-75"
                         :class="{
                             'bg-green-400': type === 'success',
                             'bg-red-400': type === 'error',
                             'bg-yellow-400': type === 'warning',
                             'bg-blue-400': type === 'info'
                         }"></div>
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
                         class="mt-4 p-4 rounded-xl border"
                         :class="{
                             'bg-green-50 border-green-200': type === 'success',
                             'bg-red-50 border-red-200': type === 'error',
                             'bg-yellow-50 border-yellow-200': type === 'warning',
                             'bg-blue-50 border-blue-200': type === 'info'
                         }">
                        <div class="flex items-start space-x-3">
                            <div class="flex-shrink-0">
                                <i class="fas text-sm"
                                   :class="{
                                       'fa-check-circle text-green-600': type === 'success',
                                       'fa-exclamation-circle text-red-600': type === 'error',
                                       'fa-exclamation-triangle text-yellow-600': type === 'warning',
                                       'fa-info-circle text-blue-600': type === 'info'
                                   }"></i>
                            </div>
                            <div class="flex-1 text-left">
                                <p class="text-sm font-medium mb-1"
                                   :class="{
                                       'text-green-800': type === 'success',
                                       'text-red-800': type === 'error',
                                       'text-yellow-800': type === 'warning',
                                       'text-blue-800': type === 'info'
                                   }">Details:</p>
                                <div class="text-xs space-y-1"
                                     :class="{
                                         'text-green-700': type === 'success',
                                         'text-red-700': type === 'error',
                                         'text-yellow-700': type === 'warning',
                                         'text-blue-700': type === 'info'
                                     }"
                                     x-html="details"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Auto-close Timer Bar for success -->
                    <div x-show="type === 'success'" class="mt-6">
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

                    <!-- Auto-close Timer Bar for others -->
                    <div x-show="autoClose && type !== 'success'" class="mt-6">
                        <div class="relative pt-1">
                            <div class="flex mb-2 items-center justify-between">
                                <div>
                                    <span class="text-xs font-semibold inline-block"
                                       :class="{
                                           'text-red-600': type === 'error',
                                           'text-yellow-600': type === 'warning',
                                           'text-blue-600': type === 'info'
                                       }">
                                        Closing in <span x-text="timer"></span> seconds
                                    </span>
                                </div>
                                <div class="text-right">
                                    <span class="text-xs font-semibold inline-block"
                                       :class="{
                                           'text-red-600': type === 'error',
                                           'text-yellow-600': type === 'warning',
                                           'text-blue-600': type === 'info'
                                       }">
                                        <button @click="closeModal()" class="hover:underline">Close now</button>
                                    </span>
                                </div>
                            </div>
                            <div class="overflow-hidden h-2 text-xs flex rounded"
                                 :class="{
                                     'bg-red-200': type === 'error',
                                     'bg-yellow-200': type === 'warning',
                                     'bg-blue-200': type === 'info'
                                 }">
                                <div class="animate-progress-bar shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center"
                                     :style="{ width: timerPercent + '%' }"
                                     :class="{
                                         'bg-red-500': type === 'error',
                                         'bg-yellow-500': type === 'warning',
                                         'bg-blue-500': type === 'info'
                                     }">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="mt-6 flex flex-col sm:flex-row gap-3 justify-center">
                        <button type="button" @click="confirmAction()"
                                class="inline-flex justify-center items-center px-5 py-2.5 border border-transparent rounded-xl shadow-sm text-sm font-medium text-white transition-all duration-200 transform hover:scale-105 focus:outline-none focus:ring-2 focus:ring-offset-2"
                                :class="{
                                    'bg-gradient-to-r from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 focus:ring-green-500': type === 'success',
                                    'bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 focus:ring-red-500': type === 'error',
                                    'bg-gradient-to-r from-yellow-500 to-yellow-600 hover:from-yellow-600 hover:to-yellow-700 focus:ring-yellow-500': type === 'warning',
                                    'bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 focus:ring-blue-500': type === 'info'
                                }">
                            <i class="fas mr-2"
                               :class="{
                                   'fa-check': type === 'success',
                                   'fa-times': type === 'error',
                                   'fa-exclamation': type === 'warning',
                                   'fa-info': type === 'info'
                               }"></i>
                            <span x-text="confirmButtonText"></span>
                        </button>
                        
                        <button x-show="showCancelButton" type="button" @click="cancelAction()"
                                class="inline-flex justify-center items-center px-5 py-2.5 border border-gray-300 rounded-xl shadow-sm text-sm font-medium text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition-all duration-200">
                            <i class="fas fa-times mr-2"></i>
                            <span x-text="cancelButtonText"></span>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Close button top right -->
            <button @click="closeModal()" class="absolute top-3 right-3 text-gray-400 hover:text-gray-600 transition-colors">
                <i class="fas fa-times text-xl"></i>
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