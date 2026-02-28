<div class="col-span-12 ">
    @include('widget.client.header-title', ['title' => 'Tagar'])
    <div class="space-y-2 container">
        <div class="grid lg:grid-cols-3 md:grid-cols-2 gap-6">
            @forelse ($data as $item)
                <a href="/tag/{{ $item->slug }}">
                    <div class="flex items-center gap-5 bg-white p-2 rounded">
                        <span class="flex items-center justify-center rounded-md w-12 h-12 bg-blue-100">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                stroke-linejoin="round" class="lucide lucide-hash text-blue-500">
                                <line x1="4" x2="20" y1="9" y2="9" />
                                <line x1="4" x2="20" y1="15" y2="15" />
                                <line x1="10" x2="8" y1="3" y2="21" />
                                <line x1="16" x2="14" y1="3" y2="21" />
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


