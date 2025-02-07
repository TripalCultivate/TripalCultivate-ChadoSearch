<?php

namespace Drupal\Tests\trpcultivate_chadosearch\Kernel\Validators;

use Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\trpcultivate_chadosearch\Services\ChadoSearchManager;
use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use Drupal\tripal_chado\Database\ChadoConnection;
use Symfony\Component\HttpFoundation\InputBag;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Tests ChadoSearch Plugin Base functions.
 *
 * @group trpcultivate_chadosearch
 * @group trpcultivate_chadosearch_plugin
 * @group ChadoSearchForm
 */
class FormBaseTest extends ChadoTestKernelBase {

  /**
   * A Database query interface for querying Chado using Tripal DBX.
   *
   * @var \Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * The ChadoSearch plugin instance being tested.
   *
   * @var Drupal\trpcultivate_chadosearch\ChadoSearch\Interfaces\ChadoSearchInterface
   */
  protected ChadoSearchInterface $search_instance;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'tripal_chado',
    'trpcultivate_chadosearch',
  ];

  /**
   * Plugin definitions for various fake plugin instances used for testing.
   *
   * @var array
   *   A list of fake plugin instances where the key is the if of the instance
   *   and the value is the plugin definition to be based in when creating it.
   */
  protected array $plugin_definitions = [
    'basically_base' => [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ],
    'less_than_1page' => [
      'id' => "less_than_1page",
      'title' => "Search Less than one page",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => FALSE,
      'pager' => TRUE,
      'num_items_per_page' => 6,
    ],
    'exactly2pages' => [
      'id' => "exactly2pages",
      'title' => "Search exactly 2 pages",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => FALSE,
      'pager' => TRUE,
      'num_items_per_page' => 3,
    ],
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    // Set test environment.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Test Chado database.
    // Create a test chado instance and then set it in the container for use by
    // our service.
    $this->chado_connection = $this->createTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);

  }

  /**
   * HELPER: Creates the instance and sets the plugin manager to return it.
   */
  protected function setUpPluginInstance($instance_info) {

    $configuration = [];
    $plugin_id = $instance_info['id'];
    $plugin_definition = $this->plugin_definitions[$plugin_id];
    $plugin_class = '\Drupal\Tests\trpcultivate_chadosearch\Fixtures\\' . $instance_info['class'];
    $this->search_instance = new $plugin_class(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $this->chado_connection
    );

    // Mock the plugin manager to ensure it returns our search instance.
    $manager = $this->createMock(ChadoSearchManager::class);
    $manager->method('createInstance')
      ->willReturn($this->search_instance);
    $this->container->set('trpcultivate_chadosearch.manager', $manager);
  }

  /**
   * HELPER: Sets up mocks for the query params of the current request.
   */
  protected function setUpUrlQueryParams($query_params) {

    // InputBag.
    $query = new InputBag(
      $query_params,
    );

    // Current request.
    $current_request = $this->createMock(Request::class);
    $current_request->query = $query;
    $current_request->request = $query;

    // Request Stack.
    $request_stack = $this->createMock(RequestStack::class);
    $request_stack->method('getCurrentRequest')
      ->willReturn($current_request);
    $this->container->set('request_stack', $request_stack);
  }

  /**
   * Data Provider: provides fake instances and associated expectations.
   *
   * @return array
   *   An array of scenarios.
   */
  public static function provideChadoSearchScenarios() {
    $scenario = [];

    // First lets define the instance info to be used for multiple scenarios.
    $instance_info = [];
    $instance_info['basically_base'] = [
      'id' => 'basically_base',
      'class' => 'ChadoSearchBasicallyBase',
    ];
    $instance_info['less_than_1page'] = [
      'id' => 'less_than_1page',
      'class' => 'ChadoSearchLess1Page',
    ];
    $instance_info['exactly2pages'] = [
      'id' => 'exactly2pages',
      'class' => 'ChadoSearchExactly2Pages',
    ];

    // #0
    // Basic test ignoring the query and as close to the base class as possible.
    $scenario[] = [
      $instance_info['basically_base'],
      [
        'offset' => 0,
        'page_num' => 1,
      ],
    ];

    // #1
    // Ignoring the query, returns 3 results (<1 page), check the first page.
    $scenario[] = [
      $instance_info['less_than_1page'],
      [
        'offset' => 0,
        'page_num' => 1,
      ],
    ];

    // #2
    // Ignoring the query, returns 3 results (<1 page), check the second page.
    $scenario[] = [
      $instance_info['less_than_1page'],
      [
        'offset' => 6,
        'page_num' => 2,
      ],
    ];

    // #3
    // Ignoring the query, returns 6 results (=2 page3), check the first page.
    $scenario[] = [
      $instance_info['exactly2pages'],
      [
        'offset' => 0,
        'page_num' => 1,
      ],
    ];

    // #4
    // Ignoring the query, returns 6 results (=2 page3), check the second page.
    $scenario[] = [
      $instance_info['exactly2pages'],
      [
        'offset' => 3,
        'page_num' => 2,
      ],
    ];

    return $scenario;
  }

  /**
   * Tests the form: no querying, no results.
   *
   * @dataProvider provideChadoSearchScenarios
   */
  public function testForm($instance_info, $query_params) {

    $this->setUpPluginInstance($instance_info);
    $this->setUpURLQueryParams($query_params);

    // Build the form using the Drupal form builder.
    $form = \Drupal::formBuilder()->getForm(
      'Drupal\trpcultivate_chadosearch\Form\ChadoSearchForm',
      'basically_base'
    );
    $this->assertIsArray($form, "We expect the form returned to be an array.");
    $this->assertEquals(
      'trpcultivate_chadosearch_search',
      $form['#form_id'],
      'We did not get the form id we expected.'
    );

  }

}
