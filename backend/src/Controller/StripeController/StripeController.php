<?php

namespace App\Controller\StripeController;

use App\Annotation\Permission;
use App\Service\StripeService\StripeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/stripe')]
class StripeController extends AbstractController
{

    public function __construct(
        private readonly StripeService $stripeService
    )
    {
    }

    #[Route(path: '/', name: 'stripe_index', methods: ["GET"])]
    #[Permission(group: 'stripe', action:"list")]
    public function index(Request $request){
        return $this->stripeService->index();
    }

    #[Route(path: '/get', name: 'get', methods: ["GET"])]
    #[Permission(group: 'stripe', action:"list")]
    public function get(Request $request){
        $this->stripeService->changeStatusPaid();
    }

}