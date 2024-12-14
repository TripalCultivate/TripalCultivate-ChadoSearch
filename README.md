![Tripal Dependency](https://img.shields.io/badge/tripal-%3E=4.0-brightgreen)
![Module is Generic](https://img.shields.io/badge/generic-confirmed-brightgreen)
![GitHub release (latest by date including pre-releases)](https://img.shields.io/github/v/release/UofS-Pulse-Binfo/chado_custom_search?include_prereleases)

[![Build Status](https://travis-ci.org/UofS-Pulse-Binfo/chado_custom_search.svg?branch=master)](https://travis-ci.org/UofS-Pulse-Binfo/chado_custom_search)
[![Maintainability](https://api.codeclimate.com/v1/badges/69080fdb30c5c3a46350/maintainability)](https://codeclimate.com/github/UofS-Pulse-Binfo/chado_custom_search/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/69080fdb30c5c3a46350/test_coverage)](https://codeclimate.com/github/UofS-Pulse-Binfo/chado_custom_search/test_coverage)

# Chado Custom Search

Provides custom search functionality for those who don't wish to use Drupal Views.

Why might you want to use this module?
- Gives you complete control of the query so you can tweak performance.
- Removes query-determination overhead of Drupal Views for better performance.
- Lets you make quick custom searches which can be under version control.
- Saves you from having to render the result table, generate the filter form,
  handle the user input and execute the query.
- Lets you focus on customizing only what you want to!

## Chado Custom Search API

1. Implement the ChadoSearch plugin type by creating a class extending ChadoSearchBase at `src/Plugin/ChadoSearch`. At a minimum you need to set the annotations, the $info property and the getQuery() method. See [BreedingCrossSearch](https://github.com/UofS-Pulse-Binfo/chado_custom_search/blob/master/example_ccsearch/srcPlugin/ChadoSearch/BreedingCrossSearch.inc) for an example.

2. Clear the cache, navigate to the path defined in the class and enjoy your custom search!

## Citation

If you use this module in your Tripal site, please use this citation to
reference our work any place where you described your resulting Tripal site.
For example, if you publish your site in a journal then this citation should be
in the reference section and anywhere functionality provided by this module is
discussed in the above text should reference it.

> Lacey-Anne Sanderson (2024). Chado Search API. Development Version. University of Saskatchewan, Pulse Crop Research Group, Saskatoon, SK, Canada.

## Technology Stack

*See specific version compatibility in the automated testing section below.*

- Drupal
- Tripal 4.x
- PostgreSQL
- PHP
- Apache2

### Docker

We use docker images within our Automated Testing Github Workflows and for
development purposes. Specifically, the Dockerfile within this repo extends the
[tripalproject/tripaldocker](https://hub.docker.com/r/tripalproject/tripaldocker)
by installing this module package.

### Automated Testing

This package is dedicated to a high standard of automated testing. We use
PHPUnit for testing and CodeClimate to ensure good test coverage and
maintainability. There are more details on [our CodeClimate project page]
describing our specific maintainability issues and test coverage.

[![Maintainability](https://api.codeclimate.com/v1/badges/69080fdb30c5c3a46350/maintainability)](https://codeclimate.com/github/UofS-Pulse-Binfo/chado_custom_search/maintainability)
[![Test Coverage](https://api.codeclimate.com/v1/badges/69080fdb30c5c3a46350/test_coverage)](https://codeclimate.com/github/UofS-Pulse-Binfo/chado_custom_search/test_coverage)

The following compatibility is proven via automated testing workflows.

|  Drupal     |  10.5.x         | 11.0.x          | 11.1.x          |
|-------------|-----------------|-----------------|-----------------|
| **PHP 8.1** | ![Grid1A-Badge] |                 |                 |
| **PHP 8.2** | ![Grid1B-Badge] |                 |                 |
| **PHP 8.3** | ![Grid1C-Badge] | ![Grid2C-Badge] | ![Grid3C-Badge] |

[our CodeClimate project page]: https://codeclimate.com/github/UofS-Pulse-Binfo/chado_custom_search

[Grid1A-Badge]: https://github.com/UofS-Pulse-Binfo/chado_custom_search/actions/workflows/MAIN-phpunit-Grid1A.yml/badge.svg
[Grid1B-Badge]: https://github.com/UofS-Pulse-Binfo/chado_custom_search/actions/workflows/MAIN-phpunit-Grid1B.yml/badge.svg
[Grid1C-Badge]: https://github.com/UofS-Pulse-Binfo/chado_custom_search/actions/workflows/MAIN-phpunit-Grid1C.yml/badge.svg

[Grid2C-Badge]: https://github.com/UofS-Pulse-Binfo/chado_custom_search/actions/workflows/MAIN-phpunit-Grid2C.yml/badge.svg

[Grid3C-Badge]: https://github.com/UofS-Pulse-Binfo/chado_custom_search/actions/workflows/MAIN-phpunit-Grid3C.yml/badge.svg
