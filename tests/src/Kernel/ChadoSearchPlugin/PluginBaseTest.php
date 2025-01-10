<?php

namespace Drupal\Tests\chado_search\Kernel\Validators;

use Drupal\Core\Form\FormState;
use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use Drupal\Tests\chado_search\Fixtures\ChadoSearchBasicallyBase;
use Drupal\tripal_chado\Database\ChadoConnection;

/**
 * Tests ChadoSearch Plugin Base functions.
 *
 * @group chado_search
 * @group chado_search_plugin
 */
class PluginBaseTest extends ChadoTestKernelBase {

  /**
   * A Database query interface for querying Chado using Tripal DBX.
   *
   * @var \Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'tripal_chado',
    'chado_search',
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
   * Tests dependency injection by checking the create() method works.
   */
  public function testCreateMethod() {

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];

    // First we create using the create() static method.
    $created_by_create = ChadoSearchBasicallyBase::create(
      $this->container,
      $configuration,
      $plugin_id,
      $plugin_definition
    );

    // Second we create using the constructor and grabbing things from the
    // container ourselves.
    $created_by_constructor = new ChadoSearchBasicallyBase(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $this->chado_connection
    );

    $this->assertEquals(
      $created_by_constructor,
      $created_by_create,
      "We should be able to create the object via the contructor or the create method equally."
    );
  }

  /**
   * Test the annotation getters in the Base Class.
   *
   * Note: I could have done a data provider but it would not have been as
   * performant. Since these tests simply check the correct annotation is
   * returned the performance hit did not seem worth it.
   */
  public function testBaseAnnotationGetters() {

    $expected_annotation = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $expected_libraries = ['library1', 'library2', 'library3'];
    $expected_fields = [
      'column1' => [
        'title' => 'Column1',
      ],
      'column2' => [
        'title' => 'Column2',
      ],
    ];
    $expected_filters = [
      'column1' => [
        'title' => 'Column1',
        'help' => 'The first filter.',
      ],
      'column2' => [
        'title' => 'Column2',
        'help' => 'The second filter.',
      ],
      'column3' => [
        'title' => 'Column3',
        'help' => 'The third filter.',
        'default' => 'FRED',
      ],
    ];

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = $expected_annotation;
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    // ID.
    $returned_value = $instance->id();
    $this->assertEquals(
      $expected_annotation['id'],
      $returned_value,
      "The ID did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Title.
    $returned_value = $instance->title();
    $this->assertEquals(
      $expected_annotation['title'],
      $returned_value,
      "The title did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Label.
    $returned_title = $returned_value;
    $returned_value = $instance->label();
    $this->assertEquals(
      $expected_annotation['title'],
      $returned_value,
      "The title did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );
    $this->assertEquals($returned_title, $returned_value, "The label should match the title that was returned.");

    // description.
    $returned_value = $instance->description();
    $this->assertEquals(
      $expected_annotation['description'],
      $returned_value,
      "The description did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // permissions.
    $returned_value = $instance->permissions();
    $this->assertEquals(
      $expected_annotation['permissions'],
      $returned_value,
      "The permissions array did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // URL Path.
    $returned_value = $instance->urlPath();
    $this->assertEquals(
      $expected_annotation['url_path'],
      $returned_value,
      "The URL path did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Button Text.
    $returned_value = $instance->buttonText();
    $this->assertEquals(
      $expected_annotation['button_text'],
      $returned_value,
      "The Button Text did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Require Submit.
    $returned_value = $instance->requireSubmit();
    $this->assertEquals(
      $expected_annotation['require_submit'],
      $returned_value,
      "The require submit did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Pager.
    $returned_value = $instance->usePager();
    $this->assertEquals(
      $expected_annotation['pager'],
      $returned_value,
      "The pager did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Number of items per page.
    $returned_value = $instance->numItemsPerPage();
    $this->assertEquals(
      $expected_annotation['num_items_per_page'],
      $returned_value,
      "The Number of items per page did not match what we expected based on the annotation set for ChadoSearchBasicallyBase.",
    );

    // Libraries.
    $returned_value = $instance->getLibraries();
    $this->assertIsArray(
      $returned_value,
      "The libraries returned should be an array.",
    );
    $this->assertEquals(
      $expected_libraries,
      $returned_value,
      "The libraries did not match what we expected based on the attached property set for ChadoSearchBasicallyBase.",
    );

    // Defined Fields.
    $returned_value = $instance->getDefinedFields();
    $this->assertIsArray(
      $returned_value,
      "The defined fields returned should be an array.",
    );
    $this->assertEquals(
      $expected_fields,
      $returned_value,
      "The fields returned did not match what we expected based on the info property set for ChadoSearchBasicallyBase.",
    );

    // Defined Filters.
    $returned_value = $instance->getDefinedFilters();
    $this->assertIsArray(
      $returned_value,
      "The defined filters returned should be an array.",
    );
    $this->assertEquals(
      $expected_filters,
      $returned_value,
      "The filters returned did not match what we expected based on the info property set for ChadoSearchBasicallyBase.",
    );
  }

  /**
   * Provides both submitted and expected values to the tests.
   *
   * @return array
   *   An array of scenarios where each scenario has two keys:
   *    - submitted: an array of values to submit.
   *    - expected: an array of values we expect to get back.
   */
  public static function provideValues() {
    $scenarios = [];

    // Both columns defined.
    $scenario = [
      'submitted' => [
        'column1' => 'NAME ' . uniqid(),
        'column2' => 'Tripalus databasica',
        'column3' => 'Sarah',
      ],
    ];
    $scenario['expected'] = $scenario['submitted'];
    $scenarios[] = $scenario;

    // Column2 missing with no default.
    $scenario = [
      'submitted' => [
        'column1' => 'NAME ' . uniqid(),
        'column3' => 'Sarah',
      ],
    ];
    $scenario['expected'] = $scenario['submitted'];
    $scenario['expected']['column2'] = NULL;
    $scenarios[] = $scenario;

    // Column3 missing; default is FRED.
    $scenario = [
      'submitted' => [
        'column1' => 'NAME ' . uniqid(),
        'column2' => 'Tripalus databasica',
      ],
    ];
    $scenario['expected'] = $scenario['submitted'];
    $scenario['expected']['column3'] = 'FRED';
    $scenarios[] = $scenario;

    return $scenarios;
  }

  /**
   * Tests the get + set methods managing values.
   *
   * @dataProvider provideValues
   */
  public function testBaseValueGetterSetters(array $submitted_values, array $expected_values) {

    $expected_defaults = [
      'column1' => NULL,
      'column2' => NULL,
      'column3' => 'FRED',
    ];

    // FIRST: Test the get/setValues (plural).
    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    // Try getting the values before we have set any.
    $retrieved_values = $instance->getValues();
    $this->assertEquals(
      $expected_defaults,
      $retrieved_values,
      "There should only be defaults before we set values."
    );

    // Try setting values.
    $instance->setValues($submitted_values);

    // Now try getting them.
    $retrieved_values = $instance->getValues();
    $this->assertEqualsCanonicalizing(
      $expected_values,
      $retrieved_values,
      "We were not able to retrieve the values we expected."
    );

    // SECOND: Test the getValue (singular).
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    // Try getting the values before we have set any.
    foreach ($expected_defaults as $key => $expected_default) {
      $retrieved_value = $instance->getValue($key);
      $this->assertEquals(
        $expected_default,
        $retrieved_value,
        "There should only be defaults before we set values but this specific value was not the expected default."
      );
    }

    // Try setting values.
    $instance->setValues($submitted_values);

    // Now try getting them one at a time.
    foreach ($expected_values as $key => $expected_value) {
      $retrieved_value = $instance->getValue($key);
      $this->assertEquals(
        $expected_value,
        $retrieved_value,
        "The retrieved value was not what we expected when retrieved on it's own."
      );
    }
  }

  /**
   * Tests the plugin default functions around the form.
   */
  public function testFormFunctions() {

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    // Check the form array.
    $form_state = new FormState();
    $form = [];
    $form = $instance->form($form, $form_state);
    $this->assertIsArray($form, "We should have been given a form array.");
    $this->assertStringContainsString(
      $plugin_definition['description'],
      $form['header']['#markup'],
    );
    foreach (['column1', 'column2', 'column3'] as $key) {
      $this->assertArrayHasKey($key, $form, "We expect the form to have an element for each filter defined by the plugin.");
      $this->assertArrayHasKey('#type', $form[$key], "Each form element for a filter should be a render array.");
      $this->assertEquals('textfield', $form[$key]['#type'], "Eacg form element for a filter should be a textfield by default.");
    }
    $this->assertArrayHasKey('submit', $form, "There should be a submit button.");

    // Currently the base validate doesn't do anything so we can just call it.
    $instance->validateForm($form, $form_state);

    // Pager Offset.
    $instance->setPagerOffset(25);
    $retrieved_offset = $instance->getPagerOffset();
    $this->assertEquals(25, $retrieved_offset, "Pager Offset: we expect to get the same value we set.");

    // Current Pager number (used by pager).
    $instance->setCurrentPageNumber(2);
    $retrieved_page_num = $instance->getCurrentPageNumber();
    $this->assertEquals(2, $retrieved_page_num, "Current Page Number: we expect to get the same value we set.");
  }

  /**
   * Provides values when testing the pager.
   *
   * @return array
   *   An array of scenarios.
   */
  public static function providePagerValues() {
    $scenarios = [];

    $scenarios[] = [
      'settings' => [
        'page_num' => 1,
        'offset' => 0,
        'num_results' => 5,
      ],
    ];

    $scenarios[] = [
      'settings' => [
        'page_num' => 2,
        'offset' => 25,
        'num_results' => 30,
      ],
    ];

    $scenarios[] = [
      'settings' => [
        'page_num' => 2,
        'offset' => 25,
        'num_results' => 5,
      ],
    ];

    $scenarios[] = [
      'settings' => [
        'page_num' => 1,
        'offset' => 5,
        'num_results' => 5,
      ],
    ];

    return $scenarios;
  }

  /**
   * Tests the plugin default functions around the pager and its management.
   *
   * @dataProvider providePagerValues
   *
   * @todo check that the left and right nav are a string or link depending
   * on the page number and number of results.
   */
  public function testAddPager(array $settings) {

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    $instance->setCurrentPageNumber($settings['page_num']);
    $instance->setPagerOffset($settings['offset']);
    $form = [];
    $form = $instance->addPager($form, $settings['num_results']);
    $pager = $form['pager'];
    $this->assertIsArray($pager, "We expect addPager to return an array.");
    $this->assertArrayHasKey('#type', $pager, "We expect addPager to return an RENDER array.");

    // We expect the following base keys.
    foreach (['left_nav', 'page', 'right_nav'] as $key) {
      $this->assertArrayHasKey($key, $pager, "NOT PRESENT: We expect this base sub-render array in the pager.");
      $this->assertIsArray($pager[$key], "NOT ARRAY: We expect this base sub-render array in the pager.");
      $this->assertArrayHasKey('#type', $pager[$key], "NOT RENDER ARRAY: We expect this base sub-render array in the pager.");
    }

  }

  /**
   * Test get results.
   */
  public function testGetResults() {

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    // Instance->getQuery() will return NULL if offset:5 is passed in.
    $results = $instance->getResults(5);
    $this->assertFalse($results, "We expect to have false returned when getQuery() returns NULL");

    // Instance->getQuery() will return all organism if offset:10 is passed in.
    $this->chado_connection->insert('1:organism')
      ->fields([
        'genus' => 'Tripalus',
        'species' => 'databasica',
      ])
      ->execute();
    $results = $instance->getResults(10);
    $this->assertIsArray($results, "We expect to have results returned when getQuery() returns a query.");
    $this->assertCount(1, $results, "There should be one result since we inserted one organism.");
  }

  /**
   * Test formatting results.
   */
  public function testFormatResults() {

    $configuration = [];
    $plugin_id = 'basically_base';
    $plugin_definition = [
      'id' => "basically_base",
      'title' => "Basically Base",
      'description' => "A Fake plugin instance to test the base plugin class.",
      'permissions' => ["access content"],
      'url_path' => "search-fakers",
      'button_text' => "Search",
      'require_submit' => TRUE,
      'pager' => TRUE,
      'num_items_per_page' => 25,
    ];
    $instance = new ChadoSearchBasicallyBase($configuration, $plugin_id, $plugin_definition, $this->chado_connection);
    $this->assertIsObject(
      $instance,
      "Unable to create ChadoSearchBasicallyBase plugin instance to test the base class."
    );

    $form = [];
    $results = [];
    $results[1] = [
      'column1' => 'val1A',
    ];
    $results[1] = (object) $results[1];
    $results[2] = [
      'column2' => 'val2B',
    ];
    $results[2] = (object) $results[2];
    $instance->formatResults($form, $results);
    $this->assertArrayHasKey('results', $form, "There should be results added to the form");
    $this->arrayHasKey('#type', $form['results'], "The results should be a render array.");
    $this->assertEquals('table', $form['results']['#type'], "The results should be a table.");
    $this->assertArrayHasKey('#rows', $form['results'], "There should be rows in the table.");
    $this->assertCount(2, $form['results']['#rows'], "There should be two rows.");
  }

}
