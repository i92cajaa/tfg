<?php


namespace App\Service\SchedulesService;



use App\Entity\Client\Client;
use App\Entity\Festive\Festive;
use App\Entity\Schedules\Schedules;
use App\Entity\Service\Service;
use App\Entity\User\User;
use App\Repository\ClientRepository;
use App\Repository\ServiceRepository;
use App\Repository\UserRepository;
use App\Repository\FestiveRepository;
use App\Repository\SchedulesRepository;
use App\Service\AppointmentService\AppointmentService;
use App\Service\ConfigService\ConfigService;
use App\Service\ServiceService\ServiceService;
use App\Service\UserService\UserService;
use App\Shared\Classes\AbstractService;
use App\Shared\Classes\UTCDateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Csrf\CsrfTokenManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;


class SchedulesService extends AbstractService
{

    /**
     * @var SchedulesRepository
     */
    private SchedulesRepository $schedulesRepository;

    /**
     * @var FestiveRepository
     */
    private FestiveRepository $festiveRepository;

    /**
     * @var ClientRepository
     */
    private ClientRepository $clientRepository;

    /**
     * @var ServiceRepository
     */
    private ServiceRepository $serviceRepository;
    
    /**
     * @var UserRepository
     */
    private UserRepository $userRepository;


    public function __construct(
        private readonly ServiceService $serviceService,
        private readonly AppointmentService $appointmentService,
        private readonly UserService $userService,
        private readonly ConfigService $configService,

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
        $this->schedulesRepository = $em->getRepository(Schedules::class);
        $this->clientRepository = $em->getRepository(Client::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->festiveRepository = $em->getRepository(Festive::class);
        $this->serviceRepository = $em->getRepository(Service::class);

        parent::__construct(
            $requestStack,
            $router,
            $twig,
            $tokenStorage,
            $tokenManager,
            $formFactory,
            $serializer,
            $translator,
            $this->schedulesRepository
        );
    }

    public function show(string $user): Response
    {
        $user = $this->userService->getUserById($user);
        $schedules = $this->schedulesRepository->findBy(['user' => $user, 'status' => 1],['timeFrom' => 'ASC']);

        return $this->render('schedules/show.html.twig', [
            'schedules' => $schedules,
            'user' => $user
        ]);
    }

    public function new(): Response
    {

        $dates = json_decode($this->getRequestPostParam('dates'));

        if ($this->isCsrfTokenValid('new', $this->getRequestPostParam('_token'))) {

            if(!empty($dates)){
                foreach ($dates as $schedule){
                    if($schedule[0] != null || $schedule[1] != null){
                        $newSchedule = new Schedules();
                        $newSchedule->setUser($this->userService->getUserById($this->getRequestPostParam('user')));
                        $newSchedule->setWeekDay($this->getRequestPostParam('weekDay'));


                        $timeFrom = explode(':', $schedule[0]);
                        $scheduleTF = UTCDateTime::create('!d','02');
                        $scheduleTF->setTime($timeFrom[0], $timeFrom[1]);

                        $timeTo = explode(':', $schedule[1]);
                        $scheduleTT = UTCDateTime::create('!d','02');
                        $scheduleTT->setTime($timeTo[0], $timeTo[1]);

                        if($scheduleTF != $scheduleTT){
                            $newSchedule->setTimeFrom($scheduleTF);
                            $newSchedule->setTimeTo($scheduleTT);
                            $newSchedule->setStatus(true);

                            $compare = $this->schedulesRepository->findOneBy(['user'=>$newSchedule->getUser(), 'weekDay' => $newSchedule->getWeekDay(), 'timeFrom' => $newSchedule->getTimeFrom(), 'timeTo' =>$newSchedule->getTimeTo()]);

                            if($compare == null ){

                                $this->schedulesRepository->persist($newSchedule);

                            }else{
                                if(!$compare->getStatus()){
                                    $compare->setStatus(true);
                                    $this->schedulesRepository->persist($compare);
                                }
                            }

                        }

                    }

                }
            }

        }

        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }else{
            return $this->redirectToRoute('schedules_show', [
                'user'=>$this->getRequestPostParam('user')
            ]);
        }

    }

    public function edit()
    {
        if ($this->isCsrfTokenValid('edit', $this->getRequestPostParam('_token'))) {

            $schedule = $this->schedulesRepository->find($this->getRequestPostParam('schedule'));

            if($schedule != null){
                if($this->getRequestPostParam('fixed') != null) {
                    $schedule->setFixed(true);
                    $schedule->setFlexScheduleInterval(null);
                }else{
                    $schedule->setFixed(false);
                    $schedule->setFlexScheduleInterval(@$this->getRequestPostParam('schedule_interval'));
                }

                $this->schedulesRepository->persist($schedule);
            }

        }

        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }else{
            return $this->redirectToRoute('schedules_show', [
                'user'=>$this->getRequestPostParam('user')
            ]);
        }

    }

    public function toggle()
    {
        $schedules = $this->schedulesRepository->find($this->getRequestPostParam('schedule'));
        if ($this->isCsrfTokenValid('edit', $this->getRequestPostParam('_token'))) {
            if ($schedules->isFixed()) {
                $schedules->setFixed(false);
            } else {
                $schedules->setFixed(true);
            }

            $this->schedulesRepository->persist($schedules);
        }

        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }else{
            return $this->redirectToRoute('schedules_show', [
                'user'=>$schedules->getUser()->getId()
            ]);
        }

    }

    public function availableDates(): JsonResponse
    {
        $timeFrom = UTCDateTime::create('d-m-Y H:i:s', '1-' . $this->getRequestPostParam('month') . '-' . $this->getRequestPostParam('year') . ' 00:00:00');
        $timeTo = clone $timeFrom;
        $timeTo->modify('last day of this month')->setTime(23, 59, 59);

        $client = $this->getRequestPostParam('client');
        $user = $this->getRequestPostParam('user');
        //dd($this->getRequestPostParam('user'));
        $totalDates = [];

        if($user){
            $client = $this->clientRepository->find($client);
            $user = $this->userRepository->find($user);

            if($user){
                $userScheduleSchema = $user->getScheduleSchema();
                $userFestiveSchema = $user->getFestiveSchema();

                while($timeFrom < $timeTo){
                    if(
                        array_key_exists($timeFrom->format('w'), $userScheduleSchema)
                        && !in_array($timeFrom->format('Y-m-d'), $userFestiveSchema)
                        && !in_array($timeFrom->format('Y-m-d'), $totalDates)
                    ){
                        $totalDates[] = $timeFrom->format('Y-m-d');
                    }

                    $timeFrom->modify('+1 day');
                }
            }

        }
        
        return new JsonResponse($totalDates);
    }

    public function availables(): JsonResponse
    {
        $finalSchedules = [];
        if(@$this->getRequestParam('selected_services')){
            $service = $this->serviceRepository->find(@$this->getRequestParam('selected_services'));
            $minutesNeeded = $this->serviceService->sumMinutes($this->getRequestParam('selected_services'));
            $schedules = $this->schedulesRepository->findAvailableSchedulesByServiceIdsAndDate($this->getRequestParam('selected_services'), $this->getRequestParam('selected_date'), $this->getRequestParam('user'));
            if(@$this->getRequestParam('selected_date')){
                foreach ($schedules as $schedule){

                    if(!@$this->getRequestPostParam('user') || $schedule['user']['id'] == $this->getRequestPostParam('user')){
                        $check = $this->appointmentService->checkIfIsCompleted($schedule['id'], $this->getRequestParam('selected_date'), $schedule['timeFrom'],$schedule['timeTo'], boolval($schedule['fixed']));

                        if ($check == false){

                            if(!$schedule['fixed'] && $minutesNeeded > 0){

                                $finalSchedules = array_merge($finalSchedules, $this->generateFixedSchedule($schedule['id'], $minutesNeeded, $schedule, $this->getRequestParam('selected_date'),$service));
                            }elseif(!$schedule['fixed'] && $minutesNeeded <= 0 && $schedule['flex_schedule_interval'] > 0){

                                $minutesNeeded = $schedule['flex_schedule_interval'];
                                $finalSchedules = array_merge($finalSchedules, $this->generateFixedSchedule($schedule['id'], $minutesNeeded, $schedule, $this->getRequestParam('selected_date'),$service));
                            }else{

                                $intervalMinutes = ($schedule['timeTo']->getTimestamp() - $schedule['timeFrom']->getTimestamp()) / 60;

                                if($minutesNeeded < $intervalMinutes){
                                    $finalSchedules[] = $schedule;
                                }
                            }
                        }
                    }

                }

                $finalSchedules = $this->uniqueSchedule($finalSchedules);


            }
        }

        $response = new JsonResponse(['schedules' => $finalSchedules]);

        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function uniqueSchedule(array $array): array
    {

        $finalArray = [];
        foreach ($array as $schedule) {
            $scheduleArray = [
                'id' => $schedule['id'],
                'timeFrom' => $schedule['timeFrom'],
                'timeTo' => $schedule['timeTo'],
                'user' => @$schedule['user']['name'] . ' ' . @$schedule['user']['surnames']
            ];


            $finalArray[] = $scheduleArray;
        }

        usort($finalArray, function($a, $b) {
            if ($a['timeFrom'] < $b['timeFrom']) {
                return -1; // $a es menor que $b
            } elseif ($a['timeFrom'] > $b['timeFrom']) {
                return 1; // $a es mayor que $b
            } else {
                return 0; // $a y $b son iguales
            }
        });

        return $finalArray;

    }

    public function generateFixedSchedule(string $scheduleId, int $minutesInterval, array $schema, string $date, ?Service $service): array
    {
        $schedule = $this->schedulesRepository->find($scheduleId);
        $result = [];

        $currentTimeFrom = $schedule->getTimeFrom();
        $currentTimeTo = clone $currentTimeFrom;
        $currentTimeTo->modify('+' . $minutesInterval . ' minutes');
        if($currentTimeTo <= $schedule->getTimeTo()){

            while($currentTimeTo <= $schedule->getTimeTo()){
                $start = UTCDateTime::create('Y-m-d',$date, new \DateTimeZone('UTC'))->setTime($currentTimeFrom->format('H'),$currentTimeFrom->format('i'),$currentTimeFrom->format('s'));
                $end = UTCDateTime::create('Y-m-d',$date, new \DateTimeZone('UTC'))->setTime($currentTimeTo->format('H'),$currentTimeTo->format('i'),$currentTimeTo->format('s'));
                $appointment = $this->appointmentService->getOneAppointmentByDatesAndSchedule($start, $end, $schedule);
                if(!$appointment or $service->isForAdmin()){
                    $scheduleData = $schema;
                    $scheduleData['timeFrom'] = clone $currentTimeFrom;
                    $scheduleData['timeTo'] = clone $currentTimeTo;
                    $result[] = $scheduleData;
                    $currentTimeFrom->modify('+ 15 minutes');
                    $currentTimeTo->modify('+ 15 minutes');
                }else{
                    $currentTimeFrom->setTime($appointment->getTimeTo()->format('H'), $appointment->getTimeTo()->format('i'));
                    $currentTimeTo = clone $currentTimeFrom;
                    $currentTimeTo->modify('+ '.$minutesInterval.' minutes');
                }


            }



        }else{
            if(!$this->appointmentService->getOneAppointmentByDatesAndSchedule($schedule->getTimeFrom(), $schedule->getTimeTo(), $schedule)) {

                $scheduleData              = $schema;
                $scheduleData['timeFrom'] = $schedule->getTimeFrom();
                $scheduleData['timeTo'] = $schedule->getTimeTo();
                $result[] = $scheduleData;
            }


        }

        return $result;

    }

    public function getSchedulesByAppointmentAndDate(): JsonResponse
    {
        $appointment = $this->appointmentService->find($this->getRequestPostParam('id'));
        $date = @$this->getRequestPostParam('date');

        $finalSchedules = [];

        if($appointment && $date){
            $user = $appointment->getUser();
            $schedules = $this->schedulesRepository->findAvailableSchedules($user->getId(), $date);

            $minutesNeeded = $this->serviceService->sumMinutes($appointment->getArrayServicesIds());

            $finalSchedules = [];
            foreach ($schedules as $schedule){
                $check = $this->appointmentService->checkIfIsCompleted($schedule['id'],$date, $schedule['timeFrom'],$schedule['timeTo'], boolval($schedule['fixed']));

                if ($check == false){

                    if(!$schedule['fixed'] && $minutesNeeded > 0){
                        $finalSchedules = array_merge($finalSchedules, $this->generateFixedSchedule($schedule['id'], $minutesNeeded, $schedule, $this->getRequestParam('selected_date'),null));
                    }elseif(!$schedule['fixed'] && $minutesNeeded <= 0 && $schedule['flex_schedule_interval'] > 0){
                        $minutesNeeded = $schedule['flex_schedule_interval'];
                        $finalSchedules = array_merge($finalSchedules, $this->generateFixedSchedule($schedule['id'], $minutesNeeded, $schedule, $this->getRequestParam('selected_date'),null));
                    }else{
                        $intervalMinutes = ($schedule['timeTo']->getTimestamp() - $schedule['timeFrom']->getTimestamp()) / 60;

                        if($minutesNeeded < $intervalMinutes){
                            $finalSchedules[] = $schedule;
                        }
                    }
                }

            }
        }

        $response = new JsonResponse($finalSchedules);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function copySchedules(): RedirectResponse
    {
        $user = $this->userService->getUserById($this->getRequestPostParam('user'));

        if ($this->isCsrfTokenValid('copy-schedule', $this->getRequestPostParam('_token'))) {

            if(
                @$this->getRequestPostParam('user')
                && @$this->getRequestPostParam('copy_weekday')
                && @$this->getRequestPostParam('next_weekday')
            ){

                $weekDay = intval($this->getRequestPostParam('copy_weekday'));
                $nextWeekDays = $this->getRequestPostParam('next_weekday');

                $schedules = $this->schedulesRepository->findSchedulesByUserAndWeekDay($user, $weekDay);

                foreach ($nextWeekDays as $nextWeekDay){
                    /** @var Schedules $schedule */
                    foreach ($schedules as $schedule){
                        $exist = $this->schedulesRepository->findOneBy(['user' => $user, 'weekDay' => $nextWeekDay, 'timeFrom' => $schedule->getTimeFrom(), 'timeTo' => $schedule->getTimeTo()]);

                        if(!$exist){
                            $this->schedulesRepository->createSchedule(
                                $schedule->getUser(),
                                $schedule->getTimeFrom(),
                                $schedule->getTimeTo(),
                                intval($nextWeekDay),
                                $schedule->getStatus(),
                                $schedule->isFixed()
                            );
                        }elseif(!$exist->getStatus()){
                            $this->schedulesRepository->toggleSchedule($exist, true);
                        }

                    }

                }

            }

        }

        return $this->redirectToRoute('schedules_show', [
            'user'=>$user->getId()
        ]);
    }

    public function checkPeriodicity(): Response
    {
        $user = $this->userService->getUserById($this->getRequestParam('user_id'));
        $schedule = $this->schedulesRepository->find($this->getRequestParam('schedule_id'));
        $schedules = $this->schedulesRepository->findWeekDaysByDatesAndUser($user, $schedule->getTimeFrom(), $schedule->getTimeTo());

        $weekDays = [];
        foreach ($schedules as $loopSchedule){
            $weekDays[] = $loopSchedule->getWeekDay();
        }
        $response = new JsonResponse($weekDays);
        $response->headers->set('Content-Type', 'application/json');
        return $response;
    }

    public function delete(): Response
    {
        if ($this->isCsrfTokenValid('delete', $this->getRequestPostParam('_token'))) {
            $schedule = $this->schedulesRepository->find($this->getRequestPostParam('schedule'));
            if(sizeof($schedule->getAppointments()->toArray()) == 0){
                $this->schedulesRepository->remove($schedule);
            }else{
                $schedule->setStatus(false);
                $this->schedulesRepository->persist($schedule);
            };

        }

        if($this->getRequestPostParam('route') != null){
            return $this->redirect($this->getRequestPostParam('route'));
        }else{
            return $this->redirectToRoute('schedules_show', [
                'user'=>$this->getRequestPostParam('user')
            ]);
        }
    }
}