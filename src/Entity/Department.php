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
    #[ORM\GeneratedValue(strategy: 'NONE')]
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

    #[ORM\JoinTable(name: 'dept_emp')]
    #[ORM\JoinColumn(name: 'dept_no', referencedColumnName: 'dept_no')]
    #[ORM\InverseJoinColumn(name: 'emp_no', referencedColumnName: 'emp_no')]
    #[ORM\ManyToMany(targetEntity: Employee::class, inversedBy: 'departments')]
    private Collection $employee;

    #[ORM\JoinTable(name: 'dept_title')]
    #[ORM\JoinColumn(name: 'dept_no', referencedColumnName: 'dept_no')]
    #[ORM\InverseJoinColumn(name: 'title_no', referencedColumnName: 'title_no')]
    #[ORM\ManyToMany(targetEntity: Title::class, inversedBy: 'departments')]
    private Collection $title;

    #[ORM\ManyToMany(targetEntity: Employee::class, inversedBy: 'managers')]
    private Collection $manager;

    public function __construct()
    {
        $this->employee = new ArrayCollection();
        $this->title = new ArrayCollection();
        $this->manager = new ArrayCollection();
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

    /**
     * @return Collection<int, Employee>
     */
    public function getEmployee(): Collection
    {
        return $this->employee;
    }

    public function addEmployee(Employee $employee): static
    {
        if (!$this->employee->contains($employee)) {
            $this->employee->add($employee);
        }

        return $this;
    }

    public function removeEmployee(Employee $employee): static
    {
        $this->employee->removeElement($employee);

        return $this;
    }

    /**
     * @return Collection<int, Title>
     */
    public function getTitle(): Collection
    {
        return $this->title;
    }

    public function addTitle(Title $title): static
    {
        if (!$this->title->contains($title)) {
            $this->title->add($title);
        }

        return $this;
    }

    public function removeTitle(Title $title): static
    {
        $this->title->removeElement($title);

        return $this;
    }

    /**
     * @return Collection<int, Employee>
     */
    public function getManager(): Collection
    {
        return $this->manager;
    }

    public function addManager(Employee $manager): static
    {
        if (!$this->manager->contains($manager)) {
            $this->manager->add($manager);
        }

        return $this;
    }

    public function removeManager(Employee $manager): static
    {
        $this->manager->removeElement($manager);

        return $this;
    }
}
