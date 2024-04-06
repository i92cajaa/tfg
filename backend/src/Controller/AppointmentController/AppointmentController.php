<?php

namespace App\Controller\AppointmentController;

use App\Annotation\Permission;
use App\Annotation\Admin;
use App\Entity\Appointment\Appointment;
use App\Service\AppointmentService\AppointmentService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Constraints\Date;

class   AppointmentController extends AbstractController
{

    public function __construct(
        private readonly AppointmentService $appointmentService
    )
    {
    }

    #[Route(path: '/', name: 'appointment_index', methods: ["GET"])]
    #[Permission(group: 'appointments', action:"list")]
    public function index(): Response
    {
        return $this->appointmentService->index();
    }

    #[Route(path: '/appointment/get-appointments', name: 'appointment_get_json', methods: ["GET"])]
    #[Permission(group: 'appointments', action:"list")]
    public function getAppointmentsJson(): JsonResponse
    {

        return $this->json($this->appointmentService->getEventsAppointmentsFromRequest());
    }

    #[Route(path: '/appointment/export-excel', name: 'appointments_export_excel', methods: ["GET"])]
    public function exportExcel(): Response
    {
        return $this->appointmentService->exportExcel();
    }

    #[Route(path: '/appointment/report/{appointment}', name: 'appointment_report', methods: ["GET", "POST"])]
    public function report(string $appointment,Request $request): Response
    {
        return $this->appointmentService->report($appointment,$request);
    }



    #[Route(path: '/appointment/export-pdf/{appointment}', name: 'appointments_export_pdf', methods: ["GET","POST"])]
    public function exportPdf(string $appointment, Request $request): Response
    {
        return $this->appointmentService->exportPdf($appointment,$request);
    }

    #[Route(path: '/appointment/list', name: 'appointment_list', methods: ["GET"])]
    #[Permission(group: 'appointments', action:"list")]
    public function list(): Response
    {
        return $this->appointmentService->renderList();
    }

    #[Route(path: '/appointment/change-status/{appointment}', name: 'change_status_appointment', defaults: ["appointment" => null], methods: ["POST"])]
    #[Permission(group: 'appointments', action:"edit")]
    public function changeStatus(string $appointment): Response
    {
        return $this->appointmentService->changeStatus($appointment);
    }

    #[Route(path: '/appointment/modify-hour/{appointment}', name: 'modify_hour_appointment', defaults: ["appointment" => null], methods: ["POST"])]
    #[Permission(group: 'appointments', action:"modify_hour")]
    #[Admin]
    public function modifyHour(string $appointment): Response
    {
        return $this->appointmentService->modifyHour($appointment);
    }

    #[Route(path: '/appointment/notify/{appointment}', name: 'notify_appointment', defaults: ["appointment" => null], methods: ["POST"])]
    #[Permission(group: 'appointments', action:"modify_hour")]
    public function notify(?string $appointment): Response
    {
        return $this->appointmentService->notifyAppointments($appointment);
    }

    #[Route(path: '/appointment/delete-multiple-appointments', name: 'delete_multiple_appointment', methods: ["POST"])]
    #[Permission(group: 'appointments', action:"delete")]
    public function deleteMultiple(): Response
    {
        return $this->appointmentService->deleteAppointments();
    }


    #[Route(path: '/appointment/copy/{appointment}', name: 'copy_appointment', defaults: ["appointment" => null], methods: ["POST"])]
    public function copy(?string $appointment): Response
    {
        return $this->appointmentService->copyAppointments($appointment);
    }

    #[Route(path: '/appointment/complete/{appointment}', name: 'complete_appointment', defaults: ["appointment" => null], methods: ["POST"])]
    #[Permission(group: 'appointments', action:"edit")]
    public function complete(string $appointment): Response
    {
        return $this->appointmentService->complete($appointment);
    }

    #[Route(path: '/appointment/new', name: 'appointment_new', methods: ["GET", "POST"])]
    #[Permission(group: 'appointments', action:"create")]
    public function new(): Response
    {
        return $this->appointmentService->new();
    }

    #[Route(path: '/appointment/get', name: 'appointment_get', methods: ["POST"])]
    public function getAppointment(): Response
    {
        return $this->appointmentService->getAppointment();
    }

    #[Route(path: '/appointment/{appointment}', name: 'appointment_show', defaults: ["appointment" => null], methods: ["GET"])]
    #[Permission(group: 'appointments', action:"show")]
    public function show(string $appointment): Response
    {
        return $this->appointmentService->show($appointment);
    }

    #[Route(path: '/appointment/documents/{appointment}', name: 'appointment_document', defaults: ["appointment" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'appointments', action:"show")]
    public function uploadDocument(string $appointment,Request $request): Response
    {
        return $this->appointmentService->uploadAppointmentDocument($appointment,$request);
    }

    #[Route(path: '/appointment/documents-photo/{appointment}', name: 'appointment_photo', defaults: ["appointment" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'appointments', action:"show")]
    public function uploadDocumentPhoto(string $appointment,Request $request): Response
    {
        return $this->appointmentService->uploadAppointmentPhoto($appointment,$request);
    }



    #[Route(path: '/appointment/edit/{appointment}', name: 'appointment_edit', defaults: ["appointment" => null], methods: ["GET","POST"])]
    #[Permission(group: 'appointments', action:"edit")]
    public function edit(string $appointment): Response
    {
        return $this->appointmentService->edit($appointment);
    }

    #[Route(path: '/appointment/delete/{appointment}', name: 'appointment_delete', defaults: ["appointment" => null], methods: ["GET","POST"])]
    #[Permission(group: 'appointments', action:"delete")]
    public function delete(string $appointment): Response
    {
        return $this->appointmentService->delete($appointment);
    }

    #[Route(path: '/appointment/delete/periodic/{appointment}', name: 'appointment_delete_periodic', methods: ["POST"])]
    #[Permission(group: 'appointments', action:"delete")]
    public function deletePeriodicId(string $appointment): Response
    {
        return $this->appointmentService->deletePeriodicId($appointment);
    }
}
