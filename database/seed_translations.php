<?php

/**
 * seed_translations.php — fills the Romanian (_ro) columns for heroes and
 * questions. Run after database/setup.php (which seeds the English canonical
 * data). Idempotent: re-running simply rewrites the same translations.
 *
 *   php database/setup.php
 *   php database/seed_translations.php
 *
 * Questions are keyed by their id, which is deterministic (1..135) because
 * schema.sql inserts them in a fixed order. Marvel proper nouns (hero names,
 * places, artifacts) are intentionally kept in their original form.
 */

require_once __DIR__ . '/../includes/db.php';

$pdo = getDatabaseConnection();

// ── Hero descriptions (by hero name) ─────────────────────────────────────────
$heroDescriptions = [
    'Iron Man'       => 'Tony Stark este un inventator genial care luptă într-un costum-armură de înaltă tehnologie.',
    'Spider-Man'     => 'Peter Parker este un erou tânăr cu puteri de păianjen și un puternic simț al responsabilității.',
    'Captain America'=> 'Steve Rogers este un supersoldat cunoscut pentru scutul, curajul și calitățile sale de lider.',
    'Black Panther'  => 'T\'Challa este regele Wakandei și protectorul poporului său în rolul de Black Panther.',
    'Thor'           => 'Zeul asgardian al tunetului mânuiește ciocanul fermecat Mjolnir pentru a proteja cele Nouă Tărâmuri.',
    'Hulk'           => 'Bruce Banner se transformă în Hulkul verde de neoprit ori de câte ori furia pune stăpânire pe el.',
    'Doctor Strange' => 'Stephen Strange este un fost chirurg devenit Vrăjitor Suprem, maestru al artelor mistice.',
    'Scarlet Witch'  => 'Wanda Maximoff mânuiește magia haosului, suficient de puternică pentru a remodela însăși realitatea.',
    'Wolverine'      => 'Logan este un mutant feroce care se regenerează, cu un schelet din adamantium și gheare retractabile.',
];

// ── Question translations, keyed by question id. Each entry is
//    [question_text_ro, option_a_ro, option_b_ro, option_c_ro, option_d_ro] ──
$q = [];

// IRON MAN (1-15)
$q[1]  = ['Care este numele adevărat al lui Iron Man?', 'Steve Rogers', 'Tony Stark', 'Bruce Banner', 'Clint Barton'];
$q[2]  = ['Ce culoare are armura iconică a lui Iron Man?', 'Albastru și argintiu', 'Negru și auriu', 'Roșu și auriu', 'Verde și mov'];
$q[3]  = ['Din ce echipă de supereroi face parte Iron Man?', 'X-Men', 'Avengers', 'Fantastic Four', 'Guardians of the Galaxy'];
$q[4]  = ['Ce companie deține Tony Stark?', 'Stark Industries', 'Oscorp', 'LexCorp', 'Wayne Enterprises'];
$q[5]  = ['În ce oraș se află Stark Tower?', 'Los Angeles', 'Chicago', 'New York', 'Boston'];
$q[6]  = ['Ce alimentează costumul lui Iron Man în multe povești Marvel?', 'Reactorul Arc', 'Nucleu de Vibranium', 'Baterie Gamma', 'Cubul Cosmic'];
$q[7]  = ['Cine este asistenta personală loială a lui Tony Stark, devenită director general?', 'Maria Hill', 'Pepper Potts', 'Natasha Romanoff', 'Jane Foster'];
$q[8]  = ['Cum se numește majordomul AI al lui Tony Stark dinainte de Vision?', 'FRIDAY', 'KAREN', 'JARVIS', 'EDITH'];
$q[9]  = ['Ce răufăcător a debutat ca rival al lui Iron Man în Iron Man 2?', 'Mandarin', 'Whiplash', 'Killmonger', 'Ultron'];
$q[10] = ['În ce țară a fost răpit Tony Stark în primul film?', 'Irak', 'Iran', 'Afganistan', 'Sokovia'];
$q[11] = ['În benzile desenate, cum se numea modelul original gri al armurii Iron Man a lui Tony Stark?', 'Mark I', 'Model 42', 'Bleeding Edge', 'Silver Centurion'];
$q[12] = ['Cine a creat personajul Iron Man?', 'Stan Lee, Larry Lieber, Don Heck, Jack Kirby', 'Steve Ditko singur', 'Bob Kane și Bill Finger', 'Jim Starlin'];
$q[13] = ['În ce an a apărut Iron Man pentru prima dată în benzile desenate?', '1961', '1963', '1968', '1972'];
$q[14] = ['Cum se numește tatăl biologic al lui Tony Stark?', 'Howard Stark', 'Edwin Stark', 'Arno Stark', 'Morgan Stark'];
$q[15] = ['Care armură Iron Man este conectată direct la sistemul nervos al lui Tony?', 'Hulkbuster', 'Armura Extremis', 'Armura Stealth', 'Armura Hydro'];

// SPIDER-MAN (16-30)
$q[16] = ['Care este numele adevărat al lui Spider-Man?', 'Peter Parker', 'Miles Warren', 'Eddie Brock', 'Harry Osborn'];
$q[17] = ['Ce i-a dat puteri lui Peter Parker?', 'Un accident de laborator', 'O mușcătură de păianjen radioactiv', 'Un super ser', 'Radiații cosmice'];
$q[18] = ['Ce frază celebră îl ghidează pe Spider-Man?', '„Cu mare putere…"', '„Răzbunători, adunarea!"', '„Eu sunt Iron Man"', '„Wakanda pentru totdeauna"'];
$q[19] = ['Ce culoare are costumul clasic al lui Spider-Man?', 'Alb și negru', 'Roșu și albastru', 'Verde și galben', 'Mov și auriu'];
$q[20] = ['Cine l-a crescut pe Peter Parker?', 'Părinții lui', 'May și Ben Parker', 'Tony Stark', 'Nick Fury'];
$q[21] = ['Ce ziar este puternic legat de poveștile Spider-Man?', 'The Daily Bugle', 'The Wakanda Times', 'The Daily Planet', 'The Stark Report'];
$q[22] = ['Cine este editorul care îl critică constant pe Spider-Man?', 'Perry White', 'J. Jonah Jameson', 'Robbie Robertson', 'Norman Osborn'];
$q[23] = ['Ce răufăcător este cunoscut și sub numele de Green Goblin?', 'Otto Octavius', 'Norman Osborn', 'Max Dillon', 'Curt Connors'];
$q[24] = ['Cum se numește primul mare interes amoros al lui Peter Parker?', 'Mary Jane Watson', 'Gwen Stacy', 'Felicia Hardy', 'Betty Brant'];
$q[25] = ['Câte brațe mecanice are Doctor Octopus?', 'Două', 'Patru', 'Șase', 'Opt'];
$q[26] = ['În ce an a apărut Spider-Man pentru prima dată în benzile desenate?', '1958', '1962', '1965', '1969'];
$q[27] = ['În ce număr a apărut Spider-Man pentru prima dată?', 'Amazing Fantasy #15', 'Amazing Spider-Man #1', 'Marvel Mystery Comics #5', 'Fantastic Four #4'];
$q[28] = ['Cine a creat Spider-Man?', 'Stan Lee și Steve Ditko', 'Stan Lee și Jack Kirby', 'Bob Kane și Bill Finger', 'Jim Starlin și John Buscema'];
$q[29] = ['Cum se numește simbiotul care se contopește cu Eddie Brock?', 'Carnage', 'Anti-Venom', 'Venom', 'Toxin'];
$q[30] = ['Ce formulă chimică folosește Peter Parker pentru fluidul de pânză?', 'Un adeziv polimeric', 'Azot lichid', 'Gel de Vibranium', 'Particule Pym'];

// CAPTAIN AMERICA (31-45)
$q[31] = ['Ce armă poartă de obicei Captain America?', 'Ciocan', 'Arc', 'Scut', 'Sabie'];
$q[32] = ['Care este numele adevărat al lui Captain America?', 'Steve Rogers', 'Bucky Barnes', 'Sam Wilson', 'Bruce Banner'];
$q[33] = ['În ce război a luptat inițial Captain America?', 'Primul Război Mondial', 'Al Doilea Război Mondial', 'Războiul din Vietnam', 'Războiul Rece'];
$q[34] = ['Ce combinație de culori are costumul lui Captain America?', 'Roșu, alb și albastru', 'Negru și roșu', 'Verde și auriu', 'Mov și argintiu'];
$q[35] = ['În ce a fost înghețat Captain America?', 'Azot lichid', 'Gheață', 'Carbonit', 'O cameră criogenică'];
$q[36] = ['Ce experiment i-a dat lui Steve Rogers abilitățile sporite?', 'Serul Supersoldatului', 'Radiații Gamma', 'Extremis', 'Particule Pym'];
$q[37] = ['Cine este cel mai bun prieten al lui Captain America, devenit Winter Soldier?', 'Sam Wilson', 'Bucky Barnes', 'Nick Fury', 'Thor'];
$q[38] = ['Din ce metal este făcut în principal scutul lui Captain America?', 'Adamantium', 'Vibranium', 'Oțel', 'Uru'];
$q[39] = ['Cine a creat serul supersoldatului?', 'Dr. Erskine', 'Dr. Banner', 'Dr. Pym', 'Dr. Strange'];
$q[40] = ['Ce organizație este arhidușmanul lui Captain America?', 'A.I.M.', 'HYDRA', 'Cele Zece Inele', 'Mâna'];
$q[41] = ['În ce an a apărut Captain America pentru prima dată în benzile desenate?', '1939', '1941', '1945', '1950'];
$q[42] = ['Cine a creat Captain America?', 'Joe Simon și Jack Kirby', 'Stan Lee și Steve Ditko', 'Stan Lee și Jack Kirby', 'Bob Kane și Bill Finger'];
$q[43] = ['Cum se numește nemesisul nazist al lui Captain America?', 'Baron Zemo', 'Red Skull', 'Crossbones', 'Arnim Zola'];
$q[44] = ['În benzile desenate, cine a preluat pentru scurt timp rolul de Captain America în anii \'80?', 'John Walker', 'Sam Wilson', 'Bucky Barnes', 'Isaiah Bradley'];
$q[45] = ['Ce meserie avea Steve Rogers înainte de a deveni Captain America?', 'Soldat', 'Artist', 'Om de știință', 'Boxer'];

// BLACK PANTHER (46-60)
$q[46] = ['Din ce țară este Black Panther?', 'Latveria', 'Wakanda', 'Sokovia', 'Asgard'];
$q[47] = ['Care este numele adevărat al lui Black Panther?', 'T\'Chaka', 'T\'Challa', 'M\'Baku', 'Killmonger'];
$q[48] = ['Care este titlul regal al lui Black Panther?', 'Prinț', 'Rege', 'Împărat', 'Șef de trib'];
$q[49] = ['După ce animal este numit Black Panther?', 'Leu', 'Tigru', 'Panteră', 'Jaguar'];
$q[50] = ['Cine este sora mai mică genială a lui Black Panther?', 'Okoye', 'Nakia', 'Shuri', 'Ramonda'];
$q[51] = ['Pentru ce metal rar este faimoasă Wakanda?', 'Adamantium', 'Uru', 'Vibranium', 'Carbonadium'];
$q[52] = ['Cine conduce Dora Milaje, garda regală a Wakandei?', 'Nakia', 'Shuri', 'Okoye', 'Ayo'];
$q[53] = ['Ce îi conferă lui Black Panther abilități sporite?', 'O mușcătură radioactivă', 'Iarba în formă de inimă', 'O injecție cu Vibranium', 'Energie cosmică'];
$q[54] = ['Cine este principalul răufăcător și văr al lui T\'Challa în MCU?', 'M\'Baku', 'Killmonger', 'Klaw', 'Baron Mordo'];
$q[55] = ['Ce trib conduce M\'Baku?', 'Tribul Râului', 'Tribul de Graniță', 'Tribul Jabari', 'Tribul Negustorilor'];
$q[56] = ['Cum se numește planul mistic unde Black Panther poate comunica cu conducătorii din trecut ai Wakandei?', 'Dimensiunea Astrală', 'Planul Ancestral', 'Tărâmul Cuantic', 'Dimensiunea Întunecată'];
$q[57] = ['În ce an a apărut Black Panther pentru prima dată în benzile desenate?', '1961', '1966', '1972', '1980'];
$q[58] = ['Cine a creat Black Panther?', 'Stan Lee și Jack Kirby', 'Stan Lee și Steve Ditko', 'Christopher Priest singur', 'Reginald Hudlin singur'];
$q[59] = ['În ce bandă desenată a apărut Black Panther pentru prima dată?', 'Black Panther #1', 'Fantastic Four #52', 'Avengers #1', 'Jungle Action #6'];
$q[60] = ['Cum se numește tatăl lui T\'Challa?', 'T\'Chaka', 'N\'Jobu', 'M\'Baku', 'Bashenga'];

// THOR (61-75)
$q[61] = ['Thor este zeul cui?', 'Tunetului', 'Războiului', 'Focului', 'Mării'];
$q[62] = ['Cum se numește ciocanul fermecat al lui Thor?', 'Stormbreaker', 'Mjolnir', 'Gungnir', 'Hofund'];
$q[63] = ['Din ce tărâm provine Thor?', 'Midgard', 'Vanaheim', 'Asgard', 'Jotunheim'];
$q[64] = ['Cine este fratele adoptiv poznaș al lui Thor?', 'Balder', 'Loki', 'Heimdall', 'Fandral'];
$q[65] = ['Ce culoare are de obicei mantia lui Thor?', 'Albastră', 'Verde', 'Roșie', 'Neagră'];
$q[66] = ['Cine este tatăl lui Thor, conducătorul Asgardului?', 'Bor', 'Odin', 'Tyr', 'Vili'];
$q[67] = ['Din ce metal rar asgardian este forjat Mjolnir?', 'Vibranium', 'Adamantium', 'Uru', 'Carbonadium'];
$q[68] = ['Cum se numește podul curcubeu care leagă Asgardul de alte tărâmuri?', 'Bifrost', 'Yggdrasil', 'Gjallarbru', 'Valhalla'];
$q[69] = ['Ce asgardian atoatevăzător păzește Bifrostul?', 'Sif', 'Heimdall', 'Volstagg', 'Hogun'];
$q[70] = ['Ce topor puternic mai mânuiește Thor pe lângă ciocanul său în poveștile ulterioare?', 'Gungnir', 'Stormbreaker', 'Sabia Amurgului', 'Dragonfang'];
$q[71] = ['Cum se numește distrugerea profețită a Asgardului?', 'Fimbulwinter', 'Ragnarok', 'Zorii lui Surtur', 'Gotterdammerung'];
$q[72] = ['În ce an a apărut Thor pentru prima dată în benzile desenate Marvel?', '1958', '1962', '1966', '1970'];
$q[73] = ['În ce bandă desenată a apărut Thor pentru prima dată?', 'Journey into Mystery #83', 'Thor #1', 'Tales of Asgard #1', 'Avengers #1'];
$q[74] = ['Cine a creat Thor?', 'Stan Lee și Steve Ditko', 'Stan Lee, Larry Lieber și Jack Kirby', 'Walt Simonson singur', 'Roy Thomas și Gene Colan'];
$q[75] = ['Ce demon al focului caută să declanșeze Ragnarok și să distrugă Asgardul?', 'Ymir', 'Surtur', 'Malekith', 'Mangog'];

// HULK (76-90)
$q[76] = ['Care este numele adevărat al lui Hulk?', 'Bruce Banner', 'Bruce Wayne', 'Reed Richards', 'Hank Pym'];
$q[77] = ['Ce culoare are Hulk?', 'Roșu', 'Albastru', 'Verde', 'Gri'];
$q[78] = ['Ce emoție declanșează de obicei transformarea lui Bruce Banner în Hulk?', 'Bucuria', 'Furia', 'Frica', 'Tristețea'];
$q[79] = ['Pentru ce calitate incredibilă este cel mai cunoscut Hulk?', 'Viteză', 'Inteligență', 'Forță', 'Zbor'];
$q[80] = ['Care este cea mai faimoasă replică a lui Hulk?', '„Hulk zdrobește"', '„E timpul de bătaie"', '„Răzbunători, adunarea"', '„Wakanda pentru totdeauna"'];
$q[81] = ['Ce tip de radiații l-au creat pe Hulk?', 'Gamma', 'Cosmice', 'Ultraviolete', 'Solare'];
$q[82] = ['Cine este principalul interes amoros al lui Bruce Banner?', 'Betty Ross', 'Jennifer Walters', 'Mary Jane Watson', 'Pepper Potts'];
$q[83] = ['Ce general militar îl vânează necontenit pe Hulk?', 'Nick Fury', 'Thaddeus Ross', 'Glenn Talbot', 'John Walker'];
$q[84] = ['Verișoara lui Bruce Banner devine ce erou?', 'She-Hulk', 'Spider-Woman', 'Ms. Marvel', 'Wasp'];
$q[85] = ['Pe ce planetă a gladiatorilor devine Hulk campion în povestea „Planet Hulk"?', 'Sakaar', 'Knowhere', 'Battleworld', 'Ego'];
$q[86] = ['În ce an a apărut Hulk pentru prima dată în benzile desenate?', '1960', '1962', '1964', '1968'];
$q[87] = ['Ce culoare avea Hulk la prima sa apariție în benzile desenate?', 'Verde', 'Gri', 'Roșu', 'Albastru'];
$q[88] = ['Cine a creat Hulk?', 'Stan Lee și Jack Kirby', 'Stan Lee și Steve Ditko', 'Jack Kirby singur', 'Peter David și Todd McFarlane'];
$q[89] = ['Cum se numește personajul Hulk gri, viclean și îmbrăcat la costum?', 'Joe Fixit', 'Devil Hulk', 'Doc Green', 'World Breaker'];
$q[90] = ['Cum se numește versiunea malefică, din viitor, a lui Hulk?', 'Maestro', 'Abomination', 'Red Hulk', 'The Leader'];

// DOCTOR STRANGE (91-105)
$q[91]  = ['Care este numele adevărat al lui Doctor Strange?', 'Stephen Strange', 'Victor Strange', 'Steven Banner', 'Stephen Vincent'];
$q[92]  = ['Ce profesie avea Stephen Strange înainte de a deveni vrăjitor?', 'Avocat', 'Chirurg', 'Soldat', 'Om de știință'];
$q[93]  = ['În ce este maestru Doctor Strange?', 'Tehnologie', 'Artele mistice', 'Doar arte marțiale', 'Chimie'];
$q[94]  = ['Ce obiect purtat de Doctor Strange poate zbura?', 'Cizmele lui', 'Mantia lui', 'Inelul lui', 'Cureaua lui'];
$q[95]  = ['Ce accidentare a pus capăt carierei chirurgicale a lui Stephen Strange?', 'A fost orbit', 'Mâinile i-au fost rănite într-un accident de mașină', 'Picioarele i-au fost paralizate', 'Spatele i-a fost rupt'];
$q[96]  = ['Cine l-a antrenat pe Stephen Strange în artele mistice?', 'The Ancient One', 'Wong', 'Agamotto', 'Baron Mordo'];
$q[97]  = ['Cum se numește prietenul loial și colegul vrăjitor al lui Strange?', 'Wong', 'Cleo', 'Kaecilius', 'Rintrah'];
$q[98]  = ['Ce relicvă găzduiește Piatra Timpului în MCU?', 'Ochiul lui Agamotto', 'Orbul lui Agamotto', 'Bagheta lui Watoomb', 'Cartea Vishanti'];
$q[99]  = ['Care este titlul lui Doctor Strange ca principal protector al Pământului împotriva amenințărilor magice?', 'Vrăjitorul Suprem', 'Maestrul Magiei', 'Mare Mag', 'Mare Vrăjitor'];
$q[100] = ['Cum se numește conacul-cartier general al lui Strange din New York?', 'Sanctum Sanctorum', 'Kamar-Taj', 'Citadela', 'Sala Misticilor'];
$q[101] = ['În ce an a apărut Doctor Strange pentru prima dată în benzile desenate?', '1961', '1963', '1966', '1968'];
$q[102] = ['Cine este creditat în principal pentru crearea lui Doctor Strange?', 'Steve Ditko', 'Jack Kirby', 'Roy Thomas', 'Jim Starlin'];
$q[103] = ['Ce entitate din dimensiunea întunecată este cel mai iconic dușman al lui Doctor Strange?', 'Dormammu', 'Nightmare', 'Shuma-Gorath', 'Mephisto'];
$q[104] = ['Cum se numește templul unde Strange învață pentru prima dată artele mistice?', 'Kamar-Taj', 'K\'un-Lun', 'Ta Lo', 'Nidavellir'];
$q[105] = ['Care dintre acestea este una dintre ființele Vishanti pe care Strange le invocă pentru putere?', 'Hoggoth', 'Cyttorak', 'Ikonn', 'Watoomb'];

// SCARLET WITCH (106-120)
$q[106] = ['Care este numele adevărat al lui Scarlet Witch?', 'Wanda Maximoff', 'Jean Grey', 'Natasha Romanoff', 'Carol Danvers'];
$q[107] = ['Ce culoare au energia și costumul caracteristice ale lui Scarlet Witch?', 'Albastru', 'Roșu', 'Verde', 'Auriu'];
$q[108] = ['Pe ce se bazează puterile lui Scarlet Witch?', 'Gheață', 'Magie', 'Superviteză', 'Apă'];
$q[109] = ['Cine este fratele geamăn iute de picior al lui Scarlet Witch?', 'Quicksilver', 'Vision', 'Hawkeye', 'Cyclops'];
$q[110] = ['Cu ce echipă de supereroi este cel mai mult asociată Scarlet Witch?', 'X-Force', 'Avengers', 'The Defenders', 'The Inhumans'];
$q[111] = ['De ce Răzbunător sintetic se îndrăgostește Wanda?', 'Ultron', 'Vision', 'Wonder Man', 'Jocasta'];
$q[112] = ['În benzile desenate, cine a fost mult timp crezut a fi tatăl Wandei și al lui Pietro?', 'Magneto', 'Professor X', 'Înaltul Evoluționist', 'Odin'];
$q[113] = ['Care este termenul pentru puterea Wandei de a altera realitatea?', 'Magia Haosului', 'Psionică', 'Vrăjitorie Supremă', 'Telekinezie'];
$q[114] = ['În „House of M", ce frază care deformează realitatea rostește Wanda?', '„Gata cu mutanții"', '„Să fie lumină"', '„Totul e bine"', '„Realitatea e a mea"'];
$q[115] = ['Cum se numesc fiii gemeni ai Wandei și ai lui Vision?', 'Billy și Tommy', 'Pietro și Lorna', 'Thomas și Wonder', 'Hank și Simon'];
$q[116] = ['În ce an a apărut Scarlet Witch pentru prima dată în benzile desenate?', '1962', '1964', '1966', '1970'];
$q[117] = ['În ce bandă desenată a apărut Scarlet Witch pentru prima dată?', 'X-Men #4', 'Avengers #1', 'Giant-Size X-Men #1', 'Tales of Suspense #4'];
$q[118] = ['Cine a creat Scarlet Witch?', 'Stan Lee și Jack Kirby', 'Chris Claremont și John Byrne', 'Steve Ditko și Stan Lee', 'Roy Thomas singur'];
$q[119] = ['Ce demon îi răpește pe fiii gemeni ai Wandei în benzile desenate?', 'Mephisto', 'Dormammu', 'Chthon', 'Nightmare'];
$q[120] = ['Care zeu străvechi este sursa magiei haosului a Wandei în benzile desenate?', 'Chthon', 'Cyttorak', 'Set', 'Gaea'];

// WOLVERINE (121-135)
$q[121] = ['Sub ce nume este cel mai cunoscut Wolverine?', 'Logan', 'Scott', 'Victor', 'Warren'];
$q[122] = ['Ce iese din dosul mâinilor lui Wolverine?', 'Pânze', 'Gheare', 'Lasere', 'Foc'];
$q[123] = ['Din ce echipă face parte de mult timp Wolverine?', 'Avengers', 'X-Men', 'Fantastic Four', 'The Defenders'];
$q[124] = ['Ce abilitate îi permite lui Wolverine să se vindece rapid de aproape orice rană?', 'Telepatie', 'Un factor de vindecare', 'Zbor', 'Invizibilitate'];
$q[125] = ['Din ce țară provine inițial Wolverine?', 'SUA', 'Canada', 'Japonia', 'Australia'];
$q[126] = ['Ce metal aproape indestructibil acoperă scheletul și ghearele lui Wolverine?', 'Vibranium', 'Adamantium', 'Uru', 'Carbonadium'];
$q[127] = ['Ce program secret a fuzionat metalul cu scheletul lui Wolverine?', 'Weapon X', 'Proiectul Renașterea', 'Extremis', 'Departamentul H'];
$q[128] = ['Care este numele de naștere al lui Wolverine, dezvăluit în seria „Origin"?', 'James Howlett', 'Logan Creed', 'John Logan', 'Victor Creed'];
$q[129] = ['Cine este rivalul de lungă durată, sălbatic și înarmat cu gheare, al lui Wolverine?', 'Sabretooth', 'Omega Red', 'Deadpool', 'Daken'];
$q[130] = ['Care clonă feminină este considerată „fiica" lui Wolverine?', 'X-23', 'Jubilee', 'Rogue', 'Domino'];
$q[131] = ['În ce an a făcut Wolverine prima sa apariție completă în benzile desenate?', '1970', '1974', '1978', '1982'];
$q[132] = ['În ce bandă desenată a făcut Wolverine prima sa apariție completă?', 'The Incredible Hulk #181', 'Giant-Size X-Men #1', 'X-Men #1', 'Uncanny X-Men #94'];
$q[133] = ['Cine este creditat pentru crearea lui Wolverine?', 'Roy Thomas, Len Wein și John Romita Sr.', 'Stan Lee și Jack Kirby', 'Chris Claremont și Frank Miller', 'Stan Lee și Steve Ditko'];
$q[134] = ['În saga clasică „Japonia", cine este marea iubire a lui Wolverine?', 'Mariko Yashida', 'Yukio', 'Jean Grey', 'Silver Fox'];
$q[135] = ['Ce metal coroziv, folosit de Omega Red, poate încetini factorul de vindecare al lui Wolverine?', 'Carbonadium', 'Adamantium', 'Promethium', 'Mysterium'];

// ── Apply ────────────────────────────────────────────────────────────────────
$pdo->beginTransaction();

$heroStmt = $pdo->prepare('UPDATE heroes SET description_ro = :d WHERE name = :n');
$heroCount = 0;
foreach ($heroDescriptions as $name => $desc) {
    $heroStmt->execute([':d' => $desc, ':n' => $name]);
    $heroCount += $heroStmt->rowCount();
}

$qStmt = $pdo->prepare(
    'UPDATE questions
     SET question_text_ro = :qt, option_a_ro = :a, option_b_ro = :b, option_c_ro = :c, option_d_ro = :d
     WHERE id = :id'
);
$qCount = 0;
foreach ($q as $id => $row) {
    $qStmt->execute([
        ':qt' => $row[0],
        ':a'  => $row[1],
        ':b'  => $row[2],
        ':c'  => $row[3],
        ':d'  => $row[4],
        ':id' => $id,
    ]);
    $qCount += $qStmt->rowCount();
}

$pdo->commit();

echo "Translated {$heroCount} hero descriptions and {$qCount} questions into Romanian.\n";
