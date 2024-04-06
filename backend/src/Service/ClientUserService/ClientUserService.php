<?php

namespace App\Service\ClientUserService;

use App\Entity\Client\Client;
use App\Form\SendEmail;
use App\Shared\Classes\AbstractService;

class ClientUserService extends AbstractService
{

    public function index(){
        return $this->render('clientDashboard/index.html.twig',[
            'client' => $this->getUser(),
            'filterService' => $this->filterService]);
    }
    public function sendEmail(){
        return $this->render('clientDashboard/alert.html.twig',[
            'client' => $this->getUser()
        ]);
    }

    public function send(){
        $client = new Client();
        $form = $this->createForm(SendEmail::class, $client);
        $form->handleRequest($this->getCurrentRequest());
        return $this->render('clientDashboard/alert.html.twig',[
            'client' => $this->getUser()
        ]);
    }


}