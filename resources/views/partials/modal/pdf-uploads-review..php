<!-- Include Review Modal -->
@include('partials.modal.pdf-uploads-review')

<!-- Add this to the existing x-data attribute -->
<script>
document.addEventListener('alpine:init', () => {
    // Add review modal method to Alpine store
    Alpine.store('reviewModal', {
        open(marriage, pageStatus, marriageStatus, wards) {
            const modalComponent = document.querySelector('[x-data="reviewModal()"]');
            if (modalComponent && modalComponent.__x) {
                modalComponent.__x.$data.openModal(marriage, pageStatus, marriageStatus, wards);
            }
        }
    });
});
</script>

<!-- Update the Marriage Teller Actions section -->
@if($userRole === 'marriage_teller' && $pdfPage->status === 'review_needed')
    <!-- Marriage Teller Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-gray-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900">
                <i class="fas fa-clipboard-list text-purple-500 mr-2"></i> Review Actions
            </h2>
        </div>
        <div class="p-6">
            <div class="space-y-3">
                @if($marriage)
                    <button type="button" 
                            onclick="openReviewModal({{ $marriage->toJson() }}, '{{ $pdfPage->status }}', '{{ $marriage->system_status }}', {{ json_encode($wards->toArray()) }})"
                            class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-purple-600 hover:bg-purple-700 transition-colors">
                        <i class="fas fa-edit mr-2"></i> Review & Edit Record
                    </button>
                @endif
                <div class="grid grid-cols-2 gap-4">
                    <form action="{{ route('pdf-pages.update-status', $pdfPage) }}" method="POST" id="approveForm">
                        @csrf
                        <input type="hidden" name="action" value="approve_completed">
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-3 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 transition-colors">
                            <i class="fas fa-check-double mr-2"></i> Approve & Complete
                        </button>
                    </form>
                    <form action="{{ route('pdf-pages.update-status', $pdfPage) }}" method="POST" id="rejectForm">
                        @csrf
                        <input type="hidden" name="action" value="reject_to_clerk">
                        <button type="submit" 
                                class="w-full inline-flex justify-center items-center px-4 py-3 border border-gray-300 rounded-lg shadow-sm text-sm font-medium text-orange-700 bg-white hover:bg-orange-50 transition-colors">
                            <i class="fas fa-undo mr-2"></i> Reject to Clerk
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endif

<!-- Add JavaScript function to open modal -->
<script>
function openReviewModal(marriage, pageStatus, marriageStatus, wards) {
    // Find the modal component
    const modalElement = document.querySelector('[x-data="reviewModal()"]');
    if (modalElement && modalElement.__x) {
        modalElement.__x.$data.openModal(marriage, pageStatus, marriageStatus, wards);
    } else {
        console.error('Modal component not found');
    }
}
</script>