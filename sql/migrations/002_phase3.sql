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
ALTER TABLE users ADD COLUMN IF NOT EXISTS api_token VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN IF NOT EXISTS token_expiry DATETIME NULL;

-- Photo support for events
ALTER TABLE karyakram ADD COLUMN IF NOT EXISTS photo_url VARCHAR(500) NULL;

-- Nakshatra in vyakti
ALTER TABLE vyakti ADD COLUMN IF NOT EXISTS nakshatra VARCHAR(50) NULL AFTER gotra;
ALTER TABLE vyakti ADD COLUMN IF NOT EXISTS rashi VARCHAR(50) NULL AFTER nakshatra;
