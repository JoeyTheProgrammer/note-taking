# Use the official PHP Apache image
FROM php:7.4-apache

# Install MySQL PDO extension
RUN docker-php-ext-install pdo pdo_mysql

# Enable mod_rewrite
RUN a2enmod rewrite
