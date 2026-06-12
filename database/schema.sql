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
    description_ro TEXT,
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
    question_text_ro TEXT,
    option_a_ro TEXT,
    option_b_ro TEXT,
    option_c_ro TEXT,
    option_d_ro TEXT,
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
('Black Panther', 'T''Challa is the king of Wakanda and protector of his people as the Black Panther.', 'assets/images/black-panther.jpg'),
('Thor', 'The Asgardian god of thunder wields the enchanted hammer Mjolnir to protect the Nine Realms.', 'assets/images/thor.jpg'),
('Hulk', 'Bruce Banner transforms into the unstoppable green Hulk whenever his rage takes over.', 'assets/images/hulk.jpg'),
('Doctor Strange', 'Stephen Strange is a former surgeon turned Sorcerer Supreme, master of the mystic arts.', 'assets/images/doctor-strange.jpeg'),
('Scarlet Witch', 'Wanda Maximoff wields chaos magic powerful enough to reshape reality itself.', 'assets/images/scarlet-witch.jpg'),
('Wolverine', 'Logan is a fierce, regenerating mutant with an adamantium skeleton and retractable claws.', 'assets/images/wolverine.jpg');

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

-- ============================================================
-- THOR (hero_id = 5)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(5, 'Thor is the god of what?', 'Thunder', 'War', 'Fire', 'The sea', 'A', 'easy'),
(5, 'What is the name of Thor''s enchanted hammer?', 'Stormbreaker', 'Mjolnir', 'Gungnir', 'Hofund', 'B', 'easy'),
(5, 'What realm does Thor come from?', 'Midgard', 'Vanaheim', 'Asgard', 'Jotunheim', 'C', 'easy'),
(5, 'Who is Thor''s mischievous adopted brother?', 'Balder', 'Loki', 'Heimdall', 'Fandral', 'B', 'easy'),
(5, 'What color is Thor''s cape usually?', 'Blue', 'Green', 'Red', 'Black', 'C', 'easy'),

(5, 'Who is Thor''s father, the ruler of Asgard?', 'Bor', 'Odin', 'Tyr', 'Vili', 'B', 'medium'),
(5, 'What rare Asgardian metal is Mjolnir forged from?', 'Vibranium', 'Adamantium', 'Uru', 'Carbonadium', 'C', 'medium'),
(5, 'What is the name of the rainbow bridge linking Asgard to other realms?', 'Bifrost', 'Yggdrasil', 'Gjallarbru', 'Valhalla', 'A', 'medium'),
(5, 'Which all-seeing Asgardian guards the Bifrost?', 'Sif', 'Heimdall', 'Volstagg', 'Hogun', 'B', 'medium'),
(5, 'What powerful axe does Thor wields in addition to his hammer in later stories?', 'Gungnir', 'Stormbreaker', 'Twilight Sword', 'Dragonfang', 'B', 'medium'),

(5, 'What is the prophesied destruction of Asgard called?', 'The Fimbulwinter', 'Ragnarok', 'Surtur''s Dawn', 'Gotterdammerung', 'B', 'hard'),
(5, 'In what year did Thor first appear in Marvel Comics?', '1958', '1962', '1966', '1970', 'B', 'hard'),
(5, 'In which comic did Thor make his first appearance?', 'Journey into Mystery #83', 'Thor #1', 'Tales of Asgard #1', 'Avengers #1', 'A', 'hard'),
(5, 'Who created Thor?', 'Stan Lee and Steve Ditko', 'Stan Lee, Larry Lieber and Jack Kirby', 'Walt Simonson alone', 'Roy Thomas and Gene Colan', 'B', 'hard'),
(5, 'What fire demon seeks to bring about Ragnarok and destroy Asgard?', 'Ymir', 'Surtur', 'Malekith', 'Mangog', 'B', 'hard');

-- ============================================================
-- HULK (hero_id = 6)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(6, 'What is the Hulk''s real name?', 'Bruce Banner', 'Bruce Wayne', 'Reed Richards', 'Hank Pym', 'A', 'easy'),
(6, 'What color is the Hulk?', 'Red', 'Blue', 'Green', 'Grey', 'C', 'easy'),
(6, 'What emotion typically triggers Bruce Banner''s transformation into the Hulk?', 'Joy', 'Anger', 'Fear', 'Sadness', 'B', 'easy'),
(6, 'The Hulk is best known for his incredible what?', 'Speed', 'Intelligence', 'Strength', 'Flight', 'C', 'easy'),
(6, 'What is the Hulk''s most famous catchphrase?', 'Hulk smash', 'It''s clobberin'' time', 'Avengers assemble', 'Wakanda forever', 'A', 'easy'),

(6, 'What type of radiation created the Hulk?', 'Gamma', 'Cosmic', 'Ultraviolet', 'Solar', 'A', 'medium'),
(6, 'Who is Bruce Banner''s main love interest?', 'Betty Ross', 'Jennifer Walters', 'Mary Jane Watson', 'Pepper Potts', 'A', 'medium'),
(6, 'Which military general relentlessly hunts the Hulk?', 'Nick Fury', 'Thaddeus Ross', 'Glenn Talbot', 'John Walker', 'B', 'medium'),
(6, 'Bruce Banner''s cousin becomes which hero?', 'She-Hulk', 'Spider-Woman', 'Ms. Marvel', 'Wasp', 'A', 'medium'),
(6, 'On what gladiator planet does the Hulk become a champion in the "Planet Hulk" story?', 'Sakaar', 'Knowhere', 'Battleworld', 'Ego', 'A', 'medium'),

(6, 'In what year did the Hulk first appear in comics?', '1960', '1962', '1964', '1968', 'B', 'hard'),
(6, 'What color was the Hulk in his very first comic appearance?', 'Green', 'Grey', 'Red', 'Blue', 'B', 'hard'),
(6, 'Who created the Hulk?', 'Stan Lee and Jack Kirby', 'Stan Lee and Steve Ditko', 'Jack Kirby alone', 'Peter David and Todd McFarlane', 'A', 'hard'),
(6, 'What is the name of the cunning, suit-wearing grey Hulk persona?', 'Joe Fixit', 'Devil Hulk', 'Doc Green', 'World Breaker', 'A', 'hard'),
(6, 'What is the name of the evil, future version of the Hulk?', 'Maestro', 'Abomination', 'Red Hulk', 'The Leader', 'A', 'hard');

-- ============================================================
-- DOCTOR STRANGE (hero_id = 7)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(7, 'What is Doctor Strange''s real name?', 'Stephen Strange', 'Victor Strange', 'Steven Banner', 'Stephen Vincent', 'A', 'easy'),
(7, 'What was Stephen Strange''s profession before becoming a sorcerer?', 'Lawyer', 'Surgeon', 'Soldier', 'Scientist', 'B', 'easy'),
(7, 'Doctor Strange is a master of what?', 'Technology', 'The mystic arts', 'Martial arts only', 'Chemistry', 'B', 'easy'),
(7, 'What item does Doctor Strange wear that can fly?', 'His boots', 'His cloak', 'His ring', 'His belt', 'B', 'easy'),
(7, 'What injury ended Stephen Strange''s surgical career?', 'His eyes were blinded', 'His hands were damaged in a car crash', 'His legs were paralysed', 'His back was broken', 'B', 'easy'),

(7, 'Who trained Stephen Strange in the mystic arts?', 'The Ancient One', 'Wong', 'Agamotto', 'Baron Mordo', 'A', 'medium'),
(7, 'What is the name of Strange''s loyal friend and fellow sorcerer?', 'Wong', 'Cleo', 'Kaecilius', 'Rintrah', 'A', 'medium'),
(7, 'Which relic houses the Time Stone in the MCU?', 'The Eye of Agamotto', 'The Orb of Agamotto', 'The Wand of Watoomb', 'The Book of Vishanti', 'A', 'medium'),
(7, 'What is Doctor Strange''s title as Earth''s top protector against magical threats?', 'Sorcerer Supreme', 'Master of Magic', 'High Mage', 'Grand Wizard', 'A', 'medium'),
(7, 'What is the name of Strange''s mansion headquarters in New York?', 'Sanctum Sanctorum', 'Kamar-Taj', 'The Citadel', 'Hall of Mystics', 'A', 'medium'),

(7, 'In what year did Doctor Strange first appear in comics?', '1961', '1963', '1966', '1968', 'B', 'hard'),
(7, 'Who is primarily credited with creating Doctor Strange?', 'Steve Ditko', 'Jack Kirby', 'Roy Thomas', 'Jim Starlin', 'A', 'hard'),
(7, 'Which dark-dimension entity is Doctor Strange''s most iconic foe?', 'Dormammu', 'Nightmare', 'Shuma-Gorath', 'Mephisto', 'A', 'hard'),
(7, 'What is the name of the temple where Strange first learns the mystic arts?', 'Kamar-Taj', 'K''un-Lun', 'Ta Lo', 'Nidavellir', 'A', 'hard'),
(7, 'Which of these is one of the Vishanti, the mystic beings Strange invokes for power?', 'Hoggoth', 'Cyttorak', 'Ikonn', 'Watoomb', 'A', 'hard');

-- ============================================================
-- SCARLET WITCH (hero_id = 8)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(8, 'What is Scarlet Witch''s real name?', 'Wanda Maximoff', 'Jean Grey', 'Natasha Romanoff', 'Carol Danvers', 'A', 'easy'),
(8, 'What color is Scarlet Witch''s signature energy and costume?', 'Blue', 'Red', 'Green', 'Gold', 'B', 'easy'),
(8, 'Scarlet Witch''s powers are based on what?', 'Ice', 'Magic', 'Super speed', 'Water', 'B', 'easy'),
(8, 'Who is Scarlet Witch''s fast-running twin brother?', 'Quicksilver', 'Vision', 'Hawkeye', 'Cyclops', 'A', 'easy'),
(8, 'Scarlet Witch is most associated with which superhero team?', 'X-Force', 'The Avengers', 'The Defenders', 'The Inhumans', 'B', 'easy'),

(8, 'Which synthetic Avenger does Wanda fall in love with?', 'Ultron', 'Vision', 'Wonder Man', 'Jocasta', 'B', 'medium'),
(8, 'In the comics, who was long believed to be Wanda and Pietro''s father?', 'Magneto', 'Professor X', 'The High Evolutionary', 'Odin', 'A', 'medium'),
(8, 'What is the term for Wanda''s reality-altering power?', 'Chaos magic', 'Psionics', 'Sorcery Supreme', 'Telekinesis', 'A', 'medium'),
(8, 'In "House of M", Wanda utters which reality-warping phrase?', 'No more mutants', 'Let there be light', 'All is well', 'Reality is mine', 'A', 'medium'),
(8, 'What are the names of Wanda and Vision''s twin sons?', 'Billy and Tommy', 'Pietro and Lorna', 'Thomas and Wonder', 'Hank and Simon', 'A', 'medium'),

(8, 'In what year did Scarlet Witch first appear in comics?', '1962', '1964', '1966', '1970', 'B', 'hard'),
(8, 'In which comic did Scarlet Witch first appear?', 'X-Men #4', 'Avengers #1', 'Giant-Size X-Men #1', 'Tales of Suspense #4', 'A', 'hard'),
(8, 'Who created Scarlet Witch?', 'Stan Lee and Jack Kirby', 'Chris Claremont and John Byrne', 'Steve Ditko and Stan Lee', 'Roy Thomas alone', 'A', 'hard'),
(8, 'Which demon famously takes away Wanda''s twin sons in the comics?', 'Mephisto', 'Dormammu', 'Chthon', 'Nightmare', 'A', 'hard'),
(8, 'Which elder god is the source of Wanda''s chaos magic in the comics?', 'Chthon', 'Cyttorak', 'Set', 'Gaea', 'A', 'hard');

-- ============================================================
-- WOLVERINE (hero_id = 9)
-- ============================================================
INSERT INTO questions (hero_id, question_text, option_a, option_b, option_c, option_d, correct_option, difficulty) VALUES
(9, 'By what single name is Wolverine most commonly known?', 'Logan', 'Scott', 'Victor', 'Warren', 'A', 'easy'),
(9, 'What comes out of the backs of Wolverine''s hands?', 'Webs', 'Claws', 'Lasers', 'Fire', 'B', 'easy'),
(9, 'Wolverine is a longtime member of which team?', 'The Avengers', 'The X-Men', 'The Fantastic Four', 'The Defenders', 'B', 'easy'),
(9, 'What ability lets Wolverine recover quickly from almost any wound?', 'Telepathy', 'A healing factor', 'Flight', 'Invisibility', 'B', 'easy'),
(9, 'What country is Wolverine originally from?', 'The USA', 'Canada', 'Japan', 'Australia', 'B', 'easy'),

(9, 'What nearly indestructible metal coats Wolverine''s skeleton and claws?', 'Vibranium', 'Adamantium', 'Uru', 'Carbonadium', 'B', 'medium'),
(9, 'What secret program bonded the metal to Wolverine''s skeleton?', 'Weapon X', 'Project Rebirth', 'Extremis', 'Department H', 'A', 'medium'),
(9, 'What is Wolverine''s birth name, revealed in the "Origin" series?', 'James Howlett', 'Logan Creed', 'John Logan', 'Victor Creed', 'A', 'medium'),
(9, 'Who is Wolverine''s savage, claw-wielding longtime rival?', 'Sabretooth', 'Omega Red', 'Deadpool', 'Daken', 'A', 'medium'),
(9, 'Which female clone is considered Wolverine''s "daughter"?', 'X-23', 'Jubilee', 'Rogue', 'Domino', 'A', 'medium'),

(9, 'In what year did Wolverine make his first full comic appearance?', '1970', '1974', '1978', '1982', 'B', 'hard'),
(9, 'In which comic did Wolverine make his first full appearance?', 'The Incredible Hulk #181', 'Giant-Size X-Men #1', 'X-Men #1', 'Uncanny X-Men #94', 'A', 'hard'),
(9, 'Who is credited with creating Wolverine?', 'Roy Thomas, Len Wein and John Romita Sr.', 'Stan Lee and Jack Kirby', 'Chris Claremont and Frank Miller', 'Stan Lee and Steve Ditko', 'A', 'hard'),
(9, 'In the classic "Japan" saga, who is Wolverine''s great love?', 'Mariko Yashida', 'Yukio', 'Jean Grey', 'Silver Fox', 'A', 'hard'),
(9, 'What corrosive metal, used by Omega Red, can slow Wolverine''s healing factor?', 'Carbonadium', 'Adamantium', 'Promethium', 'Mysterium', 'A', 'hard');
