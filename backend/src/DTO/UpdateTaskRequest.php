<?php

namespace App\DTO;

class UpdateTaskRequest
{
    public ?string $title = null;
    public ?bool $done = null;
    public ?string $priority = null;
}
