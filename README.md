# Configuration wrapper

Allows easily reading a site's configuration through the use of a `*Config()`
wrapper class. It can work on multiple platforms and environments.

This came about from the need to support multiple different platforms,
environments, and configuration setups across different project types. I wanted
a singular way to work with these different setups instead of having something
custom for each platform and project type.

## Basic example:

    require 'path/to/composer/autoload.php';

    use StevenWichers\Configuration\JSONConfig;

    $config = new JSONConfig();
    $fqdn = $config->getConfigItem('fqdn')->getValue();

See `example.php` in the examples folder for more usage examples.

## Installation

### Using Composer (recommended)

Installation using [composer](https://getcomposer.org/) is very straightforward.
You must add the repository as an entry in your composer.json, as well as
requiring the project. A complete composer.json that only includes this project
would look like this:

    {
      "repositories": [
        {
          "url": "https://github.com/swichers/configuration-framework.git",
          "type": "git"
        }
      ],
      "require": {
        "swichers/configuration-framework": "1.*@stable"
      }
    }

After your composer.json file has been created or updated, you would issue a
`composer install` command. The final step is adding the composer autoloader to
your project:

    require 'path/to/vendor/autoload.php';

### Other

You can clone this repository to your project and include the classes directly.
This project relies on a proper PSR-0/PSR-4 compliant autoloader being
configured, but otherwise does not leverage any of the other composer features
at this time.
