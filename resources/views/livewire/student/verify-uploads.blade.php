@php
    use Illuminate\Support\Facades\Storage;
@endphp

<div class="max-w-2xl mx-auto bg-white border rounded-lg p-6">
    <h2 class="text-xl font-bold mb-2">Student Verification</h2>
    <p class="text-sm text-gray-600 mb-4">
        Please upload your COR and School ID for coordinator review. You cannot enter the class until approved.
    </p>

    {{-- Auto-Redirect Alert (Hidden by default) --}}
    <div id="redirect-alert" class="hidden bg-green-50 border-l-4 border-green-400 p-4 mb-4">
        <div class="flex items-center">
            <div class="flex-shrink-0">
                <svg class="h-5 w-5 text-green-400" viewBox="0 0 20 20" fill="currentColor">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                </svg>
            </div>
            <div class="ml-3">
                <p class="text-sm font-medium text-green-800">
                    ✓ Verification Approved!
                </p>
                <p class="text-sm text-green-700 mt-1">
                    Redirecting to dashboard in <span id="countdown" class="font-bold">3</span> seconds...
                </p>
            </div>
        </div>
    </div>

    {{-- Flash messages --}}
    @if (session('message'))
        <div class="bg-green-50 border border-green-400 text-green-700 p-2 rounded mb-3">
            {{ session('message') }}
        </div>
    @endif
    @if (session('error'))
        <div class="bg-red-50 border border-red-400 text-red-700 p-2 rounded mb-3">
            {{ session('error') }}
        </div>
    @endif

    {{-- Invalid link message --}}
    @if (!empty($invalidMessage))
        <div class="bg-yellow-50 border border-yellow-400 text-yellow-800 p-2 rounded mb-3">
            {{ $invalidMessage }}
        </div>
    @endif

    {{-- Current status + view links --}}
    @if (!empty($current))
        <div class="mb-4 text-sm">
            <span class="font-semibold">Current status:</span>
            <span id="status-badge" @class([
                'uppercase px-2 py-1 rounded',
                'bg-green-100 text-green-700'   => $current->status === 'approved',
                'bg-yellow-100 text-yellow-700' => $current->status === 'pending',
                'bg-red-100 text-red-700'       => $current->status === 'rejected',
                'bg-gray-100 text-gray-700'     => ! in_array($current->status, ['approved','pending','rejected']),
            ])>
                {{ $current->status }}
            </span>
        </div>

        {{-- View uploaded files --}}
        <div class="mb-4 text-sm text-gray-700 space-x-4">
            @if (!empty($current->cor_file))
                <a class="text-blue-600 underline"
                   href="{{ route('student.verification.view', ['stream' => $current->stream_id, 'type' => 'cor']) }}"
                   target="_blank" rel="noopener">View COR</a>

                @if (Storage::disk('public')->exists($current->cor_file))
                    <a class="text-gray-600 underline"
                       href="{{ asset('storage/' . ltrim($current->cor_file, '/')) }}"
                       target="_blank" rel="noopener">(open via /storage)</a>
                @endif
            @endif

            @if (!empty($current->id_file))
                <a class="text-blue-600 underline"
                   href="{{ route('student.verification.view', ['stream' => $current->stream_id, 'type' => 'id']) }}"
                   target="_blank" rel="noopener">View ID</a>

                @if (Storage::disk('public')->exists($current->id_file))
                    <a class="text-gray-600 underline"
                       href="{{ asset('storage/' . ltrim($current->id_file, '/')) }}"
                       target="_blank" rel="noopener">(open via /storage)</a>
                @endif
            @endif
        </div>
    @endif

    <form wire:submit.prevent="submit" class="space-y-3">
        <div>
            <label class="block text-sm font-medium mb-1">Certificate of Registration (COR)</label>
            <input type="file"
                   wire:model="cor"
                   accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full border rounded p-2">
            @error('cor') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">School ID (front/back or PDF)</label>
            <input type="file"
                   wire:model="idcard"
                   accept=".pdf,.jpg,.jpeg,.png"
                   class="w-full border rounded p-2">
            @error('idcard') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div>
            <label class="block text-sm font-medium mb-1">Notes (optional)</label>
            <input type="text"
                   wire:model.defer="notes"
                   class="w-full border rounded p-2"
                   placeholder="Section / remarks">
            @error('notes') <div class="text-red-600 text-sm mt-1">{{ $message }}</div> @enderror
        </div>

        <div class="text-xs text-gray-500">Accepted: PDF/JPG/PNG • Max 10MB each</div>

        <div class="text-sm text-blue-700"
             wire:loading
             wire:target="cor,idcard,submit">
            Processing… please wait.
        </div>

        <button type="submit"
                class="bg-blue-600 text-white px-4 py-2 rounded disabled:opacity-60"
                wire:loading.attr="disabled"
                wire:target="submit">
            Submit for Review
        </button>
    </form>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get current status from the page
        const statusBadge = document.getElementById('status-badge');
        const currentStatus = statusBadge ? statusBadge.textContent.trim().toLowerCase() : '';
        
        console.log('🔍 Initial Status:', currentStatus);
        
        // If already approved, redirect immediately
        if (currentStatus === 'approved') {
            console.log('✅ Status is approved! Starting redirect...');
            showRedirectMessage();
        } else if (currentStatus === 'pending') {
            console.log('⏳ Status is pending. Starting polling...');
            startStatusPolling();
        }
        
        /**
         * Show countdown alert and redirect to dashboard
         */
        function showRedirectMessage() {
            const redirectAlert = document.getElementById('redirect-alert');
            const countdownEl = document.getElementById('countdown');
            
            if (!redirectAlert || !countdownEl) {
                console.error('❌ Redirect elements not found');
                return;
            }
            
            // Show the alert
            redirectAlert.classList.remove('hidden');
            
            let seconds = 3;
            countdownEl.textContent = seconds;
            
            const interval = setInterval(() => {
                seconds--;
                countdownEl.textContent = seconds;
                console.log(`⏱️ Redirecting in ${seconds}...`);
                
                if (seconds <= 0) {
                    clearInterval(interval);
                    
                    // Get stream ID from current URL or data
                    const streamId = '{{ $current->stream_id ?? $streamId }}';
                    const redirectUrl = `/student-class/show/${streamId}`;
                    
                    console.log('🚀 Redirecting to:', redirectUrl);
                    window.location.href = redirectUrl;
                }
            }, 1000);
        }
        
        /**
         * Poll server every 5 seconds to check verification status
         */
        function startStatusPolling() {
            let pollCount = 0;
            const maxPolls = 360; // 30 minutes (360 * 5 seconds)
            
            const pollInterval = setInterval(async () => {
                pollCount++;
                
                try {
                    console.log(`🔄 Polling attempt ${pollCount}...`);
                    
                    const response = await fetch('/student/verification/check-status', {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    
                    if (!response.ok) {
                        console.error('❌ Polling failed:', response.status);
                        return;
                    }
                    
                    const data = await response.json();
                    console.log('📊 Poll result:', data);
                    
                    // Check if approved
                    if (data.status === 'approved') {
                        console.log('✅ APPROVED! Stopping polling and redirecting...');
                        clearInterval(pollInterval);
                        
                        // Update UI
                        if (statusBadge) {
                            statusBadge.textContent = 'approved';
                            statusBadge.className = 'uppercase px-2 py-1 rounded bg-green-100 text-green-700';
                        }
                        
                        // Show redirect message
                        showRedirectMessage();
                    }
                    
                    // Check if rejected
                    if (data.status === 'rejected') {
                        console.log('❌ Verification rejected');
                        clearInterval(pollInterval);
                        
                        // Show rejection alert
                        alert('Your verification was rejected. Please check your documents and resubmit.\n\nReason: ' + (data.rejection_reason || 'Not specified'));
                        location.reload();
                    }
                    
                } catch (error) {
                    console.error('❌ Polling error:', error);
                }
                
                // Stop polling after max attempts
                if (pollCount >= maxPolls) {
                    console.log('⏹️ Max polling attempts reached. Stopping.');
                    clearInterval(pollInterval);
                }
                
            }, 5000); // Poll every 5 seconds
        }
        
        // Listen for Livewire updates
        Livewire.on('ojtInfoUpdated', () => {
            console.log('📤 Form submitted! Starting polling...');
            setTimeout(() => {
                startStatusPolling();
            }, 2000);
        });
    });
</script>
@endpush