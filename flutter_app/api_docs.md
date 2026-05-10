# परिवार API Documentation

सभी API `application/json` रिटर्न करती हैं।

## मुख्य प्रारूप
```json
{
  "safalta": true,
  "data": {},
  "sandesh": "सफलता संदेश"
}
```

## Endpoints

### 1. Authentication
- `POST /api/auth.php?action=login`
  - Params: `email`, `password`
- `POST /api/auth.php?action=register`
  - Params: `parivar_naam`, `naam`, `email`, `password`
- `POST /api/auth.php?action=join`
  - Params: `family_code`, `naam`, `email`, `password`

### 2. Vyakti (Persons)
- `GET /api/vyakti.php?action=list`
- `GET /api/vyakti.php?action=tree` (D3.js format)
- `POST /api/vyakti.php?action=banao`

### 3. Karyakram (Events)
- `GET /api/karyakram.php?action=list`
- `GET /api/karyakram.php?action=upcoming` (Next 7 days)

### 4. Panchang
- `GET /api/panchang.php?action=convert&gregorian=YYYY-MM-DD`
