# परिवार API Endpoints

## Authentication
- `POST api/auth.php?action=login` (email, password) -> returns user info + api_token
- `POST api/auth.php?action=register`

## Vyakti (Persons)
- `GET api/vyakti.php?action=list`
- `GET api/vyakti.php?action=tree` -> D3.js compatible JSON

## Karyakram (Events)
- `GET api/karyakram.php?action=upcoming`

## Panchang
- `GET api/panchang.php?gregorian=YYYY-MM-DD`
