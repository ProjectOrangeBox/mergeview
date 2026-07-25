<?php

declare(strict_types=1);

/**
 * Test fixture plugin. Merge::pluginCallBackHandler() resolves {{ shout ... }}
 * to lex_shout_plugin() by searching the configured plugin search paths.
 */
function lex_shout_plugin(string $name, array $parameters, string $content): string
{
    return 'SHOUT: ' . ($parameters['name'] ?? $content);
}
