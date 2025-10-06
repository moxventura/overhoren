# Learning Practice Website

A simple and fun learning practice website for kids built with Node.js and MySQL.

## Features

- **Test Overview**: Browse available tests on the main page
- **Interactive Testing**: Take tests with immediate feedback
- **Answer Validation**: Get instant feedback on correct/incorrect answers
- **Skip Questions**: Option to skip questions you don't know
- **Results Review**: See your score and review all answers
- **Admin Panel**: Add new tests and questions (authorized users)
- **Kid-Friendly Design**: Bright colors, large buttons, simple navigation

## Tech Stack

- **Backend**: Node.js with Express.js
- **Database**: MySQL (via XAMPP)
- **Frontend**: HTML, CSS, JavaScript
- **Styling**: Custom CSS with responsive design

## Setup Instructions

### Prerequisites
- XAMPP installed and running
- Node.js installed
- MySQL running on localhost

### Installation

1. **Install dependencies:**
```bash
npm install
```

2. **Set up the database:**
   - Open phpMyAdmin (http://localhost/phpmyadmin)
   - Import the database schema:
   ```sql
   -- Run the contents of database/schema.sql in phpMyAdmin
   ```

3. **Configure environment:**
   - The app uses default XAMPP MySQL settings:
     - Host: localhost
     - User: root
     - Password: (empty)
     - Database: overhoren

4. **Start the server:**
```bash
npm start
```

5. **Access the website:**
   - Main site: http://overhoren.test
   - Admin panel: http://overhoren.test/admin

## Project Structure

```
overhoren/
├── server.js                 # Main server file
├── package.json              # Dependencies
├── config/
│   └── database.js          # MySQL connection
├── public/                   # Frontend files
│   ├── index.html           # Main page
│   ├── test.html            # Test taking page
│   ├── results.html         # Results page
│   ├── admin.html           # Admin panel
│   └── css/
│       └── style.css        # Styling
├── database/
│   └── schema.sql           # Database schema
└── README.md
```

## Database Schema

### Tests Table
- `id` - Primary key
- `title` - Test title
- `description` - Test description
- `created_at` - Creation timestamp

### Questions Table
- `id` - Primary key
- `test_id` - Foreign key to tests
- `question` - Question text
- `correct_answer` - Correct answer
- `explanation` - Optional explanation
- `question_order` - Order of questions
- `created_at` - Creation timestamp

## Usage

### For Students
1. Visit the main page to see available tests
2. Click "Start Test" on any test
3. Answer questions one by one
4. Get immediate feedback on your answers
5. Skip questions you don't know
6. View your final score and review all answers

### For Admins
1. Go to the Admin Panel
2. Create new tests with titles and descriptions
3. Add questions with correct answers and explanations
4. Manage existing tests (view/delete)

## Sample Data

The database comes with sample tests:
- **Math Basics**: Simple addition and subtraction
- **Spelling Test**: Common words for kids
- **Colors Quiz**: Learning about colors
- **Animal Facts**: Fun facts about animals

## Development

### Running in Development Mode
```bash
npm run dev
```
This uses nodemon for automatic server restarts.

### Adding New Features
- API routes are in `server.js`
- Frontend logic is in the HTML files
- Styling is in `public/css/style.css`

## Deployment

For production deployment:
1. Set up a production MySQL database
2. Update the database configuration in `config/database.js`
3. Set environment variables for production
4. Use a process manager like PM2

## License

MIT License - feel free to use and modify for educational purposes.

