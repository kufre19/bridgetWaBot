<?php
namespace App\Traits;

use Google\Cloud\Dialogflow\V2\IntentsClient;
use Google\Cloud\Dialogflow\V2\QueryInput;
use Google\Cloud\Dialogflow\V2\TextInput;
use Google\Cloud\Dialogflow\V2\SessionName;
use Google\Cloud\Dialogflow\V2\SessionsClient;
use GPBMetadata\Google\Cloud\Dialogflow\V2\Webhook;

trait HandleDialogFlow {

    public $credentials;
    public $url;
    public $project_id;



    // public function init_dialogFlow()
    // {
    //     $this->credentials = json_decode(env("DIALOG_FLOW_CREDENTIALS"),true);
    //     $this->project_id = $this->credentials['project_id'];
    //     $this->url  = "https://dialogflow.googleapis.com/v2/projects/{$this->project_id}/agent/sessions/123456789:detectIntent";

    //     $intentsClient = new IntentsClient();
    //     $textQuery = 'Hello!';
        
    //     $response = $intentsClient->detectIntent(
    //         "projects/{$this->project_id}/agent",
    //         (new QueryInput())
    //             ->setText((new TextInput())
    //                 ->setText($textQuery)
    //                 ->setLanguageCode('en-US')),
    //         (new SessionName($this->project_id, uniqid()))
    //     );

    //     $intent = $response->getQueryResult()->getIntent()->getDisplayName();
    //     $confidence = $response->getQueryResult()->getIntentDetectionConfidence();

    // }

    public function init_dialogFlow_two()
    {
        putenv("GOOGLE_APPLICATION_CREDENTIALS=".storage_path("app/credentials/healthbot-eynv-175558159099.json"));
        $this->credentials = json_decode(file_get_contents(env("GOOGLE_APPLICATION_CREDENTIALS"))  ,true);
    
        
       
        $this->project_id = $this->credentials['project_id'];
        $sessionId = uniqid();
        $this->url  = "https://dialogflow.googleapis.com/v2/projects/{$this->project_id}/agent/sessions/123456789:detectIntent";

        $intentsClient = new IntentsClient();
        $text = $this->user_message_original;
        // $text = "what is diabetes?";
        $languageCode = "en";
            // new session
        $sessionsClient = new SessionsClient();
        $session = $sessionsClient->sessionName($this->project_id, $sessionId ?: uniqid());
        // printf('Session path: %s' . PHP_EOL, $session);

        // query for each string in array
        $textInput = new TextInput();
        $webhookRequest = new Webhook();
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
        // dd($displayName);
       
        return $displayName;

    }
}