# Orinoco Framework

[![Build Status](https://api.travis-ci.org/rawswift/Orinoco.svg?branch=master)](https://travis-ci.org/rawswift/Orinoco)

A lightweight PHP framework.

This source is the package implementation of [orinoco-framework-php](https://github.com/rawswift/orinoco-framework-php) (with some added features).

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

    php composer.phar install

Congratulations! Orinoco framework package should now be installed in your project's directory.

## Hello, World

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

## Configuration

Here's an example of a basic configuration:

    <?php

    require '../../vendor/autoload.php'; // Composer's Autoload script

    $config = new Orinoco\Configuration(
                            array(
                                'application' => array(
                                        'production' => false,
                                        'base' => '../../myapp', // Path to your controllers, views and custom class directory
                                        'autoload' => array(
                                                '/controller',
                                                '/class'
                                            )
                                    ),
                                'view' => array(
                                        'base' => '../view' // Path to your view template
                                    ),
                                'route' => array(
                                        '/foo/:id' => array(
                                                'controller' => 'foo',
                                                'action' => 'index',
                                                'segment' => array(
                                                        'id' => '(\d+)' // "id" as digits only
                                                    )
                                            ),
                                        '/foo/bar/:type/:id' => array(
                                                'controller' => 'foo',
                                                'action' => 'bar',
                                                'segment' => array(
                                                        'type' => '(\w+)', // "type" as letters and digits only
                                                        'id' => '(\d+)' // "id" as digits only
                                                    )
                                            )
                                    )
                            ));

    $app = new Orinoco\Application($config);
    $app->run();

## Controller

You can create Controller classes to organize request handling logic. Below is an example of a basic Controller class:

    <?php

    class fooController
    {
            public function __construct()
            {
                    // This method will be executed upon (this) class instantiation
                    // For example, you can use this method to initialize a private/public variable
            }

            public function index()
            {
                    // Executed on request URI /foo
            }

            public function bar()
            {
                    // Executed on request URI /foo/bar
            }
    }

Though the above Controller class will work just fine but in real world, you need to add logic to your Controller and Action methods. So here is an example of a simple `log` Controller with basic logic:

    <?php

    // Use framework's built-in View class
    use Orinoco\View;

    // Use Monolog (vendor class, installed via Composer)
    use Monolog\Logger;
    use Monolog\Handler\StreamHandler;

    class logController
    {
            // View object instance will be injected automatically
            // So you don't need to instantiate a new View object
            public function index(View $view)
            {
                    // Create a log channel
                    $log = new Logger('name');
                    $log->pushHandler(new StreamHandler('/tmp/monolog.txt', Logger::WARNING));

                    // Add records to the log channel
                    $log->addWarning('Foo');
                    $log->addError('Bar');

                    // Assuming everything went OK, output a JSON response
                    $view->renderJSON(array(
                        'ok' => true,
                        'message' => 'Log written successfully.'
                    ));
            }
    }

## View

The framework comes with a simple View class to handle basic template system. Below is the template system's default directory structure:

    +--/view
        |
        +--/layout
        |
        +--/page
        |
        +--/partial

Of course you can also customize your template directories. You can do this by configuring your View settings. For example:

    ...
    ...
    'view' => array(
        'base' => '../myView', // Root directory of your templates
        'template' => array(
                'layout' => '/myLayouts',
                'page' => '/myPages',
                'partial' => '/somePartialTemplates'
            )
    ),
    ...
    ...

Please note that you can also use other template engine library, such as Smarty, Twig, Plates, etc.

Here's an example of a Layout template: (e.g. `/myapp/view/layout/main.php`)

    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="description" content="Written using Orinoco Framework">
        <meta name="author" content="John Doe">
        <link rel="shortcut icon" href="/favicon.ico">
        <title>My Application</title>
    </head>
    <body>
    <div><?= $this->renderContent() ?></div>
    </body>
    </html>

And a typical Page template: (e.g. `/myapp/view/page/hello/index.php`)

    <h1>Hello, <?= ucfirst($this->name) ?>!</h1>
    <p>Lorem ipsum dolor sit amet, consectetur adipiscing elit. Mauris ipsum elit...</p>

## Console Runner

You can run an application as a console command, for example:

    <?php

    require 'path/to/vendor/autoload.php'; // Composer's Autoload script

    $config = new Orinoco\Configuration(
                            array(
                                'application' => array(
                                        'base' => '../../myapp', // Path to your commands, custom class, etc
                                        'autoload' => array(
                                                '/command', // Command classes
                                            )
                                    )
                            ));

    $console = new Orinoco\Console($config);

    $response = $console->run('/foobar');

    echo $response;

The above code will instantiate the class `myapp/command/foobarCommand.php` and run the `index` method:

    <?php

    class foobarCommand
    {
        public function index()
        {
            return "hello, world\n";
        }
    }


## License

Licensed under the [MIT license](http://www.opensource.org/licenses/mit-license.php)
