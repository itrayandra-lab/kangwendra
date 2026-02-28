
<div class="header-title flex justify-between items-center border-b-2 border-dashed border-gray-200 pb-4 mb-4">
    <h2 class="lg:text-lg text-sm font-bold relative">
        {{ $title }}
        <svg width="100" height="3" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path fill="#0A4B94" d="M0 0h48v4H0z"></path>
            <path fill="#1E6FBA" d="M52 0h16v4H52z"></path>
            <path fill="#3B9AE1" d="M72 0h8v4h-8z"></path>
            <path fill="#7CC1F5" d="M84 0h4v4h-4z"></path>
            <path fill="#A8D8F9" d="M90 0h4v4h-4z"></path>
            <path fill="#D4EBFC" d="M96 0h4v4h-4z"></path>
            <path fill="#E8F4FE" d="M102 0h4v4h-4z"></path>
            <path fill="#F5FAFF" d="M108 0h4v4h-4z"></path>
        </svg>
    </h2>
    @if (!empty($link))
        <a href="{{ $link }}" class="text-sm text-gray-500 hover:underline">Lihat Semua</a>
    @endif
</div>


