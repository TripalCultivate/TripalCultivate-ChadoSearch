<?php

namespace Drupal\chado_search\ChadoSearch;

use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormState;

/**
 * Base class for chado_search plugins.
 */
abstract class ChadoSearchPluginBase extends PluginBase implements ChadoSearchInterface {

  /**
   * Add CSS/JS to the form/results page.
   *
   * NOTE: paths supplied should be relative to $module.
   *
   * @var array
   */
  public static array $attached = [
    'css' => [],
    'js' => [],
  ];

  /**
   * Information regarding the fields and filters for this search.
   *
   * @var array
   */
  public static $info = [
    // Lists the columns in your results table.
    'fields' => [
      'column_name' => [
        'title' => 'Title',
        // This keyval is optional. It's used to make the current
        // column a link. The link is made automagically as long as
        // you add the id_column to your query.
        'entity_link' => [
          'chado_table' => 'feature',
          'id_column' => 'feature_id',
        ],
      ],
    ],
    // The filter criteria available to the user.
    // This is used to generate a search form which can be altered.
    'filters' => [
      'column_name' => [
        'title' => 'Title',
        'help' => 'A description for users as to what this filter is.',
      ],
    ],
  ];

  /**
   * The values submitted through the filter form by the user.
   *
   * @var array
   */
  public array $values;

  /**
   * Whether the user clicked the search button.
   *
   * @var bool
   */
  public bool $submitted = FALSE;

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    // Set some defaults based on the class information.
    // @todo is this even needed.
    foreach (static::$info['filters'] as $name => $details) {
      $this->values[$name] = (isset($details['default'])) ? $details['default'] : NULL;
    }
    $this->submitted = FALSE;
  }

  /**
   * Generate the filter form.
   *
   * The base class will generate textfields for each filter defined in $info,
   * set defaults, labels and descriptions, as well as, create the search
   * button.
   *
   * Extend this method to alter the filter form.
   *
   * @todo UPGRADE.
   *
   * @param array $form
   *   The base form definition for the form elements to be added to.
   * @param \Drupal\Core\Form\FormState $form_state
   *   The current state of the form.
   *
   * @return array
   *   The fully defined form to be rendered for the search.
   */
  public function form(array $form, FormState $form_state) {
    // @todo upgrade drupal_get_query_parameters() to new approach.
    $q = [];

    // @todo upgrade to use annotation.
    // $form['header'] = [
    //   '#type' => 'markup',
    //   '#markup' => '<p>' . self::$description . '</p>',
    //   '#weight' => -10000,
    // ];

    foreach (static::$info['filters'] as $name => $details) {
      $default = (isset($details['default'])) ? $details['default'] : '';
      if (isset($q[$name])) {
        $default = $q[$name];
      }

      $form[$name] = [
        '#type' => 'textfield',
        '#title' => $details['title'],
        '#description' => $details['help'],
        '#default_value' => $default,
      ];
    }

    // @todo upgrade to use annotation.
    // $form['submit'] = [
    //   '#type' => 'submit',
    //   '#value' => self::$button_text,
    //   '#weight' => 20,
    // ];

    return $form;
  }

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
  public function validateForm(array $form, FormState $form_state) {
  }

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
  public function formatResults(array &$form, array $results) {

    $table = [
      'attributes' => [],
      'caption' => '',
      'colgroups' => [],
      'sticky' => TRUE,
      'empty' => '',
    ];
    $table['header'] = [];
    $template_row = [];
    $link = [];
    foreach (static::$info['fields'] as $name => $details) {
      $label = $details['title'];
      $table['header'][$name] = $label;
      $template_row[$name] = '';
      if (isset($details['entity_link'])) {
        $link[$name]['entity_link'] = $details['entity_link'];
      }
      else {
        $link[$name]['entity_link'] = FALSE;
      }
    }

    $table['rows'] = [];
    $num_rows = 0;
    foreach ($results as $r) {
      $num_rows++;
      // @todo upgrade to use annotation.
      if ($num_rows <= self::$num_items_per_page) {
        $row = [];
        foreach ($template_row as $key => $default) {
          if (isset($r->{$key})) {
            $row[$key] = $r->{$key};
            if ($link[$key]['entity_link']) {
              $entity_id = chado_get_record_entity_by_table(
                $link[$key]['entity_link']['chado_table'],
                $r->{$link[$key]['entity_link']['id_column']}
              );
              if ($entity_id) {
                $row[$key] = l($r->{$key}, '/bio_data/' . $entity_id);
              }
              else {
                $row[$key] = $r->{$key};
              }
            }
          }
          else {
            $row[$key] = '';
          }
        }
        $table['rows'][] = $row;
      }
      else {
        break;
      }
    }

    // @todo upgrade to use annotation.
    if (self::$pager == TRUE) {
      $form = $this->addPager($form, $num_rows);
    }

    $form['results'] = [
      '#type' => 'markup',
      '#theme' => 'table',
      '#weight' => 30,
    ];
    foreach ($table as $key => $value) {
      $form['results']['#' . $key] = $value;
    }
  }

  /**
   * Adds a pager to the form.
   *
   * @todo UPGRADE.
   *
   * @param array $form
   *   The form array to add the pager to.
   * @param int $num_results
   *   The number of results per page.
   *
   * @return array
   *   The original form with the pager added.
   */
  public function addPager(array $form, int $num_results) {

    // Determine the current page and offset using the query parameters.
    $q = drupal_get_query_parameters();
    $offset = (isset($q['offset'])) ? $q['offset'] : 0;
    $page_num = (isset($q['page_num'])) ? $q['page_num'] : 1;

    // HTML codes for the left/right arrow.
    $left_arrow = '&#8249;previous';
    $right_arrow = 'next&#8250;';

    // Turn left/right arrow into a link if appropriate.
    // -- Left: if we are not at the beginning then link to the previous page.
    if ($offset != 0) {
      // Determine the previous page info.
      $prev_offset = $offset - $this::$num_items_per_page;
      if ($prev_offset < 0) {
        $prev_offset = 0;
      }
      $prev_page_num = ($prev_offset == 0) ? 1 : $page_num - 1;

      // Create a link to the prev page.
      $params = $q;
      $params['offset'] = $prev_offset;
      $params['page_num'] = $prev_page_num;
      $left_arrow = l(
        $left_arrow,
        $this::$menu['path'],
        [
          'query' => $params,
          'html' => TRUE,
        ]
      );
    }
    // -- Right: if we are not at the end then link to the next page.
    if ($num_results > $this::$num_items_per_page) {
      // Determine the next page info.
      $next_offset = $offset + $this::$num_items_per_page;
      if ($next_offset < 0) {
        $next_offset = 0;
      }
      $next_page_num = ($next_offset == 0) ? 1 : $page_num + 1;

      // Create a link to the next page.
      $params = $q;
      $params['offset'] = $next_offset;
      $params['page_num'] = $next_page_num;
      $right_arrow = l(
        $right_arrow,
        $this::$menu['path'],
        [
          'query' => $params,
          'html' => TRUE,
        ]
      );
    }

    $form['pager_style'] = [
      '#type' => 'markup',
      '#markup' => '<style>
      .pager-container {
        width: 100%;
        text-align: center;
      }
      .pager {
        display: inline-block;
      }
      .pager-prev, .pager-next {
        font-style: italic;
      }
      .pager-page {
        font-weight: bold;
        font-size: 1.1em;
      }
      </style>',
    ];

    $form['pager'] = [
      '#type' => 'markup',
      '#markup' => '<span class="pager-prev">' . $left_arrow . '</span>'
      . '<span class="pager-page">' . ' - Page ' . $page_num . ' - ' . '</span>'
      . '<span class="pager-next">' . $right_arrow . '</span>',
      '#prefix' => '<div class="pager-container"><div class="pager">',
      '#suffix' => '</div></div>',
      '#weight' => 100,
    ];

    return $form;
  }

  /**
   * Sets the values from the form based on user input.
   *
   * @param array $filter_values
   *   An array of the user submitted values where the key matches an element
   *   in the info['filter] array and the value is the value the user submitted
   *   for that filter.
   */
  public function setValues($filter_values) {

    // If we are setting values then we consider it submitted.
    $this->submitted = TRUE;

    // For each filter value, either set the passed in value
    // or set the default.
    foreach (static::$info['filters'] as $name => $details) {
      if (isset($filter_values[$name])) {
        $this->values[$name] = $filter_values[$name];
      }
      else {
        $this->values[$name] = (isset($details['default'])) ? $details['default'] : NULL;
      }
    }
  }

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
  public function getResults($offset) {

    // Grab the query defined for this specific child.
    $query = '';
    $args = [];
    $this->getQuery($query, $args, $offset);

    // Execute it.
    if (!empty($query)) {
      // @todo update this to the Tripal DBX API.
      return chado_query($query, $args)->fetchAll();
    }
    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast the label to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['label'];
  }

}
