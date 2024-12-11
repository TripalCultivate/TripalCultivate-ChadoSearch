<?php

namespace Drupal\chado_search\Form;

use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\chado_search\Services\ChadoSearchManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a Chado Search API form.
 */
final class ChadoSearchForm extends FormBase {

  /**
   * The plugin manager for chadosearch plugin instances.
   *
   * @var \Drupal\chado_search\Services\ChadoSearchManager
   */
  protected ChadoSearchManager $chado_search_manager;

  /**
   * The instance powering this search.
   *
   * @var Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface
   */
  protected ChadoSearchInterface $chado_search_instance;

  /**
   * The route match service.
   *
   * @var \Drupal\Core\Routing\CurrentRouteMatch
   */
  protected CurrentRouteMatch $route_match_service;

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'chado_search_search';
  }

  /**
   * Overrides the FormBase create.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The current service container.
   */
  public static function create(ContainerInterface $container) {
    $form = new static();

    $form->setChadoSearchManager($container->get('chado_search.manager'));
    $form->setRouteMatchService($container->get('current_route_match'));

    return $form;
  }

  /**
   * Sets the chado search plugin manager.
   *
   * @param Drupal\chado_search\Services\ChadoSearchManager $manager
   *   The plugin manager to set.
   */
  public function setChadoSearchManager(ChadoSearchManager $manager) {
    $this->chado_search_manager = $manager;
  }

  /**
   * Sets the route match service.
   *
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_service
   *   The route match service to be set.
   */
  public function setRouteMatchService(CurrentRouteMatch $route_service) {
    $this->route_match_service = $route_service;
  }

  /**
   * Retrieves the instance of the ChadoSearch plugin instance.
   *
   * @param string $instance_id
   *   The unique id of the ChadoSearch plugin instance for this search.
   *
   * @return Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface
   *   The instance powering this search!
   */
  public function getChadoSearchInstance(string $instance_id) {

    // Get the instance using the plugin manager.
    // We'll save it to a property for use later and also return it.
    $this->chado_search_instance = $this->chado_search_manager->createInstance($instance_id);
    return $this->chado_search_instance;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, string|null $instance_id = NULL): array {
    $q = $this->route_match_service->getParameters()->getIterator();

    // Get the instance for the search powering this form.
    $instance = $this->getChadoSearchInstance($instance_id);

    // Set the title as expected.
    $form['#title'] = $instance->title();

    // Save the class name for use in the submit.
    $form['class_name'] = [
      '#type' => 'hidden',
      '#value' => get_class($instance),
    ];

    // Add CSS/JS as defined in the class. We do this here so that it can
    // be added to and/or altered in the form method.
    $form['#attached']['library'][] = 'chado_search/search-form';
    foreach ($instance->getLibraries() as $library) {
      $form['#attached']['library'][] = $library;
    }

    // Now let the instance add to the form.
    $form = $instance->form($form, $form_state);

    // If we have values then we need to query!
    $doQuery = FALSE;
    $values = $form_state->getValues();
    if (!empty($values)) {
      $doQuery = TRUE;
    }
    elseif ($instance->requireSubmit() == FALSE) {
      $doQuery = TRUE;

      // Ensure that initial query takes into account URL parameters if present.
      $values = [];
      foreach ($instance->getDefinedFilters() as $name => $details) {
        if (isset($q[$name])) {
          $values[$name] = $q[$name];
        }
      }
    }
    elseif (!empty($q)) {
      $doQuery = TRUE;

      // Ensure that initial query takes into account URL parameters if present.
      $values = [];
      foreach ($instance->getDefinedFilters() as $name => $details) {
        if (isset($q[$name])) {
          $values[$name] = $q[$name];
        }
      }
    }

    if ($doQuery) {

      $instance->setValues($values);

      $offset = (isset($q['offset'])) ? $q['offset'] : 0;
      if (!is_numeric($offset)) {
        $offset = 0;
      }
      $results = $instance->getResults($offset);

      if ($results !== FALSE) {
        $instance->formatResults($form, $results);
      }
    }

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    $this->chado_search_instance->validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {

    // If we have values then we want to add them to the URL
    // for bookmarking of searches.
    $values = $form_state->getValues();
    if (!empty($values)) {

      // We want to add the value of each filter to the path.
      $params = [];
      foreach ($this->chado_search_instance->getDefinedFilters() as $name => $details) {
        if (is_string($values[$name])) {
          $params[$name] = trim($values[$name]);
        }
        elseif (is_array($values[$name])) {
          foreach ($values[$name] as $key => $value) {
            if (is_string($value)) {
              $params[$name][$key] = trim($value);
            }
            elseif (is_array($value)) {
              foreach ($value as $key2 => $value2) {
                $params[$name][$key][$key2] = trim($value2);
              }
            }
          }
        }
      }

      $route_name = 'chado_search.form.' . $this->chado_search_instance->id();
      $url = Url::fromRoute($route_name, $params);
      $form_state->setRedirectUrl($url);
    }
  }

}
