<div class="container">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-body">
                    <h4 class="mb-4">Berita AI dari Yahoo News</h4>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-light">
                                <tr>
                                    <th>No</th>
                                    <th>Judul</th>
                                    <th>Domain</th>
                                    <th>Tags</th>
                                    <th>Tanggal Publikasi</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($posts as $index => $post)
                                    <tr>
                                        <td>{{ ($posts->currentPage() - 1) * $posts->perPage() + $index + 1 }}</td>
                                        <td>{{ $post->title }}</td>
                                        <td>{{ $post->domain ?: '-' }}</td>
                                        <td>
                                            @php
                                                $tags = is_array($post->tags)
                                                    ? $post->tags
                                                    : json_decode($post->tags, true);
                                            @endphp
                                            @if(!empty($tags))
                                                @foreach($tags as $tag)
                                                    <span class="badge badge-info mr-1">{{ $tag }}</span>
                                                @endforeach
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($post->published_at)->format('d M Y H:i') }}</td>
                                        <td>
                                            <a href="{{ $post->source }}" target="_blank" class="btn btn-info btn-sm">
                                                <i class="fa fa-eye"></i> Lihat Artikel
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    {{ $posts->links() }}
                </div>
            </div>
        </div>
    </div>
</div>
