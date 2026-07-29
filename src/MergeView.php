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

    /**
     * @param string $viewFile Absolute path to the template - resolving a name
     *        to a path is ViewFinder's job, not a view engine's
     */
    #[\Override]
    public function render(string $viewFile = '', array $data = [], array $options = []): string
    {
        if (!is_file($viewFile)) {
            throw new ViewNotFound($viewFile);
        }

        return $this->merge->parse((string) file_get_contents($viewFile), $this->data($data), $this->pluginHandler);
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
