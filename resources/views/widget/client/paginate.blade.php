<div class="flex items-center gap-8 my-5 m-auto justify-center">
    <!-- Previous Button -->
    <button 
        {{ $data->onFirstPage() ? 'disabled' : '' }}
        class="rounded-md border border-slate-300 p-2.5 cursor-pointer active:cursor-progress text-center text-sm transition-all shadow-sm hover:shadow-lg text-slate-600 hover:text-white hover:bg-slate-800 hover:border-slate-800 focus:text-white focus:bg-slate-800 focus:border-slate-800 active:border-slate-800 active:text-white active:bg-slate-800 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
        type="button"
        onclick="window.location='{{ $data->appends(['qr' => $searchQuery])->previousPageUrl() }}'">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path fill-rule="evenodd"
                d="M11.03 3.97a.75.75 0 0 1 0 1.06l-6.22 6.22H21a.75.75 0 0 1 0 1.5H4.81l6.22 6.22a.75.75 0 1 1-1.06 1.06l-7.5-7.5a.75.75 0 0 1 0-1.06l7.5-7.5a.75.75 0 0 1 1.06 0Z"
                clip-rule="evenodd" />
        </svg>
    </button>

    <!-- Page Info -->
    <p class="text-slate-600">
        Page <strong class="text-slate-800">{{ $data->currentPage() }}</strong> of <strong class="text-slate-800">{{ $data->lastPage() }}</strong>
    </p>

    <!-- Next Button -->
    <button 
        {{ $data->hasMorePages() ? '' : 'disabled' }}
        class="rounded-md border border-slate-300 p-2.5 cursor-pointer active:cursor-progress text-center text-sm transition-all shadow-sm hover:shadow-lg text-slate-600 hover:text-white hover:bg-slate-800 hover:border-slate-800 focus:text-white focus:bg-slate-800 focus:border-slate-800 active:border-slate-800 active:text-white active:bg-slate-800 disabled:pointer-events-none disabled:opacity-50 disabled:shadow-none"
        type="button"
        onclick="window.location='{{ $data->appends(['qr' => $searchQuery])->nextPageUrl() }}'">
        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4">
            <path fill-rule="evenodd"
                d="M12.97 3.97a.75.75 0 0 1 1.06 0l7.5 7.5a.75.75 0 0 1 0 1.06l-7.5 7.5a.75.75 0 1 1-1.06-1.06l6.22-6.22H3a.75.75 0 0 1 0-1.5h16.19l-6.22-6.22a.75.75 0 0 1 0-1.06Z"
                clip-rule="evenodd" />
        </svg>
    </button>
</div>

