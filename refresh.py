import os
cmds = [
    'php artisan migrate:refresh --path=/database/migrations/2022_02_16_064131_create_qoptions_table.php',
    'php artisan db:seed --class=QoptionSeeder'
    ]
for cmd in cmds:
    os.system(cmd)
