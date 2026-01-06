BEGIN;

-- =========================================================
-- 12 clases (base 5e 2014) - inserts nivel 1
-- Tablas: dnd_class, dnd_class_proficiency, dnd_class_skill_choice,
--         dnd_class_skill_choice_option, dnd_class_starting_equipment,
--         dnd_class_feature, dnd_class_spellcasting_l1
-- =========================================================

-- =========================
-- BÁRBARO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Bárbaro','Un guerrero feroz que canaliza su furia para resistir y golpear con brutalidad.','1d12','12 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('escudos')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Fuerza'),('Constitución')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Atletismo'),('Intimidación'),('Naturaleza'),('Percepción'),('Supervivencia'),('Manejo de Animales')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('una gran hacha'),('cualquier arma marcial cuerpo a cuerpo')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('dos hachas de mano'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('equipo de explorador'),('cuatro jabalinas')
  ) x(v)
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Ira', 'bonus', '2', 'descanso largo',
       'Furia temporal: daño extra y resistencia a daño contundente/cortante/perforante (según reglas).'
FROM c
UNION ALL
SELECT id, 1, 'Defensa sin armadura', 'pasivo', '0', 'ninguna',
       'Sin armadura: CA = 10 + mod DEX + mod CON (puedes usar escudo).'
FROM c;

-- =========================
-- BARDO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Bardo','Un artista mágico que inspira y lanza conjuros a través de su interpretación.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'armadura ligera' FROM c
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('ballesta de mano'),('espada larga'),('estoque'),('espada corta')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'tres instrumentos musicales (a elegir)' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Destreza'),('Carisma')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 3 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Acrobacias'),('Atletismo'),('Engaño'),('Historia'),('Interpretación'),('Intimidación'),
    ('Investigación'),('Juego de Manos'),('Medicina'),('Naturaleza'),('Percepción'),('Perspicacia'),
    ('Persuasión'),('Religión'),('Sigilo'),('Supervivencia'),('Trato con Animales')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('un estoque'),('una espada larga'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('paquete de diplomático'),('paquete de artista')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('un laúd'),('cualquier instrumento musical')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('armadura de cuero'),('una daga')
  ) x(v)
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Carisma', 2, 2, 4, NULL, '' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Lanzamiento de conjuros', 'pasivo', '0', 'ninguna', 'Lanza conjuros de bardo (CAR).' FROM c
UNION ALL
SELECT id, 1, 'Inspiración bárdica', 'bonus', 'modificador de Carisma (mín. 1)', 'descanso largo',
       'Otorgas un dado d6 de inspiración a un aliado (según reglas).'
FROM c;

-- =========================
-- CLÉRIGO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Clérigo','Un servidor de lo divino que cura, protege y castiga con poder sagrado.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('escudos')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', 'armas simples' FROM c
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Sabiduría'),('Carisma')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Historia'),('Perspicacia'),('Medicina'),('Persuasión'),('Religión')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('una maza'),('un martillo de guerra (si es competente)')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('cota de malla'),('armadura de cuero'),('cota de escamas')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('ballesta ligera y 20 virotes'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op4', v FROM c, (VALUES
    ('paquete de sacerdote'),('paquete de explorador')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('un escudo'),('un símbolo sagrado')
  ) x(v)
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Sabiduría', 3, 2, NULL, 'nivel (1) + mod SAB', '' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Lanzamiento de conjuros', 'pasivo', '0', 'ninguna', 'Conjuros de clérigo (SAB), preparados.' FROM c
UNION ALL
SELECT id, 1, 'Dominio divino', 'eleccion', '0', 'ninguna',
       'Eliges un dominio en nivel 1; concede rasgos y conjuros de dominio (según dominio).'
FROM c;

-- =========================
-- DRUIDA
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Druida','Un guardián de la naturaleza que lanza conjuros primordiales y domina saberes antiguos.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('escudos (no de metal)')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('clava'),('daga'),('dardo'),('jabalina'),('maza'),('bastón'),
    ('cimitarra'),('hoz'),('honda'),('lanza')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'kit de herboristería' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Inteligencia'),('Sabiduría')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Arcanos'),('Trato con Animales'),('Perspicacia'),('Medicina'),
    ('Naturaleza'),('Percepción'),('Religión'),('Supervivencia')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('un escudo de madera'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('una cimitarra'),('cualquier arma cuerpo a cuerpo simple')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('armadura de cuero'),('paquete de explorador'),('un foco druídico')
  ) x(v)
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Sabiduría', 2, 2, NULL, 'nivel (1) + mod SAB', '' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Druídico', 'pasivo', '0', 'ninguna', 'Conoces el idioma secreto druídico.' FROM c
UNION ALL
SELECT id, 1, 'Lanzamiento de conjuros', 'pasivo', '0', 'ninguna', 'Conjuros de druida (SAB), preparados.' FROM c;

-- =========================
-- GUERRERO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Guerrero','Un combatiente entrenado en armas y armaduras, adaptable a muchos estilos.','1d10','10 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('armadura pesada'),('escudos')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Fuerza'),('Constitución')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Acrobacias'),('Manejo de Animales'),('Atletismo'),('Historia'),
    ('Perspicacia'),('Intimidación'),('Percepción'),('Supervivencia')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('cota de malla'),('armadura de cuero y arco largo con 20 flechas')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('un arma marcial y un escudo'),('dos armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('una ballesta ligera y 20 virotes'),('dos hachas de mano')
  ) x(v)
  UNION ALL
  SELECT id, 'op4', v FROM c, (VALUES
    ('paquete de explorador'),('paquete de aventurero')
  ) x(v)
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Estilo de combate', 'eleccion', '0', 'ninguna',
       'Eliges un estilo de combate (p. ej. Defensa, Duelos, Gran arma, etc.).'
FROM c
UNION ALL
SELECT id, 1, 'Segundo aliento', 'bonus', '1', 'descanso corto o largo',
       'Te curas 1d10 + nivel de guerrero (1).'
FROM c;

-- =========================
-- MONJE
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Monje','Un artista marcial disciplinado que combate sin armadura usando ki y técnica.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('espadas cortas')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'un tipo de herramienta de artesano o un instrumento musical (a elegir)' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Fuerza'),('Destreza')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Acrobacias'),('Atletismo'),('Historia'),('Perspicacia'),('Religión'),('Sigilo')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('una espada corta'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('paquete de explorador'),('paquete de dungeoneer')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', '10 dardos' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Defensa sin armadura', 'pasivo', '0', 'ninguna',
       'Sin armadura ni escudo: CA = 10 + mod DEX + mod SAB.'
FROM c
UNION ALL
SELECT id, 1, 'Artes marciales', 'pasivo', '0', 'ninguna',
       'Golpes desarmados/armas de monje con DEX y daño base (según nivel); permite ataque extra con bonus en ciertas condiciones.'
FROM c;

-- =========================
-- PALADÍN
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Paladín','Un campeón sagrado, juramentado, que combina marcialidad y dones de curación.','1d10','10 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('armadura pesada'),('escudos')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Sabiduría'),('Carisma')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Atletismo'),('Perspicacia'),('Intimidación'),('Medicina'),('Persuasión'),('Religión')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('un arma marcial y un escudo'),('dos armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('cinco jabalinas'),('cualquier arma simple cuerpo a cuerpo')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de sacerdote'),('paquete de explorador')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('cota de malla'),('un símbolo sagrado')
  ) x(v)
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Sentido divino', 'accion', '1 + mod CAR', 'descanso largo',
       'Detectas celestiales, infernales y no muertos cercanos (según reglas).'
FROM c
UNION ALL
SELECT id, 1, 'Imposición de manos', 'accion', 'reserva 5 PG', 'descanso largo',
       'Reservorio de curación: 5 puntos por nivel de paladín (5).'
FROM c;

-- =========================
-- EXPLORADOR
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Explorador','Un cazador y rastreador experto, especializado en sobrevivir y combatir en lo salvaje.','1d10','10 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', v FROM c, (VALUES
    ('armadura ligera'),('armadura media'),('escudos')
  ) x(v)
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('armas marciales')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Fuerza'),('Destreza')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 3 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Trato con Animales'),('Atletismo'),('Perspicacia'),('Investigación'),
    ('Naturaleza'),('Percepción'),('Sigilo'),('Supervivencia')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('cota de escamas'),('armadura de cuero')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('dos espadas cortas'),('dos armas cuerpo a cuerpo simples')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de dungeoneer'),('paquete de explorador')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('un arco largo'),('un carcaj con 20 flechas')
  ) x(v)
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Enemigo predilecto', 'eleccion', '0', 'ninguna',
       'Eliges tipo(s) de enemigo; obtienes ventajas de rastreo/conocimiento (según reglas).'
FROM c
UNION ALL
SELECT id, 1, 'Explorador nato', 'eleccion', '0', 'ninguna',
       'Eliges un terreno predilecto; mejoras de viaje y exploración (según reglas).'
FROM c;

-- =========================
-- PÍCARO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Pícaro','Un experto en habilidades, sigilo y golpes precisos cuando el enemigo está distraído.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'armadura ligera' FROM c
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('armas simples'),('ballesta de mano'),('espada larga'),('estoque'),('espada corta')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'herramientas de ladrón' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Destreza'),('Inteligencia')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 4 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Acrobacias'),('Atletismo'),('Engaño'),('Interpretación'),('Intimidación'),
    ('Investigación'),('Juego de Manos'),('Percepción'),('Perspicacia'),
    ('Persuasión'),('Sigilo'),('Supervivencia'),('Trato con Animales')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('un estoque'),('una espada corta')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('un arco corto y un carcaj con 20 flechas'),('una espada corta')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de dungeoneer'),('paquete de explorador'),('paquete de ladrón')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('armadura de cuero'),('dos dagas'),('herramientas de ladrón')
  ) x(v)
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Pericia', 'eleccion', '0', 'ninguna',
       'Eliges 2 competencias (habilidades y/o herramientas de ladrón) para doblar tu bonificador.'
FROM c
UNION ALL
SELECT id, 1, 'Ataque furtivo', 'pasivo', '0', 'ninguna',
       '1d6 extra una vez por turno si cumples condiciones (ventaja o aliado adyacente, etc.).'
FROM c
UNION ALL
SELECT id, 1, 'Jerga de los ladrones', 'pasivo', '0', 'ninguna',
       'Conoces el argot secreto de los ladrones.'
FROM c;

-- =========================
-- HECHICERO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Hechicero','Un lanzador innato cuyo poder mágico proviene de su linaje o una fuente interna.','1d6','6 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('daga'),('dardo'),('honda'),('bastón'),('ballesta ligera')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Constitución'),('Carisma')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Arcanos'),('Engaño'),('Perspicacia'),('Intimidación'),('Persuasión'),('Religión')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('una ballesta ligera y 20 virotes'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('una bolsa de componentes'),('un foco arcano')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de dungeoneer'),('paquete de explorador')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', 'dos dagas' FROM c
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Carisma', 4, 2, 2, NULL, '' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Lanzamiento de conjuros', 'pasivo', '0', 'ninguna', 'Conjuros de hechicero (CAR), conocidos.' FROM c
UNION ALL
SELECT id, 1, 'Origen hechicero', 'eleccion', '0', 'ninguna',
       'Eliges un origen en nivel 1; concede rasgos (según origen).'
FROM c;

-- =========================
-- BRUJO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Brujo','Un pactante que obtiene magia a cambio de servir o negociar con un patrón sobrenatural.','1d8','8 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'armadura ligera' FROM c
  UNION ALL
  SELECT id, 'arma', 'armas simples' FROM c
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Sabiduría'),('Carisma')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Arcanos'),('Engaño'),('Historia'),('Intimidación'),('Investigación'),('Naturaleza'),('Religión')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('una ballesta ligera y 20 virotes'),('cualquier arma simple')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('una bolsa de componentes'),('un foco arcano')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de erudito'),('paquete de dungeoneer')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', v FROM c, (VALUES
    ('armadura de cuero'),('cualquier arma simple'),('dos dagas')
  ) x(v)
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Carisma', 2, 1, 2, NULL, 'Magia de pacto: el espacio se recupera con descanso corto o largo.' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Patrón sobrenatural', 'eleccion', '0', 'ninguna',
       'Eliges un patrón en nivel 1; concede rasgos (según patrón).'
FROM c
UNION ALL
SELECT id, 1, 'Magia de pacto', 'pasivo', '0', 'ninguna',
       'Conjuros de brujo (CAR) con pocos espacios que recargan rápido.'
FROM c;

-- =========================
-- MAGO
-- =========================
WITH c AS (
  INSERT INTO dnd_class (nombre, descripcion, dado_golpe, pg_nivel_1_regla)
  VALUES ('Mago','Un estudioso de lo arcano que lanza conjuros mediante libros y preparación rigurosa.','1d6','6 + modificador de Constitución')
  RETURNING id
),
p AS (
  INSERT INTO dnd_class_proficiency (class_id, tipo, valor)
  SELECT id, 'armadura', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'arma', v FROM c, (VALUES
    ('daga'),('dardo'),('honda'),('bastón'),('ballesta ligera')
  ) x(v)
  UNION ALL
  SELECT id, 'herramienta', 'ninguna' FROM c
  UNION ALL
  SELECT id, 'salvacion', v FROM c, (VALUES
    ('Inteligencia'),('Sabiduría')
  ) x(v)
  RETURNING 1
),
sc AS (
  INSERT INTO dnd_class_skill_choice (class_id, elige)
  SELECT id, 2 FROM c
  RETURNING class_id
),
sco AS (
  INSERT INTO dnd_class_skill_choice_option (class_id, habilidad)
  SELECT id, v FROM c, (VALUES
    ('Arcanos'),('Historia'),('Perspicacia'),('Investigación'),('Medicina'),('Religión')
  ) x(v)
  RETURNING 1
),
eq AS (
  INSERT INTO dnd_class_starting_equipment (class_id, grupo, item)
  SELECT id, 'op1', v FROM c, (VALUES
    ('un bastón'),('una daga')
  ) x(v)
  UNION ALL
  SELECT id, 'op2', v FROM c, (VALUES
    ('una bolsa de componentes'),('un foco arcano')
  ) x(v)
  UNION ALL
  SELECT id, 'op3', v FROM c, (VALUES
    ('paquete de erudito'),('paquete de dungeoneer')
  ) x(v)
  UNION ALL
  SELECT id, 'fijo', 'un libro de conjuros' FROM c
  RETURNING 1
),
sp AS (
  INSERT INTO dnd_class_spellcasting_l1
    (class_id, atributo, trucos_conocidos, espacios_nivel_1, conjuros_nivel_1_conocidos, conjuros_preparados_regla, notas)
  SELECT id, 'Inteligencia', 3, 2, NULL, 'nivel (1) + mod INT', 'Conjuros en libro: 6 en nivel 1.' FROM c
  RETURNING 1
)
INSERT INTO dnd_class_feature (class_id, nivel, nombre, tipo, usos, recarga, descripcion)
SELECT id, 1, 'Lanzamiento de conjuros', 'pasivo', '0', 'ninguna',
       'Conjuros de mago (INT), preparados desde el libro.'
FROM c
UNION ALL
SELECT id, 1, 'Recuperación arcana', 'recurso', '1', 'descanso largo',
       'Tras descanso corto: recuperas espacios con total de niveles hasta la mitad de tu nivel de mago (mín. 1).'
FROM c;

COMMIT;
