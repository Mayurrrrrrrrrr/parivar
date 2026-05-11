<?php
require_once __DIR__ . '/../config/db.php';

// 1. Add share_code to vyakti
try {
    $pdo->exec("ALTER TABLE vyakti ADD COLUMN share_code VARCHAR(12) UNIQUE");
    echo "Added share_code column.\n";
} catch (Exception $e) {
    echo "share_code column might already exist.\n";
}

// 2. Create vyakti_parivar table
$pdo->exec("
CREATE TABLE IF NOT EXISTS vyakti_parivar (
  vyakti_id INT NOT NULL,
  parivar_id INT NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (vyakti_id, parivar_id),
  FOREIGN KEY (vyakti_id) REFERENCES vyakti(id) ON DELETE CASCADE,
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
");
echo "Created vyakti_parivar table.\n";

// 3. Migrate data
$stmt = $pdo->query("SELECT id, parivar_id, share_code FROM vyakti");
$vyaktis = $stmt->fetchAll();

foreach ($vyaktis as $v) {
    // Insert into vyakti_parivar
    try {
        $st = $pdo->prepare("INSERT IGNORE INTO vyakti_parivar (vyakti_id, parivar_id) VALUES (?, ?)");
        $st->execute([$v['id'], $v['parivar_id']]);
    } catch(Exception $e) {}

    // Generate share_code if missing
    if (empty($v['share_code'])) {
        $code = 'VK-' . strtoupper(substr(md5(uniqid(rand(), true)), 0, 6));
        $st = $pdo->prepare("UPDATE vyakti SET share_code = ? WHERE id = ?");
        $st->execute([$code, $v['id']]);
    }
}
echo "Migration complete.\n";
