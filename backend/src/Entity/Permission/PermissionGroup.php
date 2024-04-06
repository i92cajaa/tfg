<?php

namespace App\Entity\Permission;

use App\Repository\PermissionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: PermissionGroupRepository::class)]
class PermissionGroup
{

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // Campos

    #[ORM\Column(length: 180, nullable: false)]
    private string $name;

    #[ORM\Column(length: 180, nullable: false)]
    private string $label;

    // Colecciones

    #[ORM\OneToMany(mappedBy:"group", targetEntity: Permission::class)]
    private Collection $permissions;

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * @param string $label
     * @return PermissionGroup
     */
    public function setLabel(string $label): PermissionGroup
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param string $name
     * @return PermissionGroup
     */
    public function setName(string $name): PermissionGroup
    {
        $this->name = $name;
        return $this;
    }

    /**
     * @return ArrayCollection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * @param Permission $permission
     * @return PermissionGroup
     */
    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission))
            $this->permissions->add($permission);

        return $this;
    }

    /**
     * @param Permission $permission
     * @return PermissionGroup
     */
    public function removePermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission))
            $this->permissions->removeElement($permission);

        return $this;
    }


}
