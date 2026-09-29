-- =========================================================
--  Movie Watchlist - Database Schema
--  Import this file into phpMyAdmin / MySQL before running
--  the application.
-- =========================================================

CREATE DATABASE IF NOT EXISTS movie_watchlist
  CHARACTER SET utf8mb4
  COLLATE utf8mb4_unicode_ci;

USE movie_watchlist;

-- ---------------------------------------------------------
-- Table: users
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS users (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(100)  NOT NULL,
    email      VARCHAR(150)  NOT NULL UNIQUE,
    password   VARCHAR(255)  NOT NULL,          -- stored with password_hash()
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Table: movies
-- ---------------------------------------------------------
CREATE TABLE IF NOT EXISTS movies (
    movie_id     INT AUTO_INCREMENT PRIMARY KEY,
    user_id      INT NOT NULL,
    title        VARCHAR(150) NOT NULL,
    genre        VARCHAR(50)  NOT NULL,
    language     VARCHAR(50)  NOT NULL,
    release_year INT          NOT NULL,
    rating       TINYINT      NOT NULL DEFAULT 0,   -- 1 to 5
    status       ENUM('Watched','Not Watched') NOT NULL DEFAULT 'Not Watched',
    poster       VARCHAR(500) DEFAULT NULL,          -- poster image URL
    review       TEXT DEFAULT NULL,
    created_at   TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    CONSTRAINT fk_movies_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE
) ENGINE=InnoDB;

-- ---------------------------------------------------------
-- Optional: sample data (uncomment to use)
-- ---------------------------------------------------------
-- INSERT INTO users (name, email, password)
-- VALUES ('Demo User', 'demo@example.com', '$2y$10$examplehashvalueforreference');
--
-- INSERT INTO movies (user_id, title, genre, language, release_year, rating, status, poster, review)
-- VALUES (1, 'Inception', 'Sci-Fi', 'English', 2010, 5, 'Watched',
--         'https://image.tmdb.org/t/p/w500/9gk7adHYeDvHkCSEqAvQNLV5Uge.jpg',
--         'Mind-bending masterpiece.');
