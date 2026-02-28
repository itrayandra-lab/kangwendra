<header class="main-header">
    <div class="top-header">
        <div class="container">
            <div class="top-header-inner">
                <div class="top-left">
                    <div class="ticker-wrap">
                        <div class="ticker-title">
                            <svg xmlns="http://www.w3.org/2000/svg" height="24" viewBox="0 -960 960 960" width="24">
                                <path d="M420.001-143.082v-276.919H307.694v-439.998H653.46l-73.461 255.768h158.076L420.001-143.082Z" />
                            </svg><span>Trending:</span>
                        </div>
                        <div class="ticker-slide-wrap">
                            <div class="swiper ticker-slider">
                                <div class="swiper-wrapper">
                                    @php $trendingPosts = App\Models\Posts::getTrending(5); @endphp
                                    @if($trendingPosts->count() > 0)
                                        @foreach($trendingPosts as $trending)
                                        <div class="swiper-slide">
                                            <a href="/{{ $trending->category?->slug ?? 'news' }}/{{ $trending->slug }}">
                                                {{ Str::words($trending->title, 5, '...') }}
                                            </a>
                                        </div>
                                        @endforeach
                                    @else
                                        <div class="swiper-slide">
                                            <a href="#">No trending news available</a>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="top-right">
                    <ul class="top-right-info">
                        @if(!empty($meta->email))
                        <li><a href="mailto:{{ $meta->email }}">{{ $meta->email }}</a></li>
                        @endif
                        @if(!empty($meta->phone))
                        <li><a href="tel:{{ $meta->phone }}">{{ $meta->phone }}</a></li>
                        @endif
                    </ul>
                    <ul class="header-social">
                        @if(!empty($meta->facebook_link) && $meta->facebook_link !== '#')
                        <li>
                            <a href="{{ $meta->facebook_link }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 320 512">
                                    <path d="M80 299.3V512H196V299.3h86.5l18-97.8H196V166.9c0-51.7 20.3-71.5 72.7-71.5c16.3 0 29.4 .4 37 1.2V7.9C291.4 4 256.4 0 236.2 0C129.3 0 80 50.5 80 159.4v42.1H14v97.8H80z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if(!empty($meta->twitter_link) && $meta->twitter_link !== '#')
                        <li>
                            <a href="{{ $meta->twitter_link }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512">
                                    <path d="M389.2 48h70.6L305.6 224.2 487 464H345L233.7 318.6 106.5 464H35.8L200.7 275.5 26.8 48H172.4L272.9 180.9 389.2 48zM364.4 421.8h39.1L151.1 88h-42L364.4 421.8z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if(!empty($meta->youtube_link) && $meta->youtube_link !== '#')
                        <li>
                            <a href="{{ $meta->youtube_link }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 576 512">
                                    <path d="M549.7 124.1c-6.3-23.7-24.8-42.3-48.3-48.6C458.8 64 288 64 288 64S117.2 64 74.6 75.5c-23.5 6.3-42 24.9-48.3 48.6-11.4 42.9-11.4 132.3-11.4 132.3s0 89.4 11.4 132.3c6.3 23.7 24.8 41.5 48.3 47.8C117.2 448 288 448 288 448s170.8 0 213.4-11.5c23.5-6.3 42-24.2 48.3-47.8 11.4-42.9 11.4-132.3 11.4-132.3s0-89.4-11.4-132.3zm-317.5 213.5V175.2l142.7 81.2-142.7 81.2z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                        @if(!empty($meta->instagram_link) && $meta->instagram_link !== '#')
                        <li>
                            <a href="{{ $meta->instagram_link }}">
                                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 448 512">
                                    <path d="M224.1 141c-63.6 0-114.9 51.3-114.9 114.9s51.3 114.9 114.9 114.9S339 319.5 339 255.9 287.7 141 224.1 141zm0 189.6c-41.1 0-74.7-33.5-74.7-74.7s33.5-74.7 74.7-74.7 74.7 33.5 74.7 74.7-33.6 74.7-74.7 74.7zm146.4-194.3c0 14.9-12 26.8-26.8 26.8-14.9 0-26.8-12-26.8-26.8s12-26.8 26.8-26.8 26.8 12 26.8 26.8zm76.1 27.2c-1.7-35.9-9.9-67.7-36.2-93.9-26.2-26.2-58-34.4-93.9-36.2-37-2.1-147.9-2.1-184.9 0-35.8 1.7-67.6 9.9-93.9 36.1s-34.4 58-36.2 93.9c-2.1 37-2.1 147.9 0 184.9 1.7 35.9 9.9 67.7 36.2 93.9s58 34.4 93.9 36.2c37 2.1 147.9 2.1 184.9 0 35.9-1.7 67.7-9.9 93.9-36.2 26.2-26.2 34.4-58 36.2-93.9 2.1-37 2.1-147.8 0-184.8zM398.8 388c-7.8 19.6-22.9 34.7-42.6 42.6-29.5 11.7-99.5 9-132.1 9s-102.7 2.6-132.1-9c-19.6-7.8-34.7-22.9-42.6-42.6-11.7-29.5-9-99.5-9-132.1s-2.6-102.7 9-132.1c7.8-19.6 22.9-34.7 42.6-42.6 29.5-11.7 99.5-9 132.1-9s102.7-2.6 132.1 9c19.6 7.8 34.7 22.9 42.6 42.6 11.7 29.5 9 99.5 9 132.1s2.7 102.7-9 132.1z" />
                                </svg>
                            </a>
                        </li>
                        @endif
                    </ul>
                </div>
            </div>
        </div>
    </div>
    <div class="bottom-header">
        <div class="container">
            <div class="main-header-wapper">
                <div class="site-logo">
                    <img src="{{ getFile($meta->logo) }}" alt="{{ $meta->web_name ?? 'Portal' }}" height="60">
                </div>
                <div class="main-header-info">
                    <div class="header-menu-wrap">
                        <ul class="nav-menu">
                            @if($menu->count() > 0)
                                @php
                                    $parents = $menu->where('type_1', 'parent');
                                    $menuItems = $menu->groupBy('parent_id');
                                @endphp

                                @foreach($parents as $parent)
                                    @php
                                        $submenus = $menuItems[$parent->id] ?? collect();
                                        $hasSubmenus = $submenus->count() > 0;
                                        
                                        if ($parent->type_2 == 'page') {
                                            $parentUrl = (str_starts_with($parent->slug, 'http://') || str_starts_with($parent->slug, 'https://')) 
                                                ? $parent->slug 
                                                : url('page/' . $parent->slug);
                                        } else {
                                            $parentUrl = (str_starts_with($parent->slug, 'http://') || str_starts_with($parent->slug, 'https://')) 
                                                ? $parent->slug 
                                                : url($parent->slug);
                                        }
                                    @endphp
                                    
                                    <li>
                                        <a href="{{ $parentUrl }}" data-text="{{ $parent->name }}" @if(str_starts_with($parent->slug, 'http://') || str_starts_with($parent->slug, 'https://')) target="_blank" @endif>{{ $parent->name }}</a>
                                        @if($hasSubmenus)
                                        <ul>
                                            @foreach($submenus as $submenu)
                                                @php
                                                    if ($submenu->type_2 == 'page') {
                                                        $submenuUrl = (str_starts_with($submenu->slug, 'http://') || str_starts_with($submenu->slug, 'https://')) 
                                                            ? $submenu->slug 
                                                            : url('page/' . $submenu->slug);
                                                    } else {
                                                        $submenuUrl = (str_starts_with($submenu->slug, 'http://') || str_starts_with($submenu->slug, 'https://')) 
                                                            ? $submenu->slug 
                                                            : url($submenu->slug);
                                                    }
                                                @endphp
                                                <li><a href="{{ $submenuUrl }}" @if(str_starts_with($submenu->slug, 'http://') || str_starts_with($submenu->slug, 'https://')) target="_blank" @endif>{{ $submenu->name }}</a></li>
                                            @endforeach
                                        </ul>
                                        @endif
                                    </li>
                                @endforeach
                            @endif
                        </ul>
                    </div>
                    <div class="menu-right-item">
                        <button class="menu-search">
                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 14.811 14.811">
                                <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="translate(-2.25 -2.25)">
                                    <circle cx="5.5" cy="5.5" r="5.5" data-name="Ellipse 7" transform="translate(3 3)"></circle>
                                    <path d="m16 16-3.142-3.142"></path>
                                </g>
                            </svg>
                        </button>
                        <button class="mobile-menu-action">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                        @auth
                            <a href="/portal/login" class="default-btn text-anim" data-text="{{ auth()->user()->name }}">{{ auth()->user()->name }}</a>
                        @else
                            <a href="/portal/login" class="default-btn text-anim" data-text="Login">Login</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    </div>
</header>

<div id="popup-search-box">
    <div class="box-inner-wrap d-flex align-items-center">
        <form id="form" action="/search" method="get" role="search">
            <input id="popup-search" type="text" name="qr" placeholder="Type keywords here..." value="{{ request('qr') }}">
            <button id="popup-search-button" type="submit" name="submit">
                <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 14.811 14.811">
                    <g fill="none" stroke="currentColor" stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" transform="translate(-2.25 -2.25)">
                        <circle cx="5.5" cy="5.5" r="5.5" data-name="Ellipse 7" transform="translate(3 3)"></circle>
                        <path d="m16 16-3.142-3.142"></path>
                    </g>
                </svg>
            </button>
        </form>
        <div class="search-close">
            <svg xmlns="http://www.w3.org/2000/svg" fill="currentColor" height="24" viewBox="0 -960 960 960" width="24">
                <path d="M256-213.847 213.847-256l224-224-224-224L256-746.153l224 224 224-224L746.153-704l-224 224 224 224L704-213.847l-224-224-224 224Z" />
            </svg>
        </div>
    </div>
</div>

<div id="searchbox-overlay"></div>

