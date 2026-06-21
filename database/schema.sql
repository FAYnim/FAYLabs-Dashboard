-- ============================================================
-- FAY Portfolio CMS — Database Schema
-- ============================================================

CREATE DATABASE IF NOT EXISTS `faylabs_dashboard`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `faylabs_dashboard`;

-- ------------------------------------------------------------
-- Table: admins
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `admins` (
    `id`            INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `username`      VARCHAR(100)        NOT NULL,
    `password_hash` VARCHAR(255)        NOT NULL,
    `created_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`    TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_admins_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: projects
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `projects` (
    `id`              INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `title`           VARCHAR(150)        NOT NULL,
    `slug`            VARCHAR(180)        NOT NULL,
    `description`     VARCHAR(300)        NOT NULL,
    `cover_image`     VARCHAR(500)        NOT NULL,
    `cover_public_id` VARCHAR(255)        NULL DEFAULT NULL,
    `label`           ENUM('AI','Web App','SaaS','IoT','Mobile','Other') NOT NULL DEFAULT 'Other',
    `content`         LONGTEXT            NOT NULL,
    `tech_stack`      JSON                NOT NULL,
    `github_url`      VARCHAR(500)        NULL DEFAULT NULL,
    `demo_url`        VARCHAR(500)        NULL DEFAULT NULL,
    `project_year`    YEAR                NOT NULL,
    `status`          ENUM('draft','published') NOT NULL DEFAULT 'draft',
    `seo_title`       VARCHAR(180)        NULL DEFAULT NULL,
    `seo_description` VARCHAR(300)        NULL DEFAULT NULL,
    `views`           INT UNSIGNED        NOT NULL DEFAULT 0,
    `created_at`      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP           NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    `published_at`    TIMESTAMP           NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `uk_projects_slug` (`slug`),
    KEY `idx_projects_status` (`status`),
    KEY `idx_projects_published_at` (`published_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ------------------------------------------------------------
-- Table: emails
-- ------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `emails` (
    `id`              INT UNSIGNED                 NOT NULL AUTO_INCREMENT,
    `sender_name`     VARCHAR(150)                 NULL DEFAULT NULL,
    `sender_email`    VARCHAR(255)                 NOT NULL,
    `recipient_email` VARCHAR(255)                 NOT NULL,
    `subject`         VARCHAR(255)                 NOT NULL,
    `body`            LONGTEXT                     NOT NULL,
    `direction`       ENUM('incoming','outgoing')  NOT NULL DEFAULT 'incoming',
    `is_read`         TINYINT(1) UNSIGNED          NOT NULL DEFAULT 0,
    `sent_at`         DATETIME                     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `created_at`      TIMESTAMP                    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at`      TIMESTAMP                    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `idx_emails_direction_sent_at` (`direction`, `sent_at`),
    KEY `idx_emails_is_read` (`is_read`),
    KEY `idx_emails_sender_email` (`sender_email`),
    KEY `idx_emails_recipient_email` (`recipient_email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
