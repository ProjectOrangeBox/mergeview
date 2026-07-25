<?php

declare(strict_types=1);

namespace orange\mergeview;

use Closure;
use orange\framework\abstract\ViewAbstract;
use orange\mergeview\exceptions\ViewNotFound;
use orange\framework\interfaces\DataInterface;
use orange\framework\interfaces\ViewInterface;

/**
 * Based On the original PyroCMS LEX Parser
 *
 * https://github.com/pyrocms/lex
 *
 * This can be used for some simple "mail merging"
 *
 * Has basic looping && logic
 * It can also handles plugins
 *
 * To get a better idea of what can and can't be done see the basic unit test file
 */

class MergeView extends ViewAbstract implements ViewInterface
{
    protected Merge $merge;
    // a first class callable to Merge::pluginCallBackHandler(), not an array
    protected Closure $pluginHandler;

    protected function __construct(array $config, ?DataInterface $data = null)
    {
        $this->merge = new Merge($config);
        $this->pluginHandler = $this->merge->pluginCallBackHandler(...);

        parent::__construct($config, $data);
    }

    #[\Override]
    public function render(string $view = '', array $data = [], array $options = []): string
    {
        // resolve the view name against the search paths - handing the raw name
        // to file_get_contents() would ignore every path added via addPath()
        $found = $this->search->findFirst($view);

        if ($found === '') {
            throw new ViewNotFound($view);
        }

        return $this->merge->parse((string) file_get_contents($found), $this->data($data), $this->pluginHandler);
    }

    #[\Override]
    public function renderString(string $string, array $data = [], array $options = []): string
    {
        return $this->merge->parse($string, $this->data($data), $this->pluginHandler);
    }

    #[\Override]
    public function change(string $name, mixed $value): self
    {
        $this->merge->change($name, $value);

        return $this;
    }
}
