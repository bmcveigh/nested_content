<?php

namespace Drupal\nested_content\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Url;
use Drupal\nested_content\Entity\NestedContentEntityInterface;

/**
 * Class NestedContentEntityController.
 *
 *  Returns responses for Nested Content routes.
 */
class NestedContentEntityController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * Displays a Nested Content  revision.
   *
   * @param int $nested_content_revision
   *   The Nested Content  revision ID.
   *
   * @return array
   *   An array suitable for drupal_render().
   */
  public function revisionShow($nested_content_revision) {
    $nested_content = $this->entityManager()->getStorage('nested_content')->loadRevision($nested_content_revision);
    $view_builder = $this->entityManager()->getViewBuilder('nested_content');

    return $view_builder->view($nested_content);
  }

  /**
   * Page title callback for a Nested Content  revision.
   *
   * @param int $nested_content_revision
   *   The Nested Content  revision ID.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle($nested_content_revision) {
    $nested_content = $this->entityManager()->getStorage('nested_content')->loadRevision($nested_content_revision);
    return $this->t('Revision of %title from %date', ['%title' => $nested_content->label(), '%date' => format_date($nested_content->getRevisionCreationTime())]);
  }

  /**
   * Generates an overview table of older revisions of a Nested Content .
   *
   * @param \Drupal\nested_content\Entity\NestedContentEntityInterface $nested_content
   *   A Nested Content  object.
   *
   * @return array
   *   An array as expected by drupal_render().
   */
  public function revisionOverview(NestedContentEntityInterface $nested_content) {
    $account = $this->currentUser();
    $langcode = $nested_content->language()->getId();
    $langname = $nested_content->language()->getName();
    $languages = $nested_content->getTranslationLanguages();
    $has_translations = (count($languages) > 1);
    $nested_content_storage = $this->entityManager()->getStorage('nested_content');

    $build['#title'] = $has_translations ? $this->t('@langname revisions for %title', ['@langname' => $langname, '%title' => $nested_content->label()]) : $this->t('Revisions for %title', ['%title' => $nested_content->label()]);
    $header = [$this->t('Revision'), $this->t('Operations')];

    $revert_permission = (($account->hasPermission("revert all nested content revisions") || $account->hasPermission('administer nested content entities')));
    $delete_permission = (($account->hasPermission("delete all nested content revisions") || $account->hasPermission('administer nested content entities')));

    $rows = [];

    $vids = $nested_content_storage->revisionIds($nested_content);

    $latest_revision = TRUE;

    foreach (array_reverse($vids) as $vid) {
      /** @var \Drupal\nested_content\NestedContentEntityInterface $revision */
      $revision = $nested_content_storage->loadRevision($vid);
      // Only show revisions that are affected by the language that is being
      // displayed.
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        // Use revision link to link to revisions that are not active.
        $date = \Drupal::service('date.formatter')->format($revision->getRevisionCreationTime(), 'short');
        if ($vid != $nested_content->getRevisionId()) {
          $link = $this->l($date, new Url('entity.nested_content.revision', ['nested_content' => $nested_content->id(), 'nested_content_revision' => $vid]));
        }
        else {
          $link = $nested_content->link($date);
        }

        $row = [];
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => \Drupal::service('renderer')->renderPlain($username),
              'message' => ['#markup' => $revision->getRevisionLogMessage(), '#allowed_tags' => Xss::getHtmlTagList()],
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
              Url::fromRoute('entity.nested_content.translation_revert', ['nested_content' => $nested_content->id(), 'nested_content_revision' => $vid, 'langcode' => $langcode]) :
              Url::fromRoute('entity.nested_content.revision_revert', ['nested_content' => $nested_content->id(), 'nested_content_revision' => $vid]),
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute('entity.nested_content.revision_delete', ['nested_content' => $nested_content->id(), 'nested_content_revision' => $vid]),
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

    $build['nested_content_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
    ];

    return $build;
  }

}
