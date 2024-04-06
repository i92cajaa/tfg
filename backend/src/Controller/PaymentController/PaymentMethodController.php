<?php

namespace App\Controller\PaymentController;

use App\Entity\Payment\PaymentMethod;
use App\Repository\FestiveRepository;
use App\Service\FilterService;
use App\Service\PaymentService\PaymentMethodService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Annotation\Permission;
use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/payment-method')]
class PaymentMethodController extends AbstractController
{
    public function __construct(
        private readonly PaymentMethodService $paymentMethodService
    )
    {
    }

    #[Route(path: '/', name: 'payment_method_index', methods: ["GET"])]
    #[Permission(group: 'payment_methods', action:"list")]
    public function index(): Response
    {
        return $this->paymentMethodService->index();
    }

    #[Route(path: '/new', name: 'payment_method_new', methods: ["GET","POST"])]
    #[Permission(group: 'payment_methods', action:"create")]
    public function new(): Response
    {
        return $this->paymentMethodService->create();
    }

    #[Route(path: '/edit/{payment}', name: 'payment_method_edit', methods: ["GET","POST"])]
    #[Permission(group: 'payment_methods', action:"edit")]
    public function edit(string $payment): Response
    {
        return $this->paymentMethodService->edit($payment);
    }

    #[Route(path: '/delete/{payment}', name: 'payment_method_delete', methods: ["POST"])]
    #[Permission(group: 'payment_methods', action:"delete")]
    public function delete(string $payment): Response
    {
        return $this->paymentMethodService->delete($payment);
    }
}
