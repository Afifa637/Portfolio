-- Optional tables for the futuristic portfolio upgrade.
-- Your existing site works without these tables because the frontend has safe fallbacks.

CREATE TABLE IF NOT EXISTS experience (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  company VARCHAR(255) DEFAULT NULL,
  start_date VARCHAR(50) DEFAULT NULL,
  end_date VARCHAR(50) DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  description TEXT,
  tech_stack TEXT,
  order_no INT DEFAULT 0,
  is_current TINYINT(1) DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS current_work (
  id INT AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  type VARCHAR(100) DEFAULT 'Project',
  description TEXT,
  technologies TEXT,
  status VARCHAR(100) DEFAULT 'Active',
  progress INT DEFAULT 50,
  order_no INT DEFAULT 0,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);
