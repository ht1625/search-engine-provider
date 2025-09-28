{{-- resources/views/media_items/index.blade.php --}}
<!doctype html>
<html lang="tr">
<head>
    <meta charset="utf-8">
    <title>Media Items</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">

    {{-- Bootstrap 5 --}}
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        code.inline {
            background: #f8f9fa;
            padding: .2rem .4rem;
            border-radius: .25rem;
            font-size: .875rem;
        }
        .table td, .table th { vertical-align: middle; }
    </style>
</head>
<body>
<div class="container-xxl py-4">

    <div class="d-flex align-items-center justify-content-between mb-3">
        <h1 class="h3 mb-0">Media Items</h1>
        <a class="btn btn-outline-secondary btn-sm"
           href="{{ url('/api/media-items?' . http_build_query(request()->query())) }}"
           target="_blank" rel="noopener">SEE RESPONSE OF API(Json,Xml)</a>
    </div>

    <div class="d-flex align-items-center justify-content-between mb-3">
        <p> İlgili media-items verilerini sunan bir API geliştirilmiştir. Bu verilerin kullanıcı 
            arayüzünde gösterilmesi amacıyla bir React.js projesi planlanmış, ancak zaman yetersizliği 
            nedeniyle bu proje tamamlanamamıştır. Bu sebeple, API'den dönen verilerin görüntülenebilmesi 
            için basit bir web sayfası tasarlanarak geçici bir çözüm sunulmuştur.</p>
    </div>

    {{-- Filtre / Sıralama Formu --}}
    <form method="GET" action="{{ route('media.items.page') }}" class="card card-body shadow-sm mb-4">
        <div class="row g-3 align-items-end">
            <div class="col-12 col-md-4">
                <label class="form-label">Ara (başlık / id)</label>
                <input type="text" name="q" value="{{ $filters['q'] }}"
                       class="form-control" placeholder="ör. Interstellar">
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Tür</label>
                <select name="type" class="form-select">
                    <option value="">Hepsi</option>
                    <option value="movie"  @selected($filters['type']==='movie')>Movie</option>
                    <option value="series" @selected($filters['type']==='series')>Series</option>
                    <option value="doc"    @selected($filters['type']==='doc')>Documentary</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Sırala</label>
                <select name="sort" class="form-select">
                    <option value="score"  @selected($filters['sort']==='score')>Skor</option>
                    <option value="title"  @selected($filters['sort']==='title')>Başlık</option>
                    <option value="type"   @selected($filters['sort']==='type')>Tür</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Yön</label>
                <select name="order" class="form-select">
                    <option value="desc" @selected($filters['order']==='desc')>Azalan</option>
                    <option value="asc"  @selected($filters['order']==='asc')>Artan</option>
                </select>
            </div>

            <div class="col-6 col-md-2">
                <label class="form-label">Sayfa başı</label>
                <select name="per_page" class="form-select">
                    @foreach([10,20,50,100] as $pp)
                        <option value="{{ $pp }}" @selected(($filters['per'] ?? $filters['per_page'] ?? 20)==$pp)>{{ $pp }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-12">
                <button class="btn btn-primary">
                    Listele
                </button>
                <a href="{{ route('media.items.page') }}" class="btn btn-light ms-2">
                    Sıfırla
                </a>
            </div>
        </div>
    </form>

    <div class="card shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th style="width: 160px;">External ID</th>
                        <th>Başlık</th>
                        <th style="width: 140px;">Tür</th>
                        <th style="width: 120px;" class="text-end">Skor</th>
                    </tr>
                </thead>
                <tbody>
                @forelse($items as $it)
                    <tr>
                        <td class="font-monospace">{{ $it->external_id }}</td>
                        <td>{{ $it->title }}</td>
                        <td>
                            @php
                                $badge = match($it->type){
                                    'movie' => 'primary',
                                    'series'=> 'success',
                                    'doc'   => 'warning',
                                    default => 'secondary'
                                };
                            @endphp
                            <span class="badge text-bg-{{ $badge }}">{{ $it->type }}</span>
                        </td>
                        <td class="text-end">{{ $it->score }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="text-center text-muted py-5">
                            Kayıt bulunamadı.
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>

</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
