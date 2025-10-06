# Overhoren - Nederlandse Oefenwebsite

Een eenvoudige en leuke Nederlandse oefenwebsite voor het maken van toetsen, gebouwd met PHP en MySQL.

> **Note**: Vervang `<host>` in deze documentatie door je eigen hostname (bijv. `localhost`, `example.com`, of `192.168.1.100`). De URLs zijn nu generiek en werken voor elke deployment configuratie.

## Functies

- **Toets Overzicht**: Bekijk beschikbare toetsen op de hoofdpagina
- **Interactief Testen**: Maak toetsen met directe feedback
- **Antwoord Validatie**: Krijg directe feedback op juiste/onjuiste antwoorden
- **Vragen Overslaan**: Optie om vragen over te slaan die je niet weet
- **Resultaten Bekijken**: Zie je score en bekijk alle antwoorden
- **Beheerpaneel**: Voeg nieuwe toetsen en vragen toe
- **Database-based Authenticatie**: Veilige multi-user admin systeem
- **Gebruiksvriendelijk Ontwerp**: Moderne interface met duidelijke navigatie

## Tech Stack

- **Backend**: PHP met PDO
- **Database**: MySQL (via XAMPP)
- **Frontend**: HTML, CSS, JavaScript
- **Styling**: Custom CSS met responsive design

## Installatie Instructies

### Vereisten
- XAMPP geïnstalleerd en actief
- MySQL draait op localhost

### Installatie

#### Optie 1: Automatische Installatie (Aanbevolen)
1. **Start de installatie:**
   - Ga naar http://<host>/install
   - Volg de installatie wizard door 4 eenvoudige stappen

2. **Installatie stappen:**
   - **Stap 1:** Database configuratie (standaard XAMPP instellingen)
   - **Stap 2:** Database installatie (schema + optionele voorbeelddata)
   - **Stap 3:** Admin gebruiker aanmaken
   - **Stap 4:** Installatie voltooid - klaar voor gebruik!

#### Optie 2: Handmatige Installatie
1. **Database opzetten:**
   - Open phpMyAdmin (http://<host>/phpmyadmin)
   - Importeer het database schema:
   ```sql
   -- Voer de inhoud van database/schema.sql uit in phpMyAdmin
   ```
   - Optioneel: Importeer voorbeelddata:
   ```sql
   -- Voer de inhoud van database/data.sql uit voor Nederlandse testdata
   ```

2. **Omgeving configureren:**
   - De app gebruikt standaard XAMPP MySQL instellingen:
     - Host: <host> (standaard: localhost)
     - Gebruiker: root
     - Wachtwoord: (leeg)
     - Database: overhoren

3. **Admin credentials instellen:**
   - Ga naar http://<host>/admin voor de eerste setup
   - Je wordt automatisch doorgestuurd naar de setup pagina
   - Maak je eerste admin gebruiker aan met je eigen credentials
   - ⚠️ Kies een sterk wachtwoord (minimaal 6 karakters)!
   - Het systeem gebruikt database-based authenticatie (geen hardcoded credentials)

4. **Website benaderen:**
   - Hoofdsite: http://<host>/
   - Setup (eerste keer): http://<host>/admin (automatische redirect)
   - Inloggen: http://<host>/login
   - Beheerpaneel: http://<host>/admin

## Project Structuur

```
overhoren/
├── index.php                 # Hoofdbestand (router)
├── install.php              # Automatische installatie wizard
├── setup_admin.php          # Admin setup script
├── config/
│   ├── database.php         # MySQL verbinding
│   └── auth.php             # Database-based authenticatie systeem
├── public/                   # Frontend bestanden
│   ├── index.html           # Hoofdpagina
│   ├── test.html            # Toets maken pagina
│   ├── results.html         # Resultaten pagina
│   ├── admin.html           # Beheerpaneel
│   ├── login.html           # Inlogpagina
│   ├── setup.html           # Setup pagina (eerste admin gebruiker)
│   └── css/
│       └── style.css        # Styling
├── database/
│   ├── schema.sql           # Database schema (tabellen)
│   └── data.sql             # Voorbeelddata (Nederlandse toetsen)
└── README.md
```

## Database Schema

### Tests Tabel
- `id` - Primaire sleutel
- `title` - Toets titel
- `description` - Toets beschrijving
- `created_at` - Aanmaak timestamp

### Questions Tabel
- `id` - Primaire sleutel
- `test_id` - Foreign key naar tests
- `question` - Vraag tekst
- `correct_answer` - Correct antwoord
- `explanation` - Optionele uitleg
- `question_order` - Volgorde van vragen
- `created_at` - Aanmaak timestamp

### Admin Users Tabel
- `id` - Primaire sleutel
- `username` - Unieke gebruikersnaam
- `password_hash` - Veilig gehashed wachtwoord
- `email` - Email adres (optioneel)
- `full_name` - Volledige naam (optioneel)
- `is_active` - Actieve status (boolean)
- `last_login` - Laatste inlog timestamp
- `created_at` - Aanmaak timestamp
- `updated_at` - Laatste update timestamp

## Gebruik

### Voor Studenten
1. Bezoek de hoofdpagina om beschikbare toetsen te zien
2. Klik op "Start Toets" bij een toets
3. Beantwoord vragen een voor een
4. Krijg directe feedback op je antwoorden
5. Sla vragen over die je niet weet
6. Bekijk je eindscore en alle antwoorden

### Voor Beheerders (Eerste Setup)
1. Ga naar het beheerpaneel (/admin)
2. Je wordt automatisch doorgestuurd naar de setup pagina
3. Maak je eerste admin gebruiker aan met je eigen credentials
4. Je wordt automatisch ingelogd en doorgestuurd naar het beheerpaneel

### Voor Beheerders (Normaal Gebruik)
1. Ga naar de inlogpagina (/login)
2. Log in met je admin credentials
3. Beheer toetsen en vragen in het beheerpaneel
4. Maak nieuwe toetsen met titels en beschrijvingen
5. Voeg vragen toe met juiste antwoorden en uitleg
6. Beheer bestaande toetsen (bekijk/verwijder)

### Admin User Management
Het systeem ondersteunt meerdere admin gebruikers:
- **Nieuwe gebruikers toevoegen**: Gebruik de `createAdminUser()` functie
- **Wachtwoorden wijzigen**: Gebruik de `updateAdminUserPassword()` functie
- **Gebruikers beheren**: Gebruik de `updateAdminUser()` en `deleteAdminUser()` functies
- **Veiligheid**: Het systeem voorkomt het verwijderen van de laatste actieve admin

## Beveiliging

### Authenticatie
- **Database-based authenticatie** voor admin toegang
- Session-based authenticatie met automatische timeout (30 minuten)
- Rate limiting tegen brute force aanvallen (5 pogingen per 5 minuten)
- XSS bescherming voor alle gebruikersinvoer
- CSRF token ondersteuning
- **Multi-user support** - meerdere admin gebruikers mogelijk

### Admin Credentials
- **Geen standaard credentials** - maak je eigen admin gebruiker aan
- ⚠️ **Kies een sterk wachtwoord** (minimaal 6 karakters)!
- **Geen hardcoded credentials** - alles wordt opgeslagen in de database
- Wachtwoorden worden veilig gehashed met `password_hash()`
- **Automatische setup** via de setup pagina bij eerste bezoek
- **Flexibele user management** via database functies

### API Beveiliging
- Alle admin API routes vereisen authenticatie
- GET routes (toetsen bekijken) zijn publiek toegankelijk
- POST/PUT/DELETE routes zijn beveiligd

## Voorbeeld Data

De database bevat Nederlandse toetsen:
- **Nederlandse Taal**: Basis Nederlandse taalvaardigheid en grammatica
- **Geschiedenis van Nederland**: Belangrijke gebeurtenissen en personen
- **Aardrijkskunde**: Nederlandse provincies, steden en geografische kennis
- **Rekenen**: Basis rekenvaardigheden en wiskundige concepten
- **Nederlandse Cultuur**: Tradities, feestdagen en culturele aspecten

## Ontwikkeling

### Nieuwe Functies Toevoegen
- API routes zijn in `index.php`
- Frontend logica is in de HTML bestanden
- Styling is in `public/css/style.css`
- Authenticatie functies zijn in `config/auth.php`

### Database Functies
Het systeem biedt uitgebreide user management functies:
- `createAdminUser($username, $password, $email, $fullName)` - Nieuwe admin gebruiker
- `updateAdminUserPassword($userId, $newPassword)` - Wachtwoord wijzigen
- `updateAdminUser($userId, $username, $email, $fullName, $isActive)` - Gebruiker bijwerken
- `getAllAdminUsers()` - Alle gebruikers ophalen
- `getAdminUserById($userId)` - Specifieke gebruiker ophalen
- `deleteAdminUser($userId)` - Gebruiker verwijderen (met veiligheidscontroles)
- `usernameExists($username, $excludeUserId)` - Controleer unieke gebruikersnaam

## Deployment

Voor productie deployment:
1. Zet een productie MySQL database op
2. **Optie A - Automatisch:** Ga naar http://<host>/install en volg de wizard
3. **Optie B - Handmatig:** 
   - Update de database configuratie in `config/database.php`
   - Importeer het database schema (`database/schema.sql`)
   - Optioneel: Importeer voorbeelddata (`database/data.sql`)
   - Ga naar je website en bezoek het beheerpaneel voor de eerste setup
4. **Maak je eigen admin gebruiker aan** met sterke credentials
5. Stel omgevingsvariabelen in voor productie
6. Gebruik een webserver zoals Apache of Nginx

### Productie Checklist
- [ ] **Automatisch:** Installatie wizard voltooid (http://<host>/install)
- [ ] **Handmatig:** Database schema geïmporteerd (`database/schema.sql`)
- [ ] **Handmatig:** Optioneel: Voorbeelddata geïmporteerd (`database/data.sql`)
- [ ] **Handmatig:** Admin gebruiker aangemaakt via setup pagina
- [ ] Sterke wachtwoorden gekozen
- [ ] Productie database configuratie ingesteld
- [ ] SSL certificaat geïnstalleerd (HTTPS)
- [ ] Firewall regels geconfigureerd
- [ ] Backup strategie ingesteld

## Licentie

MIT Licentie - vrij te gebruiken en aan te passen voor educatieve doeleinden.

