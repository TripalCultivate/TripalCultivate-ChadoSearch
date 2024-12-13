<?php

namespace Drupal\Tests\chado_search\Kernel\Validators;

use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\chado_search\Services\ChadoSearchManager;
use Drupal\Tests\tripal_chado\Kernel\ChadoTestKernelBase;
use Drupal\Tests\chado_search\Fixtures\ChadoSearchBasicallyBase;
use Drupal\tripal_chado\Database\ChadoConnection;

/**
 * Tests ChadoSearch Plugin Base functions.
 *
 * @group chado_search
 * @group chado_search_plugin
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
   * @var Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface
   */
  protected ChadoSearchInterface $search_instance;

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
    $this->search_instance = new ChadoSearchBasicallyBase(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $this->chado_connection
    );

    // Mock the plugin manager to ensure it returns our search instance.
    $manager = $this->createMock(ChadoSearchManager::class);
    $manager->method('createInstance')
      ->willReturn($this->search_instance);
    $this->container->set('chado_search.manager', $manager);
  }

  /**
   * Tests the form without worrying about the query being performed.
   */
  public function testForm() {

    // Build the form using the Drupal form builder.
    $form = \Drupal::formBuilder()->getForm(
      'Drupal\chado_search\Form\ChadoSearchForm',
      'basically_base'
    );
    $this->assertIsArray($form, "We expect the form returned to be an array.");
    $this->assertEquals(
      'chado_search_search',
      $form['#form_id'],
      'We did not get the form id we expected.'
    );

  }

}
