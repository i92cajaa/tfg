<?php

namespace App\Entity\Permission;

use App\Repository\PermissionGroupRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\Collection;


#[ORM\Entity(repositoryClass: PermissionGroupRepository::class)]
class PermissionGroup
{

    // ----------------------------------------------------------------
    // Primary Key
    // ----------------------------------------------------------------

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private int $id;

    // ----------------------------------------------------------------
    // Relationships
    // ----------------------------------------------------------------

    #[ORM\OneToMany(mappedBy:"group", targetEntity: Permission::class)]
    private array|Collection $permissions;

    // ----------------------------------------------------------------
    // Fields
    // ----------------------------------------------------------------

    #[ORM\Column(length: 180, nullable: false)]
    private string $name;

    #[ORM\Column(length: 180, nullable: false)]
    private string $label;

    // ----------------------------------------------------------------
    // Magic Methods
    // ----------------------------------------------------------------

    public function __construct()
    {
        $this->permissions = new ArrayCollection();
    }

    // ----------------------------------------------------------------
    // Getter Methods
    // ----------------------------------------------------------------

    /**
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * @return ArrayCollection
     */
    public function getPermissions(): Collection
    {
        return $this->permissions;
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

    // ----------------------------------------------------------------
    // Setter Methods
    // ----------------------------------------------------------------

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
     * @param string $label
     * @return PermissionGroup
     */
    public function setLabel(string $label): PermissionGroup
    {
        $this->label = $label;
        return $this;
    }

    /**
     * @param array|Collection $permissions
     * @return $this
     */
    public function setPermissions(array|Collection $permissions): PermissionGroup
    {
        $this->permissions = $permissions;
        return $this;
    }

    // ----------------------------------------------------------------
    // Other Methods
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO ADD PERMISSION TO GROUP
     * ES: FUNCIÓN PARA AÑADIR PERMISO A GRUPO
     *
     * @param Permission $permission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function addPermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission))
            $this->permissions->add($permission);

        return $this;
    }
    // ----------------------------------------------------------------

    // ----------------------------------------------------------------
    /**
     * EN: FUNCTION TO REMOVE PERMISSION FROM GROUP
     * ES: FUNCIÓN PARA BORRAR PERMISO DE GRUPO
     *
     * @param Permission $permission
     * @return $this
     */
    // ----------------------------------------------------------------
    public function removePermission(Permission $permission): self
    {
        if (!$this->permissions->contains($permission))
            $this->permissions->removeElement($permission);

        return $this;
    }
    // ----------------------------------------------------------------

}
