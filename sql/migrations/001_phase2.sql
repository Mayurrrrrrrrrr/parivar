-- Phase 2 Migrations
ALTER TABLE users ADD COLUMN api_token VARCHAR(64) NULL;
ALTER TABLE users ADD COLUMN token_expiry DATETIME NULL;

ALTER TABLE vyakti ADD COLUMN nakshatra VARCHAR(50) NULL AFTER gotra;
ALTER TABLE vyakti ADD COLUMN rashi VARCHAR(50) NULL AFTER nakshatra;
ALTER TABLE vyakti ADD COLUMN upnaam VARCHAR(100) NULL AFTER kul_naam;
ALTER TABLE vyakti ADD COLUMN madhya_naam VARCHAR(100) NULL AFTER pratham_naam;

-- Ensure photo columns exist (might already be there)
-- ALTER TABLE parivar_feed ADD COLUMN photo_url VARCHAR(500) NULL;
-- ALTER TABLE vyakti ADD COLUMN photo_url VARCHAR(500) NULL;
