ALTER TABLE `chars`
    ADD COLUMN IF NOT EXISTS `subraza` VARCHAR(255) NOT NULL DEFAULT '' AFTER `raza`,
    ADD COLUMN IF NOT EXISTS `subclase` VARCHAR(255) NOT NULL DEFAULT '' AFTER `clase`;

UPDATE `chars`
SET `raza` = 'Elfo', `subraza` = 'Elfo de los bosques'
WHERE `raza` = 'Elfo de los bosques' AND `subraza` = '';

UPDATE `chars`
SET `raza` = 'Tiefling'
WHERE `raza` = 'Tieflin';

UPDATE `chars`
SET `clase` = 'Picaro', `subclase` = 'Asesino'
WHERE `clase` = 'Asesino' AND `subclase` = '';

UPDATE `razas`
SET `Nombre` = 'Draconido'
WHERE `id` = 1 AND `Nombre` <> 'Draconido';

INSERT INTO `clases` (`Nombre`, `short_desc`, `descr`, `rasgos_clase`, `image_path`)
SELECT
    'Artifice',
    'Inventor mágico que canaliza conjuros mediante herramientas y objetos.',
    'El artífice combina conocimiento arcano, artesanía e infusiones para apoyar al grupo y modificar su equipo.',
    '',
    ''
WHERE NOT EXISTS (
    SELECT 1 FROM `clases` WHERE `Nombre` = 'Artifice'
);
