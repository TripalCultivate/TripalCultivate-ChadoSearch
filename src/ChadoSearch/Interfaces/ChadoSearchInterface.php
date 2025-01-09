<?php

namespace Drupal\chado_search\ChadoSearch\Interfaces;

use Drupal\Core\Database\Query\Select;
use Drupal\Core\Form\FormState;

/**
 * Interface for chado_search plugins.
 */
interface ChadoSearchInterface {

  /**
   * Defines the query and arguments to use for the search.
   *
   * @param Drupal\Core\Database\Query\Select|null $query
   *   A Drupal select query object used to retrieve the results.
   *   This parameter will be NULL when requested and initialized inside
   *   this method.
   * @param int $offset
   *   The number of records to offset for the results. This is used in paging.
   */
  public function getQuery(Select|null &$query, int $offset = 0);

  /**
   * Generate the filter form.
   *
   * The base class will generate textfields for each filter defined in $info,
   * set defaults, labels and descriptions, as well as, create the search
   * button.
   *
   * Extend this method to alter the filter form.
   *
   * @param array $form
   *   The base form definition for the form elements to be added to.
   * @param \Drupal\Core\Form\FormState $form_state
   *   The current state of the form.
   *
   * @return array
   *   The fully defined form to be rendered for the search.
   */
  public function form(array $form, FormState $form_state): array;

  /**
   * Adds a pager to the form.
   *
   * @param array $form
   *   The form array to add the pager to.
   * @param int $num_results
   *   The number of results per page.
   *
   * @return array
   *   The original form with the pager added.
   */
  public function addPager(array $form, int $num_results): array;

  /**
   * Allows custom searches to validate the form results.
   *
   * Use $form_state::setError() to signal invalid values.
   *
   * @param array $form
   *   The base form definition for the form elements to be added to.
   * @param \Drupal\Core\Form\FormState $form_state
   *   The current state of the form.
   */
  public function validateForm(array $form, FormState $form_state): void;

  /**
   * Uses the class defined query and values to retrieve the results.
   *
   * @param int $offset
   *   The offset for the pager.
   *
   * @return array||false
   *   Either an array of the results returned by the query, adjusted by the
   *   offset OR False if an error is encountered.
   */
  public function getResults($offset): array|FALSE;

  /**
   * Format the results within the $form array.
   *
   * The base class will format the results as a table.
   *
   * @param array $form
   *   The current form array.
   * @param array $results
   *   The results to format. This will be an array of standard objects where
   *   the keys map to the keys in $info['fields'].
   */
  public function formatResults(array &$form, array $results): void;

  /**
   * Sets the paging offset.
   *
   * @param int $offset
   *   The offset to use in the query to support paging.
   */
  public function setPagerOffset(int $offset): void;

  /**
   * Sets the current page to support the pager.
   *
   * @param int $page_number
   *   The current page of results to show.
   */
  public function setCurrentPageNumber(int $page_number): void;

  /**
   * Sets the values from the form based on user input.
   *
   * @param array $filter_values
   *   An array of the user submitted values where the key matches an element
   *   in the info['filter] array and the value is the value the user submitted
   *   for that filter.
   */
  public function setValues(array $filter_values): void;

  /**
   * Returns the query offset used in paging.
   *
   * @return int
   *   The query offset.
   */
  public function getPagerOffset(): int;

  /**
   * Returns the current page number of results to show.
   *
   * @return int
   *   The current page number.
   */
  public function getCurrentPageNumber(): int;

  /**
   * Returns all the filter values for this search.
   *
   * @return array
   *   An array of filter values where the key is the machine name of the filter
   *   and the value is it's value.
   */
  public function getValues(): array;

  /**
   * Get a specific value for this search's filter criteria.
   *
   * @param string $name
   *   The name of the filter criteria you are interested in.
   *
   * @return mixed
   *   The value for the specified filter criteria.
   */
  public function getValue(string $name): mixed;

  /**
   * Gets the fields defined for this search instance.
   *
   * These are used to define the table columns for the results and each item
   * in the returned array maps to a result from the query.
   *
   * @return array
   *   An array of fields defined for this instance where each item is keyed by
   *   the chado column and the value is an array of details including a 'title'
   *   and optional entity_link sub array.
   */
  public function getDefinedFields(): array;

  /**
   * Get the filters defined for this search instance.
   *
   * These are used in the where clause of the query to filter the results.
   * Each machine name in the returned array should be used in the getQuery()
   * method.
   *
   * @return array
   *   An array of filters defined for this instance where each item is keyed by
   *   its machine name and the value is an array of details including 'title'
   *   and 'help'.
   */
  public function getDefinedFilters(): array;

  /**
   * Retrieves the CSS/JS libraries to attach to the form hosting this search.
   *
   * @return array
   *   A simply list of libraries which must already be defined in the
   *   libraries.yml.
   */
  public function getLibraries(): array;

  /**
   * The unique id of the chado search plugin instance.
   *
   * @return string
   *   The plugin id.
   */
  public function id(): string;

  /**
   * The label of the chado search.
   *
   * @return string
   *   The translated label of the chado search.
   */
  public function label(): string;

  /**
   * The title of the chado search.
   *
   * @return string
   *   The translated title of the chado search.
   */
  public function title(): string;

  /**
   * The description of the chado search.
   *
   * @return string
   *   The translated description of the chado search.
   */
  public function description(): string;

  /**
   * The permissions needed to access this chado search.
   *
   * @return array
   *   An array if drupal permission strings indicated the permissions needed
   *   to access the chado search.
   */
  public function permissions(): array;

  /**
   * The URL for the chado search.
   *
   * @return string
   *   The relative URL the chado search will be available at.
   */
  public function urlPath(): string;

  /**
   * The text for the submit button on the search.
   *
   * @return string
   *   The translated text to be used on the submit button.
   */
  public function buttonText(): string;

  /**
   * Indicates if this search requires submit before querying.
   *
   * @return bool
   *   TRUE means this is a search; FALSE means it browses all results before
   *   submit.
   */
  public function requireSubmit(): bool;

  /**
   * Indicated whether a pager should be used on this search.
   *
   * @return bool
   *   TRUE means a pager should be used; FALSE will result in truncated results
   *   with no pager.
   */
  public function usePager(): bool;

  /**
   * Indicates the number of results per page.
   *
   * If usePager() returns FALSE then this is the number of items returned
   * but if it returns TRUE then this many items will be shown per page.
   *
   * @return int
   *   The number of results per page.
   */
  public function numItemsPerPage(): int;

}
