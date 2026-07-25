<?php

declare(strict_types=1);

return [
    'view paths' => [],
    // the package ships no templates of its own, the application supplies them
    'default view paths' => [],
    'view aliases' => [],
    'temp directory' => sys_get_temp_dir(),
    'debug' => DEBUG,
    'extension' => '.merge',
    'allow dynamic views' => false,
    'sub path size' => 6,
    // scope glue separates the parts of a nested variable ie. {{ user.name }}
    'scope glue' => '.',
];
