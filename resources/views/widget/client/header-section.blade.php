<section class="shadow-sm shadow-gray-50" style="background: radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 0% 0% / 64px 64px, radial-gradient(circle, transparent 20%, #ffffff 20%, #ffffff 80%, transparent 80%, transparent) 32px 32px / 64px 64px, linear-gradient(#f2f2f2 2px, transparent 2px) 0px -1px / 32px 32px, linear-gradient(90deg, #f2f2f2 2px, #ffffff 2px) -1px 0px / 32px 32px #ffffff; background-size: 64px 64px, 64px 64px, 32px 32px, 32px 32px; background-color: #ffffff;">
    <div class="mx-auto max-w-7xl px-2 py-6 sm:px-6 lg:px-8">
        <div class="header-title flex flex-col justify-between items-start border-b-2 border-dashed border-gray-200 pb-4 mb-4">
            <div class="text-sm text-gray-500 flex items-center mb-2">
                {{ $segment }} <span class="mx-1">
                    <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                        class="lucide lucide-chevron-right">
                        <path d="m9 18 6-6-6-6" />
                    </svg>
                </span> {{ $data }}
            </div>

            <div class="flex justify-between items-center w-full">
                <div class="relative">
                    <h2 class="lg:text-4xl text-2xl font-bold text-gray-900">
                        {{ $data }}
                    </h2>
                    <svg width="180" class="mt-1" height="6" fill="none" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#0A4B94" d="M0 0h48v4H0z"></path>
                        <path fill="#1E6FBA" d="M52 0h16v4H52z"></path>
                        <path fill="#3B9AE1" d="M72 0h8v4h-8z"></path>
                        <path fill="#7CC1F5" d="M84 0h4v4h-4z"></path>
                        <path fill="#A8D8F9" d="M90 0h4v4h-4z"></path>
                        <path fill="#D4EBFC" d="M96 0h4v4h-4z"></path>
                        <path fill="#E8F4FE" d="M102 0h4v4h-4z"></path>
                        <path fill="#F5FAFF" d="M108 0h4v4h-4z"></path>
                    </svg>
                </div>

                <!-- Search Bar -->
                <form class="relative">
                    <input type="search" placeholder="Search" name="qr" value="{{ request('qr') }}"
                        class="w-48 py-2 pl-8 pr-4 text-sm border border-gray-300 rounded-full focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                        fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"
                        stroke-linejoin="round"
                        class="lucide lucide-search absolute left-2 top-1/2 transform -translate-y-1/2 text-gray-400">
                        <circle cx="11" cy="11" r="8" />
                        <path d="m21 21-4.3-4.3" />
                    </svg>
                </form>
            </div>
        </div>
    </div>
</section>

