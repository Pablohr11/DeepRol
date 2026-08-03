ALTER TABLE `chars`
    ADD COLUMN IF NOT EXISTS `nivel` INT(2) NOT NULL DEFAULT 1 AFTER `raza`,
    ADD COLUMN IF NOT EXISTS `clase` VARCHAR(255) NOT NULL DEFAULT '' AFTER `nivel`;

UPDATE `chars` SET `clase` = 'Druida', `nivel` = 7 WHERE `name` = 'Draelith' AND `clase` = '';
UPDATE `chars` SET `clase` = 'Brujo', `nivel` = 2 WHERE `name` = 'Prythos' AND `clase` = '';
UPDATE `chars` SET `clase` = 'Druida', `nivel` = 5 WHERE `name` = 'JoseJu' AND `clase` = '';
UPDATE `chars` SET `clase` = 'Asesino', `nivel` = 3 WHERE `name` = 'Ren' AND `clase` = '';
