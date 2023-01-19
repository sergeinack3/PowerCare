# OX Mediboard

## About

Mediboard is an open source health facility management application.

## Requirements

- Apache
- Php
- Mysql
- Pecl
- Composer

## Installation
```
composer install --no-dev --optimize-autoloader
npm install --no-save
npm run build
composer ox-install-config
composer ox-install-database
```

### Front End

#### Project setup
```
npm install
```

#### Compiles for development
```
npm run build:dev
```

#### Compiles and minifies for production
```
npm run build
```

#### Run your unit tests
```
npm run test:unit
```

#### Run your unit tests with coverage
```
npm run test:coverage
```

## Links
- Instance state: `/status`
- Api state: `/api/status`
- Api documentation: `/api/doc`

## Documentation
The documentation is available on GitLab wiki.

## ADR (Architecture Decision Record)

ADRs are available in [dev/ADR](./dev/ADR) and must be respected while developping.

## Version
0.5.0

## Licenses
- [GNU General Public License](https://www.gnu.org/licenses/gpl.html)
- [OpenXtrem Open License](https://www.openxtrem.com/licenses/oxol.html)
