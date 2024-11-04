-- 1. Create Users Table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_name VARCHAR(25) NOT NULL,
    first_name VARCHAR(50),
    last_name VARCHAR(50),
    date_created TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status INT DEFAULT 1
);

-- 2. Create Notes Table
CREATE TABLE IF NOT EXISTS notes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    note_title TEXT NOT NULL,
    description TEXT,
    updated_by INT,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    created_by INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    status INT DEFAULT 1,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

INSERT INTO users (user_name, first_name, last_name)
VALUES('acowo', 'Angel', 'Cowo');
