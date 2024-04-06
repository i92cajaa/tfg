<?php

namespace App\Controller\ExtraAppointmentFieldTypeController;

use App\Entity\Festive;
use App\Entity\ExtraAppointmentField\ExtraAppointmentFieldType;
use App\Service\FilterService;
use App\Service\ExtraAppointmentFieldTypeService\ExtraAppointmentFieldTypeService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Annotation\Permission;

use Symfony\Component\Routing\Annotation\Route;


#[Route(path: '/extra-appointment-field-type')]
class ExtraAppointmentFieldTypeController extends AbstractController
{

    public function __construct(
        private readonly ExtraAppointmentFieldTypeService $extraAppointmentFieldTypeService
    )
    {
    }

    #[Route(path: '/', name: 'extra_appointment_field_type_index', methods: ["GET"])]
    #[Permission(group: 'extra_appointment_field_types', action:"list")]
    public function index(): Response
    {
        return $this->extraAppointmentFieldTypeService->index();
    }

    #[Route(path: '/new', name: 'extra_appointment_field_type_new', methods: ["GET", "POST"])]
    #[Permission(group: 'extra_appointment_field_types', action:"create")]
    public function new(): Response
    {
        return $this->extraAppointmentFieldTypeService->create();
    }

    #[Route(path: '/edit/{extraAppointmentFieldType}', name: 'extra_appointment_field_type_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'extra_appointment_field_types', action:"edit")]
    public function edit(string $extraAppointmentFieldType): Response
    {
        return $this->extraAppointmentFieldTypeService->edit($extraAppointmentFieldType);
    }

    #[Route(path: '/delete/{extraAppointmentFieldType}', name: 'extra_appointment_field_type_delete', methods: ["POST"])]
    #[Permission(group: 'extra_appointment_field_types', action:"delete")]
    public function delete(string $extraAppointmentFieldType): Response
    {
        return $this->extraAppointmentFieldTypeService->delete($extraAppointmentFieldType);
    }
}
