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

1. Implement `hook_chado_custom_search` to tell the API about the search you would like to create.

```php
/**
 * Implement hook_chado_custom_search().
 *
 * This hook simply lists the machine name of the searches so that we can find
 * the info hook. We've done this for performance reasons.
 */
function example_ccsearch_chado_custom_search() {
  $searches = [];

  $searches['BreedingCrossSearch'] = 'Breeding Cross Search';

  return $searches;
}
```

2. Create a class which extends ChadoCustomSearch. At a minimum you need to set the editable static constants and the getQuery() method. See [BreedingCrossSearch](https://github.com/UofS-Pulse-Binfo/chado_custom_search/blob/master/example_ccsearch/examples/BreedingCrossSearch.inc) for an example.

3. Clear the cache, navigate to the path defined in the class and enjoy your custom search!

## Documentation

For more information on what you can accomplish or how to get started, see [our ReadtheDocs Documentation](https://chado-custom-search-api.readthedocs.io/en/latest/#).
