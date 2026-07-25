# MergeView

A simple "mail merge" style template parser, based on the original PyroCMS LEX parser. Supports variable substitution, loops, conditionals, and callback-driven plugin tags — without needing a full templating engine. `MergeView` adapts `Merge` to Orange Framework's `ViewInterface`; `Merge` itself is standalone.

## Example

```php
use orange\mergeview\Merge;

$merge = new Merge(['allow php' => false]);

$template = <<<'TXT'
Hello {{name}}!

{{if is_admin}}
You have admin access.
{{else}}
You are a regular user.
{{/if}}

{{products}}
- {{title}}: {{price}}
{{/products}}
TXT;

echo $merge->parse($template, [
    'name' => 'Ada',
    'is_admin' => true,
    'products' => [
        ['title' => 'Widget', 'price' => '$9.99'],
        ['title' => 'Gadget', 'price' => '$19.99'],
    ],
]);
```

Plugin tags (`{{ my_plugin some="value" }}`) are resolved through a callback — pass one as the third argument to `parse()` — which `MergeView` wires up automatically to look for `lex_<name>_plugin` functions on the configured `plugin search paths`.

That callback receives *every* tag left unresolved after variables, loops and conditionals have been parsed — plugin tags and plain variables are shape-identical, so there is no way to tell a `{{ my_plugin }}` call from a misspelled `{{ naem }}`. By default a tag with no matching plugin renders as an empty string, matching upstream Lex and the way every other unresolved construct in the parser degrades. Set `'strict plugins' => true` to have it throw a `MergeException` instead — noisy, but it turns a silently-blank template into an immediate error, which is what you want while writing templates.
