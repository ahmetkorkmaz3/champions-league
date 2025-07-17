#!/bin/bash

# Champions League Project Docker Production Deployment Script
# Bu script Docker ile production deployment süreçlerini otomatikleştirir

set -e  # Hata durumunda scripti durdur

# Renkli output için
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Log fonksiyonları
log_info() {
    echo -e "${BLUE}[INFO]${NC} $1"
}

log_success() {
    echo -e "${GREEN}[SUCCESS]${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}[WARNING]${NC} $1"
}

log_error() {
    echo -e "${RED}[ERROR]${NC} $1"
}

# Başlangıç
log_info "Champions League Project Docker Production Deployment başlatılıyor..."

# Gerekli araçların kontrolü
check_requirements() {
    log_info "Gerekli araçlar kontrol ediliyor..."
    
    if ! command -v docker &> /dev/null; then
        log_error "Docker bulunamadı. Lütfen Docker'ı yükleyin."
        exit 1
    fi
    
    if ! command -v docker-compose &> /dev/null; then
        log_error "Docker Compose bulunamadı. Lütfen Docker Compose'u yükleyin."
        exit 1
    fi
    
    log_success "Tüm gerekli araçlar mevcut"
}

# Environment dosyasını kontrol et
check_env() {
    log_info "Environment dosyası kontrol ediliyor..."
    
    if [ ! -f .env ]; then
        if [ -f .env.example ]; then
            log_warning ".env dosyası bulunamadı, .env.example'dan kopyalanıyor..."
            cp .env.example .env
            log_success ".env dosyası oluşturuldu"
        else
            log_error ".env dosyası bulunamadı ve .env.example da yok!"
            exit 1
        fi
    else
        log_success ".env dosyası mevcut"
    fi
}

# Docker imajlarını build et
build_images() {
    log_info "Docker imajları build ediliyor..."
    
    docker-compose -f docker-compose.production.yml build --no-cache
    
    log_success "Docker imajları build edildi"
}

# Docker container'larını başlat
start_containers() {
    log_info "Docker container'ları başlatılıyor..."
    
    docker-compose -f docker-compose.production.yml up -d
    
    log_success "Docker container'ları başlatıldı"
}

# Container'ların hazır olmasını bekle
wait_for_containers() {
    log_info "Container'ların hazır olması bekleniyor..."
    
    # PostgreSQL'in hazır olmasını bekle
    log_info "PostgreSQL'in hazır olması bekleniyor..."
    until docker-compose -f docker-compose.production.yml exec -T postgres pg_isready -U laravel; do
        sleep 2
    done
    
    log_success "PostgreSQL hazır"
    
    # App container'ının çalışmasını bekle
    log_info "App container'ının hazır olması bekleniyor..."
    sleep 10
    
    log_success "Container'lar hazır"
}

# Composer bağımlılıklarını yükle
install_composer_dependencies() {
    log_info "Composer bağımlılıkları yükleniyor..."
    
    # Önce vendor dizininin var olup olmadığını kontrol et
    if ! docker-compose -f docker-compose.production.yml exec -T app test -d vendor; then
        log_info "Vendor dizini bulunamadı, Composer install çalıştırılıyor..."
        docker-compose -f docker-compose.production.yml exec -T app composer install --no-dev --optimize-autoloader
    else
        log_info "Vendor dizini mevcut, Composer install kontrol ediliyor..."
        docker-compose -f docker-compose.production.yml exec -T app composer install --no-dev --optimize-autoloader
    fi
    
    log_success "Composer bağımlılıkları yüklendi"
}

# NPM bağımlılıklarını yükle ve build et
install_and_build_npm() {
    log_info "NPM bağımlılıkları yükleniyor ve build ediliyor..."
    
    # NPM bağımlılıklarını yükle
    docker-compose -f docker-compose.production.yml exec -T app npm ci --production
    
    # Development bağımlılıklarını da yükle (build için gerekli)
    docker-compose -f docker-compose.production.yml exec -T app npm install
    
    # Production build
    docker-compose -f docker-compose.production.yml exec -T app npm run build
    
    log_success "NPM bağımlılıkları yüklendi ve build tamamlandı"
}

# Laravel cache'lerini temizle ve yeniden oluştur
optimize_laravel() {
    log_info "Laravel optimizasyonları yapılıyor..."
    
    # Cache'leri temizle
    docker-compose -f docker-compose.production.yml exec -T app php artisan cache:clear
    docker-compose -f docker-compose.production.yml exec -T app php artisan config:clear
    docker-compose -f docker-compose.production.yml exec -T app php artisan route:clear
    docker-compose -f docker-compose.production.yml exec -T app php artisan view:clear
    
    # Cache'leri yeniden oluştur
    docker-compose -f docker-compose.production.yml exec -T app php artisan config:cache
    docker-compose -f docker-compose.production.yml exec -T app php artisan route:cache
    docker-compose -f docker-compose.production.yml exec -T app php artisan view:cache
    
    log_success "Laravel optimizasyonları tamamlandı"
}

# Veritabanı migration'larını çalıştır
run_migrations() {
    log_info "Veritabanı migration'ları kontrol ediliyor..."
    
    # Migration durumunu kontrol et
    if docker-compose -f docker-compose.production.yml exec -T app php artisan migrate:status | grep -q "No migrations found"; then
        log_warning "Migration bulunamadı, atlanıyor..."
    else
        log_info "Migration'lar çalıştırılıyor..."
        docker-compose -f docker-compose.production.yml exec -T app php artisan migrate --force
        log_success "Migration'lar tamamlandı"
    fi
}

# Storage link oluştur
create_storage_link() {
    log_info "Storage link oluşturuluyor..."
    
    docker-compose -f docker-compose.production.yml exec -T app php artisan storage:link
    
    log_success "Storage link oluşturuldu"
}

# Dosya izinlerini ayarla
set_permissions() {
    log_info "Dosya izinlerini ayarlanıyor..."
    
    # Storage ve cache dizinlerine yazma izni
    docker-compose -f docker-compose.production.yml exec -T app chmod -R 775 storage
    docker-compose -f docker-compose.production.yml exec -T app chmod -R 775 bootstrap/cache
    
    # .env dosyasına okuma izni
    docker-compose -f docker-compose.production.yml exec -T app chmod 644 .env
    
    log_success "Dosya izinleri ayarlandı"
}

# Container durumlarını kontrol et
check_container_status() {
    log_info "Container durumları kontrol ediliyor..."
    
    docker-compose -f docker-compose.production.yml ps
    
    log_success "Container durumları kontrol edildi"
}

# Eski container'ları temizle
cleanup_old_containers() {
    log_info "Eski container'lar temizleniyor..."
    
    # Eski container'ları durdur ve sil
    docker-compose -f docker-compose.production.yml down --remove-orphans
    
    # Kullanılmayan imajları temizle
    docker image prune -f
    
    log_success "Eski container'lar temizlendi"
}

# Laravel'in çalışıp çalışmadığını test et
test_laravel() {
    log_info "Laravel'in çalışıp çalışmadığı test ediliyor..."
    
    if docker-compose -f docker-compose.production.yml exec -T app php artisan --version; then
        log_success "Laravel başarıyla çalışıyor"
    else
        log_error "Laravel çalışmıyor! Lütfen logları kontrol edin."
        exit 1
    fi
}

# Ana deployment fonksiyonu
main() {
    log_info "Docker Production Deployment süreci başlatılıyor..."
    
    # Gerekli araçları kontrol et
    check_requirements
    
    # Environment dosyasını kontrol et
    check_env
    
    # Eski container'ları temizle
    cleanup_old_containers
    
    # Docker imajlarını build et
    build_images
    
    # Docker container'larını başlat
    start_containers
    
    # Container'ların hazır olmasını bekle
    wait_for_containers
    
    # Composer bağımlılıklarını yükle
    install_composer_dependencies
    
    # Laravel'in çalışıp çalışmadığını test et
    test_laravel
    
    # NPM bağımlılıklarını yükle ve build et
    install_and_build_npm
    
    # Laravel optimizasyonları
    optimize_laravel
    
    # Migration'ları çalıştır
    run_migrations
    
    # Storage link oluştur
    create_storage_link
    
    # Dosya izinlerini ayarla
    set_permissions
    
    # Container durumlarını kontrol et
    check_container_status
    
    log_success "Docker Production Deployment süreci başarıyla tamamlandı!"
    log_info "Proje https://champions.ahmetkorkmaz.co adresinde kullanıma hazır."
    log_info "Container durumları: docker-compose -f docker-compose.production.yml ps"
    log_info "Logları görüntülemek için: docker-compose -f docker-compose.production.yml logs -f"
}

# Script'i çalıştır
main "$@" 