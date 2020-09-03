<?php

namespace Drupal\farm_setup\EventSubscriber;

use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\State\StateInterface;
use Drupal\Core\Url;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpKernel\Event\RequestEvent;
use Symfony\Component\HttpKernel\KernelEvents;

/**
 * Event subscriber for the Farm Setup module.
 *
 * Redirects the user to the setup wizard when state the
 * 'farm_setup.farm_setup_wizard_force_run' state is TRUE.
 */
class FarmSetupSubscriber implements EventSubscriberInterface {

  /**
   * State service.
   *
   * @var \Drupal\Core\State\StateInterface
   */
  protected $state;

  /**
   * Current path service.
   *
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * Current user service.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Constructs the ConfigImportSubscriber.
   *
   * @param \Drupal\Core\State\StateInterface $state
   *   State service.
   * @param \Drupal\Core\Path\CurrentPathStack $path
   *   Current path service.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user service.
   */
  public function __construct(StateInterface $state, CurrentPathStack $path, AccountProxyInterface $current_user) {
    $this->state = $state;
    $this->currentPath = $path->getPath();
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $events[KernelEvents::REQUEST][] = ['checkForWizardRun'];
    return $events;
  }

  /**
   * Check if the setup wizard needs to be run.
   *
   * @param \Symfony\Component\HttpKernel\Event\RequestEvent $event
   *   The request event.
   */
  public function checkForWizardRun(RequestEvent $event) {

    // Bail if the setup wizard doesn't need to be run.
    if (!$this->state->get('farm_setup.wizard_force_run') ?: FALSE) {
      return;
    }

    // Bail if installation is not complete.
    if (!$this->state->get('install_task') == 'done') {
      return;
    }

    // Bail if not attempting to load the homepage.
    if ($this->currentPath != '/farm') {
      return;
    }

    if (!$this->currentUser->isAuthenticated()) {
      return;
    }

    // Redirect to setup wizard page.
    global $base_url;
    $url = Url::fromRoute('farm_setup.setup_wizard')->toString();
    $response = new RedirectResponse($base_url . $url);
    $response->send();
  }

}
