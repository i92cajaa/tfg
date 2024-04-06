<?php


namespace App\Service;


use App\Entity\Appointment\Appointment;
use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Repository\ConfigRepository;
use App\Repository\PaymentMethodRepository;
use App\Service\ConfigService\ConfigService;
use Doctrine\ORM\EntityManagerInterface;
use MessageBird\Client;
use MessageBird\Exceptions\MessageBirdException;
use MessageBird\Objects\Message;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;

class MessageBirdService
{


    private ?Client $messageBirdClient;
    private string|int|bool|array|null|float|\UnitEnum $app_phone;
    private mixed $app;

    public function __construct(
        ParameterBagInterface $parameterBag,
        ConfigService $configService
    )
    {
        $this->app = $configService->findConfigsFormatted();

        if(@$parameterBag->get('MESSAGEBIRD_KEY')){
            $this->messageBirdClient = new Client($parameterBag->get('MESSAGEBIRD_KEY'));
        }else{
            $this->messageBirdClient = null;
        }

        $this->app_phone = $parameterBag->get('APP_PHONE');
    }

    public function sendSMS(Appointment $appointment, $text = null): bool
    {
        $message = new Message;
        $message->originator = $this->app_phone;
        $message->recipients = [$appointment->getClient()->getPhone1()];

        if($this->app[ConfigType::APP_NAME_TAG] != null){
            $message->originator = $this->app[ConfigType::SHORT_COMPANY_NAME_TAG];
        }

        if($text == null){
            $message->body = 'Enhorabuena '.$appointment->getClient()->getName().', usted ha reservado una cita con '. $this->app[ConfigType::APP_NAME_TAG] . ' el día ' . $appointment->getTimeFrom()->format('d-m-Y') . ' de ' . $appointment->getTimeFrom()->format('H:i') . ' a ' . $appointment->getTimeTo()->format('H:i');
        }else{
            $message->body = 'Información: ' . $text;
        }

        if($this->messageBirdClient){
            try {
                $this->messageBirdClient->messages->create($message);
            }catch (MessageBirdException $e){
                return false;
            }
        }

        return true;
    }


}