#comprate - сайт для оценки компьютера по его комплектующим

##Сервера
* пока только локалка

##Развертывание проекта

###Требования
* php `5.5.9` и выше

###Клонировать репозиторий
```bash
mkdir comprate
git init
git remote add origin git@github.com:softmg/comprate.git
git fetch --all
git checkout master
```

###Прописать свои настройки для консоли
```bash
изменить файл ./app/config/parameters.yml
```

###Поднять базу данных для сайта и acceptance окружения
```bash
php bin/console doctrine:database:create
mysql -u<db_user> -p<db_pass> <db_name> < ./data/comprate.sql
php bin/console doctrine:schema:update --force
php bin/console doctrine:database:create --env=acceptance
mysql -u<db_user> -p<db_pass> <db_name>_acceptance < ./data/comprate.sql
php bin/console doctrine:schema:update --force --env=acceptance
```
Если ошибка при миграции, то запускаем следующие команды:
```bash
php bin/console doctrine:schema:update --dump-sql > migrate.sql
cat <(echo "SET FOREIGN_KEY_CHECKS=0;") migrate.sql | mysql -uroot <db_name>
```

###Установить права для директорий
```bash
chmod 0755 cc.sh
./cc.sh
```

###Установить зависимости
```bash
wget http://getcomposer.org/composer.phar .
php composer.phar install -o
```

###Создать виртуальный хост
Пример для Apache:
```bash
<VirtualHost 127.0.0.17:80>
    DocumentRoot "/var/www/sfprojects/comprate/web"
    DirectoryIndex app.php
    SetEnv env "dev"
    <Directory "/var/www/silex/comprate/web">
        AllowOverride All
        Order allow,deny
        Allow from All
    </Directory>
</VirtualHost>
```

###Запустить тесты
```bash
vendor/bin/behat
```

###Описание бандлов проекта
* AnalyzeBundle - бандл для анализа конфигурации введенного пользователем (компьютера)
* ApiBundle - бандл для api [фронтенда](https://github.com/softmg/comprate-frontend)
* ComputerBundle - функционал для сохранения различных конфигураций
* ParsingBundle - функционал для парсинга инфо продуктов с различных сайтов
* ProductBundle - словарь всех возможных продуктов

###ProductBundle
* Entity\Attribute - словарь свойств продуктов (сокет, частота процессора, ...)
* Entity\AttributeValue - словарь значений свойств продуктов (LGA1150, LGA1151, ...) 
* Entity\Product - словарь продуктов на рынке (Intel Xeon E3-1290 V2 @ 3.70GHz) 
* Entity\ProductAttribute - значения свойств конкретных продуктов (LGA1150, 3.70GHz, ...). Тут задаются возможные ограничения (максимум памяти, сокет, ...)
* Entity\ProductType - словарь типов продукта (Процессоры, материнки, ...)
* Entity\Vendor - производители
