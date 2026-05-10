-- Reactions table
CREATE TABLE IF NOT EXISTS parivar_feed_reactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT NOT NULL,
    user_id INT NOT NULL,
    emoji VARCHAR(10) NOT NULL,
    banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_reaction (post_id, user_id, emoji),
    FOREIGN KEY (post_id) REFERENCES parivar_feed(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id)
);

-- API tokens for Flutter
-- Note: MySQL 8.0 doesn't support IF NOT EXISTS in ALTER TABLE easily.
-- We use a procedure to check if the column exists.

DELIMITER //

CREATE PROCEDURE AddColumnIfNotExist()
BEGIN
    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'parivar' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'api_token') THEN
        ALTER TABLE users ADD COLUMN api_token VARCHAR(64) NULL;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'parivar' AND TABLE_NAME = 'users' AND COLUMN_NAME = 'token_expiry') THEN
        ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'parivar' AND TABLE_NAME = 'karyakram' AND COLUMN_NAME = 'photo_url') THEN
        ALTER TABLE karyakram ADD COLUMN photo_url VARCHAR(500) NULL;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'parivar' AND TABLE_NAME = 'vyakti' AND COLUMN_NAME = 'nakshatra') THEN
        ALTER TABLE vyakti ADD COLUMN nakshatra VARCHAR(50) NULL AFTER gotra;
    END IF;

    IF NOT EXISTS (SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = 'parivar' AND TABLE_NAME = 'vyakti' AND COLUMN_NAME = 'rashi') THEN
        ALTER TABLE vyakti ADD COLUMN rashi VARCHAR(50) NULL AFTER nakshatra;
    END IF;
END //

DELIMITER ;

CALL AddColumnIfNotExist();
DROP PROCEDURE AddColumnIfNotExist;
