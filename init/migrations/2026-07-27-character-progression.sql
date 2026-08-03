CREATE TABLE IF NOT EXISTS `character_class_levels` (
    `id_character_class` INT NOT NULL AUTO_INCREMENT,
    `id_char` INT NOT NULL,
    `class_name` VARCHAR(80) NOT NULL,
    `subclass_name` VARCHAR(160) NOT NULL DEFAULT '',
    `class_level` TINYINT UNSIGNED NOT NULL,
    `is_primary` TINYINT(1) NOT NULL DEFAULT 0,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_character_class`),
    UNIQUE KEY `uq_character_class` (`id_char`, `class_name`),
    KEY `idx_character_class_char` (`id_char`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `character_languages` (
    `id_character_language` INT NOT NULL AUTO_INCREMENT,
    `id_char` INT NOT NULL,
    `language_name` VARCHAR(80) NOT NULL,
    `sort_order` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    PRIMARY KEY (`id_character_language`),
    UNIQUE KEY `uq_character_language` (`id_char`, `language_name`),
    KEY `idx_character_language_char` (`id_char`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `character_class_levels` (
    `id_char`,
    `class_name`,
    `subclass_name`,
    `class_level`,
    `is_primary`,
    `sort_order`
)
SELECT
    `id_char`,
    `clase`,
    COALESCE(`subclase`, ''),
    LEAST(20, GREATEST(1, COALESCE(`nivel`, 1))),
    1,
    0
FROM `chars`
WHERE COALESCE(`clase`, '') <> ''
AND NOT EXISTS (
    SELECT 1
    FROM `character_class_levels`
    WHERE `character_class_levels`.`id_char` = `chars`.`id_char`
);
