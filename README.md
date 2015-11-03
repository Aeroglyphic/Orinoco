# Orinoco Framework

[![Build Status](https://api.travis-ci.org/rawswift/Orinoco.svg?branch=master)](https://travis-ci.org/rawswift/Orinoco)

A lightweight PHP framework.

## Installation

Create your project's directory:

    mkdir myapp ; cd myapp

Inside your project's directory, create a `composer.json` file and add `rawswift/orinoco` as required dependency:

    {
        "require": {
            "rawswift/orinoco": "dev-master"
        }
    }

Install `Composer` by running this command from your terminal:

    curl -sS https://getcomposer.org/installer | php

Now, install the framework using the command below:

    ./composer.phar install

Congratulations! Orinoco framework package should now be installed in your project's directory.

## Application

A simple `hello, world` application.

Require the Composer's autoloader:

    require "vendor/autoload.php";

Instantiate a Orinoco application (using default configurations):

    $app = new Orinoco\Application(new Orinoco\Configuration());

Set your home page route handler:

    $app->Route()->set('/', function() {
        echo "hello, world";
    });

Run the application:

    $app->run();

## License

Licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php)
