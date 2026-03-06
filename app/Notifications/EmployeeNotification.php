<?php

namespace App\Notifications;

use App\Models\Employee;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class EmployeeNotification extends Notification
{
    use Queueable;

    protected Employee $employee;
    protected string $action;
    protected ?User $actor;
    protected array $changes;

    public function __construct(Employee $employee, string $action, ?User $actor = null, array $changes = [])
    {
        $this->employee = $employee;
        $this->action = $action;
        $this->actor = $actor;
        $this->changes = $changes;
    }

    public function via($notifiable): array
    {
        return ['database', 'broadcast'];
    }

    public function toDatabase($notifiable): array
    {
        $messages = [
            'created' => 'added a new employee',
            'updated' => 'updated employee information',
            'deleted' => 'removed an employee',
            'joined' => 'has joined the company',
            'left' => 'has left the company',
            'promoted' => 'has been promoted',
        ];

        $message = $this->actor 
            ? "{$this->actor->full_name} {$messages[$this->action]}: {$this->employee->full_name}"
            : "Employee {$messages[$this->action]}: {$this->employee->full_name}";

        return [
            'message' => $message,
            'type' => 'employee',
            'action' => $this->action,
            'employee' => [
                'id' => $this->employee->id,
                'name' => $this->employee->full_name,
                'code' => $this->employee->employee_code,
                'position' => $this->employee->position,
            ],
            'actor' => $this->actor ? [
                'id' => $this->actor->id,
                'name' => $this->actor->full_name,
                'avatar' => $this->actor->profile_photo_url,
            ] : null,
            'changes' => $this->changes,
            'action_url' => route('employees.show', $this->employee),
            'action_text' => 'View Employee',
            'icon' => '👤',
        ];
    }
}