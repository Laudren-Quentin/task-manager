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

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Project::class)]
    private Collection $CreatedProjects;

    #[ORM\ManyToMany(targetEntity: Project::class, mappedBy: 'team')]
    private Collection $projects;

    #[ORM\OneToMany(mappedBy: 'creator', targetEntity: Task::class)]
    private Collection $CreatedTasks;

    #[ORM\OneToMany(mappedBy: 'assignedUser', targetEntity: Task::class)]
    private Collection $AssignedTasks;

    public function __construct()
    {
        $this->CreatedProjects = new ArrayCollection();
        $this->projects = new ArrayCollection();
        $this->CreatedTasks = new ArrayCollection();
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
    public function getCreatedProjects(): Collection
    {
        return $this->CreatedProjects;
    }

    public function addCreatedProject(Project $createdProject): static
    {
        if (!$this->CreatedProjects->contains($createdProject)) {
            $this->CreatedProjects->add($createdProject);
            $createdProject->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedProject(Project $createdProject): static
    {
        if ($this->CreatedProjects->removeElement($createdProject)) {
            // set the owning side to null (unless already changed)
            if ($createdProject->getCreator() === $this) {
                $createdProject->setCreator(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, Project>
     */
    public function getProjects(): Collection
    {
        return $this->projects;
    }

    public function addProject(Project $project): static
    {
        if (!$this->projects->contains($project)) {
            $this->projects->add($project);
            $project->addTeam($this);
        }

        return $this;
    }

    public function removeProject(Project $project): static
    {
        if ($this->projects->removeElement($project)) {
            $project->removeTeam($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Task>
     */
    public function getCreatedTasks(): Collection
    {
        return $this->CreatedTasks;
    }

    public function addCreatedTask(Task $createdTask): static
    {
        if (!$this->CreatedTasks->contains($createdTask)) {
            $this->CreatedTasks->add($createdTask);
            $createdTask->setCreator($this);
        }

        return $this;
    }

    public function removeCreatedTask(Task $createdTask): static
    {
        if ($this->CreatedTasks->removeElement($createdTask)) {
            // set the owning side to null (unless already changed)
            if ($createdTask->getCreator() === $this) {
                $createdTask->setCreator(null);
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
            $assignedTask->setAssignedUser($this);
        }

        return $this;
    }

    public function removeAssignedTask(Task $assignedTask): static
    {
        if ($this->AssignedTasks->removeElement($assignedTask)) {
            // set the owning side to null (unless already changed)
            if ($assignedTask->getAssignedUser() === $this) {
                $assignedTask->setAssignedUser(null);
            }
        }

        return $this;
    }
}
