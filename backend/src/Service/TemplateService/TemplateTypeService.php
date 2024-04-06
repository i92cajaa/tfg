<?php
namespace App\Service\TemplateService;


use App\Entity\Template\TemplateLineType;
use App\Entity\Template\TemplateType;
use App\Form\TemplateTypeType;
use App\Repository\TemplateTypeRepository;
use App\Shared\Classes\AbstractService;
use App\Shared\Interfaces\EntityWithExtraFields;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactory;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManager;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class TemplateTypeService extends AbstractService
{

    /**
     * @var TemplateTypeRepository
     */
    private TemplateTypeRepository $templateTypeRepository;


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
        $this->templateTypeRepository = $em->getRepository(TemplateType::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->templateTypeRepository
        );
    }


    public function findBy(?array $criteria = []): array
    {
        return $this->templateTypeRepository->findBy($criteria);
    }

    public function findTemplateTypesByIds(array $ids){
        return $this->templateTypeRepository->findTemplateTypesByIds($ids);
    }

    public function index(): Response
    {
        $templateTypes = $this->templateTypeRepository->findTemplateTypes($this->filterService);

        return $this->render('template_type/index.html.twig', [
            'totalResults' => $templateTypes['totalRegisters'],
            'lastPage' => $templateTypes['lastPage'],
            'currentPage' => $this->filterService->page,
            'template_types' => $templateTypes['data'],
            'entities' => TemplateType::ENTITIES,
            'filterService' => $this->filterService
        ]);
    }

    public function create(): RedirectResponse|Response
    {


        $templateType = new TemplateType();
        $form = $this->createForm(TemplateTypeType::class, $templateType);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {

            if(@$form->getExtraData()['template_line_types']){
                $templateLineTypes = $form->getExtraData()['template_line_types'];
                asort($form->getExtraData()['template_line_types']);
                $templateType = $this->templateTypeRepository->addTemplateLineTypes($templateType, $templateLineTypes);
            }

            $this->templateTypeRepository->persist($templateType);

            return $this->redirectToRoute('template_type_index');
        }

        return $this->render('template_type/new.html.twig', [
            'template_type' => $templateType,
            'types' => TemplateLineType::TYPES,
            'entities' => TemplateType::ENTITIES,
            'form' => $form->createView()
        ]);
    }

    public function edit(string $templateType): RedirectResponse|Response
    {
        $templateType = $this->getEntity($templateType);

        $form = $this->createForm(TemplateTypeType::class, $templateType);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $templateType = $this->templateTypeRepository->removeAllTemplateLineTypes($templateType);

            $templateLineTypes = $form->getExtraData()['template_line_types'];
            asort($form->getExtraData()['template_line_types']);
            $templateType = $this->templateTypeRepository->addTemplateLineTypes($templateType, $templateLineTypes);
            $this->templateTypeRepository->persist($templateType);

            return $this->redirectToRoute('template_type_index');
        }

        if( $form->isSubmitted() && !$form->isValid()){
            $this->addFlash('ERROR', $this->translate('The template type already exists'));
        }

        return $this->render('template_type/edit.html.twig', [
            'template_type' => $templateType,
            'types' => TemplateLineType::TYPES,
            'entities' => TemplateType::ENTITIES,
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $templateType): RedirectResponse
    {
        $templateType = $this->getEntity($templateType);

        if ($this->isCsrfTokenValid('delete'.$templateType->getId(), $this->getRequestPostParam('_token'))) {
            $this->templateTypeRepository->deleteTemplateType($templateType);
        }

        return $this->redirectToRoute('template_type_index');
    }


}