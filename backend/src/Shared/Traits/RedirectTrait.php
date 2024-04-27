<?php


namespace App\Shared\Traits;



use App\Entity\Document\Document;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

trait RedirectTrait
{

    public function redirectBack(Request $request): RedirectResponse
    {
        $referer = $request->headers->get('referer');
        return new RedirectResponse($referer);
    }

}