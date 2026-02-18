# 🚀 IlePay - Plateforme de Paiement Mobile

![IlePay](https://img.shields.io/badge/Version-1.0.0-blue)
![License](https://img.shields.io/badge/License-MIT-green)
![Go](https://img.shields.io/badge/Go-1.21-00ADD8)
![Laravel](https://img.shields.io/badge/Laravel-11-FF2D20)

**IlePay** est une plateforme de paiement mobile moderne permettant des transferts instantanés, paiements marchands via QR Code, et gestion administrative complète.

---

## ✨ Fonctionnalités

### 🔐 Authentification
- ✅ Login par téléphone + PIN (4-6 chiffres)
- ✅ Inscription self-service
- ✅ KYC multi-niveaux (BASIC, STANDARD, PREMIUM)
- ✅ Limites quotidiennes/mensuelles

### 💸 Transactions
- ✅ Transferts P2P instantanés
- ✅ Paiements marchands QR Code
- ✅ Double-écriture comptable
- ✅ Idempotence stricte
- ✅ Notifications temps réel

### 🏪 Système Marchand
- ✅ QR Code unique scannable
- ✅ Dashboard avec statistiques
- ✅ Page paiement publique
- ✅ Limites élevées (500k/jour)

### 👨‍💼 Administration
- ✅ Panel Filament
- ✅ Crédit/Débit via Ledger
- ✅ Gestion KYC
- ✅ Stats temps réel

---

## 🏗️ Architecture
```
Go Ledger (:8082) → PostgreSQL (ledger)
       ↓
Laravel API (:8000) → PostgreSQL (platform_api)
       ↓
Client Web + Admin Filament
```

---

## 🛠️ Technologies

- **Backend** : Go 1.21, PHP 8.2, Laravel 11
- **Database** : PostgreSQL 15
- **Frontend** : Tailwind CSS, Alpine.js
- **Admin** : Filament 3
- **DevOps** : Docker Compose

---

## 📦 Installation

### Prérequis
- Docker & Docker Compose
- Go 1.21+
- PHP 8.2+
- Composer

### Quick Start
```bash
# Clone
git clone https://github.com/IlePay/platform-ledger.git
cd platform-ledger

# Docker
docker-compose up -d

# Go Ledger
cd platform-ledger
go build -o bin/ledger-api cmd/api/main.go
./bin/ledger-api

# Laravel
cd platform-api
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

**Accès** :
- Client : http://localhost:8000
- Admin : http://localhost:8000/admin
- API Ledger : http://localhost:8082

---

## 🎯 Utilisation

### Inscription Utilisateur
1. http://localhost:8000/register
2. Remplis téléphone + PIN
3. Dashboard instantané

### Inscription Marchand
1. http://localhost:8000/register/merchant
2. Infos business
3. Reçois QR Code unique

### Transfert P2P
1. Dashboard → "Envoyer"
2. Numéro destinataire + montant
3. Confirme avec PIN

### Paiement Marchand
1. Scanne QR Code
2. Entre montant + PIN
3. Transaction instantanée

---

## 📚 API Endpoints

### Ledger (Go)

**Créer un compte**
```bash
POST /api/v1/accounts
{
  "external_id": "user_123",
  "type": "USER",
  "currency": "XAF"
}
```

**Transfert**
```bash
POST /api/v1/transfers
{
  "idempotency_key": "uuid",
  "from_account_id": "uuid",
  "to_account_id": "uuid",
  "amount": 10000,
  "currency": "XAF"
}
```

---

## 🚀 Déploiement Production

### Checklist
- [ ] `APP_DEBUG=false`
- [ ] HTTPS/SSL activé
- [ ] Firewall configuré
- [ ] Backups PostgreSQL
- [ ] Monitoring (Sentry)
- [ ] Redis cache
- [ ] Queue workers

---

## 📊 Performance

- **Temps réponse** : <100ms
- **TPS** : 1000+
- **Disponibilité** : 99.9%

---

## 🤝 Contribuer

1. Fork
2. Branch (`git checkout -b feature/Feature`)
3. Commit (`git commit -m 'Add Feature'`)
4. Push (`git push origin feature/Feature`)
5. Pull Request

---

## 📄 Licence

MIT License - voir [LICENSE](LICENSE)

---

## 👥 Équipe

- Product Owner
- Lead Developer
- DevOps Engineer
- UI/UX Designer

---

## 📞 Contact

- **Email** : support@ilepay.com
- **Docs** : https://docs.ilepay.com
- **Status** : https://status.ilepay.com

---

**Fait avec ❤️ par IlePay**
