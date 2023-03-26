#!/bin/bash

wp core download --allow-root
wp config create --dbhost=database --dbname=wordpress --dbuser=wordpress --dbpass=password --allow-root
wp core install --url=http://localhost --title=Example --admin_user=admin --admin_password=password --admin_email=admin@localhost.com  --skip-email --allow-root

wp option update blogname "PromPress" --allow-root
wp option update blogdescription "" --allow-root
wp option update permalink_structure "/%postname%/" --allow-root
wp option update timezone_string "Europe/London" --allow-root

wp rewrite flush --allow-root
