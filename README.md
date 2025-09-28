# 📺 Laravel Content Provider API & Dashboard

Bu proje, farklı kaynaklardan sağlanan içerikleri (JSON & XML formatındaki provider verileri) toplayarak  
tek bir standart formata dönüştüren ve bu verileri hem **API** hem de **Dashboard** üzerinden erişilebilir  
hale getiren bir uygulamadır.  

Amaç; **arama**, **filtreleme**, **sıralama**, **puanlama** ve **güncellik takibi** gibi işlevleri tek bir noktadan yönetmektir.  

![Laravel](https://img.shields.io/badge/Laravel-12-red?style=for-the-badge&logo=laravel)
![PHP](https://img.shields.io/badge/PHP-8.2-blue?style=for-the-badge&logo=php)
![MySQL](https://img.shields.io/badge/MySQL-8.0-orange?style=for-the-badge&logo=mysql)
![Docker](https://img.shields.io/badge/Docker-Engine-blue?style=for-the-badge&logo=docker)

---

## 📊 İçerik Puanlama

Her provider farklı veri formatı gönderdiği için tüm içerikler önce  
**tek tip bir puanlama sistemine dönüştürülür.**  

Bu puanlama şu kriterlere dayanır:  
- İçerik türüne göre farklı **ağırlık katsayıları** uygulanır.  
- **Kullanıcı etkileşimleri** puana yansıtılır.  
- İçeriğin **zaman bazlı güncelliği** dikkate alınır.  

Sonuçta elde edilen skorlar, kullanıcıya **popülerlik ve alakalılık sırasına göre** sunulur.   

---

## 🌍 Provider Entegrasyonu

Sistem, iki farklı içerik sağlayıcıya entegre edilmiştir:  
- JSON formatlı provider  
- XML formatlı provider  

Her provider’dan gelen veriler standart bir yapıya dönüştürülerek veritabanında saklanır.

İstek limiti yönetimi yerine **Laravel Scheduler** kullanılarak çözüm uygulanmıştır. Sistem her 15 dakikada bir provider’lardan verileri çekip veritabanını güncelleyecek şekilde ayarlanmıştır.

Yeni provider eklemek için sistem **esnek ve genişletilebilir** şekilde tasarlanmıştır.

---

## 💾 Veri Saklama

Tüm içerikler **MySQL veritabanında kalıcı olarak** saklanır.  
Performansı artırmak ve istek sınırlarını aşmamak için  
**cache mekanizması (Redis)** yapılmıştır.  

---

## ⚙️ Teknik Yaklaşım

- Kod yapısı **temiz, anlaşılır ve sürdürülebilir** olacak şekilde tasarlanmıştır.
- **Hata Yönetimi:** Merkezi exception handler yerine, kritik işlemlerde **yerel hata yakalama (try–catch)** yöntemi kullanılmıştır. Ayrıca job süreçlerinde oluşan hatalar için özel bir **job_logs** tablosu oluşturulmuş, böylece her job’un çalışma detayları kayıt altına alınmıştır.
- **Test Stratejisi:** Birim testler yerine, uygulamanın uçtan uca doğruluğunu güvence altına almak için **feature testler** yazılmıştır.
- **Ölçeklenebilirlik:** Sistem artan veri hacmine karşı performanslı çalışacak şekilde kurgulanmıştır.

---

## 🛠 Kullanılan Teknolojiler

- **Backend** → Laravel 12 (PHP 8.2)  
- **Database** → MySQL 8  
- **Cache (önerilen)** → Redis  
- **Containerization** → Docker & Docker Compose  

---

## 🧪 Mock API Sağlayıcılar

- Provider 1 → JSON formatında veri  
- Provider 2 → XML formatında veri  

---

## 🐳 Kurulum & Çalıştırma

> Gereklilik:
-   **PHP** \>= 8.2\
-   **Composer** (PHP dependency manager)\
-   **Docker & Docker Compose** (for containerized setup)\

1️⃣ Proje bağımlılıklarını indir  
```bash
composer install
cp .env.example .env
php artisan key:generate
```

2️⃣ Docker build & start  
```bash
docker-compose up --build
docker-compose up -d
```

3️⃣ Container içine gir  
```bash
docker-compose exec app bash
```

4️⃣ Migration çalıştır  
```bash
php artisan migrate
```

5️⃣ Projeyi başlat  
```bash
php artisan serve --host=0.0.0.0 --port=8080
```

👉 Artık `http://localhost:8080` üzerinden erişebilirsiniz 🎉  

---

## 👩‍💻 Katkı & İletişim

- Pull request ve issue’lara açıktır.  
- Katkı sağlamak isteyenler memnuniyetle karşılanır.  
