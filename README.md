# 🏆 Champions League Simülasyonu

4 takımlı çift devreli lig simülasyonu - Premier League kurallarına göre maç sonuçlarını tahmin edin!

## 📋 Proje Özellikleri

### ✅ Temel Özellikler
- **4 Takım**: Arsenal, Chelsea, Liverpool, Manchester United
- **6 Hafta**: Her takım diğer takımlarla 2 kez karşılaşır (çift devreli lig)
- **Premier League Kuralları**: Galibiyet 3, Beraberlik 1, Mağlubiyet 0 puan
- **Güç Seviyesi Sistemi**: Her takımın 88-95 arası güç seviyesi var
- **Ev Sahibi Avantajı**: Ev sahibi takımlar %10 güç bonusu alır

### 🎮 Simülasyon Özellikleri
- **Haftalık Oynatma**: Her haftayı ayrı ayrı oynatabilirsiniz
- **Tüm Ligi Oynat**: Tek tıklamayla tüm maçları otomatik oynatır
- **Gerçekçi Sonuçlar**: Takım güçleri ve ev sahibi avantajı dikkate alınır
- **Maç Düzenleme**: Oynanmış maçların sonuçlarını manuel olarak değiştirebilirsiniz
- **Maç Sıfırlama**: Maçları oynanmamış duruma getirebilirsiniz
- **Şampiyonluk Kutlaması**: Şampiyon belirlendiğinde otomatik kutlama efekti

### 🔮 Tahmin Sistemi
- **Akıllı Tahminler**: Oynanan maçların gerçek sonuçları + kalan maçların simülasyonu
- **Detaylı İstatistikler**: En çok gol atan, en az gol yiyen, şampiyonluk olasılıkları
- **Görsel Analiz**: Güç seviyesi grafikleri ve sıralama göstergeleri
- **Şampiyonluk Olasılıkları**: Her takımın şampiyon olma yüzdesi

## 🛠️ Teknolojiler

### Backend
- **PHP 8.2+** - Ana programlama dili
- **Laravel 12** - Modern web framework
- **PostgreSQL** - Production veritabanı
- **SQLite** - Development veritabanı
- **OOP Prensipleri** - Nesne yönelimli programlama
- **Service Layer** - İş mantığı katmanı

### Frontend
- **Vue.js 3** - Modern JavaScript framework
- **Inertia.js** - SPA deneyimi
- **TypeScript** - Tip güvenliği
- **Tailwind CSS 4** - Modern styling framework
- **Reka UI** - UI component library
- **Lucide Icons** - Modern icon set

### DevOps & Tools
- **Docker** - Containerization
- **Docker Compose** - Multi-container orchestration
- **Caddy** - Web server (production)
- **Nginx** - Web server (development)
- **Vite** - Build tool
- **ESLint & Prettier** - Code formatting

### Test
- **Pest** - Modern PHP testing framework
- **Kapsamlı Test Coverage** - Unit ve feature testler

## 🚀 Kurulum

### Gereksinimler
- PHP 8.2+
- Composer
- Node.js 18+
- PostgreSQL (production) / SQLite (development)
- Docker & Docker Compose (opsiyonel)

### Docker ile Hızlı Başlangıç

1. **Projeyi klonlayın**
```bash
git clone <repository-url>
cd champions-league
```

2. **Docker ile başlatın**
```bash
# Development
docker-compose up -d

# Production
docker-compose -f docker-compose.production.yml up -d
```

3. **Veritabanını kurun**
```bash
docker-compose exec app php artisan migrate:fresh --seed
```

4. **Tarayıcıda açın**
```
http://localhost
```

### Manuel Kurulum

1. **Projeyi klonlayın**
```bash
git clone <repository-url>
cd champions-league
```

2. **Backend bağımlılıklarını yükleyin**
```bash
composer install
```

3. **Frontend bağımlılıklarını yükleyin**
```bash
npm install
```

4. **Environment dosyasını oluşturun**
```bash
cp .env.example .env
php artisan key:generate
```

5. **Veritabanını kurun**
```bash
php artisan migrate:fresh --seed
```

6. **Uygulamayı başlatın**
```bash
composer run dev
```

7. **Tarayıcıda açın**
```
http://localhost:8000
```

## 📊 Veritabanı Yapısı

### Teams Tablosu
- `id` - Takım ID
- `name` - Takım adı
- `power_level` - Güç seviyesi (88-95)
- `city` - Şehir
- `logo` - Logo URL'i

### Matches Tablosu
- `id` - Maç ID
- `home_team_id` - Ev sahibi takım
- `away_team_id` - Deplasman takımı
- `home_score` - Ev sahibi gol sayısı
- `away_score` - Deplasman gol sayısı
- `week` - Hafta numarası (1-6)
- `is_played` - Oynandı mı?

### League_Standings Tablosu
- `team_id` - Takım ID
- `points` - Toplam puan
- `goals_for` - Atılan gol
- `goals_against` - Yenen gol
- `goal_difference` - Gol farkı
- `wins` - Galibiyet
- `draws` - Beraberlik
- `losses` - Mağlubiyet
- `position` - Sıralama

## 🧪 Testler

### Testleri çalıştırın
```bash
# Docker ile
docker-compose exec app php artisan test

# Manuel
php artisan test
```

### Belirli test dosyasını çalıştırın
```bash
php artisan test --filter=MatchServiceTest
php artisan test --filter=LeagueServiceTest
```

## 📁 Proje Yapısı

```
champions-league/
├── app/
│   ├── Http/Controllers/
│   │   ├── Api/                    # RESTful API controllers
│   │   │   ├── ChampionsLeagueApiController.php
│   │   │   ├── GameMatchController.php
│   │   │   ├── LeagueStandingController.php
│   │   │   └── TeamController.php
│   │   └── ChampionsLeagueController.php
│   ├── Models/
│   │   ├── Team.php
│   │   ├── GameMatch.php
│   │   └── LeagueStanding.php
│   └── Services/
│       ├── MatchService.php
│       └── LeagueService.php
├── database/
│   ├── migrations/
│   └── seeders/
│       └── ChampionsLeagueSeeder.php
├── resources/js/
│   ├── components/                 # Vue components
│   │   ├── ui/                     # Reka UI components
│   │   ├── LeagueTable.vue
│   │   ├── MatchRow.vue
│   │   ├── EditMatchModal.vue
│   │   └── ChampionshipCelebration.vue
│   ├── pages/ChampionsLeague/
│   │   └── Index.vue
│   └── types/
│       └── champions-league.d.ts
├── routes/
│   ├── web.php
│   └── api.php                     # API routes
├── docker/                         # Docker configuration
├── tests/
│   ├── Feature/
│   └── Unit/
└── docker-compose.yml
```

## 🎯 Kullanım

1. **Ana Sayfa**: Lig tablosu ve maçları görüntüleyin
2. **Hafta Oynat**: Her haftayı ayrı ayrı oynatın
3. **Tüm Ligi Oynat**: Tüm maçları otomatik oynatın
4. **Maç Düzenle**: Oynanmış maçların sonuçlarını değiştirin
5. **Tahminler**: 3. haftadan sonra gelecek haftalar için tahminleri görüntüleyin

## 🐳 Docker Deployment

### Development
```bash
docker-compose up -d
```

### Production
```bash
# Deployment script'ini çalıştır
chmod +x deploy.sh
./deploy.sh

# Manuel deployment
docker-compose -f docker-compose.production.yml up -d
```

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 👨‍💻 Geliştirici

Bu proje Laravel 12 + Vue.js 3 + Inertia.js + TypeScript teknolojileri kullanılarak geliştirilmiştir.

---

**Not**: Bu proje eğitim amaçlı geliştirilmiştir ve gerçek Champions League ile hiçbir bağlantısı yoktur. 
