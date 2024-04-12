<?php

namespace App\Controller\UserController;

use App\Annotation\Permission;
use App\Service\UserService\UserService;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route(path: '/user')]
class UserController extends AbstractController
{

    public function __construct(
        private readonly UserService $userService
    ) {
    }


    #[Route(path: '/', name: 'user_index', methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function index(): Response
    {

        return $this->userService->index();
    }

    #[Route(path: '/mentores', name: 'user_mentores_index', methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function mentoresIndex(): Response
    {

        return $this->userService->mentoresIndex();
    }


    #[Route(path: '/new', name: 'user_new', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "create")]
    public function new(): Response
    {
        return $this->userService->new();
    }

    #[Route(path: '/dashboard', name: 'user_dashboard', methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function dashboard(): Response
    {
        return $this->userService->dashboard(null, null,null,null,null);
    }

    #[Route(path: "/searchDateRange", name: "search_date_range", methods: ["POST"])]
    #[Permission(group: 'users', action: "list")]
    public function searchDateRange(Request $request): Response
    {
        $dateRange = $request->request->get('date_range');
        $dateArray = explode(' a ', $dateRange);
        if (count($dateArray) >= 2) {
            
            
            $startDate = DateTime::createFromFormat('d-m-Y H:i:s', $dateArray[0] . ' 00:00:00');
            $endDate = DateTime::createFromFormat('d-m-Y H:i:s', $dateArray[1] . ' 23:59:59');
            
            if ($startDate instanceof DateTime && $endDate instanceof DateTime) {
                return $this->userService->dashboard(null, null, $startDate, $endDate,null);
            }
        }

        return $this->userService->dashboard(null, null, null, null,null);
    }

    #[Route(path: "/searchDateYear", name: "search_date_year", methods: ["POST"])]
    #[Permission(group: 'users', action: "list")]
    public function searchDateYear(Request $request): Response
    {
        $selectedDate = $request->request->get('selected_date');
        
            if ($selectedDate!==null) {

                $formattedDate = \DateTime::createFromFormat('m/d/Y', $selectedDate);

                if ($formattedDate) {
                    
                    $formattedDateTime = $formattedDate->setTime(0, 0, 0);
            
                    
                    return $this->userService->dashboard(null, null, null, null, $formattedDateTime);
                } else {
                    // Manejar el caso en que la creación de la instancia de DateTime falló
                    
                }
            }
        

        return $this->userService->dashboard(null, null, null, null,null);
    }

    #[Route(path: "/searchUsers/{user}", name: "search_users", methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function searchUsers(String $user): Response
    {
        if ($user !== "null") {
            return $this->userService->dashboard($user, null,null,null,null);
        }

        return $this->userService->dashboard(null, null,null,null,null);
    }

    #[Route(path: "/searchCenter/{center}", name: "search_center", methods: ["GET"])]
    #[Permission(group: 'users', action: "list")]
    public function searchCenter(String $center): Response
    {
        if ($center !== "null") {
            return $this->userService->dashboard(null, $center,null,null,null);
        }
        return $this->userService->dashboard(null, null,null,null,null);
    }

    #[Route(path: '/mentoresByArea', name: 'get_mentores_by_area', methods: ["POST"])]
    public function mentoresByArea(): Response
    {
        return $this->userService->mentoresByArea();
    }

    #[Route(path: '/downloadMentor/{user}', name: 'user_mentor_documents', methods: ["POST", "GET"])]
    public function mentoresDocuments(string $user): Response
    {
        return $this->userService->downloadMentorAssets();
    }

    #[Route(path: '/allmentores', name: 'get_all_mentores', methods: ["POST"])]
    public function allmentores(): Response
    {
        return $this->userService->allmentores();
    }

    #[Route(path: '/{user}', name: 'user_show', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "show")]
    public function show(string $user): Response
    {
        return $this->userService->show($user);
    }

    #[Route(path: '/edit/{user}', name: 'user_edit', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function edit(string $user): Response
    {
        return $this->userService->edit($user);
    }

    #[Route(path: '/view_profile/{user}', name: 'user_view_profile', methods: ["GET", "POST"])]
    public function user_view_profile(string $user, Request $request): Response
    {
        return $this->userService->user_view_profile($user, $request);
    }

    #[Route(path: '/change-status/{user}', name: 'user_change_status', methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function change_status(string $user): Response
    {
        return $this->userService->change_status($user);
    }

    #[Route(path: '/delete/{user}', name: 'user_delete', methods: ["POST"])]
    #[Permission(group: 'users', action: "delete")]
    public function delete(string $user): Response
    {
        return $this->userService->delete($user);
    }

    #[Route(path: '/services', name: 'users_get_by_services', methods: ["POST"])]
    public function getUsersByService(): Response
    {
        return $this->userService->getUsersByService();
    }

    #[Route(path: '/services-and-dates', name: 'users_get_by_services_and_date', methods: ["POST"])]
    public function getUsersByServiceAndDate(): Response
    {
        return $this->userService->getUsersByServiceAndDate();
    }

    #[Route(path: '/service/remove', name: 'user_remove_service', methods: ["POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function removeService(): Response
    {
        return $this->userService->removeService();
    }

    #[Route(path: '/service/add', name: 'user_add_service', methods: ["POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function addService(): Response
    {

        return $this->userService->addService();
    }

    #[Route(path: '/document/{user}', name: 'user_document', defaults: ["user" => null], methods: ["GET", "POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function uploadDocument(string $user, Request $request): RedirectResponse
    {
        return $this->userService->uploadUserDocument($user, $request);
    }


    #[Route(path: '/all/surveys', name: 'user_all_surveys', methods: ["GET","POST"])]
    #[Permission(group: 'users', action: "edit")]
    public function allSurveys(): Response
    {

        return $this->userService->allSurveys();
    }
}
