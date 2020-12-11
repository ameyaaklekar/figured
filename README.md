<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400"></a></p>

<p align="center">
<a href="https://travis-ci.org/laravel/framework"><img src="https://travis-ci.org/laravel/framework.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

# Figured Inventory Cost Calculator
**Basic API** created as a step to test my coding skills for Figured. Powered by VueJs as the front end App which can be found here https://github.com/ameyaaklekar/figured-vue

## Overview
This API calculates the price for the quantity required based on the stock intake and costing. As per the requirements, the stock purchased first needs to be used first. Hence the API makes the required calculations and return the price for the required quantity.

## Installation
Follow the steps below to clone and run the project

    git clone git@github.com:ameyaaklekar/figured.git

    cd figured

    composer install

I used the **CSV** file as the data source for this project which can be found under **Storage**.

To start the server runt he following command in the terminal

    php artisan serve
this will start the server on http://localhost:8000 
The port 8000 may not be the same for some users, in that case copy the URL mentioned below the command from the terminal

    Starting Laravel development server: http://127.0.0.1:YOUR-PORT
## Usage
You can either use API client to make the request or clone the front end VueJs app from https://github.com/ameyaaklekar/figured-vue

**To use an API Client:**

 1. Use the URL http://127.0.0.1:8000/api/product/value
 2. Make a POST Request
 3. Set the header `Content-Type: application/json` and `Accept: application/json`
 4. Add the quantity parameter to the body in JSON format as follows `{ "quantity": NUMBER }`

**To use the VueJs App:**

Follow the instructions on the [VueJs App](https://github.com/ameyaaklekar/figured-vue) repository
 




