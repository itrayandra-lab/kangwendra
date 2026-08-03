@extends('layouts.client.app')

@section('content')
    <section class="page-header">
        <div class="container">
            <div class="page-content-wrap">
                <div class="page-content">
                    <h4>Hubungi Kami</h4>
                    <h2>Hubungi <span>Kami</span></h2>
                    <p>Punya pertanyaan, saran, atau ingin berkolaborasi? Kami senang mendengar dari Anda.</p>
                </div>
            </div>
        </div>
    </section>

    <section class="padding" style="background: #fff;">
        <div class="container">
            <div class="row gy-5">
                <div class="col-lg-7">
                    <h3 style="font-size: 1.6rem; margin-bottom: 25px;">Kirim Pesan</h3>
                    <form action="#" method="POST" style="display: flex; flex-direction: column; gap: 20px;">
                        <div class="row gy-3">
                            <div class="col-md-6">
                                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Nama Lengkap</label>
                                <input type="text" name="name" placeholder="Masukkan nama Anda" style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.3s; box-sizing: border-box;" onfocus="this.style.borderColor='#333'" onblur="this.style.borderColor='#ddd'">
                            </div>
                            <div class="col-md-6">
                                <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Email</label>
                                <input type="email" name="email" placeholder="email@contoh.com" style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.3s; box-sizing: border-box;" onfocus="this.style.borderColor='#333'" onblur="this.style.borderColor='#ddd'">
                            </div>
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Subjek</label>
                            <input type="text" name="subject" placeholder="Tentang apa pesan Anda?" style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.3s; box-sizing: border-box;" onfocus="this.style.borderColor='#333'" onblur="this.style.borderColor='#ddd'">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 6px; font-weight: 500; color: #333;">Pesan</label>
                            <textarea name="message" rows="6" placeholder="Tulis pesan Anda di sini..." style="width: 100%; padding: 12px 16px; border: 1px solid #ddd; border-radius: 8px; font-size: 15px; transition: border 0.3s; resize: vertical; box-sizing: border-box;" onfocus="this.style.borderColor='#333'" onblur="this.style.borderColor='#ddd'"></textarea>
                        </div>
                        <button type="submit" style="background: #333; color: #fff; padding: 14px 35px; border: none; border-radius: 25px; font-size: 15px; font-weight: 500; cursor: pointer; transition: all 0.3s ease; width: fit-content; text-decoration: none; display: inline-block;" onmouseover="this.style.background='#555'; this.style.transform='translateY(-2px)'" onmouseout="this.style.background='#333'; this.style.transform='translateY(0)'">
                            Kirim Pesan
                        </button>
                    </form>
                </div>

                <div class="col-lg-5">
                    <div class="sidebar-area">
                        <div class="sidebar-widget" style="background: #fff; border-radius: 10px; overflow: hidden; margin-bottom: 25px;">
                            <div class="widget-heading" style="background: #333; padding: 18px 20px;">
                                <h3 style="color: #fff; font-size: 1rem; margin: 0;">Informasi Kontak</h3>
                            </div>
                            <div style="padding: 25px;">
                                <ul style="list-style: none; padding: 0; margin: 0;">
                                    @if(!empty($meta->phone))
                                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid #eee;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#333">
                                                <path d="M200-120q-24 0-42-18t-18-42v-560q0-24 18-42t42-18h160q24 0 42 18t18 42v160q0 24-18 42t-42 18H240v400q0 24 18 42t42 18h560q24 0 42-18t18-42V-200q0-24-18-42t-42-18H200Zm0-80h560v-400H200v400Zm0-480h560q24 0 42 18t18 42v560q0 24-18 42t-42 18H200q-24 0-42-18t-18-42v-560q0-24 18-42t42-18Zm80 80v400-400Z"/>
                                            </svg>
                                            <div>
                                                <strong style="display: block; color: #333; font-size: 13px;">Telepon</strong>
                                                <a href="tel:{{ $meta->phone }}" style="color: #666; text-decoration: none; font-size: 14px;">{{ $meta->phone }}</a>
                                            </div>
                                        </li>
                                    @endif
                                    @if(!empty($meta->email))
                                        <li style="display: flex; align-items: center; gap: 12px; margin-bottom: 18px; padding-bottom: 18px; border-bottom: 1px solid #eee;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#333">
                                                <path d="M200-120q-33 0-56.5-23.5T120-200v-560q0-33 23.5-56.5T200-840h560q33 0 56.5 23.5T840-760v560q0 33-23.5 56.5T760-120H200Zm280-280 280-196-83-63-197 138-117-87-263 184v-376l380 289ZM200-680v320 160-480Z"/>
                                            </svg>
                                            <div>
                                                <strong style="display: block; color: #333; font-size: 13px;">Email</strong>
                                                <a href="mailto:{{ $meta->email }}" style="color: #666; text-decoration: none; font-size: 14px;">{{ $meta->email }}</a>
                                            </div>
                                        </li>
                                    @endif
                                    @if(!empty($meta->address))
                                        <li style="display: flex; align-items: flex-start; gap: 12px;">
                                            <svg xmlns="http://www.w3.org/2000/svg" height="20" viewBox="0 -960 960 960" width="20" fill="#333">
                                                <path d="M480-480q33 0 56.5-23.5T560-560q0-33-23.5-56.5T480-640q-33 0-56.5 23.5T400-560q0 33 23.5 56.5T480-480Zm0 294q75-81 117.5-165.5T640-552q0-109-69.5-178.5T392-800q-109 0-178.5 69.5T144-552q0 88 42.5 172.5T304-186q32 34 68 55t108 21q72 0 108-21t68-55ZM480-80q-146 0-273.5-63.5T56-312q49-57 119.5-88t148.5-31q209 0 343.5 134.5T920-312q-56 90-134 154.5T480-80Z"/>
                                            </svg>
                                            <div>
                                                <strong style="display: block; color: #333; font-size: 13px;">Alamat</strong>
                                                <span style="color: #666; font-size: 14px;">{{ $meta->address }}</span>
                                            </div>
                                        </li>
                                    @endif
                                </ul>
                            </div>
                        </div>

                        @if($mostPopular->count() > 0)
                            <div class="sidebar-widget" style="background: #fff; border-radius: 10px; overflow: hidden;">
                                <div class="widget-heading" style="background: #333; padding: 18px 20px;">
                                    <h3 style="color: #fff; font-size: 1rem; margin: 0;">Artikel Terpopuler</h3>
                                </div>
                                <div style="padding: 15px;">
                                    @foreach($mostPopular->take(4) as $item)
                                        <div style="display: flex; gap: 12px; margin-bottom: 15px; padding-bottom: 15px; border-bottom: 1px solid #eee;">
                                            @if($item->image)
                                                <div style="flex-shrink: 0; width: 65px; height: 50px; border-radius: 5px; overflow: hidden;">
                                                    <img src="{{ getFile($item->image) }}" alt="{{ $item->title }}" style="width: 100%; height: 100%; object-fit: cover;">
                                                </div>
                                            @endif
                                            <h4 style="font-size: 13px; margin: 0; line-height: 1.4;">
                                                <a href="/{{ $item->category?->slug ?? 'news' }}/{{ $item->slug }}" style="color: #333; text-decoration: none;">{{ $item->title }}</a>
                                            </h4>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

@push('styles')
<style>
    .page-header { background: #fff; padding: 80px 0; color: #333; }
    .page-content h4 { font-size: 1rem; font-weight: 500; margin-bottom: 10px; opacity: 0.8; }
    .page-content h2 { font-size: 2.5rem; font-weight: 700; margin-bottom: 15px; }
    .page-content h2 span { font-size: 1.2rem; font-weight: 400; opacity: 0.7; }
    .page-content p { font-size: 1.1rem; opacity: 0.8; max-width: 600px; }
    @media (max-width: 768px) {
        .page-content h2 { font-size: 2rem; }
    }
</style>
@endpush
