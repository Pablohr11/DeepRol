"""Build DeepRol's empty, fillable character-sheet template.

Run this script from the project root after installing pypdf:

    python scripts/build_character_template.py
"""

from pathlib import Path

from pypdf import PdfReader, PdfWriter


PROJECT_ROOT = Path(__file__).resolve().parents[1]
SOURCE_PDF = PROJECT_ROOT / "resources" / "chars" / "Draelith" / "ficha.pdf"
TARGET_PDF = PROJECT_ROOT / "resources" / "templates" / "ficha-personaje.pdf"
PUSH_BUTTON_FLAG = 1 << 16


def build_template() -> None:
    reader = PdfReader(str(SOURCE_PDF))
    fields = reader.get_fields() or {}

    if not fields:
        raise RuntimeError(f"{SOURCE_PDF} no contiene campos AcroForm.")

    writer = PdfWriter()
    writer.clone_document_from_reader(reader)

    empty_values: dict[str, str] = {}
    for name, field in fields.items():
        field_type = field.get("/FT")
        flags = int(field.get("/Ff", 0) or 0)

        if field_type == "/Tx":
            empty_values[name] = ""
        elif field_type == "/Btn" and not flags & PUSH_BUTTON_FLAG:
            empty_values[name] = "/Off"

    writer.update_page_form_field_values(
        None,
        empty_values,
        auto_regenerate=False,
    )

    TARGET_PDF.parent.mkdir(parents=True, exist_ok=True)
    with TARGET_PDF.open("wb") as output_stream:
        writer.write(output_stream)

    result = PdfReader(str(TARGET_PDF))
    result_fields = result.get_fields() or {}
    missing_fields = set(fields) - set(result_fields)
    if missing_fields:
        raise RuntimeError(
            "La plantilla perdió campos: " + ", ".join(sorted(missing_fields))
        )

    uncleared_text_fields = {
        name: field.get("/V")
        for name, field in result_fields.items()
        if field.get("/FT") == "/Tx" and field.get("/V") not in (None, "")
    }
    if uncleared_text_fields:
        raise RuntimeError(
            "Quedaron campos de texto con datos: "
            + ", ".join(sorted(uncleared_text_fields))
        )

    widgets = 0
    for page in result.pages:
        for annotation_ref in page.get("/Annots", []):
            annotation = annotation_ref.get_object()
            if annotation.get("/Subtype") == "/Widget":
                widgets += 1

    if widgets == 0:
        raise RuntimeError("La plantilla perdió los widgets interactivos.")

    print(
        f"Plantilla creada: {TARGET_PDF} "
        f"({len(result.pages)} páginas, {len(result_fields)} campos, {widgets} widgets)"
    )


if __name__ == "__main__":
    build_template()
