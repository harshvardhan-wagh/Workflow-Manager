<?php

namespace WorkflowManager\Services;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class LdapLoginService
{
    protected $ldapHost;
    protected $ldapPort;
    protected $ldapDn;
    protected $ldapBaseDn;
    protected $jwtSecret;

    public function __construct()
    {
        $config = require __DIR__ . '/../Config/config.php';

        $this->ldapHost    = $config['ldap_host'];
        $this->ldapPort    = $config['ldap_port'];
        $this->ldapDn      = $config['ldap_dn'];
        $this->ldapBaseDn  = $config['ldap_base_dn'];
        $this->jwtSecret   = $config['jwt_secret']; // 🔐 from config
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
            $filter     = "(uid=$employeeNo)";
            $attributes = ['givenName', 'sn', 'mail'];
            $search     = ldap_search($conn, $searchBaseDn, $filter, $attributes);
            $entries    = ldap_get_entries($conn, $search);

            if ($entries['count'] > 0) {
                $user = [
                    'employeeNo' => $employeeNo,
                    'firstName'  => $entries[0]['givenname'][0] ?? '',
                    'lastName'   => $entries[0]['sn'][0] ?? '',
                    'email'      => $entries[0]['mail'][0] ?? '',
                    'role'       => 'admin' // 🔁 or dynamically set
                ];

                // 🔐 JWT Token Generation
                $payload = [
                    'iss'  => 'workflow_manager',
                    'aud'  => 'workflow_client',
                    'iat'  => time(),
                    'exp'  => time() + (60 * 60 * 8), // 8 hours
                    'user' => $user
                ];

                $jwt = JWT::encode($payload, $this->jwtSecret, 'HS256');

                ldap_unbind($conn);

                return [
                    'success' => true,
                    'user'    => $user,
                    'token'   => $jwt
                ];
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
        // Placeholder for stateless API logout
        return ['success' => true, 'message' => 'Logged out (stateless)'];
    }
}
