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

## Documentation

For more information on what you can accomplish or how to get started, see [our ReadtheDocs Documentation](https://chado-custom-search-api.readthedocs.io/en/latest/#).
