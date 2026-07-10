import re
import json
import glob

with open('lang/si.json', encoding='utf-8') as f:
    locale_keys = set(json.load(f).keys())

blade_files = sorted(glob.glob('resources/views/**/*.blade.php', recursive=True))
used_keys = set()
plain_texts = []
for path in blade_files:
    with open(path, encoding='utf-8') as f:
        lines = f.readlines()
    for lineno, line in enumerate(lines, start=1):
        for m in re.finditer(r"__\(\s*(['\"])(.*?)\1", line):
            used_keys.add(m.group(2))
        if '>' in line and '<' in line and '__( ' not in line and '__(' not in line and '@lang' not in line and '@section' not in line:
            for text in re.findall(r'>([^<]+)<', line):
                text = text.strip()
                if text and re.search(r'[A-Za-z]', text) and not text.startswith('{{') and 'http' not in text and 'route(' not in text and '->' not in text and 'class=' not in text and 'href=' not in text:
                    cleaned = re.sub(r'\s+', ' ', text)
                    if len(cleaned) > 2 and not all(ch in '.,:;!?' for ch in cleaned):
                        plain_texts.append((path, lineno, cleaned))

missing_keys = sorted(k for k in used_keys if k not in locale_keys)
with open('analysis_missing_keys.txt', 'w', encoding='utf-8') as out:
    out.write(f'MISSING_TRANSLATION_KEYS: {len(missing_keys)}\n')
    for k in missing_keys:
        out.write(k + '\n')
    out.write('\nPLAIN_TEXTS:\n')
    for path, lineno, text in plain_texts:
        out.write(f'{path}:{lineno}:{text}\n')
print('analysis written to analysis_missing_keys.txt')
