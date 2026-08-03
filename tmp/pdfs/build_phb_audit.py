import json
import re
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent))

from build_spell_updates import (  # noqa: E402
    DOWNLOADS,
    ROOT,
    clean_line,
    clean_text,
    compact,
    field_value,
    folded,
    locate_school,
    school_and_level,
    split_duration_and_body,
)


OUTPUT = ROOT / "tmp" / "pdfs" / "phb_catalog.json"


def extracted_text_lines(first_page: int, last_page: int) -> list[str]:
    text_path = ROOT / "tmp" / "pdfs" / "phb.txt"
    text = text_path.read_text(encoding="utf-8")
    chunks = re.split(r"^===== PAGE (\d+) =====\s*$", text, flags=re.MULTILINE)
    pages = {
        int(chunks[index]): chunks[index + 1]
        for index in range(1, len(chunks), 2)
    }
    lines = []
    for page_number in range(first_page, last_page + 1):
        for raw_line in pages.get(page_number, "").splitlines():
            line = clean_line(raw_line)
            folded_line = folded(line)
            if not line:
                continue
            if "manual del jugador" in folded_line and "capitulo 11" in folded_line:
                continue
            if re.fullmatch(r"[∼~\-\d ]+", line):
                continue
            lines.append(line)
    return lines


def title_before_school(lines: list[str], school_index: int) -> tuple[str, str]:
    for count in range(1, 7):
        index = school_index - count
        if index < 0:
            break
        candidate = clean_text(lines[index:school_index])
        match = re.fullmatch(r"(.*?)\s*\[([^\[\]]+)\]\s*", candidate)
        if match:
            return match.group(1).strip(), match.group(2).strip()
    raise ValueError(
        f"No se encuentra el título antes de {lines[school_index]!r}: "
        f"{lines[max(0, school_index - 6):school_index]!r}"
    )


def parse_phb(lines: list[str]) -> list[dict]:
    time_indexes = [
        index
        for index, line in enumerate(lines)
        if compact(line).startswith("tiempodelanzamiento")
    ]
    records = []
    for record_number, time_index in enumerate(time_indexes, start=1):
        school_index, school_line = locate_school(lines, time_index)
        spanish_name, english_name = title_before_school(lines, school_index)
        school, level, ritual = school_and_level(school_line)

        positions = {"time": time_index}
        for cursor in range(time_index + 1, min(time_index + 22, len(lines))):
            key = compact(lines[cursor])
            if "range" not in positions and (
                key.startswith("alcance") or key.startswith("ajcance")
            ):
                positions["range"] = cursor
            elif "components" not in positions and (
                key.startswith("componente") or key.startswith("componenete")
            ):
                positions["components"] = cursor
            elif "duration" not in positions and key.startswith("duracion"):
                positions["duration"] = cursor
                break

        missing = {"range", "components", "duration"} - positions.keys()
        if missing:
            raise ValueError(
                f"Manual del Jugador #{record_number} ({english_name}): "
                f"faltan {sorted(missing)}"
            )

        casting_time = field_value(lines, positions["time"], positions["range"])
        spell_range = field_value(
            lines, positions["range"], positions["components"]
        )
        duration_raw = field_value(
            lines, positions["duration"], positions["duration"] + 1
        )
        duration, _ = split_duration_and_body(duration_raw)
        concentration = "si" if "concentr" in folded(duration) else "no"
        duration = re.sub(
            r"^Concentraci[oó]n,\s*", "", duration, flags=re.IGNORECASE
        )
        if duration:
            duration = duration[0].upper() + duration[1:]

        records.append(
            {
                "name": f"{spanish_name} ({english_name})",
                "spanish_name": spanish_name,
                "canonical_name": english_name,
                "duracion": duration,
                "concentracion": concentration,
                "casteo": casting_time,
                "level": level,
                "rango": spell_range,
                "escuela": school,
                "ritual": ritual,
                "source": "Manual del Jugador",
            }
        )
    return records


def main() -> None:
    lines = extracted_text_lines(178, 237)
    records = parse_phb(lines)
    canonical_names = [folded(record["canonical_name"]) for record in records]
    duplicates = sorted(
        {
            name
            for name in canonical_names
            if canonical_names.count(name) > 1
        }
    )
    if duplicates:
        raise ValueError(f"Nombres canónicos duplicados en el manual: {duplicates}")
    OUTPUT.write_text(
        json.dumps(records, ensure_ascii=False, indent=2),
        encoding="utf-8",
    )

    local_data = json.loads(
        (ROOT / "data" / "spells.json").read_text(encoding="utf-8")
    )
    local_phb = [
        item for item in local_data if str(item.get("page", "")).startswith("phb")
    ]
    unnamed_map = {
        "Rayo de bruja": "Witch Bolt",
        "Golpe iracundo": "Wrathful Smite",
        "Zona de verdad": "Zone of Truth",
        "": "Revivify",
        "Palabra de retorno": "Word of Recall",
    }
    canonical_aliases = {
        "adivinacion": "divination",
        "druidacraft": "druidcraft",
        "grasping vines": "grasping vine",
        "word recall": "word of recall",
    }

    def normalized_canonical(value: str) -> str:
        value = value.replace("’", "'").replace("‘", "'").strip().lower()
        return canonical_aliases.get(value, value)

    local_index = {}
    for item in local_phb:
        match = re.search(r"\(([^()]*)\)\s*$", item["name"])
        canonical = match.group(1) if match else unnamed_map.get(item["name"], item["name"])
        local_index.setdefault(normalized_canonical(canonical), []).append(item)

    pdf_index = {
        normalized_canonical(item["canonical_name"]): item for item in records
    }
    missing_local = sorted(
        item["canonical_name"]
        for key, item in pdf_index.items()
        if key not in local_index
    )
    extra_local = sorted(
        rows[0]["name"]
        for key, rows in local_index.items()
        if key not in pdf_index
    )
    structural_differences = []
    for key, pdf_item in pdf_index.items():
        local_rows = local_index.get(key, [])
        if not local_rows:
            continue
        local_item = local_rows[0]
        for local_field, pdf_field in (("level", "level"), ("school", "escuela")):
            if str(local_item[local_field]) != str(pdf_item[pdf_field]):
                structural_differences.append(
                    {
                        "canonical_name": pdf_item["canonical_name"],
                        "field": local_field,
                        "local": local_item[local_field],
                        "book": pdf_item[pdf_field],
                    }
                )

    print(
        json.dumps(
            {
                "output": str(OUTPUT),
                "records": len(records),
                "duplicates": len(duplicates),
                "missing_local": missing_local,
                "extra_local": extra_local,
                "structural_differences": structural_differences,
            },
            ensure_ascii=False,
        )
    )


if __name__ == "__main__":
    main()
