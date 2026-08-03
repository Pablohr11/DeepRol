import json
import re
import unicodedata
from difflib import SequenceMatcher
from pathlib import Path

import pdfplumber


ROOT = Path(__file__).resolve().parents[2]
DOWNLOADS = Path(r"C:\Users\Pablohr11\Downloads")
OUTPUT = ROOT / "tmp" / "pdfs" / "catalog_updates.json"

SCHOOL_NAMES = {
    "abjur": "Abjuracion",
    "adivin": "Adivinacion",
    "conjur": "Conjuracion",
    "encant": "Encantamiento",
    "evoca": "Evocacion",
    "ilusi": "Ilusion",
    "nigrom": "Nigromancia",
    "transm": "Transmutacion",
}

CLASS_NAMES = {
    "bard": "Bardo",
    "cleric": "Clerigo",
    "druid": "Druida",
    "paladin": "Paladin",
    "ranger": "Explorador",
    "sorcerer": "Hechicero",
    "warlock": "Brujo",
    "wizard": "Mago",
    "artificer": "Artifice",
}


def folded(value: str) -> str:
    value = unicodedata.normalize("NFKD", value.replace("\xad", ""))
    return "".join(ch for ch in value if not unicodedata.combining(ch)).lower()


def tokenized(value: str) -> str:
    return re.sub(r"[^a-z0-9]+", "", folded(value))


def compact(value: str) -> str:
    return re.sub(r"\s+", "", folded(value))


def clean_line(value: str) -> str:
    value = unicodedata.normalize("NFKC", value.replace("\xad", ""))
    return re.sub(r"\s+", " ", value).strip()


def clean_text(lines: list[str]) -> str:
    lines = [clean_line(line) for line in lines if clean_line(line)]
    value = "\n".join(lines)
    value = re.sub(r"(?<=\w)-\n(?=\w)", "", value)
    value = value.replace("\n•", "\n• ")
    value = value.replace("•\n", "• ")
    value = value.replace("\n", " ")
    value = re.sub(r"\bld(?=\d)", "1d", value, flags=re.IGNORECASE)
    value = re.sub(r"\bnivel\s*l\s*l\b", "nivel 11", value, flags=re.IGNORECASE)
    value = re.sub(r"\bnivel(?=\d)", "nivel ", value, flags=re.IGNORECASE)
    replacements = {
        "dai'lo": "daño",
        "espfritu": "espíritu",
        "diffcil": "difícil",
        "lncorp6reo": "Incorpóreo",
        "lncorpóreo": "Incorpóreo",
        "Desafio": "Desafío",
        "lSO pies": "150 pies",
        "1 O": "10",
        "+e l": "+ el",
        "S pies": "5 pies",
    }
    for source, target in replacements.items():
        value = value.replace(source, target)
    value = re.sub(r"\s+([,.;:])", r"\1", value)
    value = re.sub(r"([,.;:])(?=[A-Za-zÁÉÍÓÚÜÑáéíóúüñ])", r"\1 ", value)
    value = re.sub(r"\s+", " ", value)
    return value.strip()


def extract_book_lines(pdf_path: Path, first_page: int, last_page: int) -> list[str]:
    lines: list[str] = []
    with pdfplumber.open(pdf_path) as document:
        for page_number in range(first_page, last_page + 1):
            page = document.pages[page_number - 1]
            for column in range(2):
                left = page.width * column / 2
                right = page.width * (column + 1) / 2
                column_text = page.crop((left, 0, right, page.height)).extract_text() or ""
                for raw_line in column_text.splitlines():
                    line = clean_line(raw_line)
                    if not line:
                        continue
                    fragments = re.split(
                        r"(?=(?:Tiempo\s+de\s+lanzamiento|Alcance|Componentes|Duraci[oó]n)\s*:)",
                        line,
                        flags=re.IGNORECASE,
                    )
                    for fragment in fragments:
                        fragment = clean_line(fragment)
                        if not fragment:
                            continue
                        folded_line = folded(fragment)
                        if "capitulo 3" in folded_line and "conjuro" in folded_line:
                            continue
                        if "capitulo 3" in folded_line and "miscelanea magica" in folded_line:
                            continue
                        if re.fullmatch(r"[-—_ ]+", fragment):
                            continue
                        lines.append(fragment)
    return lines


def field_value(lines: list[str], start: int, end: int) -> str:
    value = " ".join(lines[start:end])
    if ":" in value:
        value = value.split(":", 1)[1]
    elif "." in value:
        value = value.split(".", 1)[1]
    return clean_text([value])


def locate_title_start(lines: list[str], school_index: int, expected_title: str) -> int:
    expected = tokenized(expected_title)
    best_score = 0.0
    best_index = school_index
    for count in range(1, 5):
        index = school_index - count
        if index < 0:
            break
        candidate = tokenized(" ".join(lines[index:school_index]))
        score = SequenceMatcher(None, candidate, expected).ratio()
        if score > best_score:
            best_score = score
            best_index = index
    return best_index if best_score >= 0.58 else school_index


def split_duration_and_body(value: str) -> tuple[str, str]:
    candidates = [
        r"Concentraci[oó]n,\s*hasta\s+\d+\s+(?:asaltos?|rondas?|minutos?|horas?)\.?",
        r"Concentraci[oó]n,\s*hasta\s+que\s+[^.;]+",
        r"Hasta\s+que\s+(?:sea\s+)?disipad[oa]",
        r"Hasta\s+\d+\s+(?:asaltos?|rondas?|minutos?|horas?)\.?",
        r"\d+\s+(?:asaltos?|rondas?|minutos?|horas?|d[ií]as?)\.?",
        r"Instant[aá]ne[oa]",
        r"Especial",
        r"Permanente",
    ]
    for pattern in candidates:
        match = re.match(pattern, value, flags=re.IGNORECASE)
        if match:
            return (
                clean_text([match.group(0)]).rstrip("."),
                value[match.end():].strip(" ."),
            )
    words = value.split()
    return clean_text([" ".join(words[:4])]), " ".join(words[4:]).strip()


def school_and_level(school_line: str) -> tuple[str, str, bool]:
    folded_line = folded(school_line)
    school = next((name for key, name in SCHOOL_NAMES.items() if key in folded_line), "")
    if not school:
        raise ValueError(f"No se reconoce la escuela en: {school_line!r}")
    if "truco" in folded_line:
        level = "Truco"
    else:
        level_match = re.search(r"([1-9])", folded_line)
        if not level_match:
            raise ValueError(f"No se reconoce el nivel en: {school_line!r}")
        level = f"Nivel {level_match.group(1)}"
    return school, level, "ritual" in folded_line


def locate_school(lines: list[str], time_index: int) -> tuple[int, str]:
    for lookback in range(1, 5):
        index = time_index - lookback
        candidate = " ".join(lines[index:time_index])
        folded_candidate = folded(candidate)
        if any(key in folded_candidate for key in SCHOOL_NAMES):
            return index, candidate
    raise ValueError(
        f"No se encuentra la escuela antes de {lines[time_index]!r}: "
        f"{lines[max(0, time_index - 4):time_index]!r}"
    )


def parse_records(
    lines: list[str],
    expected: list[dict],
    book: str,
) -> list[dict]:
    time_indexes = [
        index
        for index, line in enumerate(lines)
        if compact(line).startswith("tiempodelanzamiento")
    ]
    if len(time_indexes) != len(expected):
        raise ValueError(
            f"{book}: se esperaban {len(expected)} conjuros y se detectaron "
            f"{len(time_indexes)} bloques"
        )

    records: list[dict] = []
    for record_index, time_index in enumerate(time_indexes):
        metadata_indexes: dict[str, int] = {"time": time_index}
        for cursor in range(time_index + 1, min(time_index + 18, len(lines))):
            line_key = compact(lines[cursor])
            if "range" not in metadata_indexes and (
                line_key.startswith("alcance") or line_key.startswith("ajcance")
            ):
                metadata_indexes["range"] = cursor
            elif "components" not in metadata_indexes and line_key.startswith("componentes"):
                metadata_indexes["components"] = cursor
            elif "duration" not in metadata_indexes and line_key.startswith("duracion"):
                metadata_indexes["duration"] = cursor
                break

        missing = {"range", "components", "duration"} - metadata_indexes.keys()
        if missing:
            raise ValueError(
                f"{book} #{record_index + 1}: faltan {sorted(missing)} cerca de "
                f"{lines[time_index]!r}"
            )

        school_index, school_line = locate_school(lines, time_index)
        school, level, ritual = school_and_level(school_line)
        casting_time = field_value(
            lines, metadata_indexes["time"], metadata_indexes["range"]
        )
        spell_range = field_value(
            lines, metadata_indexes["range"], metadata_indexes["components"]
        )
        spell_range = re.sub(r"(?<=\d)\s+(?=\d)", "", spell_range)
        if folded(spell_range) == "yo":
            spell_range = "Lanzador"

        if record_index + 1 < len(time_indexes):
            next_school_index, _ = locate_school(
                lines, time_indexes[record_index + 1]
            )
            body_end = locate_title_start(
                lines, next_school_index, expected[record_index + 1]["es"]
            )
        else:
            body_end = len(lines)

        duration_line_index = metadata_indexes["duration"]
        raw_duration = field_value(lines, duration_line_index, duration_line_index + 1)
        duration, inline_body = split_duration_and_body(raw_duration)
        body_lines = lines[duration_line_index + 1:body_end]
        if inline_body:
            body_lines.insert(0, inline_body)
        description = clean_text(body_lines)

        concentration = "si" if "concentr" in folded(duration) else "no"
        duration = re.sub(
            r"^Concentraci[oó]n,\s*", "", duration, flags=re.IGNORECASE
        )
        if duration:
            duration = duration[0].upper() + duration[1:]

        item = expected[record_index]
        classes = [CLASS_NAMES[key] for key in item["classes"]]
        if ritual:
            classes.append("Lanzador de Rituales")

        records.append(
            {
                "name": f'{item["es"]} ({item["en"]})',
                "canonical_name": item["en"],
                "descr": description,
                "duracion": duration,
                "concentracion": concentration,
                "casteo": casting_time,
                "level": level,
                "rango": spell_range,
                "clases": ", ".join(classes),
                "escuela": school,
                "source": book,
            }
        )
    return records


def entries(names: list[tuple[str, str]], class_map: dict[str, list[str]]) -> list[dict]:
    return [
        {"es": spanish, "en": english, "classes": class_map.get(english, [])}
        for spanish, english in names
    ]


XGE_NAMES = [
    ("Abrasador de Aganazzar", "Aganazzar's Scorcher"),
    ("Absorber elementos", "Absorb Elements"),
    ("Agarre de tierra de Maximiliano", "Maximilian's Earthen Grasp"),
    ("Aliento del dragón", "Dragon's Breath"),
    ("Amanecer", "Dawn"),
    ("Arboleda druida", "Druid Grove"),
    ("Arma sagrada", "Holy Weapon"),
    ("Atar a la tierra", "Earthbind"),
    ("Catapulta", "Catapult"),
    ("Causar miedo", "Cause Fear"),
    ("Ceremonia", "Ceremony"),
    ("Congelación", "Frostbite"),
    ("Controlar llamas", "Control Flames"),
    ("Controlar vientos", "Control Winds"),
    ("Corona de estrellas", "Crown of Stars"),
    ("Crear hoguera", "Create Bonfire"),
    ("Crear homúnculo", "Create Homunculus"),
    ("Cuchillo de hielo", "Ice Knife"),
    ("Custodia primordial", "Primordial Ward"),
    ("Danza macabra", "Danse Macabre"),
    ("Diablo de polvo", "Dust Devil"),
    ("Dispersión", "Scatter"),
    ("Dragón ilusorio", "Illusory Dragon"),
    ("Empoderar habilidades", "Skill Empowerment"),
    ("Enemigos abundantes", "Enemies Abound"),
    ("Enervación", "Enervation"),
    ("Escritura celeste", "Skywrite"),
    ("Esfera acuosa", "Watery Sphere"),
    ("Esfera de tormenta", "Storm Sphere"),
    ("Esfera vitriólica", "Vitriolic Sphere"),
    ("Espina mental", "Mind Spike"),
    ("Espíritu curativo", "Healing Spirit"),
    ("Estática sináptica", "Synaptic Static"),
    ("Flechas flamígeras", "Flame Arrows"),
    ("Fortaleza poderosa", "Mighty Fortress"),
    ("Golpe de céfiro", "Zephyr Strike"),
    ("Golpe de viento de acero", "Steel Wind Strike"),
    ("Grito psíquico", "Psychic Scream"),
    ("Guardián de la naturaleza", "Guardian of Nature"),
    ("Encontrar corcel mayor", "Find Greater Steed"),
    ("Hechizar monstruo", "Charm Monster"),
    ("Hoja de sombra", "Shadow Blade"),
    ("Huesos de la tierra", "Bones of the Earth"),
    ("Infestación", "Infestation"),
    ("Inmolación", "Immolation"),
    ("Inundación de energía negativa", "Negative Energy Flood"),
    ("Investidura de hielo", "Investiture of Ice"),
    ("Investidura de llamas", "Investiture of Flame"),
    ("Investidura de piedra", "Investiture of Stone"),
    ("Investidura de viento", "Investiture of Wind"),
    ("Invocar demonio mayor", "Summon Greater Demon"),
    ("Invocar demonios menores", "Summon Lesser Demons"),
    ("Invulnerabilidad", "Invulnerability"),
    ("Ira de la naturaleza", "Wrath of Nature"),
    ("Jaula de alma", "Soul Cage"),
    ("Llamada infernal", "Infernal Calling"),
    ("Marchitamiento horrible de Abi-Dalzim", "Abi-Dalzim's Horrid Wilting"),
    ("Meteoros diminutos de Melf", "Melf's Minute Meteors"),
    ("Moldear tierra", "Mold Earth"),
    ("Muro de arena", "Wall of Sand"),
    ("Muro de agua", "Wall of Water"),
    ("Muro de luz", "Wall of Light"),
    ("Ola de marea", "Tidal Wave"),
    ("Oscuridad enloquecedora", "Maddening Darkness"),
    ("Palabra de poder: dolor", "Power Word Pain"),
    ("Palabra de resplandor", "Word of Radiance"),
    ("Palmada atronadora", "Thunderclap"),
    ("Paso de trueno", "Thunder Step"),
    ("Paso lejano", "Far Step"),
    ("Perdición elemental", "Elemental Bane"),
    ("Piedra mágica", "Magic Stone"),
    ("Pirotecnia", "Pyrotechnics"),
    ("Polimorfar en grupo", "Mass Polymorph"),
    ("Prisión mental", "Mental Prison"),
    ("Ráfaga", "Gust"),
    ("Resplandor enfermizo", "Sickening Radiance"),
    ("Salvajismo primigenio", "Primal Savagery"),
    ("Servidor minúsculo", "Tiny Servant"),
    ("Siestecita", "Catnap"),
    ("Sombra de Moil", "Shadow of Moil"),
    ("Temblor de tierra", "Earth Tremor"),
    ("Templo de los dioses", "Temple of the Gods"),
    ("Tierra en erupción", "Erupting Earth"),
    ("Tocar a los muertos", "Toll the Dead"),
    ("Torbellino", "Whirlwind"),
    ("Tormenta de bolas de nieve de Snilloc", "Snilloc's Snowball Swarm"),
    ("Trampa de lazo", "Snare"),
    ("Transferencia de vida", "Life Transference"),
    ("Transformación de Tenser", "Tenser's Transformation"),
    ("Transformar agua", "Shape Water"),
    ("Transmutar roca", "Transmute Rock"),
    ("Vorágine", "Maelstrom"),
    ("Viento de salvaguardia", "Warding Wind"),
    ("Vínculo bestial", "Beast Bond"),
    ("Virote de caos", "Chaos Bolt"),
]

XGE_CLASS_LISTS = {
    "bard": [
        "Thunderclap", "Earth Tremor", "Skywrite", "Pyrotechnics",
        "Warding Wind", "Enemies Abound", "Catnap", "Charm Monster",
        "Skill Empowerment", "Synaptic Static", "Psychic Scream",
        "Mass Polymorph",
    ],
    "warlock": [
        "Frostbite", "Create Bonfire", "Infestation", "Magic Stone",
        "Thunderclap", "Toll the Dead", "Cause Fear", "Earthbind",
        "Mind Spike", "Shadow Blade", "Enemies Abound",
        "Summon Lesser Demons", "Thunder Step", "Charm Monster",
        "Summon Greater Demon", "Elemental Bane", "Sickening Radiance",
        "Shadow of Moil", "Danse Macabre", "Enervation",
        "Synaptic Static", "Negative Energy Flood", "Infernal Calling",
        "Wall of Light", "Far Step", "Investiture of Ice",
        "Investiture of Flame", "Investiture of Stone",
        "Investiture of Wind", "Mental Prison", "Scatter", "Soul Cage",
        "Crown of Stars", "Power Word Pain", "Maddening Darkness",
        "Psychic Scream",
    ],
    "cleric": [
        "Word of Radiance", "Toll the Dead", "Ceremony",
        "Life Transference", "Dawn", "Holy Weapon", "Temple of the Gods",
    ],
    "druid": [
        "Control Flames", "Create Bonfire", "Frostbite", "Infestation",
        "Mold Earth", "Thunderclap", "Magic Stone", "Gust",
        "Primal Savagery", "Shape Water", "Absorb Elements", "Ice Knife",
        "Earth Tremor", "Snare", "Beast Bond", "Earthbind", "Dust Devil",
        "Skywrite", "Healing Spirit", "Warding Wind", "Flame Arrows",
        "Wall of Water", "Tidal Wave", "Erupting Earth", "Watery Sphere",
        "Guardian of Nature", "Charm Monster", "Elemental Bane",
        "Control Winds", "Wrath of Nature", "Transmute Rock", "Maelstrom",
        "Druid Grove", "Primordial Ward", "Bones of the Earth",
        "Investiture of Ice", "Investiture of Flame",
        "Investiture of Stone", "Investiture of Wind", "Whirlwind",
    ],
    "ranger": [
        "Absorb Elements", "Zephyr Strike", "Snare", "Beast Bond",
        "Healing Spirit", "Flame Arrows", "Guardian of Nature",
        "Steel Wind Strike", "Wrath of Nature",
    ],
    "sorcerer": [
        "Frostbite", "Control Flames", "Create Bonfire", "Infestation",
        "Mold Earth", "Thunderclap", "Gust", "Shape Water",
        "Absorb Elements", "Catapult", "Ice Knife", "Chaos Bolt",
        "Earth Tremor", "Aganazzar's Scorcher",
        "Maximilian's Earthen Grasp", "Dragon's Breath", "Earthbind",
        "Dust Devil", "Mind Spike", "Shadow Blade", "Pyrotechnics",
        "Snilloc's Snowball Swarm", "Warding Wind", "Enemies Abound",
        "Flame Arrows", "Melf's Minute Meteors", "Wall of Water",
        "Tidal Wave", "Thunder Step", "Catnap", "Erupting Earth",
        "Watery Sphere", "Storm Sphere", "Vitriolic Sphere",
        "Charm Monster", "Sickening Radiance", "Control Winds",
        "Skill Empowerment", "Enervation", "Synaptic Static",
        "Immolation", "Wall of Light", "Far Step", "Scatter",
        "Investiture of Ice", "Investiture of Flame",
        "Investiture of Stone", "Investiture of Wind", "Mental Prison",
        "Crown of Stars", "Power Word Pain", "Whirlwind",
        "Abi-Dalzim's Horrid Wilting", "Psychic Scream", "Mass Polymorph",
    ],
    "wizard": [
        "Frostbite", "Control Flames", "Create Bonfire", "Infestation",
        "Mold Earth", "Gust", "Toll the Dead", "Shape Water",
        "Thunderclap", "Absorb Elements", "Catapult", "Cause Fear",
        "Ice Knife", "Earth Tremor", "Snare", "Aganazzar's Scorcher",
        "Maximilian's Earthen Grasp", "Dragon's Breath", "Earthbind",
        "Dust Devil", "Skywrite", "Mind Spike", "Shadow Blade",
        "Pyrotechnics", "Snilloc's Snowball Swarm", "Warding Wind",
        "Enemies Abound", "Flame Arrows", "Summon Lesser Demons",
        "Wall of Sand", "Wall of Water", "Melf's Minute Meteors",
        "Tidal Wave", "Thunder Step", "Tiny Servant", "Catnap",
        "Erupting Earth", "Life Transference", "Watery Sphere",
        "Storm Sphere", "Vitriolic Sphere", "Charm Monster",
        "Summon Greater Demon", "Elemental Bane", "Sickening Radiance",
        "Dawn", "Control Winds", "Danse Macabre", "Skill Empowerment",
        "Enervation", "Synaptic Static", "Steel Wind Strike",
        "Immolation", "Negative Energy Flood", "Infernal Calling",
        "Wall of Light", "Far Step", "Transmute Rock",
        "Create Homunculus", "Scatter", "Investiture of Ice",
        "Investiture of Flame", "Investiture of Stone",
        "Investiture of Wind", "Soul Cage", "Mental Prison",
        "Tenser's Transformation", "Crown of Stars", "Power Word Pain",
        "Whirlwind", "Illusory Dragon", "Mighty Fortress",
        "Abi-Dalzim's Horrid Wilting", "Maddening Darkness",
        "Psychic Scream", "Invulnerability", "Mass Polymorph",
    ],
    "paladin": ["Ceremony", "Find Greater Steed", "Holy Weapon"],
}

XGE_CLASSES: dict[str, list[str]] = {english: [] for _, english in XGE_NAMES}
for class_name, spell_names in XGE_CLASS_LISTS.items():
    for spell_name in spell_names:
        XGE_CLASSES[spell_name].append(class_name)

TASHA_NAMES = [
    ("Apariencia espectral de Tasha", "Tasha's Otherworldly Guise"),
    ("Atracción del relámpago", "Lightning Lure"),
    ("Brebaje cáustico de Tasha", "Tasha's Caustic Brew"),
    ("Estallido de espadas", "Sword Burst"),
    ("Filo atronador", "Booming Blade"),
    ("Filo de llamas verdes", "Green-Flame Blade"),
    ("Fortaleza del intelecto", "Intellect Fortress"),
    ("Fragmento mental", "Mind Sliver"),
    ("Hoja de desastre", "Blade of Disaster"),
    ("Invocar aberración", "Summon Aberration"),
    ("Invocar autómata", "Summon Construct"),
    ("Invocar bestia", "Summon Beast"),
    ("Invocar celestial", "Summon Celestial"),
    ("Invocar elemental", "Summon Elemental"),
    ("Invocar engendro de las sombras", "Summon Shadowspawn"),
    ("Invocar feérico", "Summon Fey"),
    ("Invocar infernal", "Summon Fiend"),
    ("Invocar muerto viviente", "Summon Undead"),
    ("Látigo mental de Tasha", "Tasha's Mind Whip"),
    ("Mortaja espiritual", "Spirit Shroud"),
    ("Sueño del velo azul", "Dream of the Blue Veil"),
]

TASHA_CLASSES = {
    "Tasha's Otherworldly Guise": ["sorcerer", "warlock", "wizard"],
    "Lightning Lure": ["artificer", "sorcerer", "warlock", "wizard"],
    "Tasha's Caustic Brew": ["artificer", "sorcerer", "wizard"],
    "Sword Burst": ["artificer", "sorcerer", "warlock", "wizard"],
    "Booming Blade": ["artificer", "sorcerer", "warlock", "wizard"],
    "Green-Flame Blade": ["artificer", "sorcerer", "warlock", "wizard"],
    "Intellect Fortress": ["artificer", "bard", "sorcerer", "warlock", "wizard"],
    "Mind Sliver": ["sorcerer", "warlock", "wizard"],
    "Blade of Disaster": ["sorcerer", "warlock", "wizard"],
    "Summon Aberration": ["warlock", "wizard"],
    "Summon Construct": ["artificer", "wizard"],
    "Summon Beast": ["druid", "ranger"],
    "Summon Celestial": ["cleric", "paladin"],
    "Summon Elemental": ["druid", "ranger", "wizard"],
    "Summon Shadowspawn": ["warlock", "wizard"],
    "Summon Fey": ["druid", "ranger", "warlock", "wizard"],
    "Summon Fiend": ["warlock", "wizard"],
    "Summon Undead": ["warlock", "wizard"],
    "Tasha's Mind Whip": ["sorcerer", "wizard"],
    "Spirit Shroud": ["cleric", "paladin", "warlock", "wizard"],
    "Dream of the Blue Veil": ["bard", "sorcerer", "warlock", "wizard"],
}


def repair_cross_column_content(records: list[dict]) -> None:
    by_name = {record["canonical_name"]: record for record in records}

    spirit = by_name["Spirit Shroud"]
    stat_block_marker = "ESPÍRITU MUERTO VIVIENTE"
    spirit_tail_marker = "Hasta que el conjuro termine"
    sidebar_marker = "VIAJAR A OTROS MUNDOS"
    full_spirit = spirit["descr"]
    stat_start = full_spirit.find(stat_block_marker)
    tail_start = full_spirit.find(spirit_tail_marker)
    sidebar_start = full_spirit.find(sidebar_marker)
    if not (0 < stat_start < tail_start < sidebar_start):
        raise ValueError("No se pudo separar el contenido cruzado de Mortaja espiritual")

    summon_undead = by_name["Summon Undead"]
    summon_undead["descr"] = clean_text(
        [summon_undead["descr"], full_spirit[stat_start:tail_start]]
    )
    spirit["descr"] = clean_text(
        [full_spirit[:stat_start], full_spirit[tail_start:sidebar_start]]
    )

    dream = by_name["Dream of the Blue Veil"]
    dream["descr"] = clean_text(
        [dream["descr"].split("PERSONALIZACIÓN DE CONJUROS", 1)[0]]
    )

    chaos_bolt = by_name["Chaos Bolt"]
    chaos_bolt["descr"] = clean_text(
        [chaos_bolt["descr"].split("Apéndice A:", 1)[0]]
    )


def validate(records: list[dict]) -> None:
    canonical_names = [record["canonical_name"] for record in records]
    if len(canonical_names) != len(set(canonical_names)):
        raise ValueError("El catálogo combinado contiene nombres canónicos duplicados")
    for record in records:
        missing = [
            key
            for key in (
                "name", "descr", "duracion", "concentracion", "casteo",
                "level", "rango", "clases", "escuela",
            )
            if not record.get(key)
        ]
        if missing:
            raise ValueError(f'{record["name"]}: faltan campos {missing}')
        if len(record["descr"]) < 40:
            raise ValueError(
                f'{record["name"]}: descripción sospechosamente corta '
                f'({len(record["descr"])} caracteres)'
            )


def main() -> None:
    if len(XGE_NAMES) != 95:
        raise ValueError(f"El catálogo de Xanathar tiene {len(XGE_NAMES)} nombres")
    if len(TASHA_NAMES) != 21:
        raise ValueError(f"El catálogo de Tasha tiene {len(TASHA_NAMES)} nombres")

    xge_lines = extract_book_lines(
        DOWNLOADS / "Guia de Xanathar Para Todo.pdf", 155, 179
    )
    tasha_lines = extract_book_lines(
        DOWNLOADS / "El caldero de Tasha para todo.pdf", 108, 118
    )
    xge_records = parse_records(
        xge_lines, entries(XGE_NAMES, XGE_CLASSES), "Xanathar"
    )
    tasha_records = parse_records(
        tasha_lines, entries(TASHA_NAMES, TASHA_CLASSES), "Tasha"
    )
    records = xge_records + tasha_records
    repair_cross_column_content(records)
    validate(records)
    OUTPUT.write_text(
        json.dumps(records, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )
    print(
        json.dumps(
            {
                "output": str(OUTPUT),
                "xanathar": len(xge_records),
                "tasha": len(tasha_records),
                "total": len(records),
                "min_description": min(len(item["descr"]) for item in records),
                "max_description": max(len(item["descr"]) for item in records),
            },
            ensure_ascii=False,
        )
    )


if __name__ == "__main__":
    main()
