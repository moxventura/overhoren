-- Create database
CREATE DATABASE IF NOT EXISTS overhoren;
USE overhoren;

-- Create tests table
CREATE TABLE IF NOT EXISTS tests (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create questions table
CREATE TABLE IF NOT EXISTS questions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    test_id INT NOT NULL,
    question TEXT NOT NULL,
    correct_answer TEXT NOT NULL,
    explanation TEXT,
    question_order INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (test_id) REFERENCES tests(id) ON DELETE CASCADE
);

-- Insert Dutch test data
INSERT INTO tests (title, description) VALUES 
('Nederlandse Taal', 'Basis Nederlandse taalvaardigheid en grammatica'),
('Geschiedenis van Nederland', 'Belangrijke gebeurtenissen en personen uit de Nederlandse geschiedenis'),
('Aardrijkskunde', 'Nederlandse provincies, steden en geografische kennis'),
('Rekenen', 'Basis rekenvaardigheden en wiskundige concepten'),
('Nederlandse Cultuur', 'Tradities, feestdagen en culturele aspecten van Nederland');

-- Insert questions for Nederlandse Taal
INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES 
(1, 'Wat is de juiste spelling van het woord "fiets"?', 'fiets', 'Fiets wordt geschreven met een "i" en een "e"', 1),
(1, 'Welk lidwoord hoort bij "huis"?', 'het', 'Het woord "huis" krijgt het lidwoord "het"', 2),
(1, 'Wat is de meervoudsvorm van "kind"?', 'kinderen', 'Kind wordt in het meervoud "kinderen"', 3),
(1, 'Hoe schrijf je "gisteren" correct?', 'gisteren', 'Gisteren wordt geschreven met een "g" en "isteren"', 4),
(1, 'Wat is de verleden tijd van "lopen"?', 'liep', 'De verleden tijd van "lopen" is "liep"', 5);

-- Insert questions for Geschiedenis van Nederland
INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES 
(2, 'In welk jaar werd Willem van Oranje vermoord?', '1584', 'Willem van Oranje werd vermoord in 1584 in Delft', 1),
(2, 'Welke stad was de hoofdstad van Nederland tijdens de Gouden Eeuw?', 'amsterdam', 'Amsterdam was het centrum van handel en cultuur', 2),
(2, 'Wie was de eerste koning van Nederland?', 'willem i', 'Willem I werd in 1815 de eerste koning van het Koninkrijk der Nederlanden', 3),
(2, 'In welk jaar werd de watersnoodramp?', '1953', 'De watersnoodramp vond plaats op 1 februari 1953', 4),
(2, 'Welke Nederlandse schilder schilderde "De Nachtwacht"?', 'rembrandt', 'Rembrandt van Rijn schilderde dit beroemde werk in 1642', 5);

-- Insert questions for Aardrijkskunde
INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES 
(3, 'Hoeveel provincies heeft Nederland?', '12', 'Nederland heeft 12 provincies', 1),
(3, 'Wat is de hoofdstad van Friesland?', 'leeuwarden', 'Leeuwarden is de hoofdstad van de provincie Friesland', 2),
(3, 'Welke rivier stroomt door Amsterdam?', 'amstel', 'De Amstel stroomt door Amsterdam en mondt uit in het IJ', 3),
(3, 'Wat is het hoogste punt van Nederland?', 'vaalserberg', 'De Vaalserberg is 322,4 meter hoog en ligt in Limburg', 4),
(3, 'Welke zee grenst aan Nederland?', 'noordzee', 'De Noordzee grenst aan de westkust van Nederland', 5);

-- Insert questions for Rekenen
INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES 
(4, 'Wat is 15 + 27?', '42', '15 + 27 = 42', 1),
(4, 'Wat is 100 - 35?', '65', '100 - 35 = 65', 2),
(4, 'Wat is 8 × 7?', '56', '8 × 7 = 56', 3),
(4, 'Wat is 144 ÷ 12?', '12', '144 ÷ 12 = 12', 4),
(4, 'Wat is de helft van 84?', '42', 'De helft van 84 is 42', 5);

-- Insert questions for Nederlandse Cultuur
INSERT INTO questions (test_id, question, correct_answer, explanation, question_order) VALUES 
(5, 'Op welke datum wordt Koningsdag gevierd?', '27 april', 'Koningsdag wordt gevierd op 27 april, de verjaardag van de koning', 1),
(5, 'Wat eten Nederlanders traditioneel op Sinterklaas?', 'pepernoten', 'Pepernoten zijn een traditioneel Sinterklaas snoepgoed', 2),
(5, 'Welke kleur heeft de Nederlandse vlag?', 'rood wit blauw', 'De Nederlandse vlag heeft drie horizontale banen: rood, wit en blauw', 3),
(5, 'Wat is de nationale bloem van Nederland?', 'tulp', 'De tulp is het symbool van Nederland en wordt veel geteeld', 4),
(5, 'Welke sport is populair in Nederland?', 'voetbal', 'Voetbal is de populairste sport in Nederland', 5);

