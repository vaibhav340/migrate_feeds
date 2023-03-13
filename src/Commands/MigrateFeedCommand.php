<?php

namespace Drupal\migrate_feeds\Commands;

use Drush\Commands\DrushCommands;
use Drupal\core\Entity\EntityTypeManager;

/**
 * A Drush commandfile.
 *
 */
class MigrateFeedCommand extends DrushCommands {

  /**
   * Entity type manager
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Construct MigrateFeedCommand object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManager $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Function to create feed nodes from xml
   *
   * @param $sourceXML
   *  XML file url
   *
   * @command custom-migrate:feeds
   * @aliases migrate-feeds
   * @usage drush migrate-feeds http://feeds.feedburner.com/ndtvnews-top-stories.xml
   */
  public function migrateFeedsFromXML($sourceXML, $options = []) {
    // get sourceXml
    $contentXML = file_get_contents($sourceXML);

    $conent = new \SimpleXmlElement($contentXML);
    $numNodesCreated = 0;

    foreach($conent as $entry) {
      //creates a new node based on the infos from the entry object
      $articleNode = $this->entityTypeManager->getStorage('node')->create([
        'type' => 'article',
        'title' => $entry->title,
        'uid' => 1,
        'status' => 1,
        'promote' => 1,
        'field_guid' => [
          'value' => $entry->guid,
	],
        'field_link' => [
          'uri' => '$entry->link,'
        ],
        // 'field_story_image' => [
        //   'uri' => 'https//:abc.com',
        // ],
        'body' => [
          'value' => $entry->content,
        ]
        'created' => $entry->pubDate,
        'changed' => $entry->updatedAt,
      ]);
      $articleNode->save();

      $numNodesCreated++;
    }

    return 'finished importing from feed. created ' . $numNodesCreated .' nodes';
  }
}
