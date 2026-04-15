CREATE DATABASE plateforme_jeux CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE plateforme_jeux;

CREATE TABLE board_games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    type ENUM('solo', 'multiplayer') NOT NULL
);

CREATE TABLE games (
    id INT AUTO_INCREMENT PRIMARY KEY,
    board_game_id INT NOT NULL,
    status ENUM('waiting', 'in_progress', 'finished') DEFAULT 'waiting',
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (board_game_id) REFERENCES board_games(id)
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(100) NOT NULL UNIQUE,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created DATETIME DEFAULT CURRENT_TIMESTAMP,
    modified DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE users_ingames (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    game_id INT NOT NULL,
    nom VARCHAR(100),
    score_final INT DEFAULT 0,
    FOREIGN KEY (user_id) REFERENCES users(id),
    FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE mastermind_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL UNIQUE,
    combinaison VARCHAR(50) NOT NULL,
    steps TEXT,
    FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE filler_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL UNIQUE,
    grid TEXT NOT NULL,
    current_player INT DEFAULT 1,
    FOREIGN KEY (game_id) REFERENCES games(id)
);

CREATE TABLE labyrinth_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    game_id INT NOT NULL UNIQUE,
    map TEXT NOT NULL,
    treasure_x INT NOT NULL,
    treasure_y INT NOT NULL,
    pos_p1_x INT DEFAULT 0,
    pos_p1_y INT DEFAULT 0,
    pos_p2_x INT DEFAULT 1,
    pos_p2_y INT DEFAULT 0,
    pa_p1 INT DEFAULT 0,
    pa_p2 INT DEFAULT 0,
    FOREIGN KEY (game_id) REFERENCES games(id)
);

INSERT INTO board_games (name, type) VALUES
    ('Mastermind', 'solo'),
    ('Filler', 'multiplayer'),
    ('Labyrinthe', 'multiplayer');
