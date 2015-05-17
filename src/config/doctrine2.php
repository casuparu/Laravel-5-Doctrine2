<?php

return [
    
    /*
    |--------------------------------------------------------------------------
    | Mapper Type
    |--------------------------------------------------------------------------
    |
    | The Mapper determines how Doctrine2 maps your database.
    | The possible values are:
    | 'annotation' or 'docblock' for PHP DocBlock Annotations
    | 'xml' for XML
    | 'yaml' for YAML
    |
    | Note: 'yaml' requires a YAML reader such as "symfony/yaml"
    |
    */
    'mapper' => 'annotation',

    /*
    |--------------------------------------------------------------------------
    | Mapper Paths
    |--------------------------------------------------------------------------
    |
    | The Mapper Paths tells Doctrine2 where our entities are stored.
    |
    */
    'paths' => [
        app_path('Entities'),
    ],

];
