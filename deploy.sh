sudo chown -R 777 $USER:loogares /var/www/loogares.com/loogares
cd /var/www/loogares.com/loogares && git pull origin master
sudo chmod -R 777 app/cache
sudo chmod -R 777 app/logs
sudo chmod -R 777 web/assets/images
sudo chmod -R 777 web/assets/media
