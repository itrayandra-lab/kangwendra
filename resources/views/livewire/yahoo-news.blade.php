<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">Artikel AI dari Yahoo Tech</h4>
                    <p class="text-muted">Artikel dari tech.yahoo.com yang sudah diproses AI ke Bahasa Indonesia</p>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Kategori</th>
                                    <th>Tags</th>
                                    <th>Status</th>
                                    <th>Tanggal Publish</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($posts as $index => $post)
                                    <tr>
                                        <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</td>
                                        <td>
                                            <a href="{{ route('post_detail', [$post->category?->slug ?? 'teknologi', $post->slug]) }}" target="_blank">
                                                {{ Str::limit($post->title, 60) }}
                                            </a>
                                        </td>
                                        <td><span class="badge badge-primary">{{ $post->category?->name ?? '-' }}</span></td>
                                        <td>
                                            @if(is_array($post->tags) && count($post->tags))
                                                @foreach(array_slice($post->tags, 0, 2) as $tag)
                                                    <span class="badge badge-info mr-1">#{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($post->status === 'active')
                                                <span class="badge badge-success">Published</span>
                                            @elseif($post->status === 'draft')
                                                <span class="badge badge-warning">Draft</span>
                                            @else
                                                <span class="badge badge-secondary">{{ $post->status }}</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($post->published_at)->format('d M Y H:i') }} WIB</td>
                                        <td>
                                            <a href="{{ route('post_detail', [$post->category?->slug ?? 'teknologi', $post->slug]) }}" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fa fa-eye"></i> Lihat
                                            </a>
                                            <a href="{{ $post->source }}" target="_blank" class="btn btn-secondary btn-sm">
                                                <i class="fa fa-external-link"></i> Source
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center text-muted">
                                            Belum ada artikel dari Yahoo Tech. Klik "Scrape Yahoo" untuk mengambil artikel baru.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
