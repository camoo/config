# Config

Config is a file configuration loader that supports PHP, INI, XML, JSON,
YML, Properties and serialized files and strings.

<p align="center">
    <a href="https://github.com/camoo/config" target="_blank">
        <img alt="Build Status" src="https://github.com/camoo/config/actions/workflows/continuous-integration.yml/badge.svg">
    </a>
	<a href="https://codecov.io/gh/camoo/config">
  		<img alt="camoo-badge" src="https://codecov.io/gh/camoo/config/branch/main/graph/badge.svg" />
	</a>
</p>

## Requirements

Config requires PHP 8.1+.

> **IMPORTANT:** If you want to use YAML files or strings, require the [Symfony Yaml component](https://github.com/symfony/Yaml) in your `composer.json`.

## Installation

The supported way of installing Config is via Composer.

```sh
$ composer require camoo/config
```

## Usage

Config is designed to be very simple and straightforward to use. All you can do with
it is load, get, and set.

### Loading files

The `Config` object can be created via the factory method `load()`, or
by direct instantiation:

```php
use Camoo\Config\Config;
use Camoo\Config\Enum\Parser;

// Load a single file
$conf = Config::load('config.json');
$conf = new Config('config.json');

// Load values from multiple files
$conf = new Config(['config.json', 'config.xml']);

// Load all supported files in a directory
$conf = new Config(__DIR__ . '/config');

// Load values from optional files
$conf = new Config(['config.dist.json', '?config.json']);

// Load a file using specified parser
$conf = new Config('configuration.config', Parser::JSON);
```

Files are parsed and loaded depending on the file extension or specified
parser. If the parser is specified, it **will be used for all files**. Note
that when loading multiple files, entries with **duplicate keys will take on
the value from the last loaded file**.

When loading a directory, the path is `glob`ed and files are loaded in by
name alphabetically.

**Warning:** Do not include untrusted configuration in PHP format. It could
contain and execute malicious code.

### Loading string

Configuration from string can be created via the factory method `load()` or
by direct instantiation, with argument `$string` set to `true`:

```php
use Camoo\Config\Config;
use Camoo\Config\Enum\Parser;
use Camoo\Config\Parser\Json;
use Camoo\Config\Parser\Yaml;

$settingsJson = <<<FOOBAR
{
  "application": {
    "name": "configuration",
    "secret": "s3cr3t"
  },
  "host": "localhost",
  "port": 80,
  "servers": [
    "host1",
    "host2",
    "host3"
  ]
}
FOOBAR;

$settingsYaml = <<<FOOBAR
application:
    name: configuration
    secret: s3cr3t
host: localhost
port: 80
servers:
- host1
- host2
- host3

FOOBAR;

$conf = Config::load($settingsJson, Parser::JSON, true);
$conf = new Config($settingsYaml, Parser::YAML, true);
```

**Warning:** Do not include untrusted configuration in PHP format. It could
contain and execute malicious code.

### Getting values

Getting values can be done in three ways. One, by using the `get()` method:

```php
// Get value using key
$debug = $conf->get('debug');

// Get value using nested key
$secret = $conf->get('security.secret');

// Get a value with a fallback
$ttl = $conf->get('app.timeout', 3000);
```

The second method, is by using it like an array:

```php
// Get value using a simple key
$debug = $conf['debug'];

// Get value using a nested key
$secret = $conf['security.secret'];

// Get nested value like you would from a nested array
$secret = $conf['security']['secret'];
```

The third method, is by using the `all()` method:

```php
// Get all values
$data = $conf->all();
```

### Setting values

Although Config supports setting values via `set()` or, via the
array syntax, **any changes made this way are NOT reflected back to the
source files**. By design, if you need to make changes to your
configuration files, you have to do it manually.

```php
$conf = Config::load('config.json');

// Sample value from our config file
assert($conf['secret'] == '123');

// Update config value to something else
$conf['secret'] = '456';

// Reload the file
$conf = Config::load('config.json');

// Same value as before
assert($conf['secret'] == '123');

// This will fail
assert($conf['secret'] == '456');
```

### Saving config

It is possible to save the config back to a file in any of the supported formats
except PHP.

```php
use use Camoo\Config\Enum\Writer;

$config = Config::load('config.json');

$ini = $config->toString(Writer::INI); // Encode to string if you want to save the file yourself

$config->toFile('config.yaml');
$config->toFile('config.txt', Writer::SERIALIZE); // you can also force the writer
```

### Using with default values

Sometimes in your own projects you may want to use Config for storing
application settings, without needing file I/O. You can do this by extending
the `AbstractConfig` class and populating the `getDefaults()` method:

```php
class MyConfig extends AbstractConfig
{
    protected function getDefaults()
    {
        return [
            'host' => 'localhost',
            'port'    => 80,
            'servers' => [
                'host1',
                'host2',
                'host3'
            ],
            'application' => [
                'name'   => 'configuration',
                'secret' => 's3cr3t'
            ]
        ];
    }
}
```

### Merging instances

You may want merging multiple Config instances:

```php
$conf1 = Config::load('conf1.json');
$conf2 = Config::load('conf2.json');
$conf1->merge($conf2);
```

### Examples of supported configuration files

Examples of simple, valid configuration files can be found [here](tests/mocks/pass).


## Testing

``` bash
$ phpunit
```


## Resources
---------

* [Report issues](https://github.com/camoo/config/issues)


## Credits

- [Camoo Sarl](https://github.com/camoo)
- [Hassan Khan](https://github.com/hassankhan)


## License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
