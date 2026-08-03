<?php

final class BestiaryLocalizer
{
    private const NAMES = [
        "aboleth" => "Aboleth",
        "acolyte" => "Acólito",
        "adult-black-dragon" => "Dragón negro adulto",
        "adult-blue-dragon" => "Dragón azul adulto",
        "adult-brass-dragon" => "Dragón de oropel adulto",
        "adult-bronze-dragon" => "Dragón de bronce adulto",
        "adult-copper-dragon" => "Dragón de cobre adulto",
        "adult-gold-dragon" => "Dragón de oro adulto",
        "adult-green-dragon" => "Dragón verde adulto",
        "adult-red-dragon" => "Dragón rojo adulto",
        "adult-silver-dragon" => "Dragón de plata adulto",
        "adult-white-dragon" => "Dragón blanco adulto",
        "air-elemental" => "Elemental de aire",
        "ancient-black-dragon" => "Dragón negro anciano",
        "ancient-blue-dragon" => "Dragón azul anciano",
        "ancient-brass-dragon" => "Dragón de oropel anciano",
        "ancient-bronze-dragon" => "Dragón de bronce anciano",
        "ancient-copper-dragon" => "Dragón de cobre anciano",
        "ancient-gold-dragon" => "Dragón de oro anciano",
        "ancient-green-dragon" => "Dragón verde anciano",
        "ancient-red-dragon" => "Dragón rojo anciano",
        "ancient-silver-dragon" => "Dragón de plata anciano",
        "ancient-white-dragon" => "Dragón blanco anciano",
        "androsphinx" => "Androesfinge",
        "animated-armor" => "Armadura animada",
        "ankheg" => "Ankheg",
        "ape" => "Simio",
        "archmage" => "Archimago",
        "assassin" => "Asesino",
        "awakened-shrub" => "Arbusto despertado",
        "awakened-tree" => "Árbol despertado",
        "axe-beak" => "Pico de hacha",
        "azer" => "Azer",
        "baboon" => "Babuino",
        "badger" => "Tejón",
        "balor" => "Balor",
        "bandit" => "Bandido",
        "bandit-captain" => "Capitán bandido",
        "barbed-devil" => "Diablo punzante",
        "basilisk" => "Basilisco",
        "bat" => "Murciélago",
        "bearded-devil" => "Diablo barbado",
        "behir" => "Behir",
        "berserker" => "Bersérker",
        "black-bear" => "Oso negro",
        "black-dragon-wyrmling" => "Cría de dragón negro",
        "black-pudding" => "Pudin negro",
        "blink-dog" => "Perro intermitente",
        "blood-hawk" => "Halcón sangriento",
        "blue-dragon-wyrmling" => "Cría de dragón azul",
        "boar" => "Jabalí",
        "bone-devil" => "Diablo óseo",
        "brass-dragon-wyrmling" => "Cría de dragón de oropel",
        "bronze-dragon-wyrmling" => "Cría de dragón de bronce",
        "brown-bear" => "Oso pardo",
        "bugbear" => "Osgo",
        "bulette" => "Bulette",
        "camel" => "Camello",
        "cat" => "Gato",
        "centaur" => "Centauro",
        "chain-devil" => "Diablo de las cadenas",
        "chimera" => "Quimera",
        "chuul" => "Chuul",
        "clay-golem" => "Gólem de arcilla",
        "cloaker" => "Manto",
        "cloud-giant" => "Gigante de las nubes",
        "cockatrice" => "Cocatriz",
        "commoner" => "Plebeyo",
        "constrictor-snake" => "Serpiente constrictora",
        "copper-dragon-wyrmling" => "Cría de dragón de cobre",
        "couatl" => "Couatl",
        "crab" => "Cangrejo",
        "crocodile" => "Cocodrilo",
        "cult-fanatic" => "Sectario fanático",
        "cultist" => "Sectario",
        "darkmantle" => "Mantoscuro",
        "death-dog" => "Perro del inframundo",
        "deep-gnome-svirfneblin" => "Gnomo de las profundidades (svirfneblin)",
        "deer" => "Ciervo",
        "deva" => "Deva",
        "dire-wolf" => "Lobo terrible",
        "djinni" => "Djinn",
        "doppelganger" => "Doppelganger",
        "draft-horse" => "Caballo de tiro",
        "dragon-turtle" => "Dragón tortuga",
        "dretch" => "Dretch",
        "drider" => "Draña",
        "drow" => "Elfo drow",
        "druid" => "Druida",
        "dryad" => "Dríade",
        "duergar" => "Duergar",
        "dust-mephit" => "Mephit de polvo",
        "eagle" => "Águila",
        "earth-elemental" => "Elemental de tierra",
        "efreeti" => "Ifrit",
        "elephant" => "Elefante",
        "elk" => "Alce",
        "erinyes" => "Erinia",
        "ettercap" => "Ettercap",
        "ettin" => "Ettin",
        "fire-elemental" => "Elemental de fuego",
        "fire-giant" => "Gigante de fuego",
        "flesh-golem" => "Gólem de carne",
        "flying-snake" => "Serpiente voladora",
        "flying-sword" => "Espada voladora",
        "frog" => "Rana",
        "frost-giant" => "Gigante de escarcha",
        "gargoyle" => "Gárgola",
        "gelatinous-cube" => "Cubo gelatinoso",
        "ghast" => "Ghast",
        "ghost" => "Fantasma",
        "ghoul" => "Gul",
        "giant-ape" => "Simio gigante",
        "giant-badger" => "Tejón gigante",
        "giant-bat" => "Murciélago gigante",
        "giant-boar" => "Jabalí gigante",
        "giant-centipede" => "Ciempiés gigante",
        "giant-constrictor-snake" => "Serpiente constrictora gigante",
        "giant-crab" => "Cangrejo gigante",
        "giant-crocodile" => "Cocodrilo gigante",
        "giant-eagle" => "Águila gigante",
        "giant-elk" => "Alce gigante",
        "giant-fire-beetle" => "Escarabajo de fuego gigante",
        "giant-frog" => "Rana gigante",
        "giant-goat" => "Cabra gigante",
        "giant-hyena" => "Hiena gigante",
        "giant-lizard" => "Lagarto gigante",
        "giant-octopus" => "Pulpo gigante",
        "giant-owl" => "Búho gigante",
        "giant-poisonous-snake" => "Serpiente venenosa gigante",
        "giant-rat" => "Rata gigante",
        "giant-rat-diseased" => "Rata gigante enferma",
        "giant-scorpion" => "Escorpión gigante",
        "giant-sea-horse" => "Caballito de mar gigante",
        "giant-shark" => "Tiburón gigante",
        "giant-spider" => "Araña gigante",
        "giant-toad" => "Sapo gigante",
        "giant-vulture" => "Buitre gigante",
        "giant-wasp" => "Avispa gigante",
        "giant-weasel" => "Comadreja gigante",
        "giant-wolf-spider" => "Tarántula gigante",
        "gibbering-mouther" => "Bocón barbotante",
        "glabrezu" => "Glabrezu",
        "gladiator" => "Gladiador",
        "gnoll" => "Gnoll",
        "goat" => "Cabra",
        "goblin" => "Goblin",
        "gold-dragon-wyrmling" => "Cría de dragón de oro",
        "gorgon" => "Gorgon",
        "gray-ooze" => "Cieno gris",
        "green-dragon-wyrmling" => "Cría de dragón verde",
        "green-hag" => "Saga verde",
        "grick" => "Grick",
        "griffon" => "Grifo",
        "grimlock" => "Grimlock",
        "guard" => "Guardia",
        "guardian-naga" => "Naga guardiana",
        "gynosphinx" => "Ginoesfinge",
        "half-red-dragon-veteran" => "Semidragón rojo veterano",
        "harpy" => "Arpía",
        "hawk" => "Halcón",
        "hell-hound" => "Sabueso infernal",
        "hezrou" => "Hezrou",
        "hill-giant" => "Gigante de las colinas",
        "hippogriff" => "Hipogrifo",
        "hobgoblin" => "Hobgoblin",
        "homunculus" => "Homúnculo",
        "horned-devil" => "Diablo astado",
        "hunter-shark" => "Tiburón cazador",
        "hydra" => "Hidra",
        "hyena" => "Hiena",
        "ice-devil" => "Diablo gélido",
        "ice-mephit" => "Mephit de hielo",
        "imp" => "Diablillo",
        "invisible-stalker" => "Acechador invisible",
        "iron-golem" => "Gólem de hierro",
        "jackal" => "Chacal",
        "killer-whale" => "Orca",
        "knight" => "Caballero",
        "kobold" => "Kobold",
        "kraken" => "Kraken",
        "lamia" => "Lamia",
        "lemure" => "Lémur",
        "lich" => "Liche",
        "lion" => "León",
        "lizard" => "Lagarto",
        "lizardfolk" => "Hombre lagarto",
        "mage" => "Mago",
        "magma-mephit" => "Mephit de magma",
        "magmin" => "Magmin",
        "mammoth" => "Mamut",
        "manticore" => "Mantícora",
        "marilith" => "Marilith",
        "mastiff" => "Mastín",
        "medusa" => "Medusa",
        "merfolk" => "Sirénido",
        "merrow" => "Merrow",
        "mimic" => "Mimeto",
        "minotaur" => "Minotauro",
        "minotaur-skeleton" => "Minotauro esqueleto",
        "mule" => "Mula",
        "mummy" => "Momia",
        "mummy-lord" => "Señor de las momias",
        "nalfeshnee" => "Nalfeshnee",
        "night-hag" => "Saga de la noche",
        "nightmare" => "Pesadilla",
        "noble" => "Noble",
        "ochre-jelly" => "Gelatina ocre",
        "octopus" => "Pulpo",
        "ogre" => "Ogro",
        "ogre-zombie" => "Ogro zombi",
        "oni" => "Oni",
        "orc" => "Orco",
        "otyugh" => "Otyugh",
        "owl" => "Búho",
        "owlbear" => "Oso lechuza",
        "panther" => "Pantera",
        "pegasus" => "Pegaso",
        "phase-spider" => "Araña de fase",
        "pit-fiend" => "Diablo de la sima",
        "planetar" => "Planetar",
        "plesiosaurus" => "Plesiosaurio",
        "poisonous-snake" => "Serpiente venenosa",
        "polar-bear" => "Oso polar",
        "pony" => "Poni",
        "priest" => "Sacerdote",
        "pseudodragon" => "Pseudodragón",
        "purple-worm" => "Gusano púrpura",
        "quasit" => "Quasit",
        "quipper" => "Mordedor",
        "rakshasa" => "Rakshasa",
        "rat" => "Rata",
        "raven" => "Cuervo",
        "red-dragon-wyrmling" => "Cría de dragón rojo",
        "reef-shark" => "Tiburón de arrecife",
        "remorhaz" => "Remorhaz",
        "rhinoceros" => "Rinoceronte",
        "riding-horse" => "Caballo de monta",
        "roc" => "Roc",
        "roper" => "Lacero",
        "rug-of-smothering" => "Alfombra asfixiante",
        "rust-monster" => "Monstruo corrosivo",
        "saber-toothed-tiger" => "Tigre dientes de sable",
        "sahuagin" => "Sahuagin",
        "salamander" => "Salamandra",
        "satyr" => "Sátiro",
        "scorpion" => "Escorpión",
        "scout" => "Batidor",
        "sea-hag" => "Saga marina",
        "sea-horse" => "Caballito de mar",
        "shadow" => "Sombra",
        "shambling-mound" => "Broza movediza",
        "shield-guardian" => "Guardián escudo",
        "shrieker" => "Chillón",
        "silver-dragon-wyrmling" => "Cría de dragón de plata",
        "skeleton" => "Esqueleto",
        "solar" => "Solar",
        "specter" => "Espectro",
        "spider" => "Araña",
        "spirit-naga" => "Naga espiritual",
        "sprite" => "Duende",
        "spy" => "Espía",
        "steam-mephit" => "Mephit de vapor",
        "stirge" => "Estirge",
        "stone-giant" => "Gigante de piedra",
        "stone-golem" => "Gólem de piedra",
        "storm-giant" => "Gigante de las tormentas",
        "succubus-incubus" => "Súcubo/Íncubo",
        "swarm-of-bats" => "Enjambre de murciélagos",
        "swarm-of-beetles" => "Enjambre de escarabajos",
        "swarm-of-centipedes" => "Enjambre de ciempiés",
        "swarm-of-insects" => "Enjambre de insectos",
        "swarm-of-poisonous-snakes" => "Enjambre de serpientes venenosas",
        "swarm-of-quippers" => "Enjambre de mordedores",
        "swarm-of-rats" => "Enjambre de ratas",
        "swarm-of-ravens" => "Enjambre de cuervos",
        "swarm-of-spiders" => "Enjambre de arañas",
        "swarm-of-wasps" => "Enjambre de avispas",
        "tarrasque" => "Tarasca",
        "thug" => "Matón",
        "tiger" => "Tigre",
        "treant" => "Ent",
        "tribal-warrior" => "Guerrero tribal",
        "triceratops" => "Tricerátops",
        "troll" => "Trol",
        "tyrannosaurus-rex" => "Tiranosaurio rex",
        "unicorn" => "Unicornio",
        "vampire-bat" => "Vampiro en forma de murciélago",
        "vampire-mist" => "Vampiro en forma de niebla",
        "vampire-spawn" => "Engendro vampírico",
        "vampire-vampire" => "Vampiro",
        "veteran" => "Veterano",
        "violet-fungus" => "Hongo violeta",
        "vrock" => "Vrock",
        "vulture" => "Buitre",
        "warhorse" => "Caballo de guerra",
        "warhorse-skeleton" => "Caballo de guerra esqueleto",
        "water-elemental" => "Elemental de agua",
        "weasel" => "Comadreja",
        "werebear-bear" => "Hombre oso en forma de oso",
        "werebear-human" => "Hombre oso en forma humana",
        "werebear-hybrid" => "Hombre oso en forma híbrida",
        "wereboar-boar" => "Hombre jabalí en forma de jabalí",
        "wereboar-human" => "Hombre jabalí en forma humana",
        "wereboar-hybrid" => "Hombre jabalí en forma híbrida",
        "wererat-human" => "Hombre rata en forma humana",
        "wererat-hybrid" => "Hombre rata en forma híbrida",
        "wererat-rat" => "Hombre rata en forma de rata",
        "weretiger-human" => "Hombre tigre en forma humana",
        "weretiger-hybrid" => "Hombre tigre en forma híbrida",
        "weretiger-tiger" => "Hombre tigre en forma de tigre",
        "werewolf-human" => "Hombre lobo en forma humana",
        "werewolf-hybrid" => "Hombre lobo en forma híbrida",
        "werewolf-wolf" => "Hombre lobo en forma de lobo",
        "white-dragon-wyrmling" => "Cría de dragón blanco",
        "wight" => "Tumulario",
        "will-o-wisp" => "Fuego fatuo",
        "winter-wolf" => "Lobo invernal",
        "wolf" => "Lobo",
        "worg" => "Huargo",
        "wraith" => "Aparición",
        "wyvern" => "Guiverno",
        "xorn" => "Xorn",
        "young-black-dragon" => "Dragón negro joven",
        "young-blue-dragon" => "Dragón azul joven",
        "young-brass-dragon" => "Dragón de oropel joven",
        "young-bronze-dragon" => "Dragón de bronce joven",
        "young-copper-dragon" => "Dragón de cobre joven",
        "young-gold-dragon" => "Dragón de oro joven",
        "young-green-dragon" => "Dragón verde joven",
        "young-red-dragon" => "Dragón rojo joven",
        "young-silver-dragon" => "Dragón de plata joven",
        "young-white-dragon" => "Dragón blanco joven",
        "zombie" => "Zombi",
    ];

    private const TYPES = [
        "aberration" => "Aberración",
        "beast" => "Bestia",
        "celestial" => "Celestial",
        "construct" => "Autómata",
        "dragon" => "Dragón",
        "elemental" => "Elemental",
        "fey" => "Feérico",
        "fiend" => "Infernal",
        "giant" => "Gigante",
        "humanoid" => "Humanoide",
        "monstrosity" => "Monstruosidad",
        "ooze" => "Cieno",
        "plant" => "Planta",
        "swarm of Tiny beasts" => "Enjambre de bestias diminutas",
        "undead" => "No muerto",
    ];

    private const SIZES = [
        "Tiny" => "Diminuto",
        "Small" => "Pequeño",
        "Medium" => "Mediano",
        "Large" => "Grande",
        "Huge" => "Enorme",
        "Gargantuan" => "Gargantuesco",
    ];

    private const ALIGNMENTS = [
        "any alignment" => "cualquier alineamiento",
        "any chaotic alignment" => "cualquier alineamiento caótico",
        "any evil alignment" => "cualquier alineamiento malvado",
        "any non-good alignment" => "cualquier alineamiento no bueno",
        "any non-lawful alignment" => "cualquier alineamiento no legal",
        "chaotic evil" => "caótico malvado",
        "chaotic good" => "caótico bueno",
        "chaotic neutral" => "caótico neutral",
        "lawful evil" => "legal malvado",
        "lawful good" => "legal bueno",
        "lawful neutral" => "legal neutral",
        "neutral" => "neutral",
        "neutral evil" => "neutral malvado",
        "neutral good" => "neutral bueno",
        "neutral good (50%) or neutral evil (50%)" => "neutral bueno (50 %) o neutral malvado (50 %)",
        "unaligned" => "sin alineamiento",
    ];

    private const SUBTYPES = [
        "any race" => "cualquier raza",
        "demon" => "demonio",
        "devil" => "diablo",
        "dwarf" => "enano",
        "elf" => "elfo",
        "gnoll" => "gnoll",
        "gnome" => "gnomo",
        "goblinoid" => "trasgo",
        "grimlock" => "grimlock",
        "human" => "humano",
        "kobold" => "kobold",
        "lizardfolk" => "hombre lagarto",
        "merfolk" => "sirénido",
        "orc" => "orco",
        "sahuagin" => "sahuagin",
        "shapechanger" => "cambiaformas",
        "titan" => "titán",
    ];

    private const DAMAGE = [
        "acid" => "ácido",
        "bludgeoning" => "contundente",
        "bludgeoning, piercing, and slashing from nonmagical attacks (from stoneskin)" => "contundente, perforante y cortante de ataques no mágicos (por piel pétrea)",
        "bludgeoning, piercing, and slashing from nonmagical weapons" => "contundente, perforante y cortante de armas no mágicas",
        "bludgeoning, piercing, and slashing from nonmagical weapons that aren't adamantine" => "contundente, perforante y cortante de armas no mágicas que no sean de adamantina",
        "bludgeoning, piercing, and slashing from nonmagical weapons that aren't silvered" => "contundente, perforante y cortante de armas no mágicas que no estén bañadas en plata",
        "cold" => "frío",
        "damage from spells" => "daño de conjuros",
        "fire" => "fuego",
        "lightning" => "relámpago",
        "necrotic" => "necrótico",
        "piercing" => "perforante",
        "piercing and slashing from nonmagical weapons that aren't adamantine" => "perforante y cortante de armas no mágicas que no sean de adamantina",
        "piercing from magic weapons wielded by good creatures" => "perforante de armas mágicas empuñadas por criaturas buenas",
        "poison" => "veneno",
        "psychic" => "psíquico",
        "radiant" => "radiante",
        "slashing" => "cortante",
        "thunder" => "trueno",
    ];

    private const CONDITIONS = [
        "Blinded" => "Cegado",
        "Charmed" => "Hechizado",
        "Deafened" => "Ensordecido",
        "Exhaustion" => "Cansancio",
        "Frightened" => "Asustado",
        "Grappled" => "Agarrado",
        "Paralyzed" => "Paralizado",
        "Petrified" => "Petrificado",
        "Poisoned" => "Envenenado",
        "Prone" => "Derribado",
        "Restrained" => "Apresado",
        "Stunned" => "Aturdido",
        "Unconscious" => "Inconsciente",
    ];

    private const LANGUAGES = [
        "Undercommon" => "infracomún",
        "Deep Speech" => "habla de las profundidades",
        "Thieves' cant" => "jerga de ladrones",
        "Winter Wolf" => "lobo invernal",
        "Blink Dog" => "perro intermitente",
        "Giant Eagle" => "águila gigante",
        "Giant Elk" => "alce gigante",
        "Giant Owl" => "búho gigante",
        "Abyssal" => "abisal",
        "Aquan" => "acuano",
        "Auran" => "aurano",
        "Celestial" => "celestial",
        "Common" => "común",
        "Draconic" => "dracónico",
        "Druidic" => "druídico",
        "Dwarvish" => "enano",
        "Elvish" => "elfo",
        "Giant" => "gigante",
        "Gnoll" => "gnoll",
        "Gnomish" => "gnomo",
        "Goblin" => "goblin",
        "Ignan" => "ígneo",
        "Infernal" => "infernal",
        "Orc" => "orco",
        "Otyugh" => "otyugh",
        "Primordial" => "primordial",
        "Sahuagin" => "sahuagin",
        "Sphinx" => "esfinge",
        "Sylvan" => "silvano",
        "Terran" => "terrano",
        "Worg" => "huargo",
    ];

    private const LANGUAGE_PHRASES = [
        "understands all languages it spoke in life but can't speak" => "entiende todos los idiomas que hablaba en vida, pero no puede hablar",
        "understands all languages it knew in life but can't speak" => "entiende todos los idiomas que conocía en vida, pero no puede hablar",
        "understands commands given in any language but can't speak" => "entiende las órdenes dadas en cualquier idioma, pero no puede hablar",
        "understands the languages of its creator but can't speak" => "entiende los idiomas de su creador, pero no puede hablar",
        "the languages it knew in life" => "los idiomas que conocía en vida",
        "any languages it knew in life" => "cualquier idioma que conociera en vida",
        "one language known by its creator" => "un idioma conocido por su creador",
        "any one language (usually Common)" => "un idioma cualquiera (normalmente común)",
        "Common plus up to five other languages" => "común y hasta otros cinco idiomas",
        "Druidic plus any two languages" => "druídico y otros dos idiomas cualesquiera",
        "Thieves' cant plus any two languages" => "jerga de ladrones y otros dos idiomas cualesquiera",
        "any one language" => "un idioma cualquiera",
        "any two languages" => "dos idiomas cualesquiera",
        "any four languages" => "cuatro idiomas cualesquiera",
        "any six languages" => "seis idiomas cualesquiera",
        "works only with creatures that understand Abyssal" => "solo funciona con criaturas que entiendan abisal",
        "can't speak in boar form" => "no puede hablar en forma de jabalí",
        "but doesn't speak it" => "pero no lo habla",
        "but can't speak it" => "pero no puede hablarlo",
        "but can't speak" => "pero no puede hablar",
        "understands" => "entiende",
        "telepathy" => "telepatía",
        "all" => "todos los idiomas",
    ];

    private const SENSES = [
        "blindsight" => "visión ciega",
        "darkvision" => "visión en la oscuridad",
        "passive_perception" => "Percepción pasiva",
        "tremorsense" => "sentir vibraciones",
        "truesight" => "visión verdadera",
    ];

    public static function hasName(string $index): bool
    {
        return isset(self::NAMES[$index]);
    }

    public static function name(array $monster): string
    {
        $index = (string) ($monster["index"] ?? "");
        return self::NAMES[$index] ?? (string) ($monster["name"] ?? "Criatura");
    }

    public static function type(string $type): string
    {
        return self::TYPES[$type] ?? "Otro";
    }

    public static function size(string $size): string
    {
        return self::SIZES[$size] ?? "Tamaño desconocido";
    }

    public static function alignment(string $alignment): string
    {
        return self::ALIGNMENTS[$alignment] ?? "sin alineamiento";
    }

    public static function subtype(string $subtype): string
    {
        return self::SUBTYPES[$subtype] ?? $subtype;
    }

    public static function damageList(array $values): string
    {
        return implode(", ", array_map(
            static fn($value): string => self::DAMAGE[(string) $value] ?? (string) $value,
            $values
        ));
    }

    public static function conditionList(array $values): string
    {
        return implode(", ", array_map(
            static fn($value): string => self::CONDITIONS[(string) $value] ?? (string) $value,
            $values
        ));
    }

    public static function languages(string $languages): string
    {
        if (trim($languages) === "") {
            return "";
        }

        $translated = str_ireplace(
            array_keys(self::LANGUAGE_PHRASES),
            array_values(self::LANGUAGE_PHRASES),
            $languages
        );

        $translated = str_ireplace(
            array_keys(self::LANGUAGES),
            array_values(self::LANGUAGES),
            $translated
        );

        return self::distance(str_ireplace(
            [", and ", " and "],
            [", y ", " y "],
            $translated
        ));
    }

    public static function distance($value): string
    {
        return preg_replace_callback(
            '/(\d+(?:\.\d+)?)\s*ft\.?/i',
            static function (array $matches): string {
                $metres = (float) $matches[1] * 0.3;
                $formatted = abs($metres - round($metres)) < 0.00001
                    ? (string) (int) round($metres)
                    : number_format($metres, 1, ",", "");

                return $formatted . " m";
            },
            (string) $value
        ) ?? (string) $value;
    }

    public static function senses(array $senses): string
    {
        $parts = [];
        foreach ($senses as $sense => $value) {
            $label = self::SENSES[(string) $sense] ?? (string) $sense;
            $parts[] = $label . " " . self::distance($value);
        }

        return implode(" · ", $parts);
    }
}
