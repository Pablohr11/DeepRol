-- DeepRol: partidas multijugador en tiempo real.
-- Compatible con MariaDB/MySQL y seguro para volver a ejecutar.

CREATE TABLE IF NOT EXISTS games (
    id_game INT UNSIGNED NOT NULL AUTO_INCREMENT,
    owner_user_id INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    description VARCHAR(1000) NOT NULL DEFAULT '',
    invite_code CHAR(6) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'active',
    state_version INT UNSIGNED NOT NULL DEFAULT 0,
    current_encounter_id INT UNSIGNED NULL,
    settings_json LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_game),
    UNIQUE KEY uq_games_invite_code (invite_code),
    KEY idx_games_owner (owner_user_id),
    KEY idx_games_status (status),
    CONSTRAINT fk_games_owner
        FOREIGN KEY (owner_user_id) REFERENCES usuario (ID_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_members (
    id_game_member INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_game INT UNSIGNED NOT NULL,
    id_user INT NOT NULL,
    role VARCHAR(16) NOT NULL DEFAULT 'player',
    id_char INT NULL,
    display_name VARCHAR(100) NOT NULL DEFAULT '',
    joined_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    last_seen_at DATETIME NULL,
    PRIMARY KEY (id_game_member),
    UNIQUE KEY uq_game_members_user (id_game, id_user),
    UNIQUE KEY uq_game_members_character (id_game, id_char),
    KEY idx_game_members_user (id_user),
    KEY idx_game_members_character (id_char),
    CONSTRAINT fk_game_members_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_members_user
        FOREIGN KEY (id_user) REFERENCES usuario (ID_usuario)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_members_character
        FOREIGN KEY (id_char) REFERENCES chars (id_char)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_custom_spells (
    id_game_spell INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_game INT UNSIGNED NOT NULL,
    created_by INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    description TEXT NOT NULL,
    spell_level TINYINT UNSIGNED NOT NULL DEFAULT 0,
    school VARCHAR(40) NOT NULL DEFAULT '',
    casting_time VARCHAR(80) NOT NULL DEFAULT '',
    range_text VARCHAR(80) NOT NULL DEFAULT '',
    duration VARCHAR(100) NOT NULL DEFAULT '',
    concentration TINYINT(1) NOT NULL DEFAULT 0,
    tags_json LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_game_spell),
    KEY idx_game_custom_spells_game (id_game),
    CONSTRAINT fk_game_custom_spells_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_custom_spells_user
        FOREIGN KEY (created_by) REFERENCES usuario (ID_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_npcs (
    id_game_npc INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_game INT UNSIGNED NOT NULL,
    created_by INT NOT NULL,
    name VARCHAR(120) NOT NULL,
    source_type VARCHAR(20) NOT NULL DEFAULT 'custom',
    source_key VARCHAR(120) NULL,
    armor_class SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    max_hp INT UNSIGNED NOT NULL DEFAULT 1,
    current_hp INT UNSIGNED NOT NULL DEFAULT 1,
    initiative_modifier SMALLINT NOT NULL DEFAULT 0,
    notes TEXT NOT NULL,
    metadata_json LONGTEXT NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_game_npc),
    KEY idx_game_npcs_game (id_game),
    CONSTRAINT fk_game_npcs_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_npcs_user
        FOREIGN KEY (created_by) REFERENCES usuario (ID_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_encounters (
    id_encounter INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_game INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'setup',
    round_number INT UNSIGNED NOT NULL DEFAULT 1,
    current_turn_index INT UNSIGNED NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_encounter),
    KEY idx_game_encounters_game (id_game, status),
    CONSTRAINT fk_game_encounters_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_combatants (
    id_combatant INT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_encounter INT UNSIGNED NOT NULL,
    entity_type VARCHAR(20) NOT NULL,
    entity_id INT NULL,
    owner_user_id INT NULL,
    name VARCHAR(120) NOT NULL,
    armor_class SMALLINT UNSIGNED NOT NULL DEFAULT 10,
    max_hp INT UNSIGNED NOT NULL DEFAULT 1,
    current_hp INT UNSIGNED NOT NULL DEFAULT 1,
    temp_hp INT UNSIGNED NOT NULL DEFAULT 0,
    initiative DECIMAL(6,2) NULL,
    initiative_modifier SMALLINT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    concentrating_on VARCHAR(160) NOT NULL DEFAULT '',
    conditions_json LONGTEXT NOT NULL,
    resources_json LONGTEXT NOT NULL,
    metadata_json LONGTEXT NOT NULL,
    position_json LONGTEXT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (id_combatant),
    KEY idx_game_combatants_encounter (id_encounter, is_active, initiative),
    KEY idx_game_combatants_owner (owner_user_id),
    CONSTRAINT fk_game_combatants_encounter
        FOREIGN KEY (id_encounter) REFERENCES game_encounters (id_encounter)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_combatants_owner
        FOREIGN KEY (owner_user_id) REFERENCES usuario (ID_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_events (
    id_event BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    id_game INT UNSIGNED NOT NULL,
    id_encounter INT UNSIGNED NULL,
    actor_user_id INT NULL,
    event_type VARCHAR(80) NOT NULL,
    payload_json LONGTEXT NOT NULL,
    state_version INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id_event),
    KEY idx_game_events_stream (id_game, id_event),
    KEY idx_game_events_encounter (id_encounter, id_event),
    CONSTRAINT fk_game_events_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_events_encounter
        FOREIGN KEY (id_encounter) REFERENCES game_encounters (id_encounter)
        ON DELETE SET NULL,
    CONSTRAINT fk_game_events_actor
        FOREIGN KEY (actor_user_id) REFERENCES usuario (ID_usuario)
        ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS game_socket_tokens (
    token_hash CHAR(64) NOT NULL,
    id_game INT UNSIGNED NOT NULL,
    id_user INT NOT NULL,
    expires_at DATETIME NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (token_hash),
    KEY idx_game_socket_tokens_expiry (expires_at),
    KEY idx_game_socket_tokens_identity (id_game, id_user),
    CONSTRAINT fk_game_socket_tokens_game
        FOREIGN KEY (id_game) REFERENCES games (id_game)
        ON DELETE CASCADE,
    CONSTRAINT fk_game_socket_tokens_user
        FOREIGN KEY (id_user) REFERENCES usuario (ID_usuario)
        ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
