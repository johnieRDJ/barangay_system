<?php
$currentHost = strtolower(preg_replace('/:\d+$/', '', $_SERVER['HTTP_HOST'] ?? ''));

$config = [
    'mail' => [
        'host' => 'smtp.gmail.com',
        'username' => 'argierydertz@gmail.com',
        'password' => 'xygl mvhd jfpv sjjx',
        'encryption' => 'tls',
        'port' => 587,
        'from_email' => 'argierydertz@gmail.com',
        'from_name' => 'Barangay Digital Complaint System',
    ],
];

$isArgyHost = $currentHost === 'argy.host'
    || $currentHost === 'www.argy.host'
    || substr($currentHost, -10) === '.argy.host';

if($isArgyHost){
    $config['app_url'] = 'https://' . $currentHost;
    $config['database'] = [
        'host' => 'localhost',
        'username' => 'u845277124_brngycomplaint',
        'password' => 'BrngyComplaint_2003',
        'name' => 'u845277124_brngycomplaint',
    ];
}

return $config;
?>
