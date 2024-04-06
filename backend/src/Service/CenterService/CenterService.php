<?php

namespace App\Service\CenterService;


use App\Entity\Center\Center;
use App\Entity\Role\Role;
use App\Entity\User\User;
use App\Form\CenterType;
use App\Repository\CenterRepository;
use App\Repository\RoleRepository;
use App\Repository\UserRepository;
use App\Service\DocumentService\DocumentService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class CenterService extends AbstractService
{

    /**
     * @var CenterRepository
     */
    private CenterRepository $centerRepository;

    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;

    /**
     * @var RoleRepository
     */
    private RoleRepository $roleRepository;


    /**
     * @param CenterRepository $centerRepository
     */
    public function __construct(
        private readonly DocumentService $documentService,
        CenterRepository $centerRepository,
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
        $this->userRepository = $em->getRepository(User::class);
        $this->centerRepository = $em->getRepository(Center::class);
        $this->roleRepository = $em->getRepository(Role::class);
        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->centerRepository
        );
    }


    public function index(): Response
    {

        $centers = $this->centerRepository->findInvoices($this->filterService, true);

        return $this->render('center/index.html.twig', [
            'center' => $centers,  // Asegúrate de que esta variable esté definida
            'totalResults' => $centers['totalRegisters'],
            'lastPage' => 0,
            'currentPage' => $this->filterService->page,
            'centers' => $centers['data'],
            'filterService' => $this->filterService,
        ]);
    }

    public function new():Response{
        $center = new Center();
        $form = $this->createForm(CenterType::class, $center);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            $file = $form->get('logo')->getData();
            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'center');
                $center->setLogo($imgProfile);
            }

            $this->centerRepository->save($center,true);

            return $this->redirectToRoute('center_index');
        }

        return $this->render('center/new.html.twig', [
            'center' => $center,
            'form' => $form->createView(),
        ]);

    }

    //Función para ELIMINAR Centros

    public function delete(string $center): Response
    {
        $center = $this->getEntity($center);
        $this->centerRepository->remove($center,true);

        return $this->redirectToRoute('center_index');
    }


    //Función para MOSTRAR Centro

    public function show(string $centerId): Response
    {
        $center = $this->centerRepository->find($centerId);

        return $this->render('center/show.html.twig', [
            'center' => $center
        ]);
    }



//Función para EDITAR Centros
    public function edit(string $center): Response
    {
        $center = $this->getEntity($center);

        $form = $this->createForm(CenterType::class, $center);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $this->centerRepository->save($center,true);


            $file = $form->get('logo')->getData();
            if($file != null){
                $imgProfile = $this->documentService->uploadDocument($file, 'center');
                $center->setLogo($imgProfile);
                $this->centerRepository->save($center,true);

            }
            return $this->redirectToRoute('center_index');
        }
        return $this->render('center/edit.html.twig', [
            'center' => $center,
            'form' => $form->createView(),
        ]);
    }





}