# 🏆 Champions League Simülasyonu

4 takımlı lig simülasyonu - Premier League kurallarına göre maç sonuçlarını tahmin edin!

## 📋 Proje Özellikleri

### ✅ Temel Özellikler
- **4 Takım**: Real Madrid, Manchester City, Bayern Munich, PSG
- **6 Maç**: Her takım diğer takımlarla bir kez karşılaşır (tek devreli lig)
- **Premier League Kuralları**: Galibiyet 3, Beraberlik 1, Mağlubiyet 0 puan
- **Güç Seviyesi Sistemi**: Her takımın 1-100 arası güç seviyesi var
- **Ev Sahibi Avantajı**: Ev sahibi takımlar %10 güç bonusu alır

### 🎮 Simülasyon Özellikleri
- **Haftalık Oynatma**: Her haftayı ayrı ayrı oynatabilirsiniz
- **Tüm Ligi Oynat**: Tek tıklamayla tüm maçları otomatik oynatır
- **Gerçekçi Sonuçlar**: Takım güçleri ve ev sahibi avantajı dikkate alınır
- **Maç Düzenleme**: Oynanmış maçların sonuçlarını manuel olarak değiştirebilirsiniz
- **Maç Sıfırlama**: Maçları oynanmamış duruma getirebilirsiniz

### 🔮 Tahmin Sistemi
- **Akıllı Tahminler**: Oynanan maçların gerçek sonuçları + kalan maçların simülasyonu
- **Detaylı İstatistikler**: En çok gol atan, en az gol yiyen, şampiyonluk olasılıkları
- **Görsel Analiz**: Güç seviyesi grafikleri ve sıralama göstergeleri

## 🛠️ Teknolojiler

### Backend
- **PHP 8.2+** - Ana programlama dili
- **Laravel 12** - Web framework
- **MySQL/SQLite** - Veritabanı
- **OOP Prensipleri** - Nesne yönelimli programlama

### Frontend
- **Vue.js 3** - Modern JavaScript framework
- **Inertia.js** - SPA deneyimi
- **Tailwind CSS** - Styling framework
- **TypeScript** - Tip güvenliği

### Test
- **PHPUnit/Pest** - Unit testler
- **Kapsamlı Test Coverage** - %100 test coverage

## 🚀 Kurulum

### Gereksinimler
- PHP 8.2+
- Composer
- Node.js 18+
- MySQL/SQLite

### Adımlar

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
# Terminal 1 - Backend
php artisan serve

# Terminal 2 - Frontend
npm run dev
```

7. **Tarayıcıda açın**
```
http://localhost:8000
```

## 📊 Veritabanı Yapısı

### Teams Tablosu
- `id` - Takım ID
- `name` - Takım adı
- `power_level` - Güç seviyesi (1-100)
- `city` - Şehir
- `logo` - Logo dosyası

### Matches Tablosu
- `id` - Maç ID
- `home_team_id` - Ev sahibi takım
- `away_team_id` - Deplasman takımı
- `home_score` - Ev sahibi gol sayısı
- `away_score` - Deplasman gol sayısı
- `week` - Hafta numarası
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
├── resources/js/pages/
│   ├── Welcome.vue
│   └── ChampionsLeague/
│       ├── Index.vue
│       └── Predictions.vue
├── routes/
│   └── web.php
└── tests/Feature/
    ├── MatchServiceTest.php
    └── LeagueServiceTest.php
```

## 🎯 Kullanım

1. **Ana Sayfa**: Lig tablosu ve maçları görüntüleyin
2. **Hafta Oynat**: Her haftayı ayrı ayrı oynatın
3. **Tüm Ligi Oynat**: Tüm maçları otomatik oynatın
4. **Maç Düzenle**: Oynanmış maçların sonuçlarını değiştirin
5. **Tahminler**: Gelecek haftalar için tahminleri görüntüleyin

## 🔧 API Endpoints

### GET `/champions-league/`
Ana sayfa - Lig tablosu ve maçlar

### GET `/champions-league/predictions`
Tahmin sayfası

### POST `/champions-league/play-week`
Belirli bir haftayı oynat

### POST `/champions-league/play-all`
Tüm maçları oynat

### PUT `/champions-league/matches/{match}/update`
Maç sonucunu güncelle

### PUT `/champions-league/matches/{match}/reset`
Maçı sıfırla

### GET `/champions-league/api/standings`
Lig tablosu API

### GET `/champions-league/api/matches/{week}`
Haftalık maçlar API

## 📈 Özellikler Detayı

### Maç Simülasyonu
- Takım güç seviyeleri dikkate alınır
- Ev sahibi avantajı (+10% güç)
- Poisson dağılımı ile gerçekçi gol sayıları
- Maksimum 5 gol per maç

### Lig Hesaplamaları
- Premier League puanlama sistemi
- Gol farkı ile sıralama
- Otomatik pozisyon güncelleme
- Tahmin algoritması

### Frontend Özellikleri
- Responsive tasarım
- Real-time güncellemeler
- Modal dialoglar
- İnteraktif tablolar
- Görsel istatistikler

## 🤝 Katkıda Bulunma

1. Fork yapın
2. Feature branch oluşturun (`git checkout -b feature/amazing-feature`)
3. Commit yapın (`git commit -m 'Add amazing feature'`)
4. Push yapın (`git push origin feature/amazing-feature`)
5. Pull Request oluşturun

## 📄 Lisans

Bu proje MIT lisansı altında lisanslanmıştır.

## 👨‍💻 Geliştirici

Bu proje Laravel + Vue.js + Inertia.js teknolojileri kullanılarak geliştirilmiştir.

---

**Not**: Bu proje eğitim amaçlı geliştirilmiştir ve gerçek Champions League ile hiçbir bağlantısı yoktur. 