<?php
return [
    // abilities globales disponibles (referencial)
    'catalog' => [
        'org.members.read',
        'org.members.create',
        'org.members.update',
        'org.members.delete',
    ],

    // asignación por slug de rol
    'by_role' => [
        'admin_org'    => ['org.members.read','org.members.create','org.members.update','org.members.delete'],
        'planificador' => ['org.members.read'],
        'autorizador'  => ['org.members.read'],
        'despachador'  => ['org.members.read'],
        'conductor'    => ['org.members.read'],
        'auditor'      => ['org.members.read'],
    ],

    // estrategia para superadmin
    'superadmin' => ['*'], // o lista completa si prefieres granular
];
