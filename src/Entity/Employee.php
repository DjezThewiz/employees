<?php

namespace App\Entity;

use App\Repository\EmployeeRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

enum Gender: string {
    case Homme = 'M';
    case Femme = 'F';
    case Non_Binaire = 'X';
}

#[ORM\Table(name: 'employees')]
#[ORM\Entity(repositoryClass: EmployeeRepository::class)]
#[UniqueEntity(fields: 'email', message: 'Cette adresse e-mail est déjà utilisée.')]
class Employee implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'NONE')]
    #[ORM\Column(name: 'emp_no')]
    private ?int $id = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $birthDate = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 14)]
    #[ORM\Column(length: 14)]
    private ?string $firstName = null;

    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 16)]
    #[ORM\Column(length: 16)]
    private ?string $lastName = null;

    #[Assert\Choice(choices: ['M', 'F', 'X'])]
    #[ORM\Column(length: 1, type: 'string', enumType: Gender::class)]
    private ?Gender $gender = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $photo = null;

    #[Assert\Email]
    #[ORM\Column(length: 255, unique: true)]
    private ?string $email = null;

    #[ORM\Column(type: Types::DATE_MUTABLE)]
    private ?\DateTimeInterface $hireDate = null;

    #[ORM\Column]
    private ?bool $isVerified = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $contract = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $diploma = null;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Demand::class)]
    private Collection $demands;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: Salary::class)]
    private Collection $salaries;

    #[ORM\OneToMany(mappedBy: 'employee', targetEntity: DeptEmp::class)]
    private Collection $deptEmps;

    #[ORM\JoinTable(name: 'dept_emp')]
    #[ORM\JoinColumn(name: 'emp_no', referencedColumnName: 'emp_no', nullable: false)]
    #[ORM\InverseJoinColumn(name: 'dept_no', referencedColumnName: 'dept_no', nullable: false)]
    #[ORM\ManyToMany(targetEntity: Department::class, inversedBy: 'employees')]
    private Collection $department;

    #[ORM\ManyToMany(targetEntity: Mission::class, mappedBy: 'employees')]
    private Collection $missions;

    #[ORM\ManyToMany(targetEntity: Department::class, mappedBy: 'managers')]
    private Collection $departments;

    #[ORM\OneToMany(mappedBy: 'manager', targetEntity: DeptManager::class, orphanRemoval: true)]
    private Collection $deptManagers;

    #[ORM\ManyToMany(targetEntity: Title::class, mappedBy: 'employees')]
    private Collection $titles;

    public function __construct()
    {
        $this->demands = new ArrayCollection();
        $this->salaries = new ArrayCollection();
        $this->deptEmps = new ArrayCollection();
        $this->department = new ArrayCollection();
        $this->missions = new ArrayCollection();
        $this->departments = new ArrayCollection();
        $this->deptManagers = new ArrayCollection();
        $this->titles = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getBirthDate(): ?\DateTimeInterface
    {
        return $this->birthDate;
    }

    public function setBirthDate(\DateTimeInterface $birthDate): static
    {
        $this->birthDate = $birthDate;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getGender(): ?Gender
    {
        return $this->gender;
    }

    public function setGender(Gender $gender): static
    {
        $this->gender = $gender;

        return $this;
    }

    public function getPhoto(): ?string
    {
        return $this->photo;
    }

    public function setPhoto(?string $photo): static
    {
        $this->photo = $photo;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }

    public function getHireDate(): ?\DateTimeInterface
    {
        return $this->hireDate;
    }

    public function setHireDate(\DateTimeInterface $hireDate): static
    {
        $this->hireDate = $hireDate;

        return $this;
    }

    public function isIsVerified(): ?bool
    {
        return $this->isVerified;
    }

    public function setIsVerified(bool $isVerified): static
    {
        $this->isVerified = $isVerified;

        return $this;
    }

    /**
     * @return Collection<int, Demand>
     */
    public function getDemands(): Collection
    {
        return $this->demands;
    }

    public function addDemand(Demand $demand): static
    {
        if (!$this->demands->contains($demand)) {
            $this->demands->add($demand);
            $demand->setEmployee($this);
        }

        return $this;
    }

    public function removeDemand(Demand $demand): static
    {
        if ($this->demands->removeElement($demand)) {
            // set the owning side to null (unless already changed)
            if ($demand->getEmployee() === $this) {
                $demand->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function __toString(): string
    {
        return "{$this->firstName} {$this->lastName}";
    }

    /**
     * @return Collection<int, Salary>
     */
    public function getSalaries(): Collection
    {
        return $this->salaries;
    }

    public function addSalary(Salary $salary): static
    {
        if (!$this->salaries->contains($salary)) {
            $this->salaries->add($salary);
            $salary->setEmployee($this);
        }

        return $this;
    }

    public function removeSalary(Salary $salary): static
    {
        if ($this->salaries->removeElement($salary)) {
            // set the owning side to null (unless already changed)
            if ($salary->getEmployee() === $this) {
                $salary->setEmployee(null);
            }
        }

        return $this;
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
            $deptEmp->setEmployee($this);
        }

        return $this;
    }

    public function removeDeptEmp(DeptEmp $deptEmp): static
    {
        if ($this->deptEmps->removeElement($deptEmp)) {
            // set the owning side to null (unless already changed)
            if ($deptEmp->getEmployee() === $this) {
                $deptEmp->setEmployee(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartment(): Collection
    {
        return $this->department;
    }

    public function addDepartment(Department $department): static
    {
        if (!$this->department->contains($department)) {
            $this->department->add($department);
        }

        return $this;
    }

    public function removeDepartment(Department $department): static
    {
        $this->department->removeElement($department);

        return $this;
    }

    // LES METHODES CONCERNANT LES DOCUMENTS
    public function getContract(): ?string
    {
        return $this->contract;
    }

    public function setContract(?string $contract): self
    {
        $this->contract = $contract;

        return $this;
    }

    public function getDiploma(): ?string
    {
        return $this->diploma;
    }

    public function setDiploma(?string $diploma): self
    {
        $this->diploma = $diploma;

        return $this;
    }

    /**
     * @return Collection<int, Mission>
     */
    public function getMissions(): Collection
    {
        return $this->missions;
    }

    public function addMission(Mission $mission): static
    {
        if (!$this->missions->contains($mission)) {
            $this->missions->add($mission);
            $mission->addEmployee($this);
        }

        return $this;
    }

    public function removeMission(Mission $mission): static
    {
        if ($this->missions->removeElement($mission)) {
            $mission->removeEmployee($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Department>
     */
    public function getDepartments(): Collection
    {
        return $this->departments;
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
            $deptManager->setManager($this);
        }

        return $this;
    }

    public function removeDeptManager(DeptManager $deptManager): static
    {
        if ($this->deptManagers->removeElement($deptManager)) {
            // set the owning side to null (unless already changed)
            if ($deptManager->getManager() === $this) {
                $deptManager->setManager(null);
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
            $title->addEmployee($this);
        }

        return $this;
    }

    public function removeTitle(Title $title): static
    {
        if ($this->titles->removeElement($title)) {
            $title->removeEmployee($this);
        }

        return $this;
    }
}
