import sys
from pathlib import Path

import pdfplumber


pdf_path = Path(sys.argv[1])
first_page = int(sys.argv[2])
last_page = int(sys.argv[3])
columns = int(sys.argv[4])

with pdfplumber.open(pdf_path) as document:
    for page_number in range(first_page, last_page + 1):
        page = document.pages[page_number - 1]
        print(f"===== PDF PAGE {page_number} =====")
        for column in range(columns):
            left = page.width * column / columns
            right = page.width * (column + 1) / columns
            text = page.crop((left, 0, right, page.height)).extract_text() or ""
            print(f"----- COLUMN {column + 1} -----")
            print(text)
