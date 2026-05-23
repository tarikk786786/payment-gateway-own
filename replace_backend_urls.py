import os
import re

root_dir = r'c:\Users\tarik\Downloads\UPI22\UPI (1)'

files_to_fix = [
    r'ClientsPages\EraOne.php',
    r'ClientsPages\amiri\callback.php',
    r'ClientsPages\amiri\index.php',
    r'ClientsPages\amiri\status.php',
    r'ClientsPages\saibalaji.php',
    r'ClientsPages\vk_page.php',
    r'auth\merchant_list.php',
    r'crons\cron4.php',
    r'payment7\pay_now.php',
    r'phnpe\cheksum.php',
    r'corefilesinstance\telegram-events\bot.php'
]

for file_rel in files_to_fix:
    path = os.path.join(root_dir, file_rel)
    if os.path.exists(path):
        with open(path, 'r', encoding='utf-8', errors='ignore') as f:
            content = f.read()
        
        # Replace double quoted strings
        content = content.replace('"https://chickenpox.in', '"https://" . $_SERVER["HTTP_HOST"] . "')
        
        # Replace single quoted strings
        content = content.replace("'https://chickenpox.in", "'https://' . $_SERVER['HTTP_HOST'] . '")
        
        with open(path, 'w', encoding='utf-8') as f:
            f.write(content)
        print(f'Fixed {path}')
