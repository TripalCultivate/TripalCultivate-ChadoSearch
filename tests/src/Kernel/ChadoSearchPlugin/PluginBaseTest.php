<?php

namespace Drupal\Tests\chado_search\Kernel\Validators;

use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use Drupal\Tests\chado_search\Fixtures\ChadoSearchBasicallyBase;
use Drupal\tripal_chado\Database\ChadoConnection;
use Drupal\chado_search\Services\ChadoSearchManager;

/**
 * Tests ChadoSearch Plugin Base functions.
 *
 * @group chado_search
 * @group chado_search_plugin
 */
class PluginBaseTest extends ChadoTestKernelBase {

  /**
   * The Validators plugin manager for creating new validator instances.
   *
   * @var \Drupal\chado_search\Services\ChadoSearchManager
   */
  protected ChadoSearchManager $plugin_manager;

  /**
   * A Database query interface for querying Chado using Tripal DBX.
   *
   * @var \Drupal\tripal_chado\Database\ChadoConnection
   */
  protected ChadoConnection $chado_connection;

  /**
   * Configuration.
   *
   * @var config_entity
   */
  private $config;

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

    // Install module configuration.
    $this->installConfig(['chado_search']);

    // Test Chado database.
    // Create a test chado instance and then set it in the container for use by
    // our service.
    $this->chado_connection = $this->createTestSchema(ChadoTestKernelBase::PREPARE_TEST_CHADO);

    // Set plugin manager service.
    $this->plugin_manager = \Drupal::service('chado_search.manager');
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
      'mom_name' => [
        'title' => 'Column2',
        'help' => 'The second filter.',
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

}
