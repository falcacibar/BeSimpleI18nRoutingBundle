#! /usr/bin/env bash
php app/console doctrine:generate:entities Loogares\LugarBundle
php app/console doctrine:generate:entities Loogares\UsuarioBundle
php app/console doctrine:generate:entities Loogares\ExtraBundle
php app/console doctrine:generate:entities Loogares\AdminBundle
php app/console doctrine:generate:entities Loogares\BlogBundle
php app/console doctrine:generate:entities Loogares\MailBundle
php app/console doctrine:schema:update --force
php app/console cache:clear
