# Champions League API Dokümantasyonu

Bu dokümantasyon, Champions League simülasyonu için RESTful API endpoint'lerini açıklar.

## Genel Bilgiler

- **Base URL**: `http://localhost:8000/api/champions-league`
- **Content-Type**: `application/json`
- **Accept**: `application/json`

## Frontend Entegrasyonu

Frontend (Vue.js + Inertia.js) artık tüm API işlemleri için RESTful API endpoint'lerini kullanmaktadır:

- **Sayfa Görüntüleme**: Web route'ları (`/champions-league`)
- **Veri İşlemleri**: API route'ları (`/api/champions-league/*`)

### Frontend'te Kullanılan API Çağrıları

```javascript
// Hafta oynatma
await router.post(route('api.champions-league.matches.play-week'), { week })

// Tüm maçları oynatma
await router.post(route('api.champions-league.matches.play-all'))

// Maç güncelleme
await router.put(route('api.champions-league.matches.update', matchId), data)

// Maç sıfırlama
await router.put(route('api.champions-league.matches.update', matchId), {
  home_score: null,
  away_score: null,
  is_played: false
})

// Tüm maçları sıfırlama
await router.post(route('api.champions-league.matches.reset'))
```

## Teams (Takımlar)

### GET /teams
Tüm takımları listeler.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Real Madrid",
      "power_level": 95,
      "logo": "real-madrid.png",
      "city": "Madrid",
      "created_at": "2025-07-16T14:52:35.000000Z",
      "updated_at": "2025-07-16T14:52:35.000000Z"
    }
  ]
}
```

### POST /teams
Yeni takım oluşturur.

**Request Body:**
```json
{
  "name": "Barcelona",
  "power_level": 90,
  "logo": "barcelona.png",
  "city": "Barcelona"
}
```

**Validation Rules:**
- `name`: required, string, max:255, unique
- `power_level`: required, integer, min:1, max:100
- `logo`: nullable, string, max:255
- `city`: required, string, max:255

### GET /teams/{id}
Belirli bir takımı getirir.

### PUT /teams/{id}
Takım bilgilerini günceller.

### DELETE /teams/{id}
Takımı siler.

## Matches (Maçlar)

### GET /matches
Tüm maçları listeler.

**Query Parameters:**
- `week`: Hafta numarasına göre filtreleme
- `played`: Oynanmış/oynanmamış maçlara göre filtreleme (true/false)

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "week": 1,
      "is_played": true,
      "home_score": 2,
      "away_score": 1,
      "home_team": {
        "id": 1,
        "name": "Real Madrid",
        "power_level": 95,
        "logo": "real-madrid.png",
        "city": "Madrid"
      },
      "away_team": {
        "id": 2,
        "name": "Manchester City",
        "power_level": 92,
        "logo": "man-city.png",
        "city": "Manchester"
      },
      "result_string": "2 - 1",
      "winner": {
        "id": 1,
        "name": "Real Madrid"
      },
      "is_draw": false
    }
  ]
}
```

### POST /matches
Yeni maç oluşturur.

**Request Body:**
```json
{
  "home_team_id": 1,
  "away_team_id": 2,
  "week": 1
}
```

**Validation Rules:**
- `home_team_id`: required, integer, exists:teams,id
- `away_team_id`: required, integer, exists:teams,id, different:home_team_id
- `week`: required, integer, min:1

### GET /matches/{id}
Belirli bir maçı getirir.

### PUT /matches/{id}
Maç bilgilerini günceller.

**Request Body:**
```json
{
  "home_score": 2,
  "away_score": 1,
  "is_played": true
}
```

**Validation Rules:**
- `home_score`: nullable, integer, min:0
- `away_score`: nullable, integer, min:0
- `is_played`: boolean

### DELETE /matches/{id}
Maçı siler.

### GET /matches/by-week
Maçları haftalara göre gruplandırarak getirir.

**Response:**
```json
{
  "data": {
    "1": [
      {
        "id": 1,
        "week": 1,
        "home_team": {...},
        "away_team": {...}
      }
    ],
    "2": [...]
  }
}
```

## Standings (Lig Tablosu)

### GET /standings
Lig tablosunu getirir.

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "position": 1,
      "points": 3,
      "goals_for": 2,
      "goals_against": 1,
      "goal_difference": 1,
      "wins": 1,
      "draws": 0,
      "losses": 0,
      "matches_played": 1,
      "team": {
        "id": 1,
        "name": "Real Madrid",
        "power_level": 95,
        "logo": "real-madrid.png",
        "city": "Madrid"
      }
    }
  ]
}
```

### POST /standings
Yeni lig tablosu kaydı oluşturur.

**Request Body:**
```json
{
  "team_id": 1,
  "position": 1,
  "points": 3,
  "goals_for": 2,
  "goals_against": 1,
  "goal_difference": 1,
  "wins": 1,
  "draws": 0,
  "losses": 0
}
```

**Validation Rules:**
- `team_id`: required, integer, exists:teams,id, unique
- `position`: required, integer, min:1
- `points`: required, integer, min:0
- `goals_for`: required, integer, min:0
- `goals_against`: required, integer, min:0
- `goal_difference`: required, integer
- `wins`: required, integer, min:0
- `draws`: required, integer, min:0
- `losses`: required, integer, min:0

### GET /standings/{id}
Belirli bir lig tablosu kaydını getirir.

### PUT /standings/{id}
Lig tablosu kaydını günceller.

### DELETE /standings/{id}
Lig tablosu kaydını siler.

## League Actions (Lig İşlemleri)

### POST /matches/play-week
Belirli bir haftanın maçlarını oynatır.

**Request Body:**
```json
{
  "week": 1
}
```

**Validation Rules:**
- `week`: required, integer, min:1

**Response:** HTTP 204 (No Content)

### POST /matches/play-all
Tüm maçları oynatır.

**Response:** HTTP 204 (No Content)

### POST /matches/reset
Tüm maçları ve lig tablosunu sıfırlar.

**Response:** HTTP 204 (No Content)

## HTTP Status Codes

- **200 OK**: Başarılı GET istekleri
- **201 Created**: Başarılı POST istekleri (yeni kayıt oluşturma)
- **204 No Content**: Başarılı PUT/DELETE istekleri ve lig işlemleri
- **400 Bad Request**: Validation hataları
- **404 Not Found**: Kaynak bulunamadı
- **422 Unprocessable Entity**: Validation hataları (detaylı)

## Validation Error Response

```json
{
  "message": "The given data was invalid.",
  "errors": {
    "name": [
      "The name field is required."
    ],
    "power_level": [
      "The power level must be between 1 and 100."
    ]
  }
}
```

## Türkçe Validation Mesajları

API, Türkçe validation mesajları döndürür:

- "Takım adı zorunludur."
- "Güç seviyesi 1 ile 100 arasında olmalıdır."
- "Ev sahibi takım ID'si zorunludur."
- "Deplasman takımı ID'si zorunludur."
- "Hafta numarası zorunludur."
- "Ev sahibi takım ve deplasman takımı aynı olamaz."

## Örnek Kullanım

### cURL Örnekleri

```bash
# Tüm maçları listele
curl -X GET http://localhost:8000/api/champions-league/matches

# 1. haftayı oynat
curl -X POST http://localhost:8000/api/champions-league/matches/play-week \
  -H "Content-Type: application/json" \
  -d '{"week": 1}'

# Maç güncelle
curl -X PUT http://localhost:8000/api/champions-league/matches/1 \
  -H "Content-Type: application/json" \
  -d '{"home_score": 2, "away_score": 1, "is_played": true}'

# Tüm maçları sıfırla
curl -X POST http://localhost:8000/api/champions-league/matches/reset
```

### JavaScript/Fetch Örnekleri

```javascript
// Maçları getir
const response = await fetch('/api/champions-league/matches');
const matches = await response.json();

// Hafta oynat
await fetch('/api/champions-league/matches/play-week', {
  method: 'POST',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({ week: 1 })
});

// Maç güncelle
await fetch('/api/champions-league/matches/1', {
  method: 'PUT',
  headers: {
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    home_score: 2,
    away_score: 1,
    is_played: true
  })
});
```

## Notlar

- Tüm API endpoint'leri RESTful standartlarına uygun olarak tasarlanmıştır
- FormRequest sınıfları ile validation yapılmaktadır
- API Resource sınıfları ile tutarlı response formatı sağlanmaktadır
- HTTP status code'ları doğru şekilde kullanılmaktadır
- Türkçe validation mesajları desteklenmektedir
- Frontend ile tam entegrasyon sağlanmıştır 