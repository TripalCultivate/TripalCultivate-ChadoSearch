<?php

namespace Drupal\chado_search\Form;

use Drupal\chado_search\ChadoSearch\Interfaces\ChadoSearchInterface;
use Drupal\chado_search\Services\ChadoSearchManager;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
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

    // Let the instance completely control building of the form!
    $instance = $this->getChadoSearchInstance($instance_id);
    $form = $instance->form($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state): void {
    // @todo Validate the form here.
    // Example:
    // @code
    //   if (mb_strlen($form_state->getValue('message')) < 10) {
    //     $form_state->setErrorByName(
    //       'message',
    //       $this->t('Message should be at least 10 characters.'),
    //     );
    //   }
    // @endcode
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state): void {
    $this->messenger()->addStatus($this->t('The message has been sent.'));
    $form_state->setRedirect('<front>');
  }

}
