<?php

namespace Drupal\mental_chatbot\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Config\ConfigFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;

/**
 * Provides an example block.
 *
 * @Block(
 *   id = "mental_chatbot_block",
 *   admin_label = @Translation("Mental Chatbot"),
 *   category = @Translation("Mental Chatbot")
 * )
 */
class MentalChatbotBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Configuration state Drupal Site.
   *
   * @var \Drupal\Core\Config\ConfigFactory
   */
  protected $configFactory;
  /**
   * File Usage serivce.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * Construct method.
   */
  public function __construct(ConfigFactory $configFactory, EntityTypeManager $entity_type_manager) {
    $this->configFactory = $configFactory;
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Create method.
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('config.factory'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->configFactory->getEditable('mental_chatbot.settings');
    $mentalChatbotSettings = [
      'chatbot_title' => $config->get('mental_chatbot.chatbot_title'),
    ];
    // If alternative logo is set.
    $altLogo = $config->get('mental_chatbot.chatbot_logo');
    if ($altLogo) {
      $file = $this->entityTypeManager->getStorage('file')->load($altLogo[0]);
      $imageUrl = file_create_url($file->getFileUri());
      if (!empty($imageUrl)) {
        $mentalChatbotSettings['chatbot_logo'] = $imageUrl;
      }
    }
    // If user avatar is set.
    $userAvatar = $config->get('mental_chatbot.chatbot_user_avatar');
    if ($userAvatar) {
      $file = $this->entityTypeManager->getStorage('file')->load($userAvatar[0]);
      $imageUrl = file_create_url($file->getFileUri());
      if (!empty($imageUrl)) {
        $mentalChatbotSettings['chatbot_user_avatar'] = $imageUrl;
      }
    }
    $request = \Drupal::request();
    $base_url = $request->getSchemeAndHttpHost() . $request->getBaseUrl();
    // Attach settings and libraries to the block.
    return [
      '#type' => 'html',
      '#theme' => 'mental_chatbot',
      '#variables' => [
        'chatbot_title' => $config->get('mental_chatbot.chatbot_title'),
        'chatbot_logo' => $mentalChatbotSettings['chatbot_logo'] ? $mentalChatbotSettings['chatbot_logo'] : '',
        'base_url' => $base_url,
        'lang' => \Drupal::languageManager()->getCurrentLanguage()->getId(),
      ],
      '#attached' => [
        'library' => [
          'mental_chatbot/mental_chatbot_assets',
        ],
        'drupalSettings' => [
          'mental_chatbot' => $mentalChatbotSettings,
        ],
      ],
    ];
  }

}
