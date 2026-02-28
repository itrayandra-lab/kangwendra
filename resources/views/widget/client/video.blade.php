@if ($data->count() != 0)
    <div class="col-span-12 bg-slate-100  rounded p-4">
        @include('widget.client.header-title', ['title' => 'Video Terpopuler', 'link' => 'videos'])
        <div class="space-y-2">
            <div class="flex gap-3 overflow-x-auto custom-scrollbar-x pb-4">
                @foreach ($data as $item)
                    <a href="/video/{{ $item->slug }}" role='status'
                        class='max-w-sm  w-73  cursor-pointer focus:cursor-wait hover:text-blue-400 bg-white shadow rounded-lg p-4'>
                        <div class="bg-gray-300 h-48 w-65 rounded-lg mb-3">
                            <img class="w-full h-full object-cover rounded-lg"
                                src="{{ getFile($item->image) }}" alt="">
                        </div>
                        <div class='w-full flex justify-between items-start'>
                            <div class="block">
                                <h3 class='text-lg font-semibold mb-2'>{{ $item->title }}</h3>
                                <p class='text-xs text-gray-600'> <time
                                        datetime="{{ \Carbon\Carbon::parse($item->created_at)->toDateTimeString() }}">
                                        {{ \Carbon\Carbon::parse($item->created_at)->locale('id')->translatedFormat('l, d F Y') }}
                                    </time></p>
                            </div>
                        </div>
                    </a>
                @endforeach
            </div>
        </div>
    </div>
@endif


