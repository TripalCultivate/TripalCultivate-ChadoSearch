<?php

namespace Drupal\chado_search\ChadoSearch\Annotation;

use Drupal\Component\Annotation\Plugin;

/**
 * Defines chado_search annotation object.
 *
 * @Annotation
 */
final class ChadoSearch extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public readonly string $id;

  /**
   * The human readable title of this search.
   *
   * This will be used in listings and shown on the search page as the title.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public readonly string $title;

  /**
   * A description of this search.
   *
   * This is shown at the top of the search page and used for the menu item.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public readonly string $description;

  /**
   * The machine names of the permissions with access to this search.
   *
   * This is used to map your search to existing permissions. It must be an
   * array and is used in hook_menu(). Some examples include 'access content'
   * and 'administer tripal'.
   *
   * @var array
   */
  public array $permissions = ['access content'];

  /**
   * URL to make the search available at.
   *
   * Note: the route will be added automatically by the Chado Search API.
   *
   * @var string
   */
  public string $url_path;

  /**
   * Button label for the submit button at the bottom of the importer form.
   *
   * @var string
   *
   * @ingroup plugin_translatable
   */
  public string $button_text = 'Search';

  /**
   * Indicates whether this search requires submission to run the query.
   *
   * If TRUE, this search will require the submit button to be clicked before
   * executing the query; whereas, if FALSE it will be executed on the
   * first page load without parameters.
   *
   * NOTE: to control the results without parameters check $this->submitted
   * in getQuery() and if FALSE return your pre-submit query.
   *
   * @var bool
   */
  public bool $require_submit = TRUE;

  /**
   * Indicates whether to add a pager to search results.
   *
   * NOTE: Your query has to handle paging.
   *
   * @var bool
   */
  public bool $pager = TRUE;

  /**
   * The number of items to display on a single pager.
   *
   * Note: this generally goes together with the pager. If the pager is FALSE
   * then this will truncate the results.
   *
   * @var int
   */
  public int $num_items_per_page = 25;

}
