PRAGMA foreign_keys = ON;

CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    username TEXT NOT NULL UNIQUE,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    role TEXT NOT NULL DEFAULT 'user' CHECK (role IN ('user', 'admin')),
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS heroes (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    name TEXT NOT NULL UNIQUE,
    description TEXT NOT NULL,
    image_url TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS questions (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    hero_id INTEGER NOT NULL,
    question_text TEXT NOT NULL,
    option_a TEXT NOT NULL,
    option_b TEXT NOT NULL,
    option_c TEXT NOT NULL,
    option_d TEXT NOT NULL,
    correct_option TEXT NOT NULL CHECK (correct_option IN ('A', 'B', 'C', 'D')),
    difficulty TEXT NOT NULL CHECK (difficulty IN ('easy', 'medium', 'hard')),
    FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS scores (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    user_id INTEGER NOT NULL,
    hero_id INTEGER NOT NULL,
    difficulty TEXT NOT NULL CHECK (difficulty IN ('easy', 'medium', 'hard')),
    score INTEGER NOT NULL DEFAULT 0 CHECK (score >= 0),
    total_questions INTEGER NOT NULL CHECK (total_questions > 0),
    played_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (hero_id) REFERENCES heroes(id) ON DELETE CASCADE
);

DELETE FROM scores;
DELETE FROM questions;
DELETE FROM heroes;
DELETE FROM users;

DELETE FROM sqlite_sequence WHERE name IN ('scores', 'questions', 'heroes', 'users');

INSERT INTO heroes (name, description, image_url) VALUES
('Iron Man', 'Tony Stark is a genius inventor who fights in a high-tech armored suit.', 'assets/images/iron-man.jpg'),
('Spider-Man', 'Peter Parker is a young hero with spider-like powers and a strong sense of responsibility.', 'assets/images/spider-man.jpg'),
('Captain America', 'Steve Rogers is a super soldier known for his shield, courage, and leadership.', 'assets/images/captain-america.jpg'),
('Black Panther', 'T''Challa is the king of Wakanda and protector of his people as the Black Panther.', 'assets/images/black-panther.jpg');

INSERT INTO questions (
    hero_id,
    question_text,
    option_a,
    option_b,
    option_c,
    option_d,
    correct_option,
    difficulty
) VALUES
(
    1,
    'What is Iron Man''s real name?',
    'Steve Rogers',
    'Tony Stark',
    'Bruce Banner',
    'Clint Barton',
    'B',
    'easy'
),
(
    1,
    'What powers Iron Man''s suit in many Marvel stories?',
    'Arc Reactor',
    'Vibranium Core',
    'Gamma Battery',
    'Cosmic Cube',
    'A',
    'medium'
),
(
    2,
    'What is Spider-Man''s real name?',
    'Peter Parker',
    'Miles Warren',
    'Eddie Brock',
    'Harry Osborn',
    'A',
    'easy'
),
(
    2,
    'Which newspaper is strongly connected to Spider-Man stories?',
    'The Daily Bugle',
    'The Wakanda Times',
    'The Daily Planet',
    'The Stark Report',
    'A',
    'medium'
),
(
    3,
    'What weapon does Captain America usually carry?',
    'Hammer',
    'Bow',
    'Shield',
    'Sword',
    'C',
    'easy'
),
(
    3,
    'What experiment gave Steve Rogers his enhanced abilities?',
    'Super Soldier Serum',
    'Gamma Radiation',
    'Extremis',
    'Pym Particles',
    'A',
    'medium'
),
(
    4,
    'What country is Black Panther from?',
    'Latveria',
    'Wakanda',
    'Sokovia',
    'Asgard',
    'B',
    'easy'
),
(
    4,
    'What rare metal is Wakanda famous for?',
    'Adamantium',
    'Uru',
    'Vibranium',
    'Carbonadium',
    'C',
    'medium'
),
(
    1,
    'In the comics, what was the name of Tony Stark''s original gray Iron Man armor model?',
    'Mark I',
    'Model 42',
    'Bleeding Edge',
    'Silver Centurion',
    'A',
    'hard'
),
(
    4,
    'What is the name of the mystical plane where Black Panther can communicate with past rulers of Wakanda?',
    'Astral Dimension',
    'Ancestral Plane',
    'Quantum Realm',
    'Dark Dimension',
    'B',
    'hard'
);
