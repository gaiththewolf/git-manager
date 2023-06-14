# Description

[![Latest Version on Packagist](https://img.shields.io/packagist/v/gaiththewolf/git-manager.svg?style=flat-square)](https://packagist.org/packages/gaiththewolf/git-manager)
[![Total Downloads](https://img.shields.io/packagist/dt/gaiththewolf/git-manager.svg?style=flat-square)](https://packagist.org/packages/gaiththewolf/git-manager)
![GitHub Actions](https://github.com/gaiththewolf/git-manager/actions/workflows/main.yml/badge.svg)

This package can execute and parse git commands on project, it can provide a way to update laravel app with button click from client interface without knowing git commands

## Requirements

- Laravel 9.x to 10.x
- PHP >= 8.0

## Installation

You can install the package via composer:

```bash
composer require gaiththewolf/git-manager
```

## Features

* git version
* repository last update
* check reposiory updates (new commits)
* pull changes
* run commands like (composer, php artisan, ...)

## Usage

```php

namespace App\Http\Controllers;

use GitManager;

class UpdaterController extends Controller
{

    public function __construct()
    {
        // init and set git working directory
        GitManager::init(base_path());
    }

    public function index()
    {
        $version = GitManager::version(); // get string of installed git version
        $version = trim(str_replace("git version", "",$version));

        $lastUpdate = trim(GitManager::lastUpdate()); // get string of last update datetime

        $status = GitManager::status(); // get Object git status include command `git fetch` before

        $logs = GitManager::log(); // get Object Last 10 logs

        return view('update.index', compact('version', 'lastUpdate', 'status', 'logs'));
    }

    public function pull()
    {
        $output = GitManager::pull(); // get Object of pull changes if exist
        if ($output["is_up_to_date"]) {
            return redirect()->route('update.index');
        }
        return redirect()->route('update.index')->with("pullData", $output);
    }

}
```

## Data structure

Data structure of `GitManager::status()`. 

```php
Gaiththewolf\GitManager\Data\GitStatus {#1512 ▼ // app\Http\Controllers\UpdaterController.php:37
  +branch: "main"
  +isUpToDate: true
  +isPush: false
  +isPull: false
}
```

Data structure of `GitManager::log()`. 

```php
Illuminate\Support\Collection {#1512 ▼ // app\Http\Controllers\UpdaterController.php:37
  #items: array:4 [▶
    0 => Gaiththewolf\GitManager\Data\GitLog {#763 ▶
      +hash: "1c1efa505bb1c15f6bc40747f3d73"
      +merge: array:2 [▶
        0 => "d0f83"
        1 => "5f047"
      ]
      +author: array:2 [▶
        "full_name" => "User 1"
        "email" => "user1@mail.local"
      ]
      +date: "Tue Jun 13 14:30:35 2023 +0100"
      +message: "Merge branch 'main' of github.com:gaiththewolf/amani_stats1"
    }
    1 => Gaiththewolf\GitManager\Data\GitLog {#778 ▶
      +hash: "d0f6283eebb28d4f93c0bc9231d3e4d6e2720cd9"
      +merge: []
      +author: array:2 [▶
        "full_name" => "User 1"
        "email" => "user1@mail.local"
      ]
      +date: "Tue Jun 13 14:30:27 2023 +0100"
      +message: "fix"
    }
    2 => Gaiththewolf\GitManager\Data\GitLog {#1579 ▶
      +hash: "5f23047507e4130b07929a429ca2a7adadcfd127"
      +merge: []
      +author: array:2 [▶
        "full_name" => "User 2"
        "email" => "user2@mail.local"
      ]
      +date: "Tue Jun 13 14:29:50 2023 +0100"
      +message: "Update README.md"
    }
    3 => Gaiththewolf\GitManager\Data\GitLog {#780 ▶
      +hash: "5160f64d7ad2073c46da6922cde61bd965c2ad3a"
      +merge: []
      +author: array:2 [▶
        "full_name" => "User 1"
        "email" => "user1@mail.local"
      ]
      +date: "Tue Jun 13 14:07:35 2023 +0100"
      +message: "Update README.md"
    }
  ]
  #escapeWhenCastingToString: false
}
```

Data structure of `GitManager::pull()`. 

```php
Gaiththewolf\GitManager\Data\GitPull {#1512 ▼ // app\Http\Controllers\UpdaterController.php:37
  +files: array:1 [▶
    0 => " README.md"
  ]
  +isUpToDate: false
  +changed: "1"
  +insertion: "2"
  +deletion: "0"
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
