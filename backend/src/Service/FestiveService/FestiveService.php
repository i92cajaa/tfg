<?php


namespace App\Service\FestiveService;


use App\Entity\Config\Config;
use App\Entity\Festive\Festive;
use App\Entity\Schedules\Schedules;
use App\Form\FestiveType;
use App\Repository\FestiveRepository;
use App\Repository\SchedulesRepository;
use App\Repository\ServiceRepository;
use App\Service\UserService\UserService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
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


class FestiveService extends AbstractService
{

    /**
     * @var FestiveRepository
     */
    private FestiveRepository $festiveRepository;

    public function __construct(
        private readonly UserService $userService,

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
        $this->festiveRepository = $em->getRepository(Festive::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->festiveRepository
        );
    }

    public function index(): Response
    {
        $festives = $this->festiveRepository->findFestives($this->filterService);

        return $this->render('festive/index.html.twig', [
            'totalResults' => $festives['totalRegisters'],
            'lastPage' => $festives['lastPage'],
            'currentPage' => $this->filterService->page,
            'festives' => $festives['data'],
            'filterService' => $this->filterService
        ]);
    }

    public function new(): Response
    {
        $festive = new Festive();
        $form = $this->createForm(FestiveType::class, $festive);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $this->festiveRepository->persist($festive);

            return $this->redirectToRoute('festive_index');
        }

        return $this->render('festive/new.html.twig', [
            'festive' => $festive,
            'users' => $this->userService->getAll(),
            'form' => $form->createView(),

        ]);
    }

    public function show(string $festive): Response
    {
        $festive = $this->getEntity($festive);

        return $this->render('festive/show.html.twig', [
            'festive' => $festive,
        ]);
    }

    public function edit(string $festive): Response
    {
        $festive = $this->getEntity($festive);

        $form = $this->createForm(FestiveType::class, $festive);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->festiveRepository->persist($festive);

            return $this->redirectToRoute('festive_index');
        }

        return $this->render('festive/edit.html.twig', [
            'festive' => $festive,
            'users' => $this->userService->getAll(),
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $festive): Response
    {
        $festive = $this->getEntity($festive);

        if ($this->isCsrfTokenValid('delete'.$festive->getId(), $this->getRequestPostParam('_token'))) {
            $this->festiveRepository->remove($festive);
        }

        return $this->redirectToRoute('festive_index');
    }

}