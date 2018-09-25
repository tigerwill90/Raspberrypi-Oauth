#!/bin/bash

source ".env"

echo "
  <VirtualHost *:80>
    ServerName localhost
    DocumentRoot /var/www/html/public
    <Directory "/var/www/html/">
      Options FollowSymLinks
      AllowOverride All
      Order allow,deny
      Allow from all
      Require all granted
    </Directory>
  </VirtualHost>

  <VirtualHost *:443>
    ServerAlias $ALIAS
    ServerName localhost
    DocumentRoot /var/www/html/public
    <Directory "/var/www/html/">
      Options FollowSymLinks
      AllowOverride All
      Order allow,deny
      Allow from all
      Require all granted
    </Directory>
  </VirtualHost>
" > vhost/vhost.conf
