from pathlib import Path
from pypdf import PdfReader


SOURCES = [
    (
        Path(r"C:\Users\Pablohr11\Downloads\manual del jugador dnd Pero el cutre.pdf"),
        Path(r"C:\Users\Pablohr11\Documents\GitHub\DeepRol\tmp\pdfs\phb.txt"),
    ),
    (
        Path(r"C:\Users\Pablohr11\Downloads\El caldero de Tasha para todo.pdf"),
        Path(r"C:\Users\Pablohr11\Documents\GitHub\DeepRol\tmp\pdfs\tasha.txt"),
    ),
    (
        Path(r"C:\Users\Pablohr11\Downloads\Guia de Xanathar Para Todo.pdf"),
        Path(r"C:\Users\Pablohr11\Documents\GitHub\DeepRol\tmp\pdfs\xanathar.txt"),
    ),
]


for source, destination in SOURCES:
    if destination.exists() and destination.stat().st_size > 0:
        print(f"{source.name}\tskipped_existing={destination.name}", flush=True)
        continue

    reader = PdfReader(source)
    if reader.is_encrypted:
        reader.decrypt("")

    extracted_pages = []
    extracted_characters = 0
    for page_number, page in enumerate(reader.pages, start=1):
        text = page.extract_text() or ""
        extracted_characters += len(text)
        extracted_pages.append(f"\n===== PAGE {page_number} =====\n{text}")
        if page_number % 25 == 0:
            print(
                f"{source.name}\tprogress={page_number}/{len(reader.pages)}",
                flush=True,
            )

    destination.write_text("".join(extracted_pages), encoding="utf-8")
    print(
        f"{source.name}\tpages={len(reader.pages)}\t"
        f"characters={extracted_characters}\toutput={destination.name}",
        flush=True,
    )
