<?php

namespace App\Service\StripeService;


use App\Entity\Appointment\Appointment;
use App\Entity\ClientRequest\ClientRequest;
use App\Repository\AppointmentRepository;
use App\Repository\ClientRequestRepository;
use App\Service\MailService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\StripeClient;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class StripeService extends AbstractService
{

    /**
     * @var ClientRequestRepository
     */
    private ClientRequestRepository $clientRequestRepository;

    /**
     * @var AppointmentRepository
     */
    private AppointmentRepository $appointmentRepository;


    public function __construct(
                                EntityManagerInterface $em,
                                RouterInterface                                   $router,
                                Environment                                       $twig,
                                RequestStack                                      $requestStack,
                                TokenStorageInterface                             $tokenStorage,
                                CsrfTokenManagerInterface                         $tokenManager,
                                FormFactoryInterface                              $formFactory,
                                SerializerInterface                               $serializer,
                                TranslatorInterface $translator,
                                private readonly MailService                      $mailService,
    )
    {
        $this->clientRequestRepository = $em->getRepository(ClientRequest::class);
        $this->appointmentRepository = $em->getRepository(Appointment::class);
        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
        );
    }

    public function index()
    {
        //$payload = json_decode($request->getContent(), true);
        //$event = $payload['type'];
        //$checkId = $payload['data']['object']['id'];
        return false;
    }

    public function checkoutSession($amount,$currency,$productName){

        $stripe = new StripeClient(
            'sk_test_51Mo7yAGedZbenmSLLYdUhIPIS197oiMNAvlP5OD956cbGhMkSoBUlldzRW0ItNkgjdttwhdZADgT0xvYSGB7Ajuu003dUV8XkF'
        );
        $expiredTime = time() + (60 * 60 * 24); //Tiempo de expiracion que tenemos actualmente esta puesto que se mantenga la session para poder pagar durante 48 horas desde que se define
        // Initialize the line items array
        $lineItems = [
            [
                'price_data' => [
                    'currency' => $currency,
                    'unit_amount' => $amount,
                    'product_data' => [
                        'name' => $productName,
                    ],
                ],
                'quantity' => 1,
            ],
        ];

        // Check if the amount is 1900 and add the description if necessary
        if ($amount === 1900) {
            $description = $this->translate('The consultation fee will be deducted at the first appointment.');
            $lineItems[0]['price_data']['product_data']['description'] = $description;
        }

        // Create the checkout session with the line items
        $checkoutSession = $stripe->checkout->sessions->create([
            'success_url' => 'https://fluxua.studio128k.com/public/success',
            'line_items' => $lineItems,
            'mode' => 'payment',
            'expires_at' => $expiredTime
        ]);


        $jsonResponse = json_encode($checkoutSession);
        return json_decode($jsonResponse, true);
    }

    public function getCheckOutSession(){
        $stripe = new StripeClient(
            'sk_test_51Mo7yAGedZbenmSLLYdUhIPIS197oiMNAvlP5OD956cbGhMkSoBUlldzRW0ItNkgjdttwhdZADgT0xvYSGB7Ajuu003dUV8XkF'
        );
        $checkoutSession = $stripe->checkout->sessions->retrieve(
            'cs_test_a13JH74wB4JpAU8EDQot9aZT58QVxbez8H0Rc5YntW0VyspQydk4bivJaO',
            []
        );
        $jsonResponse = json_encode($checkoutSession);
        $response = json_decode($jsonResponse, true);
        //el valor va para aqui
    }

    public function webhook($request){
        $payload = json_decode($request->getContent(), true);
        $event = $payload['type'];
        $checkId = $payload['data']['object']['id'];

        if($event == 'checkout.session.completed'){
            $clientRequest = $this->clientRequestRepository->findOneBy(['stripe_id' => $checkId]);
            if($clientRequest){
                $clientRequest->setPaid(true);
                $this->clientRequestRepository->persist($clientRequest);
                $this->email($clientRequest);
            }
            $appointment = $this->appointmentRepository->findOneBy(['stripe_id' => $checkId]);
            if($appointment){
                $appointment->setPaid(true);
                $this->appointmentRepository->persist($appointment);
                $title = $this->translate('Dear',$appointment->getClient()->getLocale()).' '.$appointment->getClient()->getName().', '.$this->translate('you have an appointment with us', $appointment->getClient()->getLocale());
                $content = "<p>" .$this->translate('This is an informative email to remind you that you have an appointment with us, the data are reflected below', $appointment->getClient()->getLocale()) . "</p>";
                $content .= "<p>";
                $content .= $this->translate('Date', $appointment->getClient()->getLocale()) . ": <b>" . $appointment->getTimeFrom()->setTimezone($appointment->getClient()->getTimezoneObj())->format('Y-m-d') . " " . $appointment->getTimeFrom()->setTimezone($appointment->getClient()->getTimezoneObj())->format('H:i') . "-" . $appointment->getTimeTo()->setTimezone($appointment->getClient()->getTimezoneObj())->format('H:i') . "</b>";
                if($appointment->isMeetingAttached() && $appointment->getMeeting()){
                    $content .= "<br>";
                    $content .= $this->translate('Meeting', $appointment->getClient()->getLocale()) . ": <b><a href='" . $appointment->getMeeting()->getJoinUrl() . "'>" . $this->translate('Go to the Meeting', $appointment->getClient()->getLocale()) . "</a></b>";
                }
                $content .= "</p>";
                $this->mailService->sendEmail($appointment, $this->translate('New Appointment Created', $appointment->getClient()->getLocale()), $title, $content);

            }
        }
        return new Response('', 200);
    }
    public function email(ClientRequest $request){
        $title = $this->translate('Dear').' '.$request->getName();
        $content = "<p>" .$this->translate('Your payment has been successfully completed. A professional will contact you shortly.' , $request->getLocale()) . "</p>";
        $content .= $this->translate('Best regards. The Fluxua team', $request->getLocale());
        $this->mailService->sendEmailRequest($request,null,$title,$content);
    }

    public function changeStatusPaid(){
        $clientRequest = $this->clientRequestRepository->findOneBy(['stripe_id' => 'cs_test_a18sxIDGst1pArDVoEEdujHJAz76TlZZ1UK7k0YhohzvgbzeS1KaSnqFJK']);
        $clientRequest->setPaid(true);
        $this->clientRequestRepository->persist($clientRequest);

    }

    public function payIntentConfirm(){
        $stripe = new StripeClient('sk_test_51Mo7yAGedZbenmSLLYdUhIPIS197oiMNAvlP5OD956cbGhMkSoBUlldzRW0ItNkgjdttwhdZADgT0xvYSGB7Ajuu003dUV8XkF');
        $stripe->paymentIntents->confirm('pi_3N5n8qGedZbenmSL1eailLLZ');
        $paymentIntent = $stripe->paymentIntents->capture('pi_3N5n8qGedZbenmSL1eailLLZ');


        $jsonResponse = json_encode($paymentIntent);
        $response = json_decode($jsonResponse, true);

        return true;
    }


}

