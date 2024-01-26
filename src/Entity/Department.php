<?php

namespace App\Entity;

use App\Repository\DepartmentRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Table(name: 'departments')]
#[ORM\Entity(repositoryClass: DepartmentRepository::class)]
class Department
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'dept_no')]
    private ?string $id = null;

    #[ORM\Column(length: 40)]
    private ?string $deptName = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $roiUrl = null;

    #[ORM\Column(length: 255)]
    private ?string $address = null;

    #[ORM\OneToMany(mappedBy: 'department', targetEntity: DeptEmp::class)]
    private Collection $deptEmps;

    #[ORM\ManyToMany(targetEntity: Employee::class, mappedBy: 'department')]
    private Collection $employees;

    #[ORM\JoinTable(name: 'dept_manager')]
    #[ORM\JoinColumn(name: 'dept_no', referencedColumnName: 'dept_no', nullable: false)]
    #[ORM\InverseJoinColumn(name: 'emp_no', referencedColumnName: 'emp_no', nullable: false)]
    #[ORM\ManyToMany(targetEntity: Employee::class, inversedBy: 'departments')]
    private Collection $managers;

    // cascade: ["persist"] : Quand on persiste l'objet parent, symfony persistera automatiquement les objets enfants
    #[ORM\OneToMany(mappedBy: 'department', targetEntity: DeptManager::class, orphanRemoval: true, cascade: ["persist"])]
    private Collection $deptManagers;

    #[ORM\JoinTable(name: 'dept_title')]
    #[ORM\JoinColumn(name: 'dept_no', referencedColumnName: 'dept_no', nullable: false)]
    #[ORM\InverseJoinColumn(name: 'title_no', referencedColumnName: 'title_no', nullable: false)]
    #[ORM\ManyToMany(targetEntity: Title::class, inversedBy: 'departments')]
    private Collection $titles;

    public function __construct()
    {
        $this->deptEmps = new ArrayCollection();
        $this->employees = new ArrayCollection();
        $this->managers = new ArrayCollection();
        $this->deptManagers = new ArrayCollection();
        $this->titles = new ArrayCollection();
    }

    public function getId(): ?string
    {
        return $this->id;
    }

    public function getDeptName(): ?string
    {
        return $this->deptName;
    }

    public function setDeptName(string $deptName): static
    {
        $this->deptName = $deptName;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRoiUrl(): ?string
    {
        return $this->roiUrl;
    }

    public function setRoiUrl(?string $roiUrl): static
    {
        $this->roiUrl = $roiUrl;

        return $this;
    }

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    // AFFICHAGE DU NOM DU DÉPARTEMENT
    public function __toString(): string
    {
        return $this->deptName;
    }

    /**
     * @return Collection<int, DeptEmp>
     */
    public function getDeptEmps(): Collection
    {
        return $this->deptEmps;
    }

    public function addDeptEmp(DeptEmp $deptEmp): static
    {
        if (!$this->deptEmps->contains($deptEmp)) {
            $this->deptEmps->add($deptEmp);
            $deptEmp->setDepartment($this);
        }

        return $this;
    }

    public function removeDeptEmp(DeptEmp $deptEmp): static
    {
        if ($this->deptEmps->removeElement($deptEmp)) {
            // set the owning side to null (unless already changed)
            if ($deptEmp->getDepartment() === $this) {
                $deptEmp->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployees(): Collection
    {
        return $this->employees;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employees->contains($employee)) {
            $this->employees->add($employee);
            $employee->addDepartment($this);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        if ($this->employees->removeElement($employee)) {
            // set the owning side to null (unless already changed)
            if ($employee->getDepartment()->contains($this)) {
                $employee->removeDepartment($this);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getManagers(): Collection
    {
        return $this->managers;
    }

    // Méthode modifier de façon à pouvoir accepter également des tableaux
    public function addManager($manager): static
    {
        if (is_array($manager)) {
            foreach ($manager as $singleManager) {
                $this->addManager($singleManager);
            }
        } elseif (!$this->managers->contains($manager)) {
            $this->managers->add($manager);
        }

        return $this;
    }

    public function removeManager(Employee $manager): static
    {
        $this->managers->removeElement($manager);

        return $this;
    }

    /**
     * @return Collection<int, DeptManager>
     */
    public function getDeptManagers(): Collection
    {
        return $this->deptManagers;
    }

    public function addDeptManager(DeptManager $deptManager): static
    {
        if (!$this->deptManagers->contains($deptManager)) {
            $this->deptManagers->add($deptManager);
            $deptManager->setDepartment($this);
        }

        return $this;
    }

    public function removeDeptManager(DeptManager $deptManager): static
    {
        if ($this->deptManagers->removeElement($deptManager)) {
            // set the owning side to null (unless already changed)
            if ($deptManager->getDepartment() === $this) {
                $deptManager->setDepartment(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Title>
     */
    public function getTitles(): Collection
    {
        return $this->titles;
    }

    public function addTitle(Title $title): static
    {
        if (!$this->titles->contains($title)) {
            $this->titles->add($title);
        }

        return $this;
    }

    public function removeTitle(Title $title): static
    {
        $this->titles->removeElement($title);

        return $this;
    }
}
