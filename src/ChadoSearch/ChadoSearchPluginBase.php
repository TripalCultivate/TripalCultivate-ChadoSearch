<?php

namespace Drupal\chado_search\ChadoSearch;

use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\Core\Url;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Form\FormState;
use Drupal\tripal_chado\Database\ChadoConnection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for chado_search plugins.
 */
abstract class ChadoSearchPluginBase extends PluginBase implements ChadoSearchInterface, ContainerFactoryPluginInterface {

  /**
   * Add CSS/JS to the form/results page through libraries.
   *
   * NOTE: the libraries listed here must already be defined in the
   * libraries.yml file according to Drupal standards.
   *
   * @var array
   */
  public static array $attached = [];

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
  public array $values = [];

  /**
   * Whether the user clicked the search button.
   *
   * @var bool
   */
  public bool $submitted = FALSE;

  /**
   * The current page of results being shown.
   *
   * @var int
   */
  public int $pager_current_page = 1;

  /**
   * The offset to use in the query to show the current page of results.
   *
   * @var int
   */
  public int $pager_offset = 0;


  /**
   * The Tripal DBX Chado Connection.
   *
   * @var \Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * Implements ContainerFactoryPluginInterface->create().
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The Drupal service container.
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   *
   * @return static
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('tripal_chado.database'),
    );
  }

  /**
   * Constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\tripal_chado\Database\ChadoConnection $chado_connection
   *   The Tripal DBX Chado Connection service.
   */
  public function __construct(array $configuration, string $plugin_id, mixed $plugin_definition, ChadoConnection $chado_connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->chado_connection = $chado_connection;
    $this->submitted = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormState $form_state): array {

    $form['header'] = [
      '#type' => 'markup',
      '#markup' => '<p>' . $this->description() . '</p>',
      '#weight' => -10000,
    ];

    foreach ($this->getDefinedFilters() as $name => $details) {
      $form[$name] = [
        '#type' => 'textfield',
        '#title' => $details['title'],
        '#description' => $details['help'],
        '#default_value' => $this->getValue($name),
      ];
    }

    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->buttonText(),
      '#weight' => 20,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array $form, FormState $form_state): void {
  }

  /**
   * {@inheritdoc}
   */
  public function formatResults(array &$form, array $results): void {

    $table = [
      'attributes' => [],
      'caption' => '',
      'colgroups' => [],
      'sticky' => TRUE,
      'empty' => 'No results matching the specified criteria were returned.',
    ];
    $table['header'] = [];
    $template_row = [];
    $link = [];
    foreach ($this->getDefinedFields() as $name => $details) {
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
      if ($num_rows <= $this->numItemsPerPage()) {
        $row = [];
        foreach ($template_row as $key => $default) {
          if (isset($r->{$key})) {
            $row[$key] = $r->{$key};
            // @todo implement support for entity links.
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

    if ($this->usePager() == TRUE) {
      $form = $this->addPager($form, $num_rows);
    }

    $form['results'] = [
      '#type' => 'table',
      '#weight' => 30,
    ];
    foreach ($table as $key => $value) {
      $form['results']['#' . $key] = $value;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function addPager(array $form, int $num_results): array {

    // Determine the current page and offset using the query parameters.
    $offset = $this->getPagerOffset();
    $page_num = $this->getCurrentPageNumber();

    // HTML codes for the left/right arrow.
    $left_arrow = '&#8249;previous';
    $right_arrow = 'next&#8250;';

    // URL Parameters based on non-empty values.
    $query_params = [];
    foreach ($this->getValues() as $name => $value) {
      if (!empty($value)) {
        $query_params[$name] = $value;
      }
    }

    // Turn left/right arrow into a link if appropriate.
    // -- Left: if we are not at the beginning then link to the previous page.
    if ($offset != 0) {
      // Determine the previous page info.
      $prev_offset = $offset - $this->numItemsPerPage();
      if ($prev_offset < 0) {
        $prev_offset = 0;
      }
      $prev_page_num = ($prev_offset == 0) ? 1 : $page_num - 1;

      // Create a link to the prev page.
      $params = $query_params;
      $params['offset'] = $prev_offset;
      $params['page_num'] = $prev_page_num;
      $route_name = 'chado_search.form.' . $this->id();
      $left_arrow = [
        '#type' => 'link',
        '#title' => ['#markup' => $left_arrow],
        '#url' => url::fromRoute($route_name, $params),
      ];
    }
    else {
      $left_arrow = [
        '#type' => 'markup',
        '#markup' => $left_arrow,
      ];
    }
    $left_arrow['#prefix'] = '<span class="pager-prev">';
    $left_arrow['#suffix'] = '</span>';
    // -- Right: if we are not at the end then link to the next page.
    if ($num_results > $this->numItemsPerPage()) {
      // Determine the next page info.
      $next_offset = $offset + $this->numItemsPerPage();
      if ($next_offset < 0) {
        $next_offset = 0;
      }
      $next_page_num = ($next_offset == 0) ? 1 : $page_num + 1;

      // Create a link to the next page.
      $params = $query_params;
      $params['offset'] = $next_offset;
      $params['page_num'] = $next_page_num;
      $route_name = 'chado_search.form.' . $this->id();
      $right_arrow = [
        '#type' => 'link',
        '#title' => ['#markup' => $right_arrow],
        '#url' => Url::fromRoute($route_name, $params),
      ];
    }
    else {
      $right_arrow = [
        '#type' => 'markup',
        '#markup' => $right_arrow,
      ];
    }
    $right_arrow['#prefix'] = '<span class="pager-next">';
    $right_arrow['#suffix'] = '</span>';

    $form['pager'] = [
      '#prefix' => '<div class="chadosearch-pager-container"><div class="pager">',
      '#suffix' => '</div></div>',
      '#weight' => 100,
    ];
    $form['pager']['left_nav'] = $left_arrow;
    $form['pager']['page'] = [
      '#type' => 'markup',
      '#prefix' => '<span class="pager-page">',
      '#markup' => ' - Page ' . $page_num . ' - ',
      '#suffix' => '</span>',
    ];
    $form['pager']['right_nav'] = $right_arrow;

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getResults($offset): array|FALSE {

    // Grab the query defined for this specific child.
    $query = '';
    $args = [];
    $this->getQuery($query, $args, $offset);

    // Execute it.
    if (is_string($query) && !empty($query)) {
      return $this->chado_connection->query($query, $args)->fetchAll();
    }
    else {
      $result = $query->execute();
      return $result->fetchAll();
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function setPagerOffset(int $offset): void {
    $this->pager_offset = $offset;
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentPageNumber(int $page_number): void {
    $this->pager_current_page = $page_number;
  }

  /**
   * {@inheritdoc}
   */
  public function setValues($filter_values): void {

    // If we are setting values then we consider it submitted.
    $this->submitted = TRUE;

    // For each filter value, either set the passed in value
    // or set the default.
    foreach ($this->getDefinedFilters() as $name => $details) {
      if (array_key_exists($name, $filter_values) && !empty($filter_values[$name])) {
        $this->values[$name] = $filter_values[$name];
      }
      else {
        $this->values[$name] = (isset($details['default'])) ? $details['default'] : NULL;
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getPagerOffset(): int {
    return $this->pager_offset;
  }

  /**
   * {@inheritdoc}
   */
  public function getCurrentPageNumber(): int {
    return $this->pager_current_page;
  }

  /**
   * {@inheritdoc}
   */
  public function getValues(): array {
    // If there are no values set yet then set the defaults at least.
    if (empty($this->values)) {
      $this->setValues([]);
    }
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public function getValue(string $name): mixed {
    // If there are no values set yet then set the defaults at least.
    if (empty($this->values)) {
      $this->setValues([]);
    }
    return $this->values[$name];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedFields(): array {
    return $this::$info['fields'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinedFilters(): array {
    return $this::$info['filters'];
  }

  /**
   * {@inheritdoc}
   */
  public function getLibraries(): array {
    return $this::$attached;
  }

  /**
   * {@inheritdoc}
   */
  public function id(): string {
    return $this->pluginDefinition['id'];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    // Cast to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function title(): string {
    // Cast to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['title'];
  }

  /**
   * {@inheritdoc}
   */
  public function description(): string {
    // Cast to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['description'];
  }

  /**
   * {@inheritdoc}
   */
  public function permissions(): array {
    return $this->pluginDefinition['permissions'];
  }

  /**
   * {@inheritdoc}
   */
  public function urlPath(): string {
    return $this->pluginDefinition['url_path'];
  }

  /**
   * {@inheritdoc}
   */
  public function buttonText(): string {
    // Cast to a string since it is a TranslatableMarkup object.
    return (string) $this->pluginDefinition['button_text'];
  }

  /**
   * {@inheritdoc}
   */
  public function requireSubmit(): bool {
    return $this->pluginDefinition['require_submit'];
  }

  /**
   * {@inheritdoc}
   */
  public function usePager(): bool {
    return $this->pluginDefinition['pager'];
  }

  /**
   * {@inheritdoc}
   */
  public function numItemsPerPage(): int {
    return $this->pluginDefinition['num_items_per_page'];
  }

}
