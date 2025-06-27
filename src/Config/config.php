<?php

return [
    'auth_token' => 'workflow_engine_ssdg', // Change this token
    'env' => 'local',
    'log_path' => __DIR__ . '/../../storage/logs/app.log',

    'ldap_host' => '10.180.0.21',
    'ldap_port' => 389,
    'ldap_dn' => "uid=employeeNo,ou=People,dc=bl,ou=User,dc=cdac,dc=in",
    'ldap_base_dn' => 'dc=bl,ou=User,dc=cdac,dc=in',
];
