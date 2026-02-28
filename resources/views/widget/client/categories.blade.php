<div class="col-span-12 ">
    @include('widget.client.header-title', ['title' => 'Kategori'])
    <div class="space-y-2 container">
        <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
            @forelse ($data as $item)
                <a href="/{{ $item->slug }}">
                    <div class="flex items-center gap-5 bg-white p-2 rounded">
                        <span class="flex items-center justify-center rounded-md w-12 h-12 bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-tags text-blue-500">
                                <path d="m15 5 6.3 6.3a2.4 2.4 0 0 1 0 3.4L17 19" />
                                <path
                                    d="M9.586 5.586A2 2 0 0 0 8.172 5H3a1 1 0 0 0-1 1v5.172a2 2 0 0 0 .586 1.414L8.29 18.29a2.426 2.426 0 0 0 3.42 0l3.58-3.58a2.426 2.426 0 0 0 0-3.42z" />
                                <circle cx="6.5" cy="9.5" r=".5" fill="currentColor" />
                            </svg>
                        </span>
                        <h5>{{ $item->name }}</h5>
                    </div>
                </a>
            @empty
        </div>
        @for ($i = 0; $i < 5; $i++)
            <div class="col-span-12 md:col-span-6">
                @include('widget.client.load-data-1')
            </div>
        @endfor
        @endforelse

    </div>
</div>


