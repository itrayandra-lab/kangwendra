
<div class="text-center bg-white shadow-inner py-3">
    <a href="#" class="flex items-center justify-center mb-5 text-2xl font-semibold text-gray-900">
        <img src="{{ $meta->logo }}" class="h-12 mr-3" alt="Logo" width="100">
    </a>

    <span class="block text-sm text-center text-gray-500">
        © {{ now()->format('Y') }} <a href="{{ $meta->domain }}" class="hover:underline">{{ $meta->web_name }}™</a>. All
        Rights Reserved. v{{ $meta->version }}
    </span>

    <ul class="flex justify-center my-5 space-x-5">
        @if (!empty($meta->email) && $meta->email !== '#')
            <li>
                <a href="mailto:{{ $meta->email }}" aria-label="Send an email to {{ $meta->email }}"
                    class="text-gray-500 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-mails">
                        <rect width="16" height="13" x="6" y="4" rx="2" />
                        <path d="m22 7-7.1 3.78c-.57.3-1.23.3-1.8 0L6 7" />
                        <path d="M2 8v11c0 1.1.9 2 2 2h14" />
                    </svg>
                </a>
            </li>
        @endif

        <!-- Phone Number -->
        @if (!empty($meta->phone_number) && $meta->phone_number !== '#')
            <li>
                <a href="tel:{{ $meta->phone_number }}" aria-label="Call us at {{ $meta->phone_number }}"
                    class="text-gray-500 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-phone">
                        <path
                            d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72 12.84 12.84 0 0 0 .7 2.81 2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45 12.84 12.84 0 0 0 2.81.7A2 2 0 0 1 22 16.92z" />
                    </svg>
                </a>
            </li>
        @endif


        @if (!empty($meta->google_maps) && $meta->google_maps !== '#')
            <li>
                <a href="{{ $meta->google_maps }}" class="text-gray-500 hover:text-gray-900">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-map-pinned">
                        <path
                            d="M18 8c0 3.613-3.869 7.429-5.393 8.795a1 1 0 0 1-1.214 0C9.87 15.429 6 11.613 6 8a6 6 0 0 1 12 0" />
                        <circle cx="12" cy="8" r="2" />
                        <path
                            d="M8.714 14h-3.71a1 1 0 0 0-.948.683l-2.004 6A1 1 0 0 0 3 22h18a1 1 0 0 0 .948-1.316l-2-6a1 1 0 0 0-.949-.684h-3.712" />
                    </svg>
                </a>
            </li>
        @endif


        <!-- Facebook -->
        @if (!empty($meta->facebook_link) && $meta->facebook_link !== '#')
            <li>
                <a href="{{ $meta->facebook_link }}" rel="noopener noreferrer" target="_blank"
                    aria-label="Visit our Facebook page" class="text-gray-500 hover:text-gray-900">
                    <!-- Lucide Facebook SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <path d="M18 2h-3a5 5 0 0 0-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 0 1 1-1h3z" />
                    </svg>
                </a>
            </li>
        @endif

        <!-- Instagram -->
        @if (!empty($meta->instagram_link) && $meta->instagram_link !== '#')
            <li>
                <a href="{{ $meta->instagram_link }}" rel="noopener noreferrer" target="_blank"
                    aria-label="Visit our Instagram page" class="text-gray-500 hover:text-gray-900">
                    <!-- Lucide Instagram SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <rect x="2" y="2" width="20" height="20" rx="5" ry="5" />
                        <path d="M16 11.37A4 4 0 1 1 12.63 8 4 4 0 0 1 16 11.37z" />
                        <line x1="17.5" y1="6.5" x2="17.51" y2="6.5" />
                    </svg>
                </a>
            </li>
        @endif

        <!-- Twitter -->
        @if (!empty($meta->twitter_link) && $meta->twitter_link !== '#')
            <li>
                <a href="{{ $meta->twitter_link }}" rel="noopener noreferrer" target="_blank"
                    aria-label="Visit our Twitter page" class="text-gray-500 hover:text-gray-900">
                    <!-- Lucide Twitter SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="w-5 h-5">
                        <path
                            d="M23 3a10.9 10.9 0 0 1-3.14 1.53 4.48 4.48 0 0 0-7.86 3v1A10.66 10.66 0 0 1 3 4s-4 9 5 13a11.64 11.64 0 0 1-7 2c9 5 20 0 20-11.5a4.5 4.5 0 0 0-.08-.83A7.72 7.72 0 0 0 23 3z" />
                    </svg>
                </a>
            </li>
        @endif

        <!-- YouTube -->
        @if (!empty($meta->youtube_link) && $meta->youtube_link !== '#')
            <li>
                <a href="{{ $meta->youtube_link }}" rel="noopener noreferrer" target="_blank"
                    aria-label="Visit our YouTube channel" class="text-gray-500 hover:text-gray-900">
                    <!-- Lucide YouTube SVG -->
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round" class="lucide lucide-youtube">
                        <path
                            d="M2.5 17a24.12 24.12 0 0 1 0-10 2 2 0 0 1 1.4-1.4 49.56 49.56 0 0 1 16.2 0A2 2 0 0 1 21.5 7a24.12 24.12 0 0 1 0 10 2 2 0 0 1-1.4 1.4 49.55 49.55 0 0 1-16.2 0A2 2 0 0 1 2.5 17" />
                        <path d="m10 15 5-3-5-3z" />
                    </svg>
                </a>
            </li>
        @endif
    </ul>
</div>


