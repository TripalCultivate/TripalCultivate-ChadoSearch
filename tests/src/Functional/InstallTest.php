<?php

namespace Drupal\Tests\trpcultivate_chadosearch\Functional;

use Drupal\Core\Url;
use Drupal\Tests\tripal_chado\Functional\ChadoTestBrowserBase;
use Drupal\tripal_chado\Database\ChadoConnection;

/**
 * Simple test to ensure that main page loads with module enabled.
 *
 * @group ChadoSearch
 * @group Installation
 */
class InstallTest extends ChadoTestBrowserBase {

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
  protected static $modules = ['help', 'tripal', 'tripal_chado'];

  /**
   * The name of your module in the .info.yml.
   *
   * @var string
   */
  protected static string $module_name = 'Chado Search API';

  /**
   * The machine name of this module.
   *
   * @var string
   */
  protected static string $module_machinename = 'trpcultivate_chadosearch';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {

    parent::setUp();

    // Ensure we see all logging in tests.
    \Drupal::state()->set('is_a_test_environment', TRUE);

    // Open connection to Chado.
    $this->chado_connection = $this->getTestSchema(ChadoTestBrowserBase::PREPARE_TEST_CHADO);

    $moduleHandler = $this->container->get('module_handler');
    $moduleInstaller = $this->container->get('module_installer');
    $this->assertFalse($moduleHandler->moduleExists(self::$module_machinename));
    $this->assertTrue($moduleInstaller->install([self::$module_machinename]));
  }

  /**
   * Tests that a specific set of pages load with a 200 response.
   */
  public function testLoad() {
    $session = $this->getSession();

    // Ensure we have an admin user.
    $user = $this->drupalCreateUser(['access administration pages', 'administer modules']);
    $this->drupalLogin($user);

    $context = '(modules installed: ' . implode(',', self::$modules) . ')';

    // Front Page.
    $this->drupalGet(Url::fromRoute('<front>'));
    $status_code = $session->getStatusCode();
    $this->assertEquals(200, $status_code, "The front page should be able to load $context.");

    // Extend Admin page.
    $this->drupalGet('admin/modules');
    $status_code = $session->getStatusCode();
    $this->assertEquals(200, $status_code, "The module install page should be able to load $context.");
    $this->assertSession()->pageTextContains(self::$module_name);
  }

}
