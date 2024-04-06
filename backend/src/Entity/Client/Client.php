<?php

namespace App\Entity\Client;

use App\Entity\Appointment\Appointment;
use App\Entity\Center\Center;
use App\Entity\Document\Document;
use App\Entity\Service\Service;
use App\Entity\Template\Template;
use App\Entity\User\User;
use App\Entity\User\UserHasClient;
use App\Shared\Classes\UTCDateTime;
use App\Shared\Classes\EntityWithCreatedAndUpdatedDates;
use App\Shared\Utils\Util;
use DateTime;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;

#[ORM\Entity(repositoryClass: ClientRepository::class)]
class Client extends EntityWithCreatedAndUpdatedDates implements PasswordAuthenticatedUserInterface
{

    const ENTITY = 'client';

    #[ORM\Id]
    #[ORM\Column(type: 'string', unique: true, nullable: false)]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(class: 'doctrine.uuid_generator')]
    private string $id;

    // Campos
    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $name;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $socialName;

    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $members;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $girlMembers;
    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $announcement;
    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $speciality;
    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $description;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private string $goals;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $province;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $cif;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $incorporationYear;
    #[ORM\Column]
    private ?string $password;


    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $representative;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $position;
    #[ORM\Column(type:"string", length: 255, nullable: false)]
    private string $phone;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $age;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $gender;
    #[ORM\Column(type:"string", length: 255, unique:true, nullable: false)]
    private string $email;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $representative2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $position2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $phone2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $email2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $age2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $gender2;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $representative3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $position3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $phone3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $email3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $age3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $gender3;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $supportType;
    #[ORM\Column(type:"string", length: 255, nullable: true)]
    private ?string $comment;


    #[ORM\Column(type: 'boolean')]
    private bool $status;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $alumni;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $newCompany;
    #[ORM\Column(type: 'boolean', nullable: true)]
    private ?bool $digitalStartup;
    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $logo;

    #[ORM\OneToMany(mappedBy:"client", targetEntity: ClientHasDocument::class, cascade:["persist", "remove"])]
    private ?Collection $documents;

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_adhesion;

    #[ORM\OneToOne(targetEntity: Document::class)]
    private ?Document $document_confidencial;

    #[ORM\ManyToOne(targetEntity:Center::class)]
    #[ORM\JoinColumn(name:"center_id", referencedColumnName: "id")]
    private Center $center;

    #[ORM\OneToMany(mappedBy:"client", targetEntity: Appointment::class)]
    private Collection $appointments;
    #[ORM\OneToMany(mappedBy:"client", targetEntity: Template::class)]
    private Collection $templates;

    #[ORM\OneToMany(mappedBy:"client", targetEntity: UserHasClient::class, cascade:["persist", "remove"])]
    private Collection $users;

    public function __construct()
    {
        $this->name = '';
        $this->socialName = '';
        $this->members = '';
        $this->description = '';
        $this->password = '';
        $this->representative = '';
        $this->position = '';
        $this->province = '';
        $this->age='';
        $this->gender=null;
        $this->age2=null;
        $this->gender2=null;
        $this->age3=null;
        $this->gender3=null;
        $this->announcement='';
        $this->girlMembers = '';
        $this->cif = '';
        $this->digitalStartup = false;
        $this->comment = '';
        $this->incorporationYear = '';
        $this->newCompany = false;
        $this->supportType = '';
        $this->phone = '';
        $this->email = '';
        $this->speciality = '';
        $this->createdAt = UTCDateTime::setUTC(UTCDateTime::create());
        $this->status =  true;
        $this->alumni =false;
        $this->appointments = new ArrayCollection();
        $this->templates = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->documents = null;
        $this->logo = null;
        $this->document_adhesion = null;
        $this->document_confidencial = null;
        $this->goals = '';
    }

    public function __toString()
    {
        return $this->getId();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getUsers(): Collection
    {
        return $this->users;
    }

    public function setUsers(Collection $users): Client
    {
        $this->users = $users;
        return $this;
    }

    public function getUser(){
        return $this->users->last() ? $this->users->last()->getUser() : null;
    }

    public function getUsersByClient($arrayMode = true):array
    {
        if($arrayMode){
            $arrayUser = [];
            $users = $this->users;
            foreach($users as $user){
                $arrayUser[] = $user->getUser();
            }

            return $arrayUser;
        }
        return $this->users->toArray();
    }

    public function addUser(?User $user){

        $this->users->clear();

        if($user){
            $userHasClient = (new UserHasClient())
                ->setUser($user)
                ->setClient($this);

            if(!$this->users->contains($userHasClient)){
                $this->users->add($userHasClient);
            }
        }

        return $this;
    }



    /**
     * @return Collection
     */
    public function getAppointments(): Collection
    {
        return $this->appointments;
    }

    public function addAppointment(Appointment $appointment): self
    {
        if (!$this->appointments->contains($appointment)) {
            $this->appointments[] = $appointment;
            $appointment->setClient($this);
        }

        return $this;
    }

    public function removeAppointment(Appointment $appointment): self
    {
        if ($this->appointments->removeElement($appointment)) {
            // set the owning side to null (unless already changed)
            if ($appointment->getClient() === $this) {
                $appointment->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|Template[]
     */
    public function getTemplates(): Collection
    {
        return $this->templates;
    }

    public function addTemplate(Template $template): self
    {
        if (!$this->templates->contains($template)) {
            $this->templates[] = $template;
            $template->setClient($this);
        }

        return $this;
    }

    public function removeTemplate(Template $template): self
    {
        if ($this->templates->removeElement($template)) {
            // set the owning side to null (unless already changed)
            if ($template->getClient() === $this) {
                $template->setClient(null);
            }
        }

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getCreatedAt(): ?DateTime
    {
        return $this->createdAt;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @return Center
     */
    public function getCenter(): Center
    {
        return $this->center;
    }

    /**
     * @param Center $center
     */
    public function setCenter(Center $center): void
    {
        $this->center = $center;
    }

    /**
     * @return string
     */
    public function getRepresentative(): string
    {
        return $this->representative;
    }

    /**
     * @param string $representative
     */
    public function setRepresentative(string $representative): void
    {
        $this->representative = $representative;
    }

    public function getSocialName(): ?string
    {
        return $this->socialName;
    }

    public function setSocialName(?string $socialName): void
    {
        $this->socialName = $socialName;
    }

    public function getGirlMembers(): ?string
    {
        return $this->girlMembers;
    }

    public function setGirlMembers(?string $girlMembers): void
    {
        $this->girlMembers = $girlMembers;
    }

    public function getProvince(): ?string
    {
        return $this->province;
    }

    public function setProvince(?string $province): void
    {
        $this->province = $province;
    }

    public function getCif(): ?string
    {
        return $this->cif;
    }

    public function setCif(?string $cif): void
    {
        $this->cif = $cif;
    }

    public function getIncorporationYear(): ?string
    {
        return $this->incorporationYear;
    }

    public function setIncorporationYear(?string $incorporationYear): void
    {
        $this->incorporationYear = $incorporationYear;
    }

    public function getSupportType(): ?string
    {
        return $this->supportType;
    }

    public function setSupportType(?string $supportType): void
    {
        $this->supportType = $supportType;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }

    public function getNewCompany(): ?bool
    {
        return $this->newCompany;
    }

    public function setNewCompany(?bool $newCompany): void
    {
        $this->newCompany = $newCompany;
    }

    public function getDigitalStartup(): ?bool
    {
        return $this->digitalStartup;
    }

    public function setDigitalStartup(?bool $digitalStartup): void
    {
        $this->digitalStartup = $digitalStartup;
    }



    public function getAge(): ?string
    {
        return $this->age;
    }

    public function setAge(?string $age): void
    {
        $this->age = $age;
    }



    public function getGender(): ?string
    {
        return $this->gender;
    }

    public function setGender(?string $gender): void
    {
        $this->gender = $gender;
    }




    /**
     * @return string
     */
    public function getMembers(): string
    {
        return $this->members;
    }

    /**
     * @param string $members
     */
    public function setMembers(string $members): void
    {
        $this->members = $members;
    }



    /**
     * @return string
     */
    public function getAnnouncement(): string
    {
        return $this->announcement;
    }

    /**
     * @param string $announcement
     */
    public function setAnnouncement(string $announcement): void
    {
        $this->announcement = $announcement;
    }

    public function announcementName(){
        $announcementValue = $this->announcement;
        if (is_numeric($this->announcement)) {

            $announcementArray = Util::announcement;
            $announcementValue=$announcementArray[$this->announcement];
        }
        return $announcementValue;
    }



    /**
     * @return string
     */
    public function getSpeciality(): string
    {
        return $this->speciality;
    }

    /**
     * @param string $speciality
     */
    public function setSpeciality(string $speciality): void
    {
        $this->speciality = $speciality;
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->description;
    }

    /**
     * @param string $description
     */
    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    /**
     * @return Document|null
     */
    public function getLogo(): ?Document
    {
        return $this->logo;
    }

    /**
     * @param Document|null $logo
     */
    public function setLogo(?Document $logo): void
    {
        $this->logo = $logo;
    }

    /**
     * @return array
     */
    public function getDocuments(User $user): array
    {
       return $user->getDocuments();
    }

    /**
     * @param Collection|null $documents
     */
    public function setDocuments(?Collection $documents): void
    {
        $this->documents = $documents;
    }

    public function getPassword(): ?string
    {
        return (string) $this->password;
    }

    /**
     * @param string|null $password
     */
    public function setPassword(?string $password): void
    {
        $this->password = $password;
    }

    /**
     * @return string
     */
    public function getPosition(): string
    {
        return $this->position;
    }

    /**
     * @param string $position
     */
    public function setPosition(string $position): void
    {
        $this->position = $position;
    }

    /**
     * @return string
     */
    public function getPhone(): string
    {
        return $this->phone;
    }

    /**
     * @param string $phone
     */
    public function setPhone(string $phone): void
    {
        $this->phone = $phone;
    }

    /**
     * @return string
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * @param string $email
     */
    public function setEmail(string $email): void
    {
        $this->email = $email;
    }

    /**
     * @return string|null
     */
    public function getRepresentative2(): ?string
    {
        return $this->representative2;
    }

    /**
     * @param string|null $representative2
     */
    public function setRepresentative2(?string $representative2): void
    {
        $this->representative2 = $representative2;
    }

    /**
     * @return string|null
     */
    public function getPosition2(): ?string
    {
        return $this->position2;
    }

    /**
     * @param string|null $position2
     */
    public function setPosition2(?string $position2): void
    {
        $this->position2 = $position2;
    }

    /**
     * @return string|null
     */
    public function getPhone2(): ?string
    {
        return $this->phone2;
    }

    /**
     * @param string|null $phone2
     */
    public function setPhone2(?string $phone2): void
    {
        $this->phone2 = $phone2;
    }

    /**
     * @return string|null
     */
    public function getEmail2(): ?string
    {
        return $this->email2;
    }

    /**
     * @param string|null $email2
     */
    public function setEmail2(?string $email2): void
    {
        $this->email2 = $email2;
    }



    /**
     * @return string|null
     */
    public function getRepresentative3(): ?string
    {
        return $this->representative3;
    }

    /**
     * @param string|null $representative3
     */
    public function setRepresentative3(?string $representative3): void
    {
        $this->representative3 = $representative3;
    }

    /**
     * @return string|null
     */
    public function getPosition3(): ?string
    {
        return $this->position3;
    }

    /**
     * @param string|null $position3
     */
    public function setPosition3(?string $position3): void
    {
        $this->position3 = $position3;
    }

    /**
     * @return string|null
     */
    public function getPhone3(): ?string
    {
        return $this->phone3;
    }

    /**
     * @param string|null $phone3
     */
    public function setPhone3(?string $phone3): void
    {
        $this->phone3 = $phone3;
    }

    /**
     * @return string|null
     */
    public function getEmail3(): ?string
    {
        return $this->email3;
    }

    /**
     * @param string|null $email3
     */
    public function setEmail3(?string $email3): void
    {
        $this->email3 = $email3;
    }

    /**
     * @return string|null
     */
    public function getAge2(): ?string
    {
        return $this->age2;
    }

    /**
     * @param string|null $age2
     */
    public function setAge2(?string $age2): void
    {
        $this->age2 = $age2;
    }

    /**
     * @return string|null
     */
    public function getGender2(): ?string
    {
        return $this->gender2;
    }

    /**
     * @param string|null $gender2
     */
    public function setGender2(?string $gender2): void
    {
        $this->gender2 = $gender2;
    }

    /**
     * @return string|null
     */
    public function getAge3(): ?string
    {
        return $this->age3;
    }

    /**
     * @param string|null $age3
     */
    public function setAge3(?string $age3): void
    {
        $this->age3 = $age3;
    }

    /**
     * @return string|null
     */
    public function getGender3(): ?string
    {
        return $this->gender3;
    }

    /**
     * @param string|null $gender3
     */
    public function setGender3(?string $gender3): void
    {
        $this->gender3 = $gender3;
    }

    /**
     * @return bool
     */
    public function isStatus(): bool
    {
        return $this->status;
    }

    /**
     * @param bool $status
     */
    public function setStatus(bool $status): void
    {
        $this->status = $status;
    }

    public function getAlumni(): ?bool
    {
        return $this->alumni;
    }

    public function setAlumni(?bool $alumni): void
    {
        $this->alumni = $alumni;
    }


    public function addDocument(Document $document): self
    {
        if ($this->documents == null){
            $this->documents = new ArrayCollection();
        }
        if (!in_array($document,$this->getDocuments())) {
            $clientHasDocument = (new ClientHasDocument());
            $clientHasDocument->setDocument($document);
            $clientHasDocument->setClient($this);
            if($this->documents != null){
                $this->documents->add($clientHasDocument);
            }

        }

        return $this;
    }

    public function removeService(Service $service): self
    {
        foreach ($this->documents as $clientHasDocument){
            if ($clientHasDocument->getDocument() == $service) {
                $this->documents->removeElement($clientHasDocument);
            }
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getGoals(): string|array
    {
        return explode(',', $this->goals);
    }

    public function getGoalsIdentity(): array{
        $allGoals = [];
        foreach ($this->getGoals() as $goal){
            if ($goal !== null && $goal !== '') {
                $allGoals [] = Util::goals_array[$goal];
            }

        }

        return $allGoals;
    }

    /**
     * @param string $goals
     */
    public function setGoals(array $goals): void
    {
        $this->goals = implode(',', $goals);
    }

    /**
     * @return Document|null
     */
    public function getDocumentAdhesion(): ?Document
    {
        if ($this->getUser()){
            return $this->getUser()->getDocumentAdhesion();
        }
        return null;
    }

    /**
     * @return Document|null
     */
    public function getDocumentConfidencial(): ?Document
    {
        if ($this->getUser()){
            return $this->getUser()->getDocumentConfidencial();
        }
        return null;
    }

}
