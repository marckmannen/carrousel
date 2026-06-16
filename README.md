# Carrousel - Apotheek website

Een webinterface voor het apotheeksmedicijnbestelsysteem. Klanten kunnen medicijnen bestellen via een centrale API, en de bestellingen kunnen worden beheerd door admins.

## vereisten

- **php** 8.3
- **composer** 2.x
- **node.js** 18+ en **npm** 10+
- **sqlite** of **mysql**
- **Apotheek aangemaakt op de centrale server: https://wdc.ti.datalabrotterdam.nl/**

## installatiestappen

```bash
# 1. dependencies installeren
composer install
npm install

# 2. environment bestand aanmaken en vullen met juiste gegevens, zie "belangrijke .env variabelen")
cp .env.example .env
php artisan key:generate

# 3. database opzetten
php artisan migrate

# 4. assets bouwen
npm run build

# 5. development server starten
php artisan serve
npm run dev
```

## belangrijke `.env` variabelen

| variabele | omschrijving |
|---|---|
| `DB_CONNECTION` | `mysql` |
| `DB_DATABASE` | databasenaam (bijv. `carrousel`) |
| `PHARMACY_ID` | unieke id van deze apotheek |
| `PHARMACY_SECRET_KEY` | api authenticatietoken |
| `PHARMACY_API_URL` | url naar centrale medicine api |
