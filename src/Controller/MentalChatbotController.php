<?php

namespace Drupal\mental_chatbot\Controller;

use Google\Cloud\Dialogflow\V2\SessionsClient;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Drupal\Core\Controller\ControllerBase;
use Symfony\Component\HttpFoundation\JsonResponse;


/**
 * Returns responses for mental_chatbot routes.
 */
class MentalChatbotController extends ControllerBase
{

  /**
   * Make conversation with chatbot dialogflow.
   */
  public function chat()
  {
    $mental_chatbot_config = \Drupal::config('mental_chatbot.settings');
    $session_id = mt_rand();
    $project_id = $mental_chatbot_config->get('mental_chatbot.project_id');

    $msg = \Drupal::request()->get('msg');
    $chatResponse = $this->detectIntentTexts($project_id, $msg, $session_id);
    $response = [
      'data' => [
        'message' => $chatResponse['message'],
        'intent' => $chatResponse['intent']
      ],
      'method' => 'POST',
      'status' => 200
    ];
    return new JsonResponse($response);
  }

  /**
   * @param $projectId
   * @param $text
   * @param $sessionId
   * @param string $languageCode
   * @return array
   */
  private function detectIntentTexts($projectId, $text, $sessionId, $languageCode = 'en-US')
  {
    // new session
    $sessionCredentials = ['credentials' => $this->getClientSecretKey()];
    $sessionsClient = new SessionsClient($sessionCredentials);
    $session = $sessionsClient->sessionName($projectId, $sessionId ?: uniqid());

    // create text input
    $textInput = new TextInput();
    $textInput->setText($text);
    $textInput->setLanguageCode($languageCode);

    // create query input
    $queryInput = new QueryInput();
    $queryInput->setText($textInput);

    // get response and relevant info
    $response = $sessionsClient->detectIntent($session, $queryInput);
    $queryResult = $response->getQueryResult();
    $queryText = $queryResult->getQueryText();
    $intent = $queryResult->getIntent();
    $displayName = $intent->getDisplayName();
    $confidence = $queryResult->getIntentDetectionConfidence();
    $fulfilmentText = $queryResult->getFulfillmentText();

    $sessionsClient->close();
    return [
      'message' => $fulfilmentText,
      'intent' => $displayName
    ];
  }

  /**
   * Get client secret json file path.
   * @return string|bool
   */
  private function getClientSecretKey()
  {
    $clientSecretKeyJson = \Drupal::config('mental_chatbot.settings')->get('mental_chatbot.client_secret');
    if ($clientSecretKeyJson) {
      return  json_decode($clientSecretKeyJson,true);
    }
    return NULL;
  }

}
