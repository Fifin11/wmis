import re, json, glob, os

keys = set()
for path in glob.glob('resources/views/**/*.blade.php', recursive=True):
    with open(path, encoding='utf-8') as f:
        text = f.read()
    for m in re.finditer(r"__\(\s*(['\"])(.*?)\1", text):
        keys.add(m.group(2))

json_keys = set(json.loads(open('lang/si.json', encoding='utf-8').read()).keys())
missing = sorted(k for k in keys if k not in json_keys)
print('MISSING_TRANSLATION_KEYS:', len(missing))
for k in missing:
    print(k)
print('---')

pattern = re.compile(r'>([^<]+)<')
for path in glob.glob('resources/views/**/*.blade.php', recursive=True):
    with open(path, encoding='utf-8') as f:
        for i, line in enumerate(f, start=1):
            if '__( ' in line or '__(' in line or '@lang' in line or '@section' in line or '@foreach' in line or '@if' in line or '@endif' in line or '@endforeach' in line or '@end' in line:
                continue
            if '>' in line and '<' in line:
                for m in pattern.finditer(line):
                    text = m.group(1).strip()
                    if text and re.search(r'[A-Za-z]', text) and not text.startswith('{{') and 'http' not in text and 'route(' not in text and '->' not in text and 'class=' not in text and 'href=' not in text:
                        cleaned = re.sub(r'\s+', ' ', text)
                        if cleaned and len(cleaned) > 2 and not all(ch in '.,:;!?' for ch in cleaned):
                            print(f'PLAIN_TEXT::{path}:{i}::{cleaned}')
