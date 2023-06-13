# Description

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gaiththewolf/git-manager.svg?style=flat-square)](https://packagist.org/packages/gaiththewolf/git-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/gaiththewolf/git-manager.svg?style=flat-square)](https://packagist.org/packages/gaiththewolf/git-manager)
![GitHub Actions](https://github.com/gaiththewolf/git-manager/actions/workflows/main.yml/badge.svg)

This package can execute and parse git commands on project, it can provide a way to update laravel app with button click from client interface without knowing git commands

## Installation

You can install the package via composer:

```bash
composer require gaiththewolf/git-manager
```

## Usage

```php

namespace App\Http\Controllers;

class UpdaterController extends Controller
{

    public function __construct()
    {
        // for windows users you need to set windows mode
        GitManager::windowsMode();
        // set .git directory
        GitManager::setWorkingDirectory(base_path());
    }

    public function index()
    {
        $version = GitManager::version(); // get string of installed git version
        $version = trim(str_replace("git version", "",$version));

        $lastUpdate = trim(GitManager::lastUpdate()); // get string of last update datetime

        $status = GitManager::status(); // get array git status

        $logs = GitManager::log(); // get array Last 10 logs

        return view('update.index', compact('version', 'lastUpdate', 'status', 'logs'));
    }

    public function pull()
    {
        $output = GitManager::pull(); // pull changes if exist
        if ($output["is_up_to_date"]) {
            return redirect()->route('update.index');
        }
        return redirect()->route('update.index')->with("pullData", $output);
    }

}
```

### Testing

```bash
composer test
```

### Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

### Security

If you discover any security related issues, please email gaiththewolf@gmail.com instead of using the issue tracker.

## Credits

-   [Mr.Wolf](https://github.com/gaiththewolf)
-   [All Contributors](../../contributors)

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
