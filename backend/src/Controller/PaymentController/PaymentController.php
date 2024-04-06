<?php

namespace App\Controller\PaymentController;

use App\Entity\Client;
use App\Entity\Payment\Payment;
use App\Entity\User;
use App\Repository\AppointmentRepository;
use App\Repository\ConfigRepository;
use App\Repository\PaymentRepository;
use App\Repository\ClientRepository;
use App\Repository\UserRepository;
use App\Service\FilterService;
use App\Service\PaymentService\PaymentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Annotation\Permission;


#[Route(path: '/payment')]
class PaymentController extends AbstractController
{

    public function __construct(
        private readonly PaymentService $paymentService
    )
    {
    }

    #[Route(path: '/get-create-template', name: 'payment_get_create_template', methods: ["POST"])]
    #[Permission(group: 'appointments', action:"manage_payments")]
    public function getCreatePaymentTemplate(): Response
    {
        return $this->paymentService->getCreatePaymentTemplate();
    }

    #[Route(path: '/new', name: 'payment_new', methods: ["GET", "POST"])]
    #[Permission(group: 'appointments', action:"manage_payments")]
    public function new(): Response
    {
        return $this->paymentService->createPaymentByRequest();

    }

    #[Route(path: '/get-edit-template', name: 'payment_get_edit_template', methods: [ "POST"])]
    #[Permission(group: 'appointments', action:"manage_payments")]
    public function getEditPaymentTemplate(): Response
    {
        return $this->paymentService->getEditPaymentTemplate();
    }

    #[Route(path: '/edit/{payment}', name: 'payment_edit', methods: [ "POST"])]
    #[Permission(group: 'appointments', action:"manage_payments")]
    public function edit(string $payment): Response
    {
        return $this->paymentService->editPaymentByRequest($payment);
    }

    #[Route(path: '/delete/{payment}', name: 'payment_delete', methods: [ "POST"])]
    #[Permission(group: 'appointments', action:"manage_payments")]
    public function delete(string $payment): Response
    {
        return $this->paymentService->deletePayment($payment);
    }

}
