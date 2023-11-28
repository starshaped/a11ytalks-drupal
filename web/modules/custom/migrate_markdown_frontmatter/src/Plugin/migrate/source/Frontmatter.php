<?php

namespace Drupal\migrate_markdown_frontmatter\Plugin\migrate\source;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Utility\NestedArray;
use Drupal\migrate\Plugin\MigrationInterface;
use Drupal\migrate\Plugin\migrate\source\SourcePluginBase;
use League\CommonMark\Extension\FrontMatter\FrontMatterExtension;

/**
 * Takes markdown files from the folder path and
 * pull out the front matter fields defined in the configuration to return as an
 * iterator to be used.
 * 
 * source:
 *   plugin: 'frontmatter'
 *   path: '/path/to/markdown/folder'
 *   ids:
 *     - slug
 *   fields:
 *     0:
 *       name: slug
 *       label: 'Slug of file'
 *     1:
 *       name: title
 *       label: 'Title of event'
 *     2:
 *       name: subtitle
 *       label: 'Presenter name'
 *   get_body: FALSE
 * process:
 *   title: title
 *   type:
 *     plugin: default_value
 *     default_value: event
 *
 * destination:
 *   plugin: entity_node
 *   
 */
class Frontmatter extends SourcepluginBase implements ConfigurableInterface {

  /**
   * {@inheritdoc}
   *
   * @throws \InvalidArgumentException
   * @throws \Drupal\migrate\MigrateException
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, MigrationInterface $migration) {
    parent::__construct($configuration, $plugin_id, $plugin_definition, $migration);
    $this->setConfiguration($configuration);

    // Path is required.
    if (empty($this->configuration['path'])) {
      throw new \InvalidArgumentException('You must declare the "path" to the source file in your source settings.');
    }
    // IDs are required.
    if (empty($this->configuration['ids']) || !is_array($this->configuration['ids'])) {
      throw new \InvalidArgumentException('You must declare "ids" as a unique array of fields in your source settings.');
    }
    // IDs must be an array of strings.
    if ($this->configuration['ids'] !== array_unique(array_filter($this->configuration['ids'], 'is_string'))) {
      throw new \InvalidArgumentException('The ids must a flat array with unique string values.');
    }

  }

  /**
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration) {
    // We must preserve integer keys for column_name mapping.
    $this->configuration = NestedArray::mergeDeepArray([$this->defaultConfiguration(), $configuration], TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'path' => '',
      'ids' => [],
      'fields' => [],
      'get_body' => FALSE,
    ];
  }

  /**
   * Return a string representing the source file path.
   *
   * @return string
   *   The file path.
   */
  public function __toString() {
    return $this->configuration['path'];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\migrate\MigrateException
   * @throws \League\Csv\Exception
   */
  public function initializeIterator() {

  }

  

  /**
   * {@inheritdoc}
   */
  public function getIds() {
    $ids = [];
    foreach ($this->configuration['ids'] as $value) {
      $ids[$value]['type'] = 'string';
    }
    return $ids;
  }

  /**
   * {@inheritdoc}
   */
  public function fields() {
    $fields = [];
    foreach ($this->configuration['fields'] as $field) {
      $fields[$field['name']] = isset($field['label']) ? $field['label'] : $field['name'];
    }
    return $fields;
  }



  protected function readFrontmatter($markdown) {
    $frontMatterExtension = new FrontMatterExtension();
    $result = $frontMatterExtension->getFrontMatterParser()->parse($markdown);
    return [
      'frontmatter' => $result->getFrontMatter(),
      'content' => $result->getContent(),
    ];
  }
}
