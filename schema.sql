-- Schema for Press Release Council
-- MySQL dialect

-- Drop existing tables (safe for development)
SET FOREIGN_KEY_CHECKS=0;
DROP TABLE IF EXISTS DistributionRecords;
DROP TABLE IF EXISTS Events;
DROP TABLE IF EXISTS PressReleases;
DROP TABLE IF EXISTS MediaOutlets;
DROP TABLE IF EXISTS Members;
SET FOREIGN_KEY_CHECKS=1;

-- Members table
CREATE TABLE Members (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  role VARCHAR(100) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  bio TEXT DEFAULT NULL,
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_members_email (email)
);

-- Press Releases
CREATE TABLE PressReleases (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  slug VARCHAR(255) DEFAULT NULL,
  summary TEXT DEFAULT NULL,
  content LONGTEXT DEFAULT NULL,
  published_at DATETIME DEFAULT NULL,
  status ENUM('Draft','Published','Archived') NOT NULL DEFAULT 'Draft',
  author_id INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  updated_at DATETIME DEFAULT NULL,
  KEY idx_pr_author (author_id),
  UNIQUE KEY uk_pr_slug (slug),
  CONSTRAINT fk_pr_author FOREIGN KEY (author_id) REFERENCES Members(id) ON DELETE SET NULL
);

-- Media Outlets
CREATE TABLE MediaOutlets (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  contact_person VARCHAR(255) DEFAULT NULL,
  email VARCHAR(255) DEFAULT NULL,
  phone VARCHAR(50) DEFAULT NULL,
  outlet_type VARCHAR(100) DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY uk_media_name (name)
);

-- Distribution Records
CREATE TABLE DistributionRecords (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  release_id INT UNSIGNED NOT NULL,
  media_outlet_id INT UNSIGNED DEFAULT NULL,
  sent_to VARCHAR(255) DEFAULT NULL,
  sent_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  status ENUM('Sent','Failed','Queued') NOT NULL DEFAULT 'Sent',
  note TEXT DEFAULT NULL,
  KEY idx_dist_release (release_id),
  KEY idx_dist_media (media_outlet_id),
  CONSTRAINT fk_dist_release FOREIGN KEY (release_id) REFERENCES PressReleases(id) ON DELETE CASCADE,
  CONSTRAINT fk_dist_media FOREIGN KEY (media_outlet_id) REFERENCES MediaOutlets(id) ON DELETE SET NULL
);

-- Events
CREATE TABLE Events (
  id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  title VARCHAR(255) NOT NULL,
  description TEXT DEFAULT NULL,
  event_date DATETIME DEFAULT NULL,
  location VARCHAR(255) DEFAULT NULL,
  created_by INT UNSIGNED DEFAULT NULL,
  related_release_id INT UNSIGNED DEFAULT NULL,
  created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY idx_events_creator (created_by),
  KEY idx_events_release (related_release_id),
  CONSTRAINT fk_events_creator FOREIGN KEY (created_by) REFERENCES Members(id) ON DELETE SET NULL,
  CONSTRAINT fk_events_release FOREIGN KEY (related_release_id) REFERENCES PressReleases(id) ON DELETE SET NULL
);

-- Sample data (small subset matching the demo API)
INSERT INTO Members (id, name, role, email, joined_at) VALUES
  (1, 'Alice Smith', 'Editor', 'alice@example.com', '2024-01-10 09:00:00'),
  (2, 'Bob Jones', 'Reporter', 'bob@example.com', '2024-03-05 10:30:00');

INSERT INTO PressReleases (id, title, published_at, status, author_id) VALUES
  (1, 'New Initiative Launched', '2025-10-01 09:00:00', 'Published', 1),
  (2, 'Annual Report Released', '2025-04-15 00:00:00', 'Draft', 2);

INSERT INTO MediaOutlets (id, name, contact_person, email) VALUES
  (1, 'Daily News', NULL, 'news@example.com'),
  (2, 'Broadcast Co', NULL, 'broadcast@example.com');

INSERT INTO DistributionRecords (id, release_id, media_outlet_id, sent_to, sent_at) VALUES
  (1, 1, 1, 'Daily News', '2025-10-02 11:00:00');

INSERT INTO Events (id, title, event_date, location) VALUES
  (1, 'Press Briefing', '2025-11-10 14:00:00', 'Hall A');

-- End of schema
