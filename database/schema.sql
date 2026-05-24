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
    correct_count INTEGER NOT NULL DEFAULT 0,
    time_taken INTEGER NOT NULL DEFAULT 0,
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
('Iron Man', 'Tony Stark is a genius inventor who fights in a high-tech armored suit.', 'assets/images/iron-Man.jpg'),
('Spider-Man', 'Peter Parker is a young hero with spider-like powers and a strong sense of responsibility.', 'assets/images/spider-man.jpg'),
('Captain America', 'Steve Rogers is a super soldier known for his shield, courage, and leadership.', 'assets/images/captain-america.jpg'),
('Black Panther', 'T''Challa is the king of Wakanda and protector of his people as the Black Panther.', 'assets/images/black-panther.jpg');

-- ============================================================
-- IRON MAN (hero_id = 1)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(1, 'What is Iron Man''s real name?', 'Steve Rogers', 'Tony Stark', 'Bruce Banner', 'Clint Barton', 'B', 'easy'),
(1, 'What color is Iron Man''s iconic armor?', 'Blue and silver', 'Black and gold', 'Red and gold', 'Green and purple', 'C', 'easy'),
(1, 'Iron Man is a member of which superhero team?', 'X-Men', 'Avengers', 'Fantastic Four', 'Guardians of the Galaxy', 'B', 'easy'),
(1, 'What company does Tony Stark own?', 'Stark Industries', 'Oscorp', 'LexCorp', 'Wayne Enterprises', 'A', 'easy'),
(1, 'In what city is Stark Tower located?', 'Los Angeles', 'Chicago', 'New York', 'Boston', 'C', 'easy'),

(1, 'What powers Iron Man''s suit in many Marvel stories?', 'Arc Reactor', 'Vibranium Core', 'Gamma Battery', 'Cosmic Cube', 'A', 'medium'),
(1, 'Who is Tony Stark''s loyal personal assistant turned CEO?', 'Maria Hill', 'Pepper Potts', 'Natasha Romanoff', 'Jane Foster', 'B', 'medium'),
(1, 'What is the name of Tony Stark''s AI butler before Vision?', 'FRIDAY', 'KAREN', 'JARVIS', 'EDITH', 'C', 'medium'),
(1, 'Which villain debuted as Iron Man''s rival in Iron Man 2?', 'Mandarin', 'Whiplash', 'Killmonger', 'Ultron', 'B', 'medium'),
(1, 'Tony Stark was kidnapped in what country in the first film?', 'Iraq', 'Iran', 'Afghanistan', 'Sokovia', 'C', 'medium'),

(1, 'In the comics, what was the name of Tony Stark''s original gray Iron Man armor model?', 'Mark I', 'Model 42', 'Bleeding Edge', 'Silver Centurion', 'A', 'hard'),
(1, 'Who created the Iron Man character?', 'Stan Lee, Larry Lieber, Don Heck, Jack Kirby', 'Steve Ditko alone', 'Bob Kane and Bill Finger', 'Jim Starlin', 'A', 'hard'),
(1, 'In what year did Iron Man first appear in comics?', '1961', '1963', '1968', '1972', 'B', 'hard'),
(1, 'What is the name of Tony Stark''s biological father?', 'Howard Stark', 'Edwin Stark', 'Arno Stark', 'Morgan Stark', 'A', 'hard'),
(1, 'Which Iron Man armor is bonded directly to Tony''s nervous system?', 'Hulkbuster', 'Extremis Armor', 'Stealth Armor', 'Hydro Armor', 'B', 'hard');

-- ============================================================
-- SPIDER-MAN (hero_id = 2)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(2, 'What is Spider-Man''s real name?', 'Peter Parker', 'Miles Warren', 'Eddie Brock', 'Harry Osborn', 'A', 'easy'),
(2, 'What gave Peter Parker his powers?', 'A lab accident', 'A radioactive spider bite', 'A super serum', 'Cosmic radiation', 'B', 'easy'),
(2, 'What famous phrase guides Spider-Man?', '"With great power..."', '"Avengers Assemble"', '"I am Iron Man"', '"Wakanda Forever"', 'A', 'easy'),
(2, 'What color is Spider-Man''s classic costume?', 'Black and white', 'Red and blue', 'Green and yellow', 'Purple and gold', 'B', 'easy'),
(2, 'Who raised Peter Parker?', 'His parents', 'May and Ben Parker', 'Tony Stark', 'Nick Fury', 'B', 'easy'),

(2, 'Which newspaper is strongly connected to Spider-Man stories?', 'The Daily Bugle', 'The Wakanda Times', 'The Daily Planet', 'The Stark Report', 'A', 'medium'),
(2, 'Who is the editor that constantly criticizes Spider-Man?', 'Perry White', 'J. Jonah Jameson', 'Robbie Robertson', 'Norman Osborn', 'B', 'medium'),
(2, 'Which villain is also known as the Green Goblin?', 'Otto Octavius', 'Norman Osborn', 'Max Dillon', 'Curt Connors', 'B', 'medium'),
(2, 'What is the name of Peter Parker''s first major love interest?', 'Mary Jane Watson', 'Gwen Stacy', 'Felicia Hardy', 'Betty Brant', 'B', 'medium'),
(2, 'Doctor Octopus has how many mechanical arms?', 'Two', 'Four', 'Six', 'Eight', 'B', 'medium'),

(2, 'In what year did Spider-Man first appear in comics?', '1958', '1962', '1965', '1969', 'B', 'hard'),
(2, 'In what issue did Spider-Man first appear?', 'Amazing Fantasy #15', 'Amazing Spider-Man #1', 'Marvel Mystery Comics #5', 'Fantastic Four #4', 'A', 'hard'),
(2, 'Who created Spider-Man?', 'Stan Lee and Steve Ditko', 'Stan Lee and Jack Kirby', 'Bob Kane and Bill Finger', 'Jim Starlin and John Buscema', 'A', 'hard'),
(2, 'What is the name of the symbiote that bonds with Eddie Brock?', 'Carnage', 'Anti-Venom', 'Venom', 'Toxin', 'C', 'hard'),
(2, 'What chemical formula does Peter Parker use for his web fluid?', 'A polymer adhesive', 'Liquid nitrogen', 'Vibranium gel', 'Pym particles', 'A', 'hard');

-- ============================================================
-- CAPTAIN AMERICA (hero_id = 3)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(3, 'What weapon does Captain America usually carry?', 'Hammer', 'Bow', 'Shield', 'Sword', 'C', 'easy'),
(3, 'What is Captain America''s real name?', 'Steve Rogers', 'Bucky Barnes', 'Sam Wilson', 'Bruce Banner', 'A', 'easy'),
(3, 'In what war did Captain America originally fight?', 'World War I', 'World War II', 'Vietnam War', 'Cold War', 'B', 'easy'),
(3, 'What color scheme is Captain America''s suit?', 'Red, white, and blue', 'Black and red', 'Green and gold', 'Purple and silver', 'A', 'easy'),
(3, 'Captain America was frozen in what?', 'Liquid nitrogen', 'Ice', 'Carbonite', 'A cryogenic chamber', 'B', 'easy'),

(3, 'What experiment gave Steve Rogers his enhanced abilities?', 'Super Soldier Serum', 'Gamma Radiation', 'Extremis', 'Pym Particles', 'A', 'medium'),
(3, 'Who is Captain America''s best friend turned Winter Soldier?', 'Sam Wilson', 'Bucky Barnes', 'Nick Fury', 'Thor', 'B', 'medium'),
(3, 'Captain America''s shield is primarily made of what metal?', 'Adamantium', 'Vibranium', 'Steel', 'Uru', 'B', 'medium'),
(3, 'Who created the super soldier serum?', 'Dr. Erskine', 'Dr. Banner', 'Dr. Pym', 'Dr. Strange', 'A', 'medium'),
(3, 'What organization is Captain America''s archenemy?', 'A.I.M.', 'HYDRA', 'Ten Rings', 'The Hand', 'B', 'medium'),

(3, 'In what year did Captain America first appear in comics?', '1939', '1941', '1945', '1950', 'B', 'hard'),
(3, 'Who created Captain America?', 'Joe Simon and Jack Kirby', 'Stan Lee and Steve Ditko', 'Stan Lee and Jack Kirby', 'Bob Kane and Bill Finger', 'A', 'hard'),
(3, 'What is the name of Captain America''s Nazi nemesis?', 'Baron Zemo', 'Red Skull', 'Crossbones', 'Arnim Zola', 'B', 'hard'),
(3, 'In the comics, who briefly took up the mantle of Captain America in the 80s?', 'John Walker', 'Sam Wilson', 'Bucky Barnes', 'Isaiah Bradley', 'A', 'hard'),
(3, 'What was Steve Rogers'' job before becoming Captain America?', 'Soldier', 'Artist', 'Scientist', 'Boxer', 'B', 'hard');

-- ============================================================
-- BLACK PANTHER (hero_id = 4)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(4, 'What country is Black Panther from?', 'Latveria', 'Wakanda', 'Sokovia', 'Asgard', 'B', 'easy'),
(4, 'What is Black Panther''s real name?', 'T''Chaka', 'T''Challa', 'M''Baku', 'Killmonger', 'B', 'easy'),
(4, 'What is Black Panther''s royal title?', 'Prince', 'King', 'Emperor', 'Chief', 'B', 'easy'),
(4, 'What animal is Black Panther named after?', 'Lion', 'Tiger', 'Panther', 'Jaguar', 'C', 'easy'),
(4, 'Who is Black Panther''s genius younger sister?', 'Okoye', 'Nakia', 'Shuri', 'Ramonda', 'C', 'easy'),

(4, 'What rare metal is Wakanda famous for?', 'Adamantium', 'Uru', 'Vibranium', 'Carbonadium', 'C', 'medium'),
(4, 'Who is the leader of the Dora Milaje, Wakanda''s royal guard?', 'Nakia', 'Shuri', 'Okoye', 'Ayo', 'C', 'medium'),
(4, 'What gives the Black Panther enhanced abilities?', 'A radioactive bite', 'The Heart-Shaped Herb', 'Vibranium injection', 'Cosmic energy', 'B', 'medium'),
(4, 'Who is T''Challa''s primary villain and cousin in the MCU?', 'M''Baku', 'Killmonger', 'Klaw', 'Baron Mordo', 'B', 'medium'),
(4, 'What tribe does M''Baku lead?', 'River Tribe', 'Border Tribe', 'Jabari Tribe', 'Merchant Tribe', 'C', 'medium'),

(4, 'What is the name of the mystical plane where Black Panther can communicate with past rulers of Wakanda?', 'Astral Dimension', 'Ancestral Plane', 'Quantum Realm', 'Dark Dimension', 'B', 'hard'),
(4, 'In what year did Black Panther first appear in comics?', '1961', '1966', '1972', '1980', 'B', 'hard'),
(4, 'Who created Black Panther?', 'Stan Lee and Jack Kirby', 'Stan Lee and Steve Ditko', 'Christopher Priest alone', 'Reginald Hudlin alone', 'A', 'hard'),
(4, 'In what comic did Black Panther first appear?', 'Black Panther #1', 'Fantastic Four #52', 'Avengers #1', 'Jungle Action #6', 'B', 'hard'),
(4, 'What is the name of T''Challa''s father?', 'T''Chaka', 'N''Jobu', 'M''Baku', 'Bashenga', 'A', 'hard');
