<?php

namespace Drupal\migration_module\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\migration_module\Entity\CityEntityInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class CityEntityController.
 *
 *  Returns responses for Cities List routes.
 */
class CityEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The date formatter.
   *
   * @var \Drupal\Core\Datetime\DateFormatter
   */
  protected $dateFormatter;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\Renderer
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $instance = parent::create($container);
    $instance->dateFormatter = $container->get('date.formatter');
    $instance->renderer = $container->get('renderer');
    return $instance;
  }

  /**
   * Displays a Cities List revision.
   *
   * @param int $city_entity_revision
   *   The Cities List revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($city_entity_revision) {
    $city_entity = $this->entityTypeManager()->getStorage('city_entity')
      ->loadRevision($city_entity_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('city_entity');

    return $view_builder->view($city_entity);
  }

  /**
   * Page title callback for a Cities List revision.
   *
   * @param int $city_entity_revision
   *   The Cities List revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($city_entity_revision) {
    $city_entity = $this->entityTypeManager()->getStorage('city_entity')
      ->loadRevision($city_entity_revision);
    return $this->t('Revision of %title from %date', [
      '%title' => $city_entity->label(),
      '%date' => $this->dateFormatter->format($city_entity->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of a Cities List.
   *
   * @param \Drupal\migration_module\Entity\CityEntityInterface $city_entity
   *   A Cities List object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(CityEntityInterface $city_entity) {
    $account = $this->currentUser();
    $city_entity_storage = $this->entityTypeManager()->getStorage('city_entity');

    $langcode = $city_entity->language()->getId();
    $langname = $city_entity->language()->getName();
    $languages = $city_entity->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $city_entity->label()]) : $this->t('Revisions for %title', ['%title' => $city_entity->label()]);

    $header = [$this->t('Revision'), $this->t('Operations')];
    $revert_permission = (($account->hasPermission("revert all cities list revisions") || $account->hasPermission('administer cities list entities')));
    $delete_permission = (($account->hasPermission("delete all cities list revisions") || $account->hasPermission('administer cities list entities')));

    $rows = [];

    $vids = $city_entity_storage->revisionIds($city_entity);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\migration_module\Entity\CityEntityInterface $revision */
      $revision = $city_entity_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = $this->dateFormatter->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $city_entity->getRevisionId()) {
          $link = Link::fromTextAndUrl($date, new Url('entity.city_entity.revision', [
            'city_entity' => $city_entity->id(),
            'city_entity_revision' => $vid,
          ]))->toString();
        }
        else {
          $link = $city_entity->toLink($date)->toString();
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $this->renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];
        $row[] = $column;

        if ($latest_revision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];
          foreach ($row as &$current) {
            $current['class'] = ['revision-current'];
          }
          $latest_revision = FALSE;
        }
        else {
          $links = [];
          if ($revert_permission) {
            $links['revert'] = [
              'title' => $this->t('Revert'),
              'url' => $has_translations ?
              Url::fromRoute('entity.city_entity.translation_revert', [
                'city_entity' => $city_entity->id(),
                'city_entity_revision' => $vid,
                'langcode' => $langcode,
              ]) :
              Url::fromRoute('entity.city_entity.revision_revert', [
                'city_entity' => $city_entity->id(),
                'city_entity_revision' => $vid,
              ]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.city_entity.revision_delete', [
                'city_entity' => $city_entity->id(),
                'city_entity_revision' => $vid,
              ]),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];
        }

        $rows[] = $row;
      }
    }

    $build['city_entity_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
