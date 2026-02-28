<div class="bg-[#111111] text-white h-[50px] w-full relative border-b border-gray-800">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full flex items-center justify-between relative overflow-hidden">

        <div id="trending-section" class="flex items-center space-x-3 w-full sm:w-auto overflow-hidden transition-all duration-300 sm:opacity-100">
            <div class="flex items-center justify-center bg-transparent flex-shrink-0 z-10 bg-[#111111] pr-2">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-amber-500">
                    <path fill-rule="evenodd" d="M12.963 2.286a.75.75 0 0 0-1.071-.136 9.742 9.742 0 0 0-3.539 6.177 7.547 7.547 0 0 1-1.705-1.715.75.75 0 0 0-1.152-.082A9 9 0 1 0 15.68 4.534a7.46 7.46 0 0 1-2.717-2.248ZM15.75 14.25a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z" clip-rule="evenodd" />
                </svg>
            </div>
            <div class="flex-1 overflow-hidden relative h-6 flex items-center sm:block sm:h-auto">
                <div class="flex items-center text-sm whitespace-nowrap w-full">
                    <span class="text-gray-400 font-bold mr-2 uppercase tracking-wide text-xs hidden sm:inline-block flex-shrink-0">Trending:</span>
                    <span class="font-medium text-gray-200 sm:truncate sm:max-w-md">
                        @foreach (App\Models\Posts::getTrending(1) as $item)
                            <a href="/news/{{ $item->slug }}" target="_blank" rel="noopener noreferrer">{{ $item->title }}</a>
                        @endforeach
                    </span>
                </div>
            </div>
        </div>

        <div id="social-section" class="flex items-center lg:space-x-5 space-x-3 flex-shrink-0 bg-[#111111] pl-2 z-10">
            @if (!empty($meta->facebook_link) && $meta->facebook_link !== '#')
            <a href="{{ $meta->facebook_link }}" class="text-white hover:text-blue-500 transition-colors ">
                <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
            </a>
            @endif
            @if (!empty($meta->instagram_link) && $meta->instagram_link !== '#')
            <a href="{{ $meta->instagram_link }}" class="text-white hover:text-pink-500 transition-colors ">
                <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24"><path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.919 0 3.274-.012 3.654-.069 4.919-.148 3.228-1.691 4.771-4.919 4.919-1.265.058-1.645.069-4.919.069-3.274 0-3.654-.011-4.919-.069-3.228-.148-4.771-1.691-4.919-4.919-.058-1.265-.069-1.645-.069-4.919 0-3.274.012-3.654.069-4.919.148-3.228 1.691-4.771 4.919-4.919 1.265-.058 1.645-.069 4.919-.069zM12 0C8.741 0 8.333.014 7.053.072 2.695.272.273 2.69.073 7.052.014 8.333 0 8.741 0 12c0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98C8.333 23.986 8.741 24 12 24c3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.668-.072-4.948-.2-4.354-2.618-6.782-6.979-6.98C15.668.014 15.259 0 12 0zm0 5.838a6.162 6.162 0 1 0 0 12.324 6.162 6.162 0 0 0 0-12.324zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.406-11.845a1.44 1.44 0 1 0 0 2.881 1.44 1.44 0 0 0 0-2.881z"/></svg>
            </a>
            @endif
            @if (!empty($meta->email) && $meta->email !== '#')
            <a href="mailto:{{ $meta->email }}" class="text-white hover:text-gray-300 transition-colors ">
                <svg fill="currentColor" class="w-4 h-4" viewBox="0 0 24 24"><path d="M20 4H4c-1.1 0-1.99.9-1.99 2L2 18c0 1.1.9 2 2 2h16c1.1 0 2-.9 2-2V6c0-1.1-.9-2-2-2zm0 4l-8 5-8-5V6l8 5 8-5v2z"/></svg>
            </a>
            @endif
            <div class="h-4 w-px bg-gray-700 mx-2 "></div>
            <button id="search-trigger" class="text-white hover:text-gray-300 focus:outline-none">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
            </button>
        </div>

        <div id="search-overlay" class="hidden absolute top-0 left-0 w-full h-full bg-[#111111] z-50 flex items-center px-4 sm:px-6 lg:px-8 opacity-0 transition-opacity duration-200 border-b border-gray-700">
            <form action="/search" class="w-full flex items-center relative">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 absolute left-0 pointer-events-none">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                </svg>
                <input type="text" name="qr" id="search-input" value="{{ request('qr') }}" class="w-full bg-transparent border-0 text-white placeholder-gray-500 focus:outline-none pl-8 pr-10 py-2 text-sm" placeholder="Type and hit enter to search..." autocomplete="off">
                <button type="button" id="close-search" class="absolute right-0 text-gray-400 hover:text-white focus:outline-none">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" />
                    </svg>
                </button>
            </form>
        </div>
    </div>
</div>


