<?php

/**
 * List of enabled modules for this application.
 *
 * This should be an array of module namespaces used in the application.
 */
return [
    'Laminas\Mvc\Plugin\FilePrg',
    'Laminas\Mvc\Plugin\FlashMessenger',
    'Laminas\Mvc\Plugin\Identity',
    'Laminas\Mvc\Plugin\Prg',
    'Laminas\Session',
    'Laminas\Form',
    'Laminas\I18n',
    'Laminas\InputFilter',
    'Laminas\Filter',
    'Laminas\Hydrator',
    'Laminas\Db',
    'Laminas\Router',
    'Laminas\Validator',
    'Laminas\ZendFrameworkBridge',
    //------------------------------------------------------------------------------
//  MODULOS POR DEFECTO (No Eliminar):
//------------------------------------------------------------------------------
    'Inicio',
    'Layout',
    'Usuarios',
    'Controlacceso',

    //------------------------------------------------------------------------------
    //  MODULOS IMPLEMENTADOS:
//------------------------------------------------------------------------------
    // Aqui deben ir los modulos implementados
    'Dashboard',
    'Indicadores',
    //------------------------------------------------------------------------------
];
