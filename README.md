Loogares Symfony Edition
------------------------
Generacion de Entidades:
====
php app/console doctrine:generate:entities

Update del Schema:
===
php app/console doctrine:schema:update --force

Ver Cambios del Schema:
===
php app/console doctrine:schema:update --dump-sql

Limpiar Cache:
===
php app/console cache:clear
