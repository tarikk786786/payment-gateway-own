import os

root_dir = r'c:\Users\tarik\Downloads\UPI22\UPI (1)'
target = "$url = 'https://chickenpox.in/secret/create_qr.php';"
replacement = "$url = 'https://' . $_SERVER['HTTP_HOST'] . '/secret/create_qr.php';"

for root, _, files in os.walk(root_dir):
    for name in files:
        if name == 'pay_now.php':
            path = os.path.join(root, name)
            with open(path, 'r', encoding='utf-8', errors='ignore') as f:
                content = f.read()
            if target in content:
                content = content.replace(target, replacement)
                with open(path, 'w', encoding='utf-8') as f:
                    f.write(content)
                print(f'Updated {path}')
