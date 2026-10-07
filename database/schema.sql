-- ===========================================================================
-- Portfolio database schema
-- ---------------------------------------------------------------------------
-- Every piece of content the public site renders is editable from /admin.
-- config/profile.php remains the fallback, so the site still works if the
-- database is unreachable — but when it is reachable, the database wins.
--
-- Fresh install:
--   mysql -u root -p -e "CREATE DATABASE portfolio_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
--   mysql -u root -p portfolio_db < database/schema.sql
--   mysql -u root -p portfolio_db < database/seed.sql
--   php database/create_admin.php
--
-- Existing install:
--   php database/migrate.php        (additive and idempotent — keeps your rows)
--
-- Every statement here is safe to re-run.
-- ===========================================================================

SET NAMES utf8mb4;


-- --------------------------------------------------------------- admins ---

CREATE TABLE IF NOT EXISTS admins (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(120)  NOT NULL DEFAULT 'Administrator',
    email      VARCHAR(190)  NOT NULL,
    password   VARCHAR(255)  NOT NULL COMMENT 'bcrypt hash — never a plaintext password',
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_admins_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- -------------------------------------------------------- site_settings ---
--
-- A self-describing key/value store for every scalar string on the site:
-- name, job title, hero pitch, section headings, SEO metadata.
--
-- Each row carries its own label, input type and hint, so the settings screen
-- renders itself from the data. Adding a new editable string is an INSERT,
-- not a code change.

CREATE TABLE IF NOT EXISTS site_settings (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    group_key   VARCHAR(40)  NOT NULL DEFAULT 'general' COMMENT 'identity | hero | about | contact | seo',
    setting_key VARCHAR(80)  NOT NULL,
    value       TEXT         DEFAULT NULL,
    label       VARCHAR(160) NOT NULL,
    input_type  VARCHAR(20)  NOT NULL DEFAULT 'text' COMMENT 'text | textarea | email | url | image | bool',
    hint        VARCHAR(255) DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0,
    UNIQUE KEY uq_settings_key (setting_key),
    KEY idx_settings_group (group_key, order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ------------------------------------------------------------ home_info ---
-- Retained for backward compatibility with the original admin screens.

CREATE TABLE IF NOT EXISTS home_info (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    name          VARCHAR(160)  NOT NULL,
    subtitle      VARCHAR(190)  DEFAULT NULL,
    description   TEXT          DEFAULT NULL,
    location      VARCHAR(160)  DEFAULT NULL,
    email         VARCHAR(190)  DEFAULT NULL,
    availability  VARCHAR(160)  DEFAULT NULL,
    profile_image VARCHAR(255)  DEFAULT NULL,
    cv_link       VARCHAR(255)  DEFAULT NULL,
    updated_at    DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------------- home_roles ---
-- The rotating job titles in the hero type-effect.

CREATE TABLE IF NOT EXISTS home_roles (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    role     VARCHAR(160) NOT NULL,
    order_no INT          NOT NULL DEFAULT 0,
    KEY idx_home_roles_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------- home_socials ---

CREATE TABLE IF NOT EXISTS home_socials (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    platform   VARCHAR(80)  NOT NULL,
    url        VARCHAR(255) NOT NULL,
    icon_class VARCHAR(120) DEFAULT NULL COMMENT 'Legacy Font Awesome class, mapped to an inline icon',
    order_no   INT          NOT NULL DEFAULT 0,
    KEY idx_home_socials_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ---------------------------------------------------------------- about ---

CREATE TABLE IF NOT EXISTS about (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    short_intro   TEXT         DEFAULT NULL COMMENT 'Lead paragraph',
    long_intro    TEXT         DEFAULT NULL COMMENT 'Body text, blank lines separate paragraphs',
    profile_image VARCHAR(255) DEFAULT NULL,
    updated_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------------- about_facts ---
-- The "Based in / Studying / Focus / Languages" list beside the portrait.

CREATE TABLE IF NOT EXISTS about_facts (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    label    VARCHAR(120) NOT NULL,
    value    VARCHAR(255) NOT NULL,
    order_no INT          NOT NULL DEFAULT 0,
    KEY idx_about_facts_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ---------------------------------------------------------- skill_groups ---

CREATE TABLE IF NOT EXISTS skill_groups (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    name     VARCHAR(80)  NOT NULL,
    icon     VARCHAR(40)  NOT NULL DEFAULT 'code' COMMENT 'Inline icon name from src/helpers.php',
    note     VARCHAR(190) DEFAULT NULL COMMENT 'Small caption under the group name',
    order_no INT          NOT NULL DEFAULT 0,
    UNIQUE KEY uq_skill_groups_name (name),
    KEY idx_skill_groups_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------------- skills ---

CREATE TABLE IF NOT EXISTS skills (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    skill_name  VARCHAR(120) NOT NULL,
    group_id    INT          DEFAULT NULL,
    category    VARCHAR(80)  DEFAULT NULL COMMENT 'Legacy group name, kept in sync with group_id',
    percentage  TINYINT UNSIGNED DEFAULT NULL COMMENT 'Legacy, the site does not render skill percentages',
    description VARCHAR(255) DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0,
    KEY idx_skills_group (group_id, order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------- project_categories ---

CREATE TABLE IF NOT EXISTS project_categories (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    slug     VARCHAR(40)  NOT NULL,
    label    VARCHAR(80)  NOT NULL,
    order_no INT          NOT NULL DEFAULT 0,
    UNIQUE KEY uq_project_categories_slug (slug)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ------------------------------------------------------------- projects ---
-- Full case-study record: everything the project modal renders.

CREATE TABLE IF NOT EXISTS projects (
    id            INT AUTO_INCREMENT PRIMARY KEY,
    title         VARCHAR(190) NOT NULL,
    slug          VARCHAR(190) DEFAULT NULL,
    subtitle      VARCHAR(255) DEFAULT NULL COMMENT 'One-line descriptor under the title',
    category      VARCHAR(60)  NOT NULL DEFAULT 'fullstack',
    image         VARCHAR(255) DEFAULT NULL,
    summary       TEXT         DEFAULT NULL COMMENT 'Card text and modal lead',
    description   TEXT         DEFAULT NULL COMMENT 'Legacy long description',
    details_title VARCHAR(190) DEFAULT NULL,
    problem       TEXT         DEFAULT NULL COMMENT 'What needed solving',
    challenges    TEXT         DEFAULT NULL COMMENT 'Hardest part',
    outcome       TEXT         DEFAULT NULL,
    learned       TEXT         DEFAULT NULL,
    skills_used   VARCHAR(500) DEFAULT NULL COMMENT 'Comma-separated technology list',
    role          VARCHAR(190) DEFAULT NULL,
    year          VARCHAR(16)  DEFAULT NULL,
    view_link     VARCHAR(255) DEFAULT NULL COMMENT 'Live demo URL',
    repo_name     VARCHAR(190) DEFAULT NULL COMMENT 'GitHub repo, merges live API stats',
    featured      TINYINT(1)   NOT NULL DEFAULT 0,
    is_published  TINYINT(1)   NOT NULL DEFAULT 1,
    order_no      INT          NOT NULL DEFAULT 0,
    created_at    DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_projects_order (order_no),
    KEY idx_projects_featured (featured),
    KEY idx_projects_published (is_published)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------- project_features ---
-- The "What it does" bullets inside a case study.

CREATE TABLE IF NOT EXISTS project_features (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    project_id INT          NOT NULL,
    feature    VARCHAR(500) NOT NULL,
    order_no   INT          NOT NULL DEFAULT 0,
    KEY idx_project_features (project_id, order_no),
    CONSTRAINT fk_project_features_project
        FOREIGN KEY (project_id) REFERENCES projects (id) ON DELETE CASCADE
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ------------------------------------------------------------ education ---

CREATE TABLE IF NOT EXISTS education (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    degree      VARCHAR(190) NOT NULL,
    major       VARCHAR(190) DEFAULT NULL COMMENT 'Shown as a badge beside the grade',
    institution VARCHAR(190) NOT NULL,
    location    VARCHAR(160) DEFAULT NULL,
    grade       VARCHAR(80)  DEFAULT NULL,
    start_year  VARCHAR(16)  DEFAULT NULL,
    end_year    VARCHAR(16)  DEFAULT NULL COMMENT 'Blank or "Present" marks it current',
    description TEXT         DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0,
    KEY idx_education_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------------- experience ---

CREATE TABLE IF NOT EXISTS experience (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    title       VARCHAR(190) NOT NULL,
    company     VARCHAR(190) DEFAULT NULL,
    start_date  VARCHAR(50)  DEFAULT NULL,
    end_date    VARCHAR(50)  DEFAULT NULL,
    location    VARCHAR(160) DEFAULT NULL,
    description TEXT         DEFAULT NULL,
    tech_stack  VARCHAR(500) DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0,
    is_current  TINYINT(1)   NOT NULL DEFAULT 0,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_experience_order (is_current, order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------------- activities ---
-- Clubs, societies, competitions, volunteering.

CREATE TABLE IF NOT EXISTS activities (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    role     VARCHAR(120) NOT NULL COMMENT 'Member, Participant, Lead…',
    org      VARCHAR(255) NOT NULL,
    detail   TEXT         DEFAULT NULL,
    icon     VARCHAR(40)  NOT NULL DEFAULT 'award',
    order_no INT          NOT NULL DEFAULT 0,
    KEY idx_activities_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ------------------------------------------------------------- services ---
-- "What I can build for you".

CREATE TABLE IF NOT EXISTS services (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    title    VARCHAR(190) NOT NULL,
    icon     VARCHAR(40)  NOT NULL DEFAULT 'server',
    body     TEXT         DEFAULT NULL,
    order_no INT          NOT NULL DEFAULT 0,
    KEY idx_services_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------- contact_channels ---

CREATE TABLE IF NOT EXISTS contact_channels (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    label    VARCHAR(120) NOT NULL,
    value    VARCHAR(255) NOT NULL,
    href     VARCHAR(255) DEFAULT NULL COMMENT 'Blank renders as plain text, not a link',
    icon     VARCHAR(40)  NOT NULL DEFAULT 'mail',
    order_no INT          NOT NULL DEFAULT 0,
    KEY idx_contact_channels_order (order_no)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------- contact_purposes ---
-- Options in the "What is this about?" dropdown.

CREATE TABLE IF NOT EXISTS contact_purposes (
    id       INT AUTO_INCREMENT PRIMARY KEY,
    label    VARCHAR(120) NOT NULL,
    order_no INT          NOT NULL DEFAULT 0
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------- contact_info ---
-- Legacy table from the original admin; retained so nothing breaks.

CREATE TABLE IF NOT EXISTS contact_info (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    type        VARCHAR(80)  DEFAULT NULL,
    icon        VARCHAR(120) DEFAULT NULL,
    data        VARCHAR(255) DEFAULT NULL,
    input_type  VARCHAR(40)  NOT NULL DEFAULT 'card',
    label       VARCHAR(190) DEFAULT NULL,
    value       VARCHAR(190) DEFAULT NULL,
    description VARCHAR(255) DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0,
    KEY idx_contact_info_type (input_type)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- ----------------------------------------------------- contact_messages ---

CREATE TABLE IF NOT EXISTS contact_messages (
    id         INT AUTO_INCREMENT PRIMARY KEY,
    name       VARCHAR(160) NOT NULL,
    email      VARCHAR(190) NOT NULL,
    subject    VARCHAR(255) NOT NULL,
    message    TEXT         NOT NULL,
    purpose    VARCHAR(120) DEFAULT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    KEY idx_messages_created (created_at),
    KEY idx_messages_unread (is_read)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------------- footer ---

CREATE TABLE IF NOT EXISTS footer (
    id          INT AUTO_INCREMENT PRIMARY KEY,
    social_name VARCHAR(80)  DEFAULT NULL,
    social_icon VARCHAR(120) DEFAULT NULL,
    social_link VARCHAR(255) DEFAULT NULL,
    footer_text VARCHAR(255) DEFAULT NULL,
    order_no    INT          NOT NULL DEFAULT 0
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;


-- --------------------------------------------------------- current_work ---

CREATE TABLE IF NOT EXISTS current_work (
    id           INT AUTO_INCREMENT PRIMARY KEY,
    title        VARCHAR(190) NOT NULL,
    type         VARCHAR(80)  NOT NULL DEFAULT 'Project',
    description  TEXT         DEFAULT NULL,
    technologies VARCHAR(500) DEFAULT NULL,
    status       VARCHAR(80)  NOT NULL DEFAULT 'Active',
    progress     TINYINT UNSIGNED NOT NULL DEFAULT 50,
    order_no     INT          NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
