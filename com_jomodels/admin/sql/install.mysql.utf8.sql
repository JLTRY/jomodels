SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

CREATE TABLE IF NOT EXISTS `#__jomodels_modl` (
    `id` INT(11) NOT NULL AUTO_INCREMENT,
    `asset_id` INT(10) unsigned NULL DEFAULT 0 COMMENT 'FK to the #__assets table.',
    `alias` CHAR(64) NULL DEFAULT '',
    `text` VARCHAR(2048) NULL DEFAULT '',
    `type` CHAR(1) NULL DEFAULT '',
    `params` TEXT NULL,
    `published` TINYINT(3) NULL DEFAULT 1,
    `created_by` INT unsigned NULL,
    `modified_by` INT unsigned,
    `created` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `modified` DATETIME,
    `checked_out` int unsigned,
    `checked_out_time` DATETIME,
    `version` INT(10) unsigned NULL DEFAULT 1,
    `hits` INT(10) unsigned NULL DEFAULT 0,
    `access` INT(10) unsigned NULL DEFAULT 0,
    `ordering` INT(11) NULL DEFAULT 0,
    PRIMARY KEY  (`id`),
    KEY `idx_alias` (`alias`),
    KEY `idx_access` (`access`),
    KEY `idx_checkout` (`checked_out`),
    KEY `idx_createdby` (`created_by`),
    KEY `idx_modifiedby` (`modified_by`),
    KEY `idx_state` (`published`)
) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8mb4 DEFAULT COLLATE=utf8mb4_unicode_ci ROW_FORMAT=DYNAMIC;


