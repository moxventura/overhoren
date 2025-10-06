# Overhoren - Nederlandse Oefenwebsite

Een eenvoudige en leuke Nederlandse oefenwebsite voor het maken van toetsen, gebouwd met PHP en MySQL.

## Functies

- **Toets Overzicht**: Bekijk beschikbare toetsen op de hoofdpagina
- **Interactief Testen**: Maak toetsen met directe feedback
- **Antwoord Validatie**: Krijg directe feedback op juiste/onjuiste antwoorden
- **Vragen Overslaan**: Optie om vragen over te slaan die je niet weet
- **Resultaten Bekijken**: Zie je score en bekijk alle antwoorden
- **Beheerpaneel**: Voeg nieuwe toetsen en vragen toe
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

1. **Database opzetten:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Importeer het database schema:
   ```sql
   -- Voer de inhoud van database/schema.sql uit in phpMyAdmin
   ```

2. **Omgeving configureren:**
   - De app gebruikt standaard XAMPP MySQL instellingen:
     - Host: localhost
     - Gebruiker: root
     - Wachtwoord: (leeg)
     - Database: overhoren

3. **Website benaderen:**
   - Hoofdsite: http://localhost/overhoren
   - Beheerpaneel: http://localhost/overhoren/admin

## Project Structuur

```
overhoren/
├── index.php                 # Hoofdbestand (router)
├── config/
│   └── database.php         # MySQL verbinding
├── public/                   # Frontend bestanden
│   ├── index.html           # Hoofdpagina
│   ├── test.html            # Toets maken pagina
│   ├── results.html         # Resultaten pagina
│   ├── admin.html           # Beheerpaneel
│   └── css/
│       └── style.css        # Styling
├── database/
│   └── schema.sql           # Database schema
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

## Gebruik

### Voor Studenten
1. Bezoek de hoofdpagina om beschikbare toetsen te zien
2. Klik op "Start Toets" bij een toets
3. Beantwoord vragen een voor een
4. Krijg directe feedback op je antwoorden
5. Sla vragen over die je niet weet
6. Bekijk je eindscore en alle antwoorden

### Voor Beheerders
1. Ga naar het Beheerpaneel
2. Maak nieuwe toetsen met titels en beschrijvingen
3. Voeg vragen toe met juiste antwoorden en uitleg
4. Beheer bestaande toetsen (bekijk/verwijder)

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

## Deployment

Voor productie deployment:
1. Zet een productie MySQL database op
2. Update de database configuratie in `config/database.php`
3. Stel omgevingsvariabelen in voor productie
4. Gebruik een webserver zoals Apache of Nginx

## Licentie

MIT Licentie - vrij te gebruiken en aan te passen voor educatieve doeleinden.

