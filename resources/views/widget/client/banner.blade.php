@if ($data)
    <div class="col-span-12 py-2">
        @foreach ($data as $item)
            <div class="mx-auto w-full rounded mb-2 relative group">
                <div class="flex space-x-4">
                    <div class="w-full h-30 lg:h-50 rounded bg-gray-200 relative overflow-hidden">
                        <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}"
                            class="w-full h-full object-cover rounded transition-opacity duration-300 group-hover:opacity-80">
                        <div
                            class="absolute inset-0 flex items-center justify-center  bg-opacity-0 group-hover:bg-opacity-30 transition-all duration-300">
                            <a href="/banner/{{ $item->slug }}" title="detail banner"
                                class="text-white bg-gray-700 p-1 rounded-full mr-4 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                    xmlns="http://www.w3.org/2000/svg">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                            </a>
                            <a href="/banners" title="Lihat Lainnya"
                                class="text-white bg-gray-700 p-1 rounded-full opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none"
                                    stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                    stroke-linejoin="round" class="lucide lucide-circle-ellipsis w-6 h-6">
                                    <circle cx="12" cy="12" r="10" />
                                    <path d="M17 12h.01" />
                                    <path d="M12 12h.01" />
                                    <path d="M7 12h.01" />
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
@endif


