<?php

namespace App\Service\SurveyRangeService;

use App\Entity\Document\SurveyRange;
use App\Form\SurveyRangeType;
use App\Repository\SurveyRangeRepository;
use App\Service\FilterService;
use App\Shared\Classes\AbstractService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class SurveyRangeService extends AbstractService
{
    public function __construct(
        private readonly SurveyRangeRepository $surveyRangeRepository,

        EntityManagerInterface                            $em,

        RouterInterface                                   $router,
        Environment                                       $twig,
        RequestStack                                      $requestStack,
        TokenStorageInterface                             $tokenStorage,
        CsrfTokenManagerInterface                         $tokenManager,
        FormFactoryInterface                              $formFactory,
        SerializerInterface                               $serializer,
        TranslatorInterface $translator,
        protected KernelInterface $kernel
    )
    {
        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->surveyRangeRepository
        );
    }

    public function index()
    {
        $surveyRanges = $this->surveyRangeRepository->findSurveyRanges($this->filterService);

        return $this->render('survey_range/index.html.twig', [
            'totalResults' => $surveyRanges['totalRegisters'],
            'lastPage' => $surveyRanges['lastPage'],
            'currentPage' => $this->filterService->page,
            'surveyRanges' => $surveyRanges['data'],
            'filterService' => $this->filterService
        ]);
    }

    public function new()
    {
        $surveyRange = new SurveyRange();
        $form = $this->createForm(SurveyRangeType::class, $surveyRange);
        $this->getCurrentRequest()->get('survey_range');
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('survey_range')['startDate'] != null &&
            $this->getRequestPostParam('survey_range')['endDate'] != null) {

            $startDate = \DateTime::createFromFormat('d-m-Y', $this->getRequestPostParam('survey_range')['startDate'])->setTime(0, 0, 0);
            $endDate = \DateTime::createFromFormat('d-m-Y', $this->getRequestPostParam('survey_range')['endDate'])->setTime(23, 59, 59);

            $surveyRange->setStartDate($startDate);
            $surveyRange->setEndDate($endDate);
            $surveyRange->setStatus(true);

            $isAvailable = $this->checkAvailableDates($this->getRequestPostParam('survey_range')['startDate'], $this->getRequestPostParam('survey_range')['endDate']);

            if (!$isAvailable || $startDate > $endDate) {
                return $this->render('survey_range/new.html.twig', [
                    'surveyRange' => $surveyRange,
                    'form' => $form->createView(),
                ]);
            }

            $this->allRangesToDisabled();

            $this->surveyRangeRepository->persist($surveyRange);

            return $this->redirectToRoute('survey_range_index');
        }

        return $this->render('survey_range/new.html.twig', [
            'surveyRange' => $surveyRange,
            'form' => $form->createView(),
        ]);
    }

    public function edit(string $surveyRange): Response
    {
        $surveyRange = $this->getEntity($surveyRange);

        $form = $this->createForm(SurveyRangeType::class, $surveyRange);
        $form->handleRequest($this->getCurrentRequest());

        if ($form->isSubmitted() &&
            $this->getRequestPostParam('survey_range')['startDate'] != null &&
            $this->getRequestPostParam('survey_range')['endDate'] != null &&
            $this->getRequestPostParam('survey_range')['status'] != null) {

            $startDate = \DateTime::createFromFormat('d-m-Y', $this->getRequestPostParam('survey_range')['startDate'])->setTime(0, 0, 0);
            $endDate = \DateTime::createFromFormat('d-m-Y', $this->getRequestPostParam('survey_range')['endDate'])->setTime(23, 59, 59);
            $status = $this->getRequestPostParam('survey_range')['status'] === '1';

            $surveyRange->setStartDate($startDate);
            $surveyRange->setEndDate($endDate);
            $surveyRange->setStatus($status);

            $isAvailable = $this->checkAvailableDates($this->getRequestPostParam('survey_range')['startDate'],
                $this->getRequestPostParam('survey_range')['endDate'], $surveyRange);

            if (!$isAvailable || $startDate > $endDate) {
                return $this->render('survey_range/edit.html.twig', [
                    'surveyRange' => $surveyRange,
                    'form' => $form->createView(),
                ]);
            }

            $activeSurvey = $this->surveyRangeRepository->findOneBy(['status' => true]);

            if ($surveyRange->getStatus() && $activeSurvey != null && $surveyRange->getId() != $activeSurvey->getId()) {
                $activeSurvey->setStatus(false);
                $this->surveyRangeRepository->persist($activeSurvey);
            }

            $this->surveyRangeRepository->persist($surveyRange);

            return $this->redirectToRoute('survey_range_index');
        }

        return $this->render('survey_range/edit.html.twig', [
            'surveyRange' => $surveyRange,
            'form' => $form->createView(),
        ]);
    }

    public function delete(string $surveyRange): Response
    {
        $surveyRange = $this->getEntity($surveyRange);

        if ($this->isCsrfTokenValid('delete'.$surveyRange->getId(), $this->getRequestPostParam('_token'))) {

            $this->surveyRangeRepository->deleteSurveyRange($surveyRange);

            $filterService = new FilterService($this->getCurrentRequest());
            $filterService->addOrderValue('endDate', 'DESC');

            $nextActiveSurveyRange = (empty($this->surveyRangeRepository->findSurveyRanges($filterService)['data'])) ? null : $this->surveyRangeRepository->findSurveyRanges($filterService)['data'][0];
            if($nextActiveSurveyRange != null) {
                $nextActiveSurveyRange->setStatus(true);
                $this->surveyRangeRepository->persist($nextActiveSurveyRange);
            }
        }

        return $this->redirectToRoute('survey_range_index');
    }

    public function allRangesToDisabled()
    {
        /** @var SurveyRange $surveyRange*/
        $surveyRange = $this->surveyRangeRepository->findOneBy(['status' => true]);

        $surveyRange->setStatus(false);

        $this->surveyRangeRepository->persist($surveyRange);
    }

    public function checkAvailableDates($startDate, $endDate, $surveyRangeEdit = null)
    {
        $filterService = new FilterService($this->getCurrentRequest());
        $filterService->addFilter('startDate', $startDate);
        $sRStartStart = $this->surveyRangeRepository->findSurveyRanges($filterService);

        $filterService->addFilter('startDate', $endDate);
        $sREndStart = $this->surveyRangeRepository->findSurveyRanges($filterService);

        $surveyRanges = [];
        foreach ($sRStartStart['data'] as $surveyRange) {
            if (in_array($surveyRange, $sREndStart['data'])) {
                $surveyRanges[] = $surveyRange;
            }
        }

        $filterService->addFilter('startDate', null);

        $filterService->addFilter('endDate', $startDate);
        $sRStartEnd = $this->surveyRangeRepository->findSurveyRanges($filterService);

        $filterService->addFilter('endDate', $endDate);
        $sREndEnd = $this->surveyRangeRepository->findSurveyRanges($filterService);

        foreach ($sRStartEnd['data'] as $surveyRange) {
            if (in_array($surveyRange, $sREndEnd['data'])) {
                $surveyRanges[] = $surveyRange;
            }
        }

        $allSurveyRanges = $this->surveyRangeRepository->findAll();
        foreach ($allSurveyRanges as $key => &$surveyRange) {
            if (in_array($surveyRange, $surveyRanges)) {
                unset($allSurveyRanges[$key]);
            }

            if ($surveyRangeEdit != null && $surveyRangeEdit->getId() == $surveyRange->getId()) {
                unset($allSurveyRanges[$key]);
            }
        }

        return empty($allSurveyRanges);
    }
}