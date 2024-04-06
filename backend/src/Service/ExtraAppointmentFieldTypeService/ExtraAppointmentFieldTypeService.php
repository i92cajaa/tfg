<?php
namespace App\Service\ExtraAppointmentFieldTypeService;

use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Form\ExtraAppointmentFieldTypeType;
use App\Repository\ExtraAppointmentFieldTypeRepository;
use App\Service\DivisionService\DivisionService;
use App\Service\DocumentService\DocumentService;
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

class ExtraAppointmentFieldTypeService extends AbstractService
{

    /**
     * @var ExtraAppointmentFieldTypeRepository
     */
    private ExtraAppointmentFieldTypeRepository $extraAppointmentFieldTypeRepository;


    public function __construct(
        private readonly DivisionService $divisionService,
        private readonly DocumentService $documentService,

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
        $this->extraAppointmentFieldTypeRepository = $em->getRepository(ExtraAppointmentFieldType::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->extraAppointmentFieldTypeRepository
        );
    }

    public function findAllExtraAppointmentFieldTypes(?bool $hydrateObject = false)
    {
        return $this->extraAppointmentFieldTypeRepository->findAllExtraAppointmentFieldTypes($hydrateObject);
    }

    public function findBy(?array $criteria = []): array
    {
        return $this->extraAppointmentFieldTypeRepository->findBy($criteria);
    }

    public function index(): Response
    {
        $extraAppointmentFieldTypes = $this->extraAppointmentFieldTypeRepository->findExtraAppointmentFieldTypes($this->filterService);

        return $this->render('extra_appointment_field_type/index.html.twig', [
            'totalResults' => $extraAppointmentFieldTypes['totalRegisters'],
            'lastPage' => $extraAppointmentFieldTypes['lastPage'],
            'currentPage' => $this->filterService->page,
            'types' => ExtraAppointmentFieldType::TYPES,
            'extra_appointment_field_types' => $extraAppointmentFieldTypes['data'],
            'filterService' => $this->filterService
        ]);
    }

    public function create(): RedirectResponse|Response
    {


        $extraAppointmentFieldType = new ExtraAppointmentFieldType();
        $form = $this->createForm(ExtraAppointmentFieldTypeType::class, $extraAppointmentFieldType);
        $form->handleRequest($this->getCurrentRequest());


        if ($form->isSubmitted() && $form->isValid()) {

            $options = @$form->getExtraData()['options']?:[];
            $extraAppointmentFieldType->setOptions($options);

            $this->extraAppointmentFieldTypeRepository->persist($extraAppointmentFieldType);

            return $this->redirectToRoute('extra_appointment_field_type_index');
        }


        return $this->render('extra_appointment_field_type/new.html.twig', [
            'extra_appointment_field_type' => $extraAppointmentFieldType,
            'types' => ExtraAppointmentFieldType::TYPES,
            'positions' => ExtraAppointmentFieldType::POSITIONS,
            'divisions' => $this->divisionService->findAll(),
            'form' => $form->createView()
        ]);
    }

    public function edit(string $extraAppointmentFieldType): RedirectResponse|Response
    {
        $extraAppointmentFieldType = $this->getEntity($extraAppointmentFieldType);

        $form = $this->createForm(ExtraAppointmentFieldTypeType::class, $extraAppointmentFieldType);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() && $form->isValid()) {
            $options = @$form->getExtraData()['options']?:[];
            $extraAppointmentFieldType->setOptions($options);
            $this->extraAppointmentFieldTypeRepository->persist($extraAppointmentFieldType);

            return $this->redirectToRoute('extra_appointment_field_type_index');
        }

        return $this->render('extra_appointment_field_type/edit.html.twig', [
            'extra_appointment_field_type' => $extraAppointmentFieldType,
            'types' => ExtraAppointmentFieldType::TYPES,
            'positions' => ExtraAppointmentFieldType::POSITIONS,
            'divisions' => $this->divisionService->findAll(),
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $extraAppointmentFieldType): RedirectResponse
    {
        $extraAppointmentFieldType = $this->getEntity($extraAppointmentFieldType);

        if ($this->isCsrfTokenValid('delete'.$extraAppointmentFieldType->getId(), $this->getRequestPostParam('_token'))) {
            $this->extraAppointmentFieldTypeRepository->deleteExtraAppointmentFieldType($extraAppointmentFieldType);
        }

        return $this->redirectToRoute('extra_appointment_field_type_index');
    }

    public function format(?EntityWithExtraFields $entity, array $extraAppointmentFields, array $files, string $directory): array
    {
        $extraFields = [];
        foreach($extraAppointmentFields as $index => $field) {
            if ($field['type'] == ExtraAppointmentFieldType::SOURCE_TYPE) {
                $value = @$files[$index]['value'];

                if($value != null){
                    $field['value'] = $this->documentService->uploadDocument($value, $directory)->getId();
                }elseif($entity){
                    $field['value'] = $entity->extraFieldValueByTitle($field['title']);
                }else{
                    $field['value'] = '';
                }

            } elseif ($field['type'] == ExtraAppointmentFieldType::BOOLEAN_TYPE) {
                $field['value'] = @$field['value'] ? 'Si' : 'No';
            }
            $extraFields[] = $field;

        }

        return $extraFields;
    }


}