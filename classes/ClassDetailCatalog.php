<?php

final class ClassDetailCatalog
{
    private static $subclassDetails = null;

    private const PROFILES = [
        "Artifice" => [
            "role" => "Especialista versátil que combina magia, herramientas e infusiones.",
            "primary" => "Inteligencia",
            "saves" => "Constitución e Inteligencia",
            "armor" => "Armadura ligera, armadura media y escudos",
            "weapons" => "Armas simples",
            "skills" => "Elige dos entre Arcano, Historia, Investigación, Medicina, Naturaleza, Percepción y Juego de Manos.",
            "hit_die" => "d8",
            "magic" => "Medio lanzador",
            "features" => [
                1 => "Retoques mágicos · Lanzamiento de conjuros",
                2 => "Infundir objeto",
                3 => "Especialidad de artífice · La herramienta adecuada",
                4 => "Mejora de característica",
                5 => "Rasgo de especialista",
                6 => "Pericia con herramientas",
                7 => "Destello de genio",
                8 => "Mejora de característica",
                9 => "Rasgo de especialista",
                10 => "Adepto de objetos mágicos",
                11 => "Objeto contenedor de conjuros",
                12 => "Mejora de característica",
                14 => "Sabio de objetos mágicos",
                15 => "Rasgo de especialista",
                16 => "Mejora de característica",
                18 => "Maestro de objetos mágicos",
                19 => "Mejora de característica",
                20 => "Alma de artífice",
            ],
            "core" => [
                "Infundir objeto" => "Convierte objetos mundanos en prototipos mágicos y reparte esas mejoras entre el grupo.",
                "Destello de genio" => "Usa su reacción e Inteligencia para reforzar una prueba o salvación cercana.",
                "Objeto contenedor de conjuros" => "Almacena un conjuro de nivel bajo en un objeto para que otra criatura pueda activarlo.",
            ],
        ],
        "Barbaro" => [
            "role" => "Combatiente resistente que transforma la furia en daño y supervivencia.",
            "primary" => "Fuerza y Constitución",
            "saves" => "Fuerza y Constitución",
            "armor" => "Armadura ligera, armadura media y escudos",
            "weapons" => "Armas simples y marciales",
            "skills" => "Elige dos entre Atletismo, Intimidación, Naturaleza, Percepción, Supervivencia y Trato con Animales.",
            "hit_die" => "d12",
            "magic" => "Marcial",
            "features" => [
                1 => "Furia · Defensa sin armadura",
                2 => "Ataque temerario · Sentido del peligro",
                3 => "Senda primigenia",
                4 => "Mejora de característica",
                5 => "Ataque adicional · Movimiento rápido",
                6 => "Rasgo de senda",
                7 => "Instinto salvaje",
                8 => "Mejora de característica",
                9 => "Crítico brutal (1 dado)",
                10 => "Rasgo de senda",
                11 => "Furia implacable",
                12 => "Mejora de característica",
                13 => "Crítico brutal (2 dados)",
                14 => "Rasgo de senda",
                15 => "Furia persistente",
                16 => "Mejora de característica",
                17 => "Crítico brutal (3 dados)",
                18 => "Fuerza indómita",
                19 => "Mejora de característica",
                20 => "Campeón primigenio",
            ],
            "core" => [
                "Furia" => "Otorga daño adicional con Fuerza y resistencia al daño físico habitual, pero impide lanzar o mantener conjuros.",
                "Ataque temerario" => "Permite atacar con ventaja a cambio de conceder ventaja a los ataques recibidos hasta el siguiente turno.",
                "Furia implacable" => "Ofrece una salvación creciente para permanecer a 1 punto de golpe cuando la vida cae a cero.",
            ],
        ],
        "Bardo" => [
            "role" => "Apoyo, experto social y lanzador capaz de cubrir cualquier carencia del grupo.",
            "primary" => "Carisma",
            "saves" => "Destreza y Carisma",
            "armor" => "Armadura ligera",
            "weapons" => "Armas simples, ballestas de mano, espadas largas, estoques y espadas cortas",
            "skills" => "Elige tres habilidades cualesquiera.",
            "hit_die" => "d8",
            "magic" => "Lanzador completo",
            "features" => [
                1 => "Lanzamiento de conjuros · Inspiración de bardo (d6)",
                2 => "Aprendiz de mucho · Canción de descanso (d6)",
                3 => "Colegio de bardos · Pericia",
                4 => "Mejora de característica",
                5 => "Inspiración (d8) · Fuente de inspiración",
                6 => "Contraencantamiento · Rasgo de colegio",
                8 => "Mejora de característica",
                9 => "Canción de descanso (d8)",
                10 => "Inspiración (d10) · Pericia · Secretos mágicos",
                12 => "Mejora de característica",
                13 => "Canción de descanso (d10)",
                14 => "Secretos mágicos · Rasgo de colegio",
                15 => "Inspiración (d12)",
                16 => "Mejora de característica",
                17 => "Canción de descanso (d12)",
                18 => "Secretos mágicos",
                19 => "Mejora de característica",
                20 => "Inspiración superior",
            ],
            "core" => [
                "Inspiración de bardo" => "Entrega a un aliado un dado que puede sumar a una prueba, ataque o salvación.",
                "Aprendiz de mucho" => "Añade la mitad de la competencia a las pruebas que todavía no la incluyen.",
                "Secretos mágicos" => "Permite aprender conjuros de cualquier lista y convierte al bardo en un lanzador muy adaptable.",
            ],
        ],
        "Brujo" => [
            "role" => "Lanzador de recursos cortos ligado a un patrón y definido por invocaciones.",
            "primary" => "Carisma",
            "saves" => "Sabiduría y Carisma",
            "armor" => "Armadura ligera",
            "weapons" => "Armas simples",
            "skills" => "Elige dos entre Arcano, Engaño, Historia, Intimidación, Investigación, Naturaleza y Religión.",
            "hit_die" => "d8",
            "magic" => "Magia de pacto",
            "features" => [
                1 => "Patrón sobrenatural · Magia de pacto",
                2 => "Invocaciones sobrenaturales",
                3 => "Don del pacto",
                4 => "Mejora de característica",
                6 => "Rasgo de patrón",
                8 => "Mejora de característica",
                10 => "Rasgo de patrón",
                11 => "Arcanum místico (nivel 6)",
                12 => "Mejora de característica",
                13 => "Arcanum místico (nivel 7)",
                14 => "Rasgo de patrón",
                15 => "Arcanum místico (nivel 8)",
                16 => "Mejora de característica",
                17 => "Arcanum místico (nivel 9)",
                19 => "Mejora de característica",
                20 => "Maestro del misterio",
            ],
            "core" => [
                "Magia de pacto" => "Usa pocos espacios, siempre del nivel más alto disponible, que regresan con un descanso corto o largo.",
                "Invocaciones sobrenaturales" => "Personalizaciones permanentes que mejoran trucos, conceden conjuros o cambian la forma de jugar.",
                "Don del pacto" => "Define una herramienta central del brujo: arma, familiar, grimorio u otras variantes de sus fuentes.",
            ],
        ],
        "Clerigo" => [
            "role" => "Lanzador divino resistente, capaz de apoyar, controlar y combatir.",
            "primary" => "Sabiduría",
            "saves" => "Sabiduría y Carisma",
            "armor" => "Armadura ligera, armadura media y escudos",
            "weapons" => "Armas simples",
            "skills" => "Elige dos entre Historia, Perspicacia, Medicina, Persuasión y Religión.",
            "hit_die" => "d8",
            "magic" => "Lanzador completo",
            "features" => [
                1 => "Lanzamiento de conjuros · Dominio divino",
                2 => "Canalizar divinidad · Rasgo de dominio",
                4 => "Mejora de característica",
                5 => "Destruir muertos vivientes (VD 1/2)",
                6 => "Canalizar divinidad (2 usos) · Rasgo de dominio",
                8 => "Mejora de característica · Rasgo de dominio · Destruir muertos vivientes (VD 1)",
                10 => "Intervención divina",
                11 => "Destruir muertos vivientes (VD 2)",
                12 => "Mejora de característica",
                14 => "Destruir muertos vivientes (VD 3)",
                16 => "Mejora de característica",
                17 => "Rasgo de dominio · Destruir muertos vivientes (VD 4)",
                18 => "Canalizar divinidad (3 usos)",
                19 => "Mejora de característica",
                20 => "Intervención divina mejorada",
            ],
            "core" => [
                "Dominio divino" => "La subclase llega en nivel 1 y amplía conjuros preparados, competencias y formas de canalizar poder.",
                "Canalizar divinidad" => "Activa efectos divinos de uso limitado que dependen del dominio y permite expulsar muertos vivientes.",
                "Intervención divina" => "Solicita ayuda directa a la deidad; su fiabilidad aumenta hasta ser automática en nivel 20.",
            ],
        ],
        "Druida" => [
            "role" => "Lanzador natural centrado en control, exploración y transformación.",
            "primary" => "Sabiduría",
            "saves" => "Inteligencia y Sabiduría",
            "armor" => "Armadura ligera, armadura media y escudos no metálicos",
            "weapons" => "Armas druídicas y armas simples concretas",
            "skills" => "Elige dos entre Arcano, Trato con Animales, Perspicacia, Medicina, Naturaleza, Percepción, Religión y Supervivencia.",
            "hit_die" => "d8",
            "magic" => "Lanzador completo",
            "features" => [
                1 => "Druídico · Lanzamiento de conjuros",
                2 => "Forma salvaje · Círculo druídico",
                4 => "Mejora de característica · Forma salvaje mejorada",
                6 => "Rasgo de círculo",
                8 => "Mejora de característica · Forma salvaje mejorada",
                10 => "Rasgo de círculo",
                12 => "Mejora de característica",
                14 => "Rasgo de círculo",
                16 => "Mejora de característica",
                18 => "Cuerpo eterno · Conjuros de bestia",
                19 => "Mejora de característica",
                20 => "Archidruida",
            ],
            "core" => [
                "Forma salvaje" => "Adopta formas de bestia con límites que crecen por nivel y que cada círculo puede transformar.",
                "Conjuros de bestia" => "Permite realizar componentes verbales y somáticos de conjuros mientras se usa Forma salvaje.",
                "Archidruida" => "Elimina el límite ordinario de Forma salvaje y permite ignorar muchos componentes materiales.",
            ],
        ],
        "Explorador" => [
            "role" => "Combatiente y explorador que combina armas, movilidad y magia natural.",
            "primary" => "Destreza o Fuerza y Sabiduría",
            "saves" => "Fuerza y Destreza",
            "armor" => "Armadura ligera, armadura media y escudos",
            "weapons" => "Armas simples y marciales",
            "skills" => "Elige tres entre Trato con Animales, Atletismo, Perspicacia, Investigación, Naturaleza, Percepción, Sigilo y Supervivencia.",
            "hit_die" => "d10",
            "magic" => "Medio lanzador",
            "features" => [
                1 => "Enemigo predilecto · Explorador nato",
                2 => "Estilo de combate · Lanzamiento de conjuros",
                3 => "Arquetipo de explorador · Conciencia primigenia",
                4 => "Mejora de característica",
                5 => "Ataque adicional",
                6 => "Mejoras de exploración",
                7 => "Rasgo de arquetipo",
                8 => "Mejora de característica · Zancada por la tierra",
                10 => "Ocultarse a plena vista",
                11 => "Rasgo de arquetipo",
                12 => "Mejora de característica",
                14 => "Desvanecerse",
                15 => "Rasgo de arquetipo",
                16 => "Mejora de característica",
                18 => "Sentidos salvajes",
                19 => "Mejora de característica",
                20 => "Azote de enemigos",
            ],
            "core" => [
                "Explorador nato" => "Mejora la orientación, la búsqueda de alimento y el desplazamiento del grupo en terrenos dominados.",
                "Estilo de combate" => "Especializa el uso de armas a distancia, dos armas, defensa u otras opciones de la fuente elegida.",
                "Desvanecerse" => "Permite esconderse como acción adicional y evita ser rastreado por medios no mágicos.",
            ],
        ],
        "Guerrero" => [
            "role" => "Maestro de armas muy configurable, con más ataques y mejoras que cualquier otra clase.",
            "primary" => "Fuerza o Destreza",
            "saves" => "Fuerza y Constitución",
            "armor" => "Todas las armaduras y escudos",
            "weapons" => "Armas simples y marciales",
            "skills" => "Elige dos entre Acrobacias, Trato con Animales, Atletismo, Historia, Perspicacia, Intimidación, Percepción y Supervivencia.",
            "hit_die" => "d10",
            "magic" => "Marcial / subclase",
            "features" => [
                1 => "Estilo de combate · Tomar aliento",
                2 => "Oleada de acción (1 uso)",
                3 => "Arquetipo marcial",
                4 => "Mejora de característica",
                5 => "Ataque adicional",
                6 => "Mejora de característica",
                7 => "Rasgo de arquetipo",
                8 => "Mejora de característica",
                9 => "Indomable (1 uso)",
                10 => "Rasgo de arquetipo",
                11 => "Ataque adicional (2)",
                12 => "Mejora de característica",
                13 => "Indomable (2 usos)",
                14 => "Mejora de característica",
                15 => "Rasgo de arquetipo",
                16 => "Mejora de característica",
                17 => "Oleada de acción (2 usos) · Indomable (3 usos)",
                18 => "Rasgo de arquetipo",
                19 => "Mejora de característica",
                20 => "Ataque adicional (3)",
            ],
            "core" => [
                "Oleada de acción" => "Concede una acción adicional en el turno y se recupera con descanso corto o largo.",
                "Indomable" => "Permite repetir una salvación fallida, aunque obliga a conservar el nuevo resultado.",
                "Ataque adicional" => "Escala hasta cuatro ataques con la acción de Atacar en nivel 20.",
            ],
        ],
        "Hechicero" => [
            "role" => "Lanzador innato que modifica conjuros mediante metamagia.",
            "primary" => "Carisma",
            "saves" => "Constitución y Carisma",
            "armor" => "Sin competencias de armadura",
            "weapons" => "Dagas, dardos, hondas, bastones y ballestas ligeras",
            "skills" => "Elige dos entre Arcano, Engaño, Perspicacia, Intimidación, Persuasión y Religión.",
            "hit_die" => "d6",
            "magic" => "Lanzador completo",
            "features" => [
                1 => "Lanzamiento de conjuros · Origen sortílego",
                2 => "Fuente de magia",
                3 => "Metamagia",
                4 => "Mejora de característica",
                6 => "Rasgo de origen",
                8 => "Mejora de característica",
                10 => "Metamagia adicional",
                12 => "Mejora de característica",
                14 => "Rasgo de origen",
                16 => "Mejora de característica",
                17 => "Metamagia adicional",
                18 => "Rasgo de origen",
                19 => "Mejora de característica",
                20 => "Restauración sortílega",
            ],
            "core" => [
                "Fuente de magia" => "Los puntos de hechicería convierten espacios en puntos o puntos en espacios adicionales.",
                "Metamagia" => "Cambia alcance, objetivos, componentes, duración o velocidad de lanzamiento según las opciones elegidas.",
                "Restauración sortílega" => "Recupera una pequeña reserva de puntos de hechicería al terminar un descanso corto.",
            ],
        ],
        "Mago" => [
            "role" => "Lanzador erudito con la lista y el repertorio de conjuros más amplios.",
            "primary" => "Inteligencia",
            "saves" => "Inteligencia y Sabiduría",
            "armor" => "Sin competencias de armadura",
            "weapons" => "Dagas, dardos, hondas, bastones y ballestas ligeras",
            "skills" => "Elige dos entre Arcano, Historia, Perspicacia, Investigación, Medicina y Religión.",
            "hit_die" => "d6",
            "magic" => "Lanzador completo",
            "features" => [
                1 => "Lanzamiento de conjuros · Recuperación arcana",
                2 => "Tradición arcana",
                4 => "Mejora de característica",
                6 => "Rasgo de tradición",
                8 => "Mejora de característica",
                10 => "Rasgo de tradición",
                12 => "Mejora de característica",
                14 => "Rasgo de tradición",
                16 => "Mejora de característica",
                18 => "Maestría de conjuros",
                19 => "Mejora de característica",
                20 => "Conjuros característicos",
            ],
            "core" => [
                "Recuperación arcana" => "Recupera parte de los espacios de conjuro durante un descanso corto una vez al día.",
                "Libro de conjuros" => "Permite copiar nuevos conjuros encontrados y preparar una selección distinta tras cada descanso largo.",
                "Maestría de conjuros" => "Elige conjuros de nivel 1 y 2 que puede lanzar a voluntad en su nivel base.",
            ],
        ],
        "Monje" => [
            "role" => "Combatiente móvil que transforma ki en defensa, control y ráfagas de golpes.",
            "primary" => "Destreza y Sabiduría",
            "saves" => "Fuerza y Destreza",
            "armor" => "Sin competencias de armadura",
            "weapons" => "Armas simples y espadas cortas",
            "skills" => "Elige dos entre Acrobacias, Atletismo, Historia, Perspicacia, Religión y Sigilo.",
            "hit_die" => "d8",
            "magic" => "Marcial",
            "features" => [
                1 => "Defensa sin armadura · Artes marciales",
                2 => "Ki · Movimiento sin armadura",
                3 => "Tradición monástica · Desviar proyectiles",
                4 => "Mejora de característica · Caída lenta",
                5 => "Ataque adicional · Golpe aturdidor",
                6 => "Golpes potenciados por ki · Rasgo de tradición",
                7 => "Evasión · Quietud de la mente",
                8 => "Mejora de característica",
                9 => "Movimiento sin armadura mejorado",
                10 => "Pureza del cuerpo",
                11 => "Rasgo de tradición",
                12 => "Mejora de característica",
                13 => "Lengua del sol y la luna",
                14 => "Alma diamantina",
                15 => "Cuerpo atemporal",
                16 => "Mejora de característica",
                17 => "Rasgo de tradición",
                18 => "Cuerpo vacío",
                19 => "Mejora de característica",
                20 => "Yo perfecto",
            ],
            "core" => [
                "Ki" => "Una reserva por nivel alimenta Ráfaga de golpes, Defensa paciente, Paso del viento y rasgos posteriores.",
                "Golpe aturdidor" => "Al impactar, gasta ki para exigir una salvación de Constitución o aturdir hasta el final del siguiente turno.",
                "Alma diamantina" => "Concede competencia en todas las salvaciones y permite repetir una fallida gastando ki.",
            ],
        ],
        "Paladin" => [
            "role" => "Defensor divino que combina armadura pesada, auras, curación y golpes explosivos.",
            "primary" => "Fuerza y Carisma",
            "saves" => "Sabiduría y Carisma",
            "armor" => "Todas las armaduras y escudos",
            "weapons" => "Armas simples y marciales",
            "skills" => "Elige dos entre Atletismo, Perspicacia, Intimidación, Medicina, Persuasión y Religión.",
            "hit_die" => "d10",
            "magic" => "Medio lanzador",
            "features" => [
                1 => "Percepción divina · Imposición de manos",
                2 => "Estilo de combate · Lanzamiento de conjuros · Castigo divino",
                3 => "Salud divina · Juramento sagrado",
                4 => "Mejora de característica",
                5 => "Ataque adicional",
                6 => "Aura de protección",
                7 => "Rasgo de juramento",
                8 => "Mejora de característica",
                10 => "Aura de valor",
                11 => "Castigo divino mejorado",
                12 => "Mejora de característica",
                14 => "Toque purificador",
                15 => "Rasgo de juramento",
                16 => "Mejora de característica",
                18 => "Auras mejoradas",
                19 => "Mejora de característica",
                20 => "Rasgo culminante de juramento",
            ],
            "core" => [
                "Imposición de manos" => "Reserva diaria de curación que también neutraliza enfermedades o venenos.",
                "Castigo divino" => "Convierte espacios de conjuro en daño radiante después de confirmar un impacto cuerpo a cuerpo.",
                "Aura de protección" => "Añade Carisma a las salvaciones propias y de aliados cercanos mientras el paladín esté consciente.",
            ],
        ],
        "Picaro" => [
            "role" => "Especialista preciso y escurridizo que concentra gran daño en un único impacto.",
            "primary" => "Destreza",
            "saves" => "Destreza e Inteligencia",
            "armor" => "Armadura ligera",
            "weapons" => "Armas simples, ballestas de mano, espadas largas, estoques y espadas cortas",
            "skills" => "Elige cuatro entre Acrobacias, Atletismo, Engaño, Perspicacia, Intimidación, Investigación, Percepción, Interpretación, Persuasión, Juego de Manos y Sigilo.",
            "hit_die" => "d8",
            "magic" => "Marcial / subclase",
            "features" => [
                1 => "Pericia · Ataque furtivo · Jerga de ladrones",
                2 => "Acción astuta",
                3 => "Arquetipo de pícaro",
                4 => "Mejora de característica",
                5 => "Esquiva asombrosa",
                6 => "Pericia",
                7 => "Evasión",
                8 => "Mejora de característica",
                9 => "Rasgo de arquetipo",
                10 => "Mejora de característica",
                11 => "Talento fiable",
                12 => "Mejora de característica",
                13 => "Rasgo de arquetipo",
                14 => "Sentido ciego",
                15 => "Mente escurridiza",
                16 => "Mejora de característica",
                17 => "Rasgo de arquetipo",
                18 => "Elusivo",
                19 => "Mejora de característica",
                20 => "Golpe de suerte",
            ],
            "core" => [
                "Ataque furtivo" => "Una vez por turno añade dados de daño si ataca con ventaja o un aliado amenaza al objetivo.",
                "Acción astuta" => "Permite Correr, Destrabarse u Ocultarse como acción adicional.",
                "Talento fiable" => "En pruebas con competencia, cualquier resultado de d20 inferior a 10 cuenta como 10.",
            ],
        ],
    ];

    private const SUBCLASS_LEVELS = [
        "Artifice" => [3, 5, 9, 15],
        "Barbaro" => [3, 6, 10, 14],
        "Bardo" => [3, 6, 14],
        "Brujo" => [1, 6, 10, 14],
        "Clerigo" => [1, 2, 6, 8, 17],
        "Druida" => [2, 6, 10, 14],
        "Explorador" => [3, 7, 11, 15],
        "Guerrero" => [3, 7, 10, 15, 18],
        "Hechicero" => [1, 6, 14, 18],
        "Mago" => [2, 6, 10, 14],
        "Monje" => [3, 6, 11, 17],
        "Paladin" => [3, 7, 15, 20],
        "Picaro" => [3, 9, 13, 17],
    ];

    private const SUBCLASS_KEYWORDS = [
        "berserker" => "Convierte la furia en una ofensiva todavía más agresiva, con ataques adicionales y respuestas violentas.",
        "totem" => "Adopta espíritus animales que modifican la resistencia, la movilidad y la protección del grupo.",
        "fuego salvaje" => "Invoca un espíritu de llamas que combina teletransporte, daño ígneo y renovación natural.",
        "luna" => "Potencia Forma salvaje para combatir con bestias más peligrosas y transformarse con mayor frecuencia.",
        "tierra" => "Profundiza en la magia de regiones naturales y recupera energía mágica durante los descansos.",
        "campeon" => "Perfecciona la capacidad física, amplía el margen de crítico y mejora el rendimiento atlético.",
        "caballero arcano" => "Añade una progresión de conjuros limitada y combina ataques de arma con magia defensiva y evocadora.",
        "tramposo arcano" => "Combina sigilo con ilusiones, encantamientos y una Mano de mago especialmente versátil.",
        "asesino" => "Se especializa en infiltración, identidades falsas y golpes devastadores contra objetivos desprevenidos.",
        "ladron" => "Mejora el uso rápido de objetos, la escalada, el sigilo y el aprovechamiento de artefactos mágicos.",
        "devocion" => "Representa el ideal caballeresco de honor, protección y lucha contra criaturas impías.",
        "venganza" => "Persigue a un enemigo prioritario con movilidad, ventaja y castigos difíciles de evitar.",
        "ancestros" => "Protege al grupo mediante espíritus guardianes que dificultan los ataques enemigos.",
        "vida" => "Refuerza la curación, la resistencia del grupo y los conjuros que restauran puntos de golpe.",
        "luz" => "Usa resplandor y fuego para revelar amenazas, proteger aliados y castigar grupos de enemigos.",
        "tempestad" => "Canaliza truenos, relámpagos y vientos violentos para controlar el campo de batalla.",
        "evocacion" => "Moldea conjuros de energía para proteger aliados dentro de sus áreas y aumentar el daño.",
        "adivinacion" => "Manipula presagios y resultados mediante una comprensión excepcional de lo que está por ocurrir.",
        "abjuracion" => "Levanta una salvaguarda arcana y sobresale anulando o conteniendo magia hostil.",
        "hojas" => "Integra arma, movimiento y conjuros en una danza defensiva de gran movilidad.",
        "draconica" => "Expresa una herencia de dragón mediante resistencia, afinidad elemental y presencia sobrenatural.",
        "magia salvaje" => "Acepta una magia impredecible capaz de alterar tiradas y provocar efectos extraordinarios.",
        "hexblade" => "Vincula el pacto a armas conscientes o fuerzas sombrías y permite combatir usando Carisma.",
        "celestial" => "Obtiene luz, curación y resistencia radiante de un patrón de los planos superiores.",
        "archifey" => "Usa fascinación, miedo, engaño y desplazamiento feérico para controlar encuentros.",
        "maestro de batalla" => "Aprende maniobras alimentadas por dados de superioridad para controlar el ritmo del combate.",
        "samurai" => "Combina determinación, presencia social y ráfagas de ataques realizados con ventaja.",
        "sombras" => "Usa ki para ocultarse, desplazarse entre penumbras y sorprender desde posiciones imposibles.",
        "mano abierta" => "Perfecciona Ráfaga de golpes para empujar, derribar y negar reacciones.",
        "misericordia" => "Alterna daño necrótico y curación mediante un dominio preciso del cuerpo y el ki.",
        "cazador" => "Elige técnicas adaptadas a hordas, criaturas grandes o amenazas concretas.",
        "bestias" => "Combate junto a un compañero animal cuya eficacia crece con el explorador.",
        "espadas" => "Convierte la inspiración en florituras de arma, defensa y movilidad.",
        "conocimiento" => "Amplía competencias e información y permite recurrir temporalmente a habilidades ajenas.",
    ];

    public static function profile(string $className): array
    {
        return self::PROFILES[$className] ?? [
            "role" => "Clase aventurera con una progresión propia de rasgos y especializaciones.",
            "primary" => "Según la configuración",
            "saves" => "Consulta la fuente de la clase",
            "armor" => "Según la clase",
            "weapons" => "Según la clase",
            "skills" => "Selecciona las competencias indicadas por la clase.",
            "hit_die" => "d8",
            "magic" => "Marcial",
            "features" => [],
            "core" => [],
        ];
    }

    public static function subclassLevels(string $className): array
    {
        return self::SUBCLASS_LEVELS[$className] ?? [3, 6, 10, 14];
    }

    public static function subclassSummary(
        string $className,
        string $subclassName,
        string $source
    ): string {
        $detail = self::subclassDetail($className, $subclassName);
        if ((string) ($detail["overview"] ?? "") !== "") {
            return (string) $detail["overview"];
        }

        $normalised = self::normalise($subclassName);
        if ($className === "Mago" && strpos($normalised, "canto de espadas") !== false) {
            return "Combina concentración arcana, velocidad y defensa para luchar con un arma sin abandonar el repertorio de mago.";
        }
        if ($className === "Bardo" && strpos($normalised, "espadas") !== false) {
            return "Convierte la inspiración en florituras de arma, defensa y movilidad.";
        }
        if ($className === "Paladin" && strpos($normalised, "ancestros") !== false) {
            return "Defiende la vida y la luz con magia feérica, control de enemigos y auras de resistencia sobrenatural.";
        }
        if ($className === "Barbaro" && strpos($normalised, "bestia") !== false) {
            return "Manifiesta armas y adaptaciones animales durante la furia para cambiar su forma de atacar y desplazarse.";
        }
        foreach (self::SUBCLASS_KEYWORDS as $keyword => $description) {
            if (strpos($normalised, $keyword) !== false) {
                return $description;
            }
        }

        $profile = self::profile($className);
        return "Esta especialización desarrolla una variante de "
            . mb_strtolower((string) $profile["role"])
            . " Sus rasgos exclusivos se incorporan en los niveles de subclase "
            . "y proceden de " . ($source ?: "una fuente oficial") . ".";
    }

    public static function subclassDetail(
        string $className,
        string $subclassName
    ): array {
        $classKey = self::normalise($className);
        $subclassKey = self::normalise($subclassName);
        foreach (self::loadSubclassDetails() as $detail) {
            if (
                self::normalise((string) ($detail["class"] ?? "")) === $classKey
                && self::normalise((string) ($detail["name"] ?? "")) === $subclassKey
            ) {
                return $detail;
            }
        }

        return [];
    }

    private static function loadSubclassDetails(): array
    {
        if (is_array(self::$subclassDetails)) {
            return self::$subclassDetails;
        }

        $path = __DIR__ . "/../data/subclass-details.json";
        $contents = is_file($path) ? file_get_contents($path) : false;
        $decoded = is_string($contents) ? json_decode($contents, true) : null;
        self::$subclassDetails = is_array($decoded["subclasses"] ?? null)
            ? $decoded["subclasses"]
            : [];

        return self::$subclassDetails;
    }

    private static function normalise(string $value): string
    {
        $transliterator = Transliterator::create("NFD; [:Nonspacing Mark:] Remove; NFC");
        $normalised = $transliterator
            ? $transliterator->transliterate($value)
            : $value;
        return mb_strtolower((string) $normalised);
    }
}
