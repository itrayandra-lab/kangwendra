<!doctype html>
<html>

<head>
    <meta charset="utf-8" />
    <title>Maintenance</title>
    <!-- Meta Tags -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
    @vite('resources/css/app.css')
    @vite('resources/css/styles.css')

</head>
{{-- stack ui css --}}
@stack('styles')

<body>
    <div class="min-h-full">
        <main>
            @yield('header')
            <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8 ">
                <section class="py-24 relative">
                    <div class="w-full max-w-7xl px-4 md:px-5 lg:px-5 mx-auto">
                        <div class="w-full flex-col justify-center items-center lg:gap-14 gap-10 inline-flex">

                            <div class="w-full flex-col justify-center items-center gap-5 flex">
                                <img src="https://pagedone.io/asset/uploads/1718004199.png" alt="under maintenance image"
                                    class="object-cover">
                                <div class="w-full flex-col justify-center items-center gap-6 flex">
                                    <div class="w-full flex-col justify-start items-center gap-2.5 flex">
                                        <h2
                                            class="text-center text-gray-800 text-3xl font-bold font-manrope leading-normal">
                                            Mohon Maaf! Website kami sedang dalam pemeliharaan.
                                        </h2>
                                        <p class="text-center text-gray-500 text-base font-normal leading-relaxed">
                                            Saat ini website belum aktif. Kami sedang melakukan perbaikan dan akan
                                            segera kembali online.
                                        </p>
                                    </div>

                                </div>

                            </div>
                        </div>
                    </div>
                </section>


            </div>
        </main>
    </div>
</body>
{{-- stack script --}}
@stack('scripts')

</html>



