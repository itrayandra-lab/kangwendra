@if ($data->count() != 0)
    <div class="col-span-12 md:col-span-8 p-4 rounded">
        @include('widget.client.header-title', ['title' => 'Galery', 'link' => 'albums'])
        <div class="space-y-2">
            <div class="grid grid-cols-12 gap-4">
                @foreach ($data as $item)
                    <!-- Column -->
                    <div class="col-span-6 md:col-span-6 h-38 lg:col-span-3 relative group">
                        <a href="{{ getFile($item->image) }}"
                            class="block w-full h-full">
                            <img class="w-full h-full object-cover object-center max-w-full rounded-lg"
                                src="{{ getFile($item->image) }}"
                                alt="{{ $item->title ?? 'image-photo' }}" />
                        </a>
                        <!-- Ikon Play dari Lucide -->
                        <div
                            class="absolute inset-0 flex items-center justify-center transition-opacity duration-300">
                            <a href="{{ getFile($item->image) }}"
                                data-lightbox="{{ $item->album_id }}"
                                data-title="{{ $item->album->name ?? 'Galery Image' }}"
                                class="rounded-full p-2 bg-gray-700 cursor-pointer transition-all duration-300">
                                <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                    viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                    stroke-linecap="round" stroke-linejoin="round"
                                    class="lucide lucide-monitor-play text-white">
                                    <path
                                        d="M10 7.75a.75.75 0 0 1 1.142-.638l3.664 2.249a.75.75 0 0 1 0 1.278l-3.664 2.25a.75.75 0 0 1-1.142-.64z" />
                                    <path d="M12 17v4" />
                                    <path d="M8 21h8" />
                                    <rect x="2" y="3" width="20" height="14" rx="2" />
                                </svg>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endif

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/css/lightbox.min.css">
@endpush

@push('scripts')
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"
        integrity="sha256-/xUj+3OJU5yExlq6GSYGSHk7tPXikynS7ogEvDej/m4=" crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/lightbox2/2.11.3/js/lightbox.min.js"></script>
@endpush


