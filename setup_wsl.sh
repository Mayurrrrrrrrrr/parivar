#!/bin/bash
# परिवार (Parivar) — WSL Setup Script

echo "🚀 परिवार पोर्टल सेटअप शुरू हो रहा है..."

# 1. आवश्यक पैकेज इंस्टॉल करें
sudo apt-get update
sudo apt-get install -y php php-mysql php-gd php-mbstring mysql-server apache2

# 2. डेटाबेस सेटअप
echo "📦 डेटाबेस कॉन्फ़िगर कर रहा है..."
# नोट: MySQL रूट पासवर्ड खाली मानकर चल रहे हैं
sudo mysql -e "CREATE DATABASE IF NOT EXISTS parivar CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
sudo mysql parivar < sql/schema.sql

# 3. अपाचे कॉन्फ़िगरेशन
echo "🌐 वेब सर्वर सेटअप कर रहा है..."
sudo mkdir -p /var/www/html/parivar
sudo cp -r . /var/www/html/parivar/
sudo chown -R www-data:www-data /var/www/html/parivar
sudo chmod -R 755 /var/www/html/parivar

# 4. सेवाएं शुरू करें
sudo systemctl start apache2 mysql

echo "------------------------------------------------"
echo "✅ परिवार तैयार है!"
echo "ब्राउज़र में खोलें: http://localhost/parivar/"
echo "लॉगिन: admin@parivar.com | पासवर्ड: admin123"
echo "------------------------------------------------"
