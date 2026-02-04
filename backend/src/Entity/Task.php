<?php

namespace App\Entity;

use ApiPlatform\Metadata\ApiResource;
use App\Repository\TaskRepository;
use Doctrine\ORM\Mapping as ORM;
use App\Enum\TaskPriority;
use App\Entity\Traits\TimestampableTrait;
use Symfony\Component\Serializer\Annotation\Groups;
use Symfony\Component\Serializer\Annotation\MaxDepth;

#[ORM\Entity(repositoryClass: TaskRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Task
{
    use TimestampableTrait;

    #[ORM\Id, ORM\GeneratedValue, ORM\Column(type: "integer")]
    #[Groups(["task:read", "todo_list:read"])]
    private ?int $id = null;

    #[ORM\Column(type: "string", length: 255)]
    #[Groups(["task:read", "todo_list:read"])]
    private ?string $title = null;

    #[ORM\Column(type: "boolean")]
    #[Groups(["task:read"])]
    private bool $done = false;

    #[ORM\Column(type: "string", enumType: TaskPriority::class)]
    #[Groups(["task:read"])]
    private TaskPriority $priority = TaskPriority::MEDIUM;

    #[ORM\ManyToOne(targetEntity: TodoList::class, inversedBy: "tasks")]
    #[ORM\JoinColumn(nullable: false)]
    #[Groups(["task:read"])]
    private ?TodoList $todoList = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function isDone(): bool
    {
        return $this->done;
    }

    public function setDone(bool $done): self
    {
        $this->done = $done;
        return $this;
    }

    public function getPriority(): TaskPriority
    {
        return $this->priority;
    }

    public function setPriority(TaskPriority $priority): self
    {
        $this->priority = $priority;
        return $this;
    }

    public function getTodoList(): ?TodoList
    {
        return $this->todoList;
    }

    public function setTodoList(?TodoList $todoList): self
    {
        $this->todoList = $todoList;
        return $this;
    }
}
