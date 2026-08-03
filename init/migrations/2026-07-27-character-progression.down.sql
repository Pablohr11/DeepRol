-- Reversión opcional. Conserva primero una copia si ya hay progresiones
-- multiclase o idiomas creados con la nueva versión.
DROP TABLE IF EXISTS `character_languages`;
DROP TABLE IF EXISTS `character_class_levels`;
