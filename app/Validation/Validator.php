<?php

namespace App\Validation;

use Rakit\Validation\Validator as RakitValidator;
use App\Validation\Rules\EmailUnique;
use App\Repositories\UserRepository;

class Validator
{
    protected $validator;
    protected $userRepo;

    public function __construct(UserRepository $userRepo)
    {
        $this->validator = new RakitValidator;
        $this->userRepo  = $userRepo;
    }

    public function validate(array $data, array $rules, array $messages = [])
    {
        // register custom rule
        $this->validator->addValidator(
            'email_unique',
            new EmailUnique($this->userRepo)
        );

        $validation = $this->validator->make($data, $rules);

        if ($messages) {
            $validation->setMessages($messages);
        }

        $validation->validate();

        if ($validation->fails()) {
            $_SESSION['errors'] = $validation->errors()->firstOfAll();
            return false;
        }

        unset($_SESSION['errors']);
        return true;
    }
}
