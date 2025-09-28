# Media Items API Dokümantasyonu

Medya içeriklerini arama, filtreleme, sıralama ve sayfalama özellikleriyle listelemek için kullanılan uç nokta.

## Base URL
- Local: `http://localhost:8080`

## Endpoint
```
GET /api/media-items
```

## Amaç
Medya içeriklerini (`video`, `text`) arama (`q`), tipe göre filtreleme (`type`), sıralama (`sort`, `order`) ve sayfalama (`page`, `per_page`) ile JSON formatında döndürür.

---

## Sorgu Parametreleri

| Ad        | Tip     | Zorunlu | Varsayılan | Kısıtlar / Örnek Değerler                  | Açıklama |
|-----------|---------|---------|------------|--------------------------------------------|----------|
| `q`       | string  | Hayır   | `null`     | max 120                                     | Serbest metin araması. |
| `type`    | string  | Hayır   | `null`     | `video`, `text`                             | Medya tipi filtresi. |
| `sort`    | string  | Hayır   | `score`    | `title`, `type`, `score`                    | Sıralama alanı. |
| `order`   | string  | Hayır   | `desc`     | `asc`, `desc`                               | Sıralama yönü. |
| `page`    | integer | Hayır   | `1`        | `min:1`                                     | Sayfa numarası. |
| `per_page`| integer | Hayır   | `10`       | `min:1`, `max:100`                          | Sayfa başına kayıt sayısı. |

**Not:** Varsayılanlar backend’de `validated()` içinde atanır: `sort=score`, `order=desc`, `per_page=10`, `page` verilmezse 1.

---

## Örnek İstekler

### 1) Tipe göre filtre + skora göre azalan sıralama
```http
GET /api/media-items?type=video&sort=score&order=desc&page=1&per_page=10
```

### 2) Arama + başlığa göre artan sıralama
```http
GET /api/media-items?q=concurrency&sort=title&order=asc&page=2&per_page=5
```

### 3) Sadece varsayılanlarla (hiç parametre yok)
```http
GET /api/media-items
```

---

## Örnek Başarılı Yanıt

```json
{
  "success": true,
  "meta": {
    "page": 1,
    "per_page": 10,
    "total": 14,
    "last_page": 2,
    "sort": "score",
    "order": "desc",
    "filters": {
      "q": null,
      "type": "video"
    }
  },
  "data": [
    {
      "external_id": "VID-000123",
      "title": "Advanced Go Concurrency Patterns",
      "type": "video",
      "score": 69.84
    }
  ]
}
```

### `meta` Açıklaması
- `page`: Mevcut sayfa.
- `per_page`: Sayfa başına kayıt.
- `total`: Toplam kayıt sayısı.
- `last_page`: Toplam sayfa sayısı.
- `sort`/`order`: Aktif sıralama bilgisi.
- `filters`: Uygulanan filtrelerin yansıması.

`data` alanı `MediaItemResource` koleksiyonundan gelir ve her öğe için en az `external_id`, `title`, `type`, `score` alanlarını içerir.

---

## Hata Yanıtları

### 422 Unprocessable Entity (Doğrulama hatası örneği)
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "type": [
      "The selected type is invalid."
    ]
  }
}
```
**Ne zaman olur?** Örn. `type=audio` gönderilirse (`Rule::in(['video','text'])`).

### 500 Internal Server Error
```json
{
  "success": false,
  "message": "Media items could not be retrieved.",
  "error": "Internal error details (loglarda ayrıntı mevcut)."
}
```

---

## Önbellekleme (Cache)

- **Tag-based cache** kullanılır: `media_items`
- Cache anahtarı:  
  `media_items:v2:q={q}|type={type}|sort={sort}|order={order}|page={page}|per={per_page}`
- **TTL:** 10 dakika  
- **İnvalidation (öneri):** İçerik güncellemelerinde `Cache::tags(['media_items'])->flush()` çağrılabilir.

---

## Sıralama & Arama Davranışı

- `search($v['q'])`: `q` boş değilse arama kriteri uygular (uygulama tarafındaki `scopeSearch`’e göre davranır).
- `type($v['type'])`: `type` girildiyse filtreler (`video`/`text`).
- `sortBy($v['sort'], $v['order'])`: Yalnızca `title`, `type`, `score` alanlarını kabul eder.
- `paginate($v['per_page'])->appends($v)`: Sayfalama ve linklerde parametrelerin korunması.

---

## cURL Örnekleri

```bash
# Basit listeleme
curl -s "http://localhost:8080/api/media-items"

# Filtre + sıralama + sayfalama
curl -s "http://localhost:8080/api/media-items?type=video&sort=score&order=desc&page=1&per_page=10"

# Arama + başlığa göre artan sıra
curl -s "http://localhost:8080/api/media-items?q=concurrency&sort=title&order=asc&per_page=5"
```
