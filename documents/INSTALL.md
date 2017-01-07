# Project Installation

## Prerequisites

It's required to have the following installed and configured to run:

- [**Composer**](getcomposer.org): Standard PHP dependency management
    - Requires: [**PHP 5.6.0+**](php.net) and [**GIT**](git-scm.com)
- 

## Add packages to project

Add this project to parent project `composer` configuration:
```bash
$ composer require suncoast-connection/claims-to-emr
```

If your running the Gearman workers, add the following:
```bash
$ composer require kicken/gearman-php phpseclib/phpseclib:2.0.*
```

Install the project dependencies:
```bash
$ composer install
```
