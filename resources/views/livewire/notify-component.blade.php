<div
    class="notify pointer-events-none fixed inset-x-0 bottom-4 z-[140] flex justify-center px-4 sm:inset-x-auto sm:right-6 sm:bottom-6 sm:px-0 sm:justify-end">
    <div
        x-data="{
            showToastr: @entangle('showToastr').live,
            toastKey: @entangle('toastKey').live,
            hideTimeout: null,
            queueHide() {
                clearTimeout(this.hideTimeout);
                this.showToastr = true;
                this.hideTimeout = setTimeout(() => {
                    this.showToastr = false;
                }, 4000);
            }
        }"
        x-init="$watch('toastKey', value => { if (value) { queueHide(); } })"
        x-show="showToastr"
        x-cloak
        x-transition:enter="transform ease-out duration-250 transition"
        x-transition:enter-start="translate-y-3 opacity-0 sm:translate-y-0 sm:translate-x-3"
        x-transition:enter-end="translate-y-0 opacity-100 sm:translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 translate-y-2"
        class="pointer-events-auto flex w-full max-w-sm items-center gap-3 rounded-[8px] border border-white/10 bg-[#1A1A1A] px-4 py-3 text-sm text-white shadow-[0_18px_40px_rgba(0,0,0,0.45)]"
    >
        <div class="flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-full bg-emerald-500/15 text-emerald-400">
            <svg
                xmlns="http://www.w3.org/2000/svg"
                viewBox="0 0 24 24"
                fill="none"
                stroke="currentColor"
                stroke-width="2.2"
                stroke-linecap="round"
                stroke-linejoin="round"
                class="h-4 w-4"
                aria-hidden="true"
            >
                <path d="M20 6 9 17l-5-5"/>
            </svg>
        </div>

        <div class="min-w-0 flex-1">
            <p class="text-sm font-medium leading-5 text-white">
                {{ $message['message'] ?? '' }}
            </p>
        </div>

        <button
            type="button"
            @click="showToastr = false"
            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full text-white/45 transition hover:bg-white/5 hover:text-white focus:outline-none"
            aria-label="{{ __('Close notification') }}"
        >
            <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="h-4 w-4" aria-hidden="true">
                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 0 1 1.414 0L10 8.586l4.293-4.293a1 1 0 1 1 1.414 1.414L11.414 10l4.293 4.293a1 1 0 0 1-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 0 1-1.414-1.414L8.586 10 4.293 5.707a1 1 0 0 1 0-1.414Z" clip-rule="evenodd"/>
            </svg>
        </button>
    </div>
</div>
