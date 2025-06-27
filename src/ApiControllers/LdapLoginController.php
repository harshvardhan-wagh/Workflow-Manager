<?php

namespace WorkflowManager\ApiControllers;
use WorkflowManager\Services\LdapLoginService;
use WorkflowManager\Validators\LdapLoginDataValidator;
use Exception;

class LdapLoginController
{
    protected $ldapLoginService;

    public function __construct(LdapLoginService $ldapLoginService)
    {
        $this->ldapLoginService = $ldapLoginService;
    }

    public function login(array $data)
    {
        LdapLoginDataValidator::validate($data);
        $clean = LdapLoginDataValidator::normalize($data);

        return $this->ldapLoginService->login($clean);
    }

    public function logout()
    {
        return $this->ldapLoginService->logout();
    }
}