<?php

namespace WorkflowManager\Services;

class LdapLoginService
{
    protected $ldapHost;
    protected $ldapPort;
    protected $ldapDn;
    protected $ldapBaseDn;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';
        $this->ldapHost = $config['ldap_host'];
        $this->ldapPort = $config['ldap_port'];
        $this->ldapDn   = $config['ldap_dn'];
        $this->ldapBaseDn = $config['ldap_base_dn'];
    }

    public function login(array $credentials)
    {
        $employeeNo = $credentials['employeeNo'] ?? '';
        $password   = $credentials['password'] ?? '';

        if (empty($employeeNo) || empty($password)) {
            return ['success' => false, 'message' => 'Missing credentials'];
        }
        $ldapUri = "ldap://{$this->ldapHost}:{$this->ldapPort}";
        $conn = ldap_connect($ldapUri);

        if (!$conn) {
            return ['success' => false, 'message' => 'Could not connect to LDAP server'];
        }

        ldap_set_option($conn, LDAP_OPT_PROTOCOL_VERSION, 3);
        ldap_set_option($conn, LDAP_OPT_REFERRALS, 0);

        $userDn = str_replace('employeeNo', $employeeNo, $this->ldapDn);
        $searchBaseDn = $this->ldapBaseDn;


        if (@ldap_bind($conn, $userDn, $password)) {
            $filter = "(uid=$employeeNo)";
            $attributes = ['givenName', 'sn', 'mail'];
            $search = ldap_search($conn, $searchBaseDn, $filter, $attributes);
            $entries = ldap_get_entries($conn, $search);

            if ($entries['count'] > 0) {
                $user = [
                    'employeeNo' => $employeeNo,
                    'firstName'  => $entries[0]['givenname'][0] ?? '',
                    'lastName'   => $entries[0]['sn'][0] ?? '',
                    'email'      => $entries[0]['mail'][0] ?? ''
                ];

                ldap_unbind($conn);
                return ['success' => true, 'user' => $user];
            } else {
                ldap_unbind($conn);
                return ['success' => false, 'message' => 'User not found in LDAP directory'];
            }
        } else {
            ldap_unbind($conn);
            return ['success' => false, 'message' => 'Invalid credentials'];
        }
    }

    public function logout()
    {
        // For stateless APIs, "logout" is often handled client-side by clearing tokens or session data.
        // This is a placeholder if you want to expand with session handling later.
        return ['success' => true, 'message' => 'Logged out (noop for stateless authentication)'];
    }
}