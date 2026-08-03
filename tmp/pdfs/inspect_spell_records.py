import re
import sys
import unicodedata
from pathlib import Path


def folded(value: str) -> str:
    value = unicodedata.normalize("NFKD", value)
    return "".join(ch for ch in value if not unicodedata.combining(ch)).lower()


def compact(value: str) -> str:
    return re.sub(r"\s+", "", folded(value))


path = Path(sys.argv[1])
start_page = int(sys.argv[2])
end_page = int(sys.argv[3])
text = path.read_text(encoding="utf-8")
chunks = re.split(r"^===== PAGE (\d+) =====\s*$", text, flags=re.MULTILINE)
pages = {
    int(chunks[index]): chunks[index + 1]
    for index in range(1, len(chunks), 2)
}

candidates = []
for page_number in range(start_page, end_page + 1):
    lines = [line.strip() for line in pages.get(page_number, "").splitlines()]
    for index, line in enumerate(lines):
        if compact(line).startswith("tiempodelanzamiento"):
            context = []
            cursor = index - 1
            while cursor >= 0 and len(context) < 5:
                if lines[cursor]:
                    context.append(lines[cursor])
                cursor -= 1
            candidates.append((page_number, index, line, context))

print(f"{path.name}: {len(candidates)} metadata records")
for page_number, index, line, context in candidates:
    print(f"{page_number}:{index + 1}: {list(reversed(context))} -> {line}")
