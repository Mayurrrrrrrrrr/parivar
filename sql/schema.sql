/**
 * परिवार (Parivar) — MySQL Schema
 */

-- 1. परिवार (parivar)
CREATE TABLE IF NOT EXISTS parivar (
  id INT AUTO_INCREMENT PRIMARY KEY,
  naam VARCHAR(100) NOT NULL COMMENT 'परिवार का नाम',
  parivar_code VARCHAR(8) UNIQUE NOT NULL COMMENT '6-अंकीय join code',
  gotra VARCHAR(100) COMMENT 'कुल गोत्र',
  kuldevi VARCHAR(100) COMMENT 'कुलदेवी का नाम',
  mul_sthan VARCHAR(200) COMMENT 'मूल ग्राम/स्थान',
  banaya_user_id INT,
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. उपयोगकर्ता (users)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parivar_id INT NOT NULL,
  naam VARCHAR(100) NOT NULL,
  email VARCHAR(150) UNIQUE,
  phone VARCHAR(15),
  password_hash VARCHAR(255) NOT NULL,
  bhumika ENUM('mukhya','sadasy') DEFAULT 'sadasy' COMMENT 'मुख्य=admin, सदस्य=member',
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  token VARCHAR(255) COMMENT 'API Token',
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. व्यक्ति (vyakti)
CREATE TABLE IF NOT EXISTS vyakti (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parivar_id INT NOT NULL,
  joda_user_id INT COMMENT 'किसने जोड़ा',
  
  -- नाम
  pratham_naam VARCHAR(100) NOT NULL COMMENT 'पहला नाम',
  madhya_naam VARCHAR(100) COMMENT 'मध्य नाम',
  kul_naam VARCHAR(100) COMMENT 'कुल/उपनाम',
  upnaam VARCHAR(50) COMMENT 'घर का नाम/उपनाम',
  
  -- परिचय
  ling ENUM('purush','stri','anya') NOT NULL,
  jeevit TINYINT(1) DEFAULT 1 COMMENT '1=जीवित, 0=दिवंगत',
  
  -- जन्म
  janm_tithi_gregorian DATE COMMENT 'ग्रेगोरियन जन्म तिथि',
  janm_tithi_vs VARCHAR(100) COMMENT 'जैसे: कार्तिक शुक्ल चतुर्दशी, वि.सं. २०४८',
  janm_sthan VARCHAR(200) COMMENT 'जन्म स्थान',
  
  -- मृत्यु (यदि लागू)
  mrityu_tithi_gregorian DATE,
  mrityu_tithi_vs VARCHAR(100),
  
  -- धार्मिक जानकारी (UNIQUE FEATURE)
  gotra VARCHAR(100) COMMENT 'गोत्र',
  pravara VARCHAR(200) COMMENT 'प्रवर',
  nakshatra VARCHAR(50) COMMENT 'जन्म नक्षत्र',
  rashi VARCHAR(50) COMMENT 'राशि',
  
  -- सम्पर्क (केवल मुख्य सदस्य देख सकते हैं)
  phone VARCHAR(15),
  email VARCHAR(150),
  vartaman_sheher VARCHAR(200) COMMENT 'वर्तमान शहर',
  share_code VARCHAR(12) UNIQUE COMMENT 'Profile link code',
  
  -- अन्य
  photo_url VARCHAR(500),
  jeevan_parichay TEXT COMMENT 'जीवन परिचय',
  
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3.5. व्यक्ति परिवार लिंकिंग (vyakti_parivar)
CREATE TABLE IF NOT EXISTS vyakti_parivar (
  vyakti_id INT NOT NULL,
  parivar_id INT NOT NULL,
  joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (vyakti_id, parivar_id),
  FOREIGN KEY (vyakti_id) REFERENCES vyakti(id) ON DELETE CASCADE,
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. संबंध (sambandh)
CREATE TABLE IF NOT EXISTS sambandh (
  id INT AUTO_INCREMENT PRIMARY KEY,
  vyakti_a_id INT NOT NULL COMMENT 'व्यक्ति A',
  vyakti_b_id INT NOT NULL COMMENT 'व्यक्ति B',
  sambandh_prakar ENUM(
    'pita','mata','pati','patni',
    'bhai','behen','putra','putri',
    'datta_putra','datta_putri','datta_pita','datta_mata'
  ) NOT NULL COMMENT 'A का B से संबंध',
  
  -- विवाह जानकारी (यदि sambandh_prakar = pati/patni)
  vivah_tithi_gregorian DATE,
  vivah_tithi_vs VARCHAR(100) COMMENT 'विवाह तिथि वि.सं. में',
  vivah_sthan VARCHAR(200),
  
  tippani VARCHAR(500) COMMENT 'अतिरिक्त टिप्पणी',
  
  FOREIGN KEY (vyakti_a_id) REFERENCES vyakti(id) ON DELETE CASCADE,
  FOREIGN KEY (vyakti_b_id) REFERENCES vyakti(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. कार्यक्रम (karyakram)
CREATE TABLE IF NOT EXISTS karyakram (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parivar_id INT NOT NULL,
  vyakti_id INT COMMENT 'किससे संबंधित (NULL = पूरे परिवार से)',
  banaya_user_id INT,
  
  shirshak VARCHAR(200) NOT NULL COMMENT 'कार्यक्रम का नाम',
  vivaran TEXT COMMENT 'विवरण',
  
  prakar ENUM(
    'janmdin','vivah_varshgaanth','punya_tithi',
    'puja','samskar','utsav','any'
  ) NOT NULL,
  
  -- दोनों कैलेंडर में तिथि
  tithi_gregorian DATE NOT NULL,
  tithi_vs VARCHAR(150) COMMENT 'जैसे: मार्गशीर्ष शुक्ल पंचमी, वि.सं. २०८१',
  
  -- पुनरावृत्ति
  punravrutti TINYINT(1) DEFAULT 1 COMMENT 'प्रतिवर्ष होगा?',
  punravrutti_prakar ENUM('gregorian_varshik','tithi_varshik','ek_baar') DEFAULT 'gregorian_varshik',
  
  -- दृश्यता
  drashyata ENUM('sabhi','sirf_mukhya') DEFAULT 'sabhi',
  
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE,
  FOREIGN KEY (vyakti_id) REFERENCES vyakti(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. परिवार फ़ीड (parivar_feed)
CREATE TABLE IF NOT EXISTS parivar_feed (
  id INT AUTO_INCREMENT PRIMARY KEY,
  parivar_id INT NOT NULL,
  user_id INT NOT NULL,
  vyakti_id INT COMMENT 'किस व्यक्ति से संबंधित (optional tag)',
  karyakram_id INT COMMENT 'किस कार्यक्रम से संबंधित (optional)',
  
  sandesh TEXT NOT NULL COMMENT 'संदेश / पोस्ट',
  photo_url VARCHAR(500),
  
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  
  FOREIGN KEY (parivar_id) REFERENCES parivar(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6.5. परिवार फ़ीड प्रतिक्रिया (parivar_feed_reactions)
CREATE TABLE IF NOT EXISTS parivar_feed_reactions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  feed_id INT NOT NULL,
  user_id INT NOT NULL,
  reaction_type ENUM('like', 'love', 'pray', 'sad') DEFAULT 'like',
  banaya_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY unique_reaction (feed_id, user_id),
  FOREIGN KEY (feed_id) REFERENCES parivar_feed(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. गोत्र निर्देश (gotra_nirdesha)
CREATE TABLE IF NOT EXISTS gotra_nirdesha (
  id INT AUTO_INCREMENT PRIMARY KEY,
  gotra_naam VARCHAR(100) UNIQUE NOT NULL,
  rishi VARCHAR(200) COMMENT 'मूल ऋषि',
  pravara_count INT COMMENT 'प्रवर संख्या (3 या 5)',
  tippani TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Seed Data: गोत्र
INSERT INTO gotra_nirdesha (gotra_naam, rishi, pravara_count) VALUES
('कश्यप', 'महर्षि कश्यप', 3),
('भारद्वाज', 'महर्षि भारद्वाज', 3),
('वशिष्ठ', 'महर्षि वशिष्ठ', 5),
('अत्रि', 'महर्षि अत्रि', 3),
('विश्वामित्र', 'महर्षि विश्वामित्र', 3),
('गौतम', 'महर्षि गौतम', 3),
('जमदग्नि', 'महर्षि जमदग्नि', 3),
('शांडिल्य', 'महर्षि शांडिल्य', 3),
('पराशर', 'महर्षि पराशर', 3),
('कौशिक', 'महर्षि कौशिक', 3);

-- Seed Data: Demo परिवार
INSERT INTO parivar (naam, parivar_code, gotra, kuldevi, mul_sthan) VALUES
('शर्मा परिवार', 'SHRM01', 'भारद्वाज', 'वैष्णो देवी', 'जम्मू');

-- Seed Data: Admin User (password: admin123)
-- Hash for 'admin123' using PASSWORD_BCRYPT is roughly: $2y$10$8u.R6F6YvG.ZpG2.YpG2.u.u.u.u.u.u.u.u.u.u.u.u.u.u.u.u (Place holder)
-- Actual hash for 'admin123'
INSERT INTO users (parivar_id, naam, email, password_hash, bhumika) VALUES
(1, 'Admin', 'admin@parivar.com', '$2y$12$vKo3lWMvLAYfgsw38rwAfududYNXihZ/yqTMeMKIwhmf1VKp0B38W', 'mukhya');

-- Seed Data: Vyakti
INSERT INTO vyakti (id, parivar_id, pratham_naam, kul_naam, ling, jeevit, janm_tithi_gregorian, gotra) VALUES
(1, 1, 'रामप्रसाद', 'शर्मा', 'purush', 0, '1940-08-15', 'भारद्वाज'),
(2, 1, 'सावित्री', 'शर्मा', 'stri', 1, '1945-04-10', 'भारद्वाज'),
(3, 1, 'सुरेश', 'शर्मा', 'purush', 1, '1965-11-14', 'भारद्वाज'),
(4, 1, 'मीना', 'शर्मा', 'stri', 1, '1968-03-22', 'कश्यप'),
(5, 1, 'राहुल', 'शर्मा', 'purush', 1, '1992-07-05', 'भारद्वाज');

-- Seed Data: Sambandh
INSERT INTO sambandh (vyakti_a_id, vyakti_b_id, sambandh_prakar) VALUES
(1, 3, 'pita'), (2, 3, 'mata'),
(3, 4, 'pati'), (4, 3, 'patni'),
(3, 5, 'pita'), (4, 5, 'mata');

-- Seed Data: Karyakram
INSERT INTO karyakram (parivar_id, vyakti_id, shirshak, prakar, tithi_gregorian, tithi_vs, punravrutti_prakar) VALUES
(1, 5, 'राहुल का जन्मदिन', 'janmdin', '1992-07-05', 'आषाढ़ शुक्ल पंचमी, वि.सं. २०४९', 'gregorian_varshik'),
(1, 3, 'सुरेश-मीना विवाह वर्षगाँठ', 'vivah_varshgaanth', '1990-02-20', 'फाल्गुन कृष्ण दशमी, वि.सं. २०४६', 'gregorian_varshik'),
(1, 1, 'रामप्रसाद पुण्यतिथि', 'punya_tithi', '2018-01-12', 'पौष कृष्ण एकादशी, वि.सं. २०७४', 'tithi_varshik');
