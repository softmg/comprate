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

###Поднять базу данных
```bash
php bin/console doctrine:database:create
mysql -u<db_user> -p<db_pass> <db_name> < ./data/comprate.sql
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