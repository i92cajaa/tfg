<?php


namespace App\Service;



use App\Repository\UserRepository;
use App\Entity\Appointment\Appointment;
use App\Entity\Client\Client;
use App\Entity\User\User;
use App\Entity\ClientRequest\ClientRequest;
use App\Entity\Config\Config;
use App\Entity\Config\ConfigType;
use App\Repository\ConfigRepository;
use App\Repository\PaymentMethodRepository;
use App\Service\ConfigService\ConfigService;
use Doctrine\DBAL\Exception;
use Doctrine\ORM\EntityManagerInterface;
use ProxyManager\Exception\ExceptionInterface;
use Psr\Log\InvalidArgumentException;
use Symfony\Bridge\Twig\Mime\TemplatedEmail;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Address;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mime\Exception\RfcComplianceException;
use TypeError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;


class MailService
{


    private UserRepository $userRepository;
    private MailerInterface $mailer;
    private string|int|bool|array|null|float|\UnitEnum $app_email;
    private mixed $app;
    private string $app_url;

    public function __construct(
        ParameterBagInterface $parameterBag,
        MailerInterface $mailer,
        ConfigService $configService,
        UserRepository $userRepository

    ) {
        $this->app = $configService->findConfigsFormatted();
        $this->mailer = $mailer;
        $this->app_email = $parameterBag->get('APP_EMAIL');
        $this->app_url = $parameterBag->get('APP_URL');
        $this->userRepository = $userRepository;
    }

    public function sendEmail(Appointment $appointment, $subject = null, string $title = null, $text = null): bool
    {

        try {
            $emailTo = new Address($appointment->getClient()->getEmail());
        } catch (RfcComplianceException | TypeError $e) {
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);


        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $subject")
            ->htmlTemplate('email/appointment.html.twig')
            ->context([
                'appUrl' => $this->app_url,
                'appointment' => $appointment,
                'configuration' => $this->app,
                'title' => $title,
                'content' => $text
            ]);



        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function rememberPassword(Request $request): bool
    {

        $email = $request->request->get('email');
        $title = 'Andalucía Open Future';

        $token = bin2hex(random_bytes(32));
        try {
            $emailTo = new Address($email);
        } catch (RfcComplianceException | TypeError $e) {
            return false;
        }

        
        $user = new User();
        $user = $this->userRepository->findUserByEmail($email);

        if (!$user) {

            return true;
        }

        $idUser=$user->getId();
        $this->userRepository->updateUserTokenById($idUser,$token);

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);
      
        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $title")
            ->htmlTemplate('email/reset_password.html.twig')
            ->context([
                'token' => $token,
                'appointment' => $title,
                'configuration' => $this->app,
                'title' => $title,
                'APP_URL'=> $this->app_url
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendAppointmentPayment(Appointment $appointment, $emailToSend, $subject = null, string $title = null, $text = null): bool
    {
        try{
            $emailTo = new Address($emailToSend);
        } catch (RfcComplianceException | TypeError $e){
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);
//        var_dump($this->app_email);
//        var_dump($emailTo);
//        exit();

        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $subject")
            ->htmlTemplate('email/request.html.twig')
            ->context([
                'appointment' => $appointment,
                'configuration' => $this->app,
                'title' => $title,
                'content' => $text
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendPublicEmail(Appointment $appointment = null, $subject = null, string $title = null, $text = null): bool
    {

        try {
            $emailTo = new Address("i92cajaa@uco.es");
        } catch (RfcComplianceException | TypeError $e) {
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);


        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $subject")
            ->htmlTemplate('email/request.html.twig')
            ->context([
                'appointment' => $appointment,
                'configuration' => $this->app,
                'title' => $title,
                'content' => $text
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }

    public function sendEmailClient(Client $client, $subject = null, string $title = null, $text = null): bool
    {

        try {
            $emailTo = new Address($client->getEmail());
        } catch (RfcComplianceException | TypeError $e) {
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);


        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $subject")
            ->htmlTemplate('email/request.html.twig')
            ->context([
                'appointment' => $client,
                'configuration' => $this->app,
                'title' => $title,
                'content' => $text
            ]);

        try {
            //$this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }


    public function sendEmailRequest(ClientRequest $clientRequest, $subject = null, string $title = null, $text = null): bool
    {

        try {
            $emailTo = new Address($clientRequest->getEmail());
        } catch (RfcComplianceException | TypeError $e) {
            return false;
        }

        $email = (new TemplatedEmail())
            ->from($this->app_email)
            ->to($emailTo);


        $email->subject("{$this->app[ConfigType::APP_NAME_TAG]}: $subject")
            ->htmlTemplate('email/request.html.twig')
            ->context([
                'appointment' => $clientRequest,
                'configuration' => $this->app,
                'title' => $title,
                'content' => $text
            ]);

        try {
            $this->mailer->send($email);
            return true;
        } catch (TransportExceptionInterface $e) {
            return false;
        }
    }
}
