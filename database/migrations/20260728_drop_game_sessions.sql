-- Reversión manual del módulo de partidas.
-- No se ejecuta automáticamente porque elimina todo el historial de partidas.
DROP TABLE IF EXISTS game_socket_tokens;
DROP TABLE IF EXISTS game_events;
DROP TABLE IF EXISTS game_combatants;
DROP TABLE IF EXISTS game_encounters;
DROP TABLE IF EXISTS game_npcs;
DROP TABLE IF EXISTS game_custom_spells;
DROP TABLE IF EXISTS game_members;
DROP TABLE IF EXISTS games;
