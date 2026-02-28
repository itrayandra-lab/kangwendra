<div class="col-span-12 bg-gray-100 rounded p-4 ">
    @include('widget.client.header-title', ['title' => 'Terpopuler', 'link' => '/posts?type=populer'])
    <div class="space-y-2">
        <div class="grid grid-cols-12 lg:gap-3">
            @forelse ($data as $item)
                <div class="col-span-12 md:col-span-4 ">
                    <div class="mx-auto w-full p-2 mb-2">
                        <div class="flex space-x-4 items-center">
                            <div class="text-blue-900 font-bold text-2xl italic">
                                {{ $loop->iteration }}
                            </div>
                            <div class="flex-1">
                                <div>
                                    <a class="text-gray-900 font-bold lg:text-lg text-sm hover:text-gray-600 transition-colors duration-200"
                                        href="/{{ $item->category->slug }}/{{ $item->slug }}">
                                        {{ $item->title }}
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <hr class=" border border-dashed border-gray-200  ">
                </div>
            @empty
                @for ($i = 0; $i < 5; $i++)
                    <div class="col-span-12 md:col-span-6">
                        @include('widget.client.load-data-1')
                    </div>
                @endfor
            @endforelse
        </div>
    </div>
</div>

