*** Заготовка под битрикс в докере

1. `mkdir www bitrix upload logs logs/apache`
2. `sudo chown -R www-data:www-data ./www ./bitrix ./upload`  
    `sudo chmod -R 755 ./www ./bitrix ./upload`
3. `cd www`  
    `wget https://www.1c-bitrix.ru/download/scripts/bitrixsetup.php`
4. `make run`
