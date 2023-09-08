<?php

namespace Drupal\migration_module\Entity;

use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Entity\EditorialContentEntityBase;
use Drupal\Core\Entity\RevisionableInterface;
use Drupal\Core\Entity\EntityChangedTrait;
use Drupal\Core\Entity\EntityPublishedTrait;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\user\UserInterface;

/**
 * Defines the Cities List entity.
 *
 * @ingroup migration_module
 *
 * @ContentEntityType(
 *   id = "city_entity",
 *   label = @Translation("Cities List"),
 *   handlers = {
 *     "storage" = "Drupal\migration_module\CityEntityStorage",
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\migration_module\CityEntityListBuilder",
 *     "views_data" = "Drupal\migration_module\Entity\CityEntityViewsData",
 *     "translation" = "Drupal\migration_module\CityEntityTranslationHandler",
 *
 *     "form" = {
 *       "default" = "Drupal\migration_module\Form\CityEntityForm",
 *       "add" = "Drupal\migration_module\Form\CityEntityForm",
 *       "edit" = "Drupal\migration_module\Form\CityEntityForm",
 *       "delete" = "Drupal\migration_module\Form\CityEntityDeleteForm",
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\migration_module\CityEntityHtmlRouteProvider",
 *     },
 *     "access" = "Drupal\migration_module\CityEntityAccessControlHandler",
 *   },
 *   base_table = "city_entity",
 *   data_table = "city_entity_field_data",
 *   revision_table = "city_entity_revision",
 *   revision_data_table = "city_entity_field_revision",
 *   show_revision_ui = TRUE,
 *   translatable = TRUE,
 *   admin_permission = "administer cities list entities",
 *   entity_keys = {
 *     "id" = "id",
 *     "revision" = "vid",
 *     "label" = "name",
 *     "uuid" = "uuid",
 *     "uid" = "user_id",
 *     "langcode" = "langcode",
 *     "published" = "status",
 *   },
*   revision_metadata_keys = {
*     "revision_user" = "revision_uid",
*     "revision_created" = "revision_timestamp",
*     "revision_log_message" = "revision_log"
*   },
 *   links = {
 *     "canonical" = "/admin/structure/city_entity/{city_entity}",
 *     "add-form" = "/admin/structure/city_entity/add",
 *     "edit-form" = "/admin/structure/city_entity/{city_entity}/edit",
 *     "delete-form" = "/admin/structure/city_entity/{city_entity}/delete",
 *     "version-history" = "/admin/structure/city_entity/{city_entity}/revisions",
 *     "revision" = "/admin/structure/city_entity/{city_entity}/revisions/{city_entity_revision}/view",
 *     "revision_revert" = "/admin/structure/city_entity/{city_entity}/revisions/{city_entity_revision}/revert",
 *     "revision_delete" = "/admin/structure/city_entity/{city_entity}/revisions/{city_entity_revision}/delete",
 *     "translation_revert" = "/admin/structure/city_entity/{city_entity}/revisions/{city_entity_revision}/revert/{langcode}",
 *     "collection" = "/admin/structure/city_entity",
 *   },
 *   field_ui_base_route = "city_entity.settings"
 * )
 */
class CityEntity extends EditorialContentEntityBase implements CityEntityInterface {

  use EntityChangedTrait;
  use EntityPublishedTrait;

  /**
   * {@inheritdoc}
   */
  public static function preCreate(EntityStorageInterface $storage_controller, array &$values) {
    parent::preCreate($storage_controller, $values);
    $values += [
      'user_id' => \Drupal::currentUser()->id(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  protected function urlRouteParameters($rel) {
    $uri_route_parameters = parent::urlRouteParameters($rel);

    if ($rel === 'revision_revert' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }
    elseif ($rel === 'revision_delete' && $this instanceof RevisionableInterface) {
      $uri_route_parameters[$this->getEntityTypeId() . '_revision'] = $this->getRevisionId();
    }

    return $uri_route_parameters;
  }

  /**
   * {@inheritdoc}
   */
  public function preSave(EntityStorageInterface $storage) {
    parent::preSave($storage);
    
    // If we have a country set via base address, add it to base country field.
    $address = $this->address->getValue();
    $address[0]['country_code'] = 'US';
    if (is_array($address) && isset($address[0]['country_code'])) {
      $this->country = 'US';
    }

    foreach (array_keys($this->getTranslationLanguages()) as $langcode) {
      $translation = $this->getTranslation($langcode);

      // If no owner has been set explicitly, make the anonymous user the owner.
      if (!$translation->getOwner()) {
        $translation->setOwnerId(0);
      }
    }

    // If no revision author has been set explicitly,
    // make the city_entity owner the revision author.
    if (!$this->getRevisionUser()) {
      $this->setRevisionUserId($this->getOwnerId());
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->get('name')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setName($name) {
    $this->set('name', $name);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getCreatedTime() {
    return $this->get('created')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function setCreatedTime($timestamp) {
    $this->set('created', $timestamp);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwner() {
    return $this->get('user_id')->entity;
  }

  /**
   * {@inheritdoc}
   */
  public function getOwnerId() {
    return $this->get('user_id')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwnerId($uid) {
    $this->set('user_id', $uid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setOwner(UserInterface $account) {
    $this->set('user_id', $account->id());
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = parent::baseFieldDefinitions($entity_type);

    // Add the published field.
    $fields += static::publishedBaseFieldDefinitions($entity_type);

    $fields['user_id'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(t('Authored by'))
      ->setDescription(t('The user ID of author of the Cities List entity.'))
      ->setRevisionable(TRUE)
      ->setSetting('target_type', 'user')
      ->setSetting('handler', 'default')
      ->setTranslatable(TRUE)
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ])
      ->setDisplayOptions('form', [
        'type' => 'entity_reference_autocomplete',
        'weight' => 5,
        'settings' => [
          'match_operator' => 'CONTAINS',
          'size' => '60',
          'autocomplete_type' => 'tags',
          'placeholder' => '',
        ],
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE);

    $fields['name'] = BaseFieldDefinition::create('string')
      ->setLabel(t('Name'))
      ->setDescription(t('The name of the Cities List entity.'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['status']->setDescription(t('A boolean indicating whether the Cities List is published.'))
      ->setDisplayOptions('form', [
        'type' => 'boolean_checkbox',
        'weight' => -3,
      ]);
      
    $fields['gps'] = BaseFieldDefinition::create('geolocation')
      ->setLabel(t('GPS Coordinates'))
      ->setDescription(t('The GPS Coordinates of this Destination. You may enter address in the search field and the location will be retrieved via Google Maps.'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'geolocation_map',
        'weight' => -2,
        'settings' => array(
          'set_marker' => '1',
          'info_text' => 'lat,long: :lat,:lng',
          'google_map_settings' => array(
            'type' => 'TERRAIN',
            'zoom' => 9,
            'mapTypeControl' => TRUE,
            'streetViewControl' => FALSE,
            'zoomControl' => TRUE,
            'scrollwheel' => FALSE,
            'disableDoubleClickZoom' => FALSE,
            'draggable' => TRUE,
            'height' => '300px',
            'width' => '100%',
            'info_auto_display' => TRUE,
            'disableAutoPan' => TRUE,
            'preferScrollingToZooming' => FALSE,
            'gestureHandling' => 'auto',
          ),
        ),
      ))
      ->setDisplayOptions('form', array(
        'type' => 'geolocation_googlegeocoder',
        'weight' => -2,
        'title' => t('title thing'),
        'settings' => array(
          'set_marker' => '1',
          'info_text' => t('Some info text'),
          'use_overridden_map_settings' => 0,
          'google_map_settings' => array(
            'type' => 'TERRAIN',
            'zoom' => 5,
            'mapTypeControl' => TRUE,
            'streetViewControl' => FALSE,
            'zoomControl' => TRUE,
            'scrollwheel' => FALSE,
            'disableDoubleClickZoom' => FALSE,
            'draggable' => TRUE,
            'height' => '300px',
            'width' => '100%',
            'info_auto_display' => TRUE,
            'disableAutoPan' => TRUE,
            'preferScrollingToZooming' => FALSE,
            'gestureHandling' => 'auto',
          ),
          'populate_address_field' => TRUE,
          'target_address_field' => 'address',
          'default_longitude' => 25.39459,
          'default_latitude' => 42.73639,
          'auto_client_location' => FALSE,
          'auto_client_location_marker' => FALSE,
          'allow_override_map_settings' => FALSE,
        ),
      ));

    $fields['pop'] = BaseFieldDefinition::create('string')
      ->setLabel(t('pop'))
      ->setRevisionable(TRUE)
      ->setSettings([
        'max_length' => 50,
        'text_processing' => 0,
      ])
      ->setDefaultValue('')
      ->setDisplayOptions('view', [
        'label' => 'above',
        'type' => 'string',
        'weight' => -4,
      ])
      ->setDisplayOptions('form', [
        'type' => 'string_textfield',
        'weight' => -4,
      ])
      ->setDisplayConfigurable('form', TRUE)
      ->setDisplayConfigurable('view', TRUE)
      ->setRequired(TRUE);

    $fields['address'] = BaseFieldDefinition::create('address')
      ->setLabel(t('Address'))
      ->setDescription(t('The address of this Destination, if known. Choosing a location on the map above will attempt to fill this out using the Google Maps API. You might know better!'))
      ->setRevisionable(TRUE)
      ->setDisplayOptions('view', array(
        'label' => 'hidden',
        'type' => 'address_default',
        'weight' => -2,
      ))
      ->setDisplayOptions('form', array(
        'type' => 'address_default',
        'weight' => -2,
      ))
      ->setSettings(array(
        'fields' => array(
          'administrativeArea' => 'administrativeArea',
        ),
      ));

    $fields['created'] = BaseFieldDefinition::create('created')
      ->setLabel(t('Created'))
      ->setDescription(t('The time that the entity was created.'));

    $fields['changed'] = BaseFieldDefinition::create('changed')
      ->setLabel(t('Changed'))
      ->setDescription(t('The time that the entity was last edited.'));

    $fields['revision_translation_affected'] = BaseFieldDefinition::create('boolean')
      ->setLabel(t('Revision translation affected'))
      ->setDescription(t('Indicates if the last edit of a translation belongs to current revision.'))
      ->setReadOnly(TRUE)
      ->setRevisionable(TRUE)
      ->setTranslatable(TRUE);

    return $fields;
  }

}
