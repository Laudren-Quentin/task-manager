<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;

#[ORM\Entity(repositoryClass: UserRepository::class)]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180, unique: true)]
    private ?string $email = null;

    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'band')]
    private Collection $ParticipatingProjects;

    #[ORM\OneToMany(mappedBy: 'creator_id', targetEntity: Task::class)]
    private Collection $tasksCreated;

    #[ORM\OneToMany(mappedBy: 'ueserID', targetEntity: Task::class)]
    private Collection $AssignedTasks;

    public function __construct()
    {
        $this->ParticipatingProjects = new ArrayCollection();
        $this->tasksCreated = new ArrayCollection();
        $this->AssignedTasks = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    

    /**
     * @return Collection<int, Project>
     */
    public function getParticipatingProjects(): Collection
    {
        return $this->ParticipatingProjects;
    }

    public function addParticipatingProject(Project $participatingProject): static
    {
        if (!$this->ParticipatingProjects->contains($participatingProject)) {
            $this->ParticipatingProjects->add($participatingProject);
            $participatingProject->addBand($this);
        }

        return $this;
    }

    public function removeParticipatingProject(Project $participatingProject): static
    {
        if ($this->ParticipatingProjects->removeElement($participatingProject)) {
            $participatingProject->removeBand($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getTasksCreated(): Collection
    {
        return $this->tasksCreated;
    }

    public function addTasksCreated(Task $tasksCreated): static
    {
        if (!$this->tasksCreated->contains($tasksCreated)) {
            $this->tasksCreated->add($tasksCreated);
            $tasksCreated->setCreatorId($this);
        }

        return $this;
    }

    public function removeTasksCreated(Task $tasksCreated): static
    {
        if ($this->tasksCreated->removeElement($tasksCreated)) {
            // set the owning side to null (unless already changed)
            if ($tasksCreated->getCreatorId() === $this) {
                $tasksCreated->setCreatorId(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getAssignedTasks(): Collection
    {
        return $this->AssignedTasks;
    }

    public function addAssignedTask(Task $assignedTask): static
    {
        if (!$this->AssignedTasks->contains($assignedTask)) {
            $this->AssignedTasks->add($assignedTask);
            $assignedTask->setUeserID($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $assignedTask): static
    {
        if ($this->AssignedTasks->removeElement($assignedTask)) {
            // set the owning side to null (unless already changed)
            if ($assignedTask->getUeserID() === $this) {
                $assignedTask->setUeserID(null);
            }
        }

        return $this;
    }
}
