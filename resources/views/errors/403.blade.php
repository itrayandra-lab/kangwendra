@extends('layouts.client.app')
@section('content')
    <div class="col-span-12 my-25">
        <div class="w-full flex items-center flex-wrap justify-center gap-10">
            <div class="grid gap-4 w-70">
                <svg class="mx-auto" xmlns="http://www.w3.org/2000/svg" width="116" height="121" viewBox="0 0 116 121"
                    fill="none">
                    <path
                        d="M0.206909 63.57C0.206909 31.7659 25.987 6.12817 57.6487 6.12817C89.2631 6.12817 115.079 31.7541 115.079 63.57C115.079 77.0648 110.43 89.4805 102.627 99.2755C91.8719 112.853 75.4363 121 57.6487 121C39.7426 121 23.4018 112.794 12.6582 99.2755C4.85538 89.4805 0.206909 77.0648 0.206909 63.57Z"
                        fill="#EEF2FF" />
                    <path
                        d="M72.7942 0.600875L72.7942 0.600762L72.7836 0.599331C72.3256 0.537722 71.8622 0.5 71.3948 0.5H22.1643C17.1256 0.5 13.0403 4.56385 13.0403 9.58544V107.286C13.0403 112.308 17.1256 116.372 22.1643 116.372H93.1214C98.1725 116.372 102.245 112.308 102.245 107.286V29.4482C102.245 28.7591 102.17 28.0815 102.019 27.4162L99.2941 22.7574C97.9566 20.5287 75.5462 2.89705 72.7942 0.600875Z"
                        fill="white" stroke="#E5E7EB" />
                    <text x="25" y="69" font-family="Arial, sans-serif" font-size="40" font-weight="bold"
                        fill="#6B7280">403</text>
                    <rect x="28.9248" y="16.3846" width="30.7692" height="2.05128" rx="1.02564" fill="#4F46E5" />
                    <rect x="28.9248" y="100.487" width="41.0256" height="4.10256" rx="2.05128" fill="#A5B4FC" />
                    <rect x="28.9248" y="22.5385" width="10.2564" height="2.05128" rx="1.02564" fill="#4F46E5" />
                    <circle cx="42.2582" cy="23.5641" r="1.02564" fill="#4F46E5" />
                    <circle cx="46.3607" cy="23.5641" r="1.02564" fill="#4F46E5" />
                    <circle cx="50.4633" cy="23.5641" r="1.02564" fill="#4F46E5" />
                </svg>
                <div>
                    <h2 class="text-center text-black text-base font-semibold leading-relaxed pb-1">Uppss.. Akses Tidak
                        Diizinkan!</h2>
                    <p class="text-center text-black text-sm font-normal leading-snug pb-4">Maaf, Anda tidak memiliki izin
                        untuk mengakses halaman ini.</p>
                    <div class="flex gap-3">
                        <button onclick="window.history.back()"
                            class="w-full px-3 py-2 bg-indigo-600 cursor-pointer active:cursor-progress hover:bg-indigo-700 transition-all duration-500 rounded-full text-white text-xs font-semibold leading-4">
                            Kembali ke Sebelumnya </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection


