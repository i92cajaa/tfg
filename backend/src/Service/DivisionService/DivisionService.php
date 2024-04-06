<?php


namespace App\Service\DivisionService;


use App\Entity\Appointment;
use App\Entity\Service\Division;
use App\Form\DivisionType;
use App\Repository\DivisionRepository;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class DivisionService extends AbstractService
{

    /**
     * @var DivisionRepository
     */
    private DivisionRepository $divisionRepository;

    public function __construct(
        EntityManagerInterface $em,

        RouterInterface       $router,
        Environment           $twig,
        RequestStack          $requestStack,
        TokenStorageInterface $tokenStorage,
        CsrfTokenManagerInterface      $tokenManager,
        FormFactoryInterface           $formFactory,
        SerializerInterface            $serializer,
        TranslatorInterface $translator
    )
    {

        $this->divisionRepository = $em->getRepository(Division::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->divisionRepository
        );
    }

    public function findAll(): array
    {
        return $this->divisionRepository->findAll();
    }

    public function new(): RedirectResponse|Response
    {
        $division = new Division();
        $form = $this->createForm(DivisionType::class, $division);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->divisionRepository->persist($division);

            return $this->redirectToRoute('service_index');
        }

        return $this->render('division/new.html.twig', [
            'division' => $division,
            'form' => $form->createView()
        ]);
    }

    public function edit(string $division): RedirectResponse|Response
    {
        $division = $this->getEntity($division);

        $form = $this->createForm(DivisionType::class, $division);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->divisionRepository->persist($division);

            return $this->redirectToRoute('service_index');
        }

        if( $form->isSubmitted() && !$form->isValid()){
            $this->addFlash('ERROR', $this->translate('The payment method already exists'));
        }

        return $this->render('division/edit.html.twig', [
            'division' => $division,
            'form' => $form->createView(),
        ]);
    }


    public function delete(string $division): RedirectResponse
    {
        $division = $this->getEntity($division);

        if ($this->isCsrfTokenValid('delete'.$division->getId(), $this->getRequestPostParam('_token'))) {
            $this->divisionRepository->remove($division);
        }

        return $this->redirectToRoute('service_index');
    }
}