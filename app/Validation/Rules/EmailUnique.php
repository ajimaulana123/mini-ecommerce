<?php

namespace App\Validation\Rules;

use Rakit\Validation\Rule;
use App\Repositories\UserRepository;

class EmailUnique extends Rule
{
    protected $userRepo;

    protected $message = 'Email ini sudah terdaftar.';

    public function __construct(UserRepository $userRepo)
    {
        $this->userRepo = $userRepo;
    }

    public function check($value): bool
    {
        $users = $this->userRepo->where('email', $value);

        return count($users) === 0;
    }

    public function message(string $message): Rule
    {
        $this->message = $message;
        return $this;
    }
}
