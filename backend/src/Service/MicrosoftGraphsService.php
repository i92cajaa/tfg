<?php

namespace App\Service;

use App\Entity\Config\ConfigType;
use App\Entity\Token\Token;
use App\Repository\MeetingRepository;
use App\Service\ConfigService\ConfigService;
use App\Service\TokenService\TokenService;
use DateTime;
use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\Exception\TooManyRedirectsException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Model\Identity;
use Microsoft\Graph\Model\IdentitySet;
use Microsoft\Graph\Model\JoinMeetingIdSettings;
use Microsoft\Graph\Model\MeetingParticipantInfo;
use Microsoft\Graph\Model\MeetingParticipants;
use Microsoft\Graph\Model\OnlineMeeting;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Form\Exception\AccessException;
use Symfony\Component\HttpFoundation\Exception\BadRequestException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

class MicrosoftGraphsService
{

    private string $tenantId;
    private string $clientId;
    private string $clientSecret;
    private string $serviceUser;

    private ?string $accessToken;

    private int $tries = 3;

    private ?Graph $graph = null;



    public function __construct(
        private readonly ConfigService $configService,
        private readonly TokenService $tokenService,
    )
    {

    }
    public function init(): void
    {
        $configs = $this->configService->findConfigValueByTags([
            ConfigType::MEETING_TENANT_ID,
            ConfigType::MEETING_CLIENT_ID,
            ConfigType::MEETING_CLIENT_SECRET_ID,
            ConfigType::MEETING_SERVICE_USER_ID,
        ]);

        $this->tenantId = @$configs[ConfigType::MEETING_TENANT_ID];
        $this->clientId = @$configs[ConfigType::MEETING_CLIENT_ID];
        $this->clientSecret = @$configs[ConfigType::MEETING_CLIENT_SECRET_ID];
        $this->serviceUser = @$configs[ConfigType::MEETING_SERVICE_USER_ID];
    }

    public function access(): void
    {
        try {
            $guzzle = new \GuzzleHttp\Client();
            $url    = 'https://login.microsoftonline.com/' . $this->tenantId . '/oauth2/v2.0/token';
            $token  = json_decode($guzzle->get($url, [
                'form_params' => [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'scope' => 'https://graph.microsoft.com/.default',
                    'grant_type' => 'client_credentials',
                ]
            ])->getBody()->getContents());

            if($token){

                $this->accessToken = $token->access_token;

                $this->instanceGraph();

                $this->tokenService->newAzureAccessToken(
                    intval($token->expires_in),
                    intval($token->ext_expires_in),
                    $token->access_token
                );
            }



        } catch (\Exception $e) {
            dd($e);
        }
    }

    public function instanceGraph(): void
    {
        $this->graph = new Graph();
        $this->graph->setAccessToken($this->accessToken);
    }

    public function getAccessToken(): void
    {
        $this->accessToken = $this->tokenService->findTokenValueByTagAndType(
            Token::AZURE_ACCESS_TAG,
            Token::AZURE_ACCESS_TOKEN_TYPE
        );

        $this->instanceGraph();

        if(!$this->accessToken){

            $this->handleTries();

            $this->access();

            if(!$this->accessToken){
                $this->getAccessToken();
            }

        }

    }

    public function configureTokens(): void
    {

        $this->tries = 3;
        $this->getAccessToken();
    }

    public function createMeeting(
        DateTime $startDate,
        DateTime $endDate,
        string $subject,
    ): ?array
    {
        $this->configureTokens();

        $requestBody = new OnlineMeeting();
        $requestBody->setStartDateTime($startDate);

        $requestBody->setEndDateTime($endDate);

        $requestBody->setSubject($subject);

        try {
            $requestResult = $this->graph->createRequest('POST', '/users/' . $this->serviceUser . '/onlineMeetings')
                ->addHeaders([
                    'Authorization' => 'Bearer ' . $this->accessToken,
                    'Content-Type' => 'application/json'
                ])
                ->attachBody($requestBody->jsonSerialize())
                ->setReturnType(OnlineMeeting::class)
                ->execute();

        }catch (\Exception $e){
            dd($e);
        }

        return $requestResult ? $requestResult->jsonSerialize() : null;
    }

    public function handleTries(): void
    {
        $this->tries--;
        if($this->tries <= 0){
            throw new AccessException();
        }
    }









}