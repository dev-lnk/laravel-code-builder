<?php

use DevLnk\LaravelCodeBuilder\Enums\BuildType;

return [
    'builders' => [
        // Core
        BuildType::MODEL,
        BuildType::ADD_ACTION,
        BuildType::EDIT_ACTION,
        BuildType::REQUEST,
        BuildType::CONTROLLER,
        BuildType::ROUTE,
        BuildType::FORM,
        // Additionally
    ],

//    'generation_path' => 'Generation',

//    'stub_dir' => base_path('code_stubs'),
];