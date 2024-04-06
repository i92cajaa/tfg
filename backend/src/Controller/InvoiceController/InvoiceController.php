<?php

namespace App\Controller\InvoiceController;

use App\Entity\Appointment\Appointment;
use App\Entity\Invoice\Invoice;
use App\Service\InvoiceService\InvoiceService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Annotation\Permission;


#[Route(path: '/invoice')]
class InvoiceController extends AbstractController
{

    public function __construct(
        private readonly InvoiceService $invoiceService
    )
    {
    }

    #[Route(path: '/', name: 'invoice_index', methods: ["GET"])]
    #[Permission(group: 'invoices', action:"list")]
    public function index(): Response
    {
        return $this->invoiceService->findServices();
    }

    #[Route(path: '/generate/{appointment}', name: 'invoice_generate', defaults:["appointment" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'invoices', action:"create")]
    public function generate(?string $appointment): Response
    {
        return $this->invoiceService->generateInvoice($appointment);
    }

    #[Route(path: '/generate/users/multiple', name: 'invoice_generate_multiple', methods: ["GET", "POST"])]
    #[Permission(group: 'invoices', action:"create")]
    public function generateMultiple(): Response
    {
        return $this->invoiceService->generateUserInvoiceMultiple();
    }

    #[Route(path: '/generate/pdf/multiple', name: 'invoice_export_pdf_multiple', methods: ["POST"])]
    #[Permission(group: 'invoices', action:"create")]
    public function generateInvoicePdf(): bool
    {
        return $this->invoiceService->generateInvoicePdf();
    }

    #[Route(path: '/{invoice}', name: 'invoice_show', methods: ["GET"])]
    #[Permission(group: 'invoices', action:"show")]
    public function show(string $invoice): Response
    {
        return $this->invoiceService->renderShow($invoice);
    }

    #[Route(path: '/edit/{invoice}', name: 'invoice_edit', methods: ["GET"])]
    #[Permission(group: 'invoices', action:"edit")]
    public function renderEdit(string $invoice): Response
    {
        return $this->invoiceService->renderEdit($invoice);
    }

    #[Route(path: '/edit/{invoice}/process', name: 'invoice_edit_process', methods: ["POST"])]
    #[Permission(group: 'invoices', action:"edit")]
    public function processEdit(string $invoice): Response
    {
        return $this->invoiceService->processEdit($invoice);
    }

    #[Route(path: '/export/{invoice}/pdf', name: 'invoice_export_pdf', methods: ["GET"])]
    #[Permission(group: 'invoices', action:"create")]
    public function exportPdf(string $invoice): Response
    {
        return $this->invoiceService->exportPdf($invoice);
    }

    #[Route(path: '/delete/{invoice}', name: 'invoice_delete', methods: ["POST"])]
    #[Permission(group: 'invoices', action:"delete")]
    public function delete(string $invoice): Response
    {
        return $this->invoiceService->deleteInvoice($invoice);
    }
}
