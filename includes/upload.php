<?php
/**
 * Photo Upload Handler
 */
function uploadPhoto(array $file, string $subfolder = 'general'): ?string {
    $allowed = ['image/jpeg', 'image/png', 'image/webp'];
    $maxSize = 2 * 1024 * 1024; // 2MB
    
    if (!in_array($file['type'], $allowed)) return null;
    if ($file['size'] > $maxSize) return null;
    
    $ext = pathinfo($file['name'], PATHINFO_EXTENSION);
    $newName = bin2hex(random_bytes(16)) . '.' . strtolower($ext);
    
    $uploadDir = __DIR__ . '/../assets/uploads/' . $subfolder . '/';
    if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
    
    if (move_uploaded_file($file['tmp_name'], $uploadDir . $newName)) {
        return 'assets/uploads/' . $subfolder . '/' . $newName;
    }
    return null;
}
