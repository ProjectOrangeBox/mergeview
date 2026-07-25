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
    // let templates run raw PHP - off unless you trust whoever writes them
    'allow php' => false,
    // directories searched for lex_<name>_plugin.php when a plugin tag is hit
    'plugin search paths' => [],
    /**
     * false: a tag with no matching plugin renders empty, the way every other
     * unresolved construct in the parser degrades.
     * true: it throws instead. Plugin tags and plain variables look identical
     * to the parser, so this is how you catch a typo'd {{ naem }} - worth
     * turning on while writing templates.
     */
    'strict plugins' => false,
];
