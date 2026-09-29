# CineList — Movie Watchlist Web App

A dark, movie-streaming-themed watchlist app built with **PHP, MySQL, Bootstrap 5,
vanilla JavaScript and XMLHttpRequest (AJAX)**.

## Features
- Registration / Login / Logout with PHP sessions and hashed passwords (`password_hash`)
- Dashboard with summary cards (Total / Watched / Watchlist)
- Full CRUD on movies (Add, View, Edit, Delete)
- Live search (by title) and filters (genre, watch status) — all via AJAX, no page reloads
- One-click "Mark Watched / Not Watched" toggle
- HTML + JavaScript + PHP validation on every form
- Responsive Bootstrap 5 dark UI with hover animations

## Folder Structure
```
movie-watchlist/
├── css/style.css              Dark theme stylesheet
├── js/script.js                All XMLHttpRequest / AJAX logic
├── images/                     (optional local image assets)
├── database/schema.sql         MySQL schema (users + movies tables)
├── includes/
│   ├── auth_check.php          Session guard, included on protected pages
│   └── navbar.php              Shared navigation bar
├── db.php                      MySQL connection (mysqli)
├── index.php                   Login page
├── register.php                Registration page
├── logout.php                  Destroys session
├── dashboard.php                Summary cards + recent movies
├── movies.php                   Movie grid, search & filters (AJAX)
├── add_movie.php                Add movie form (AJAX submit)
├── edit_movie.php               Edit movie form (AJAX submit)
├── profile.php                  Account info page
├── fetch_movies.php             AJAX GET endpoint (search/filter/list)
├── insert_movie.php             AJAX POST endpoint (create)
├── update_movie.php             AJAX POST endpoint (update / toggle status)
└── delete_movie.php             AJAX POST endpoint (delete)
```

## Setup Instructions

1. **Install a local PHP/MySQL stack** (XAMPP, WAMP, MAMP, or Laragon).
2. Copy the `movie-watchlist` folder into your server's web root
   (e.g. `htdocs/` for XAMPP).
3. **Create the database**: open phpMyAdmin (or the MySQL CLI) and import
   `database/schema.sql`. This creates the `movie_watchlist` database along
   with the `users` and `movies` tables.
4. **Check credentials** in `db.php` — defaults match a stock XAMPP install
   (`host: localhost`, `user: root`, `password: ""`). Update if yours differ.
5. Start Apache + MySQL, then visit:
   ```
   http://localhost/movie-watchlist/index.php
   ```
6. Click **"Create one"** to register a new account, then log in.

## Notes
- Poster images are supplied as URLs (e.g. from TMDB or any public image link) —
  no file upload handling is required.
- All AJAX calls use the native `XMLHttpRequest` object as required (no `fetch()`).
- Passwords are never stored in plain text — `password_hash()` / `password_verify()` are used.
- All SQL queries use prepared statements to prevent SQL injection.
