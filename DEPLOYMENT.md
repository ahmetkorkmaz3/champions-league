# Champions League Project - Deployment Guide

Bu dokümantasyon, Champions League projesinin Docker tabanlı production deployment süreçlerini açıklar.

## 🚀 Hızlı Başlangıç

### Gereksinimler

- Docker
- Docker Compose
- Git

### Deployment Script'i Çalıştırma

```bash
# Script'i çalıştırılabilir hale getir
chmod +x deploy-docker.sh

# Production deployment'ı başlat
./deploy-docker.sh
```

## 📋 Deployment Script'i Ne Yapar?

`deploy-docker.sh` script'i aşağıdaki adımları otomatik olarak gerçekleştirir:

### 1. Gereksinim Kontrolü
- Docker ve Docker Compose'un yüklü olup olmadığını kontrol eder
- Eksik araçlar varsa hata verir ve durur

### 2. Environment Dosyası Kontrolü
- `.env` dosyasının varlığını kontrol eder
- Yoksa `.env.example`'dan kopyalar

### 3. Docker İşlemleri
- Eski container'ları temizler
- Docker imajlarını yeniden build eder
- Container'ları başlatır
- Container'ların hazır olmasını bekler

### 4. Bağımlılık Yükleme
- **Composer**: PHP bağımlılıklarını production modunda yükler
- **NPM**: Node.js bağımlılıklarını yükler ve production build yapar

### 5. Laravel Optimizasyonları
- Cache'leri temizler ve yeniden oluşturur
- Config, route ve view cache'lerini optimize eder

### 6. Veritabanı İşlemleri
- Migration'ları çalıştırır
- Veritabanı yapısını günceller

### 7. Dosya İzinleri
- Storage ve cache dizinlerine gerekli izinleri verir
- `.env` dosyasına uygun izinleri ayarlar

## 🐳 Docker Servisleri

Production deployment'ında aşağıdaki servisler çalışır:

### App (Laravel)
- **Port**: 9000
- **Image**: Custom PHP 8.3-FPM
- **Environment**: Production
- **Database**: PostgreSQL

### PostgreSQL
- **Port**: 5432
- **Image**: postgres:15
- **Database**: laravel
- **User**: laravel
- **Password**: secret

### Caddy (Web Server)
- **Port**: 80, 443
- **Image**: caddy:2
- **Domain**: champions.ahmetkorkmaz.co
- **SSL**: Otomatik (Let's Encrypt)

## 🔧 Manuel Komutlar

### Container Durumlarını Kontrol Etme
```bash
docker-compose -f docker-compose.production.yml ps
```

### Logları Görüntüleme
```bash
# Tüm servislerin logları
docker-compose -f docker-compose.production.yml logs -f

# Sadece app servisinin logları
docker-compose -f docker-compose.production.yml logs -f app
```

### Container'a Bağlanma
```bash
# Laravel app container'ına bağlan
docker-compose -f docker-compose.production.yml exec app bash

# PostgreSQL container'ına bağlan
docker-compose -f docker-compose.production.yml exec postgres psql -U laravel -d laravel
```

### Laravel Artisan Komutları
```bash
# Migration çalıştır
docker-compose -f docker-compose.production.yml exec app php artisan migrate

# Cache temizle
docker-compose -f docker-compose.production.yml exec app php artisan cache:clear

# Storage link oluştur
docker-compose -f docker-compose.production.yml exec app php artisan storage:link
```

## 🛠️ Sorun Giderme

### Container Başlamıyor
```bash
# Container loglarını kontrol et
docker-compose -f docker-compose.production.yml logs app

# Container'ı yeniden başlat
docker-compose -f docker-compose.production.yml restart app
```

### Veritabanı Bağlantı Sorunu
```bash
# PostgreSQL'in çalışıp çalışmadığını kontrol et
docker-compose -f docker-compose.production.yml exec postgres pg_isready -U laravel

# Veritabanı bağlantısını test et
docker-compose -f docker-compose.production.yml exec app php artisan tinker
```

### Build Sorunları
```bash
# Docker cache'ini temizle
docker system prune -a

# İmajları yeniden build et
docker-compose -f docker-compose.production.yml build --no-cache
```

## 🔒 Güvenlik

### Environment Değişkenleri
- Production'da `.env` dosyasındaki hassas bilgileri güncelleyin
- `APP_KEY` değerinin ayarlandığından emin olun
- Veritabanı şifrelerini güçlü şifrelerle değiştirin

### SSL Sertifikası
- Caddy otomatik olarak Let's Encrypt SSL sertifikası alır
- Domain'in DNS ayarlarının doğru olduğundan emin olun

## 📊 Monitoring

### Container Kaynak Kullanımı
```bash
# Container'ların kaynak kullanımını görüntüle
docker stats
```

### Disk Kullanımı
```bash
# Docker disk kullanımını kontrol et
docker system df
```

## 🔄 Güncelleme

### Kod Güncellemesi
```bash
# Yeni kodu çek
git pull origin main

# Deployment script'ini çalıştır
./deploy-docker.sh
```

### Sadece Bağımlılık Güncellemesi
```bash
# Composer bağımlılıklarını güncelle
docker-compose -f docker-compose.production.yml exec app composer update --no-dev

# NPM bağımlılıklarını güncelle
docker-compose -f docker-compose.production.yml exec app npm update

# Build et
docker-compose -f docker-compose.production.yml exec app npm run build
```

## 📞 Destek

Deployment ile ilgili sorunlar için:
1. Logları kontrol edin
2. Container durumlarını kontrol edin
3. Gerekli araçların yüklü olduğundan emin olun
4. Environment dosyasının doğru yapılandırıldığından emin olun 