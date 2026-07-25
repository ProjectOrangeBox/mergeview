<?php

declare(strict_types=1);

use orange\framework\Data;
use orange\mergeview\Merge;
use orange\mergeview\MergeView;
use orange\mergeview\exceptions\Merge as MergeException;

final class MailMergeTest extends unitTestHelper
{
    protected $instance;

    protected $sampleData = [];

    protected function setUp(): void
    {
        $config = [
            'view paths' => [],
            'view aliases' => [],
            'temp directory' => sys_get_temp_dir(),
            'debug' => false,
            'extension' => '.merge',
        ];

        // render()/addPath() live on the view, and both it and Data have
        // protected constructors - newInstance() is the test-facing factory
        $this->instance = MergeView::newInstance($config, Data::newInstance([]));

        $this->instance->search()->addDirectory(__DIR__ . '/support/mergeViews');

        $this->sampleData = array(
            'user'      => [
                'group' => 'user',
            ],
            'title'     => 'Lex is Awesome!',
            'name'      => 'World',
            'real_name' => array(
                'first' => 'Lex',
                'last'  => 'Luther',
            ),
            'title'     => 'Current Projects',
            'projects'  => array(
                array(
                    'name' => 'Acme Site',
                    'assignees' => array(
                        array('name' => 'Dan'),
                        array('name' => 'Phil'),
                    ),
                ),
                array(
                    'name' => 'Lex',
                    'contributors' => array(
                        array('name' => 'Dan'),
                        array('name' => 'Ziggy'),
                        array('name' => 'Jerel')
                    ),
                ),
            ),
        );
    }

    // Tests
    public function testRender(): void
    {
        $this->assertEquals('<h1>Hello World!</h1>', $this->instance->render('test', ['who' => 'World']));
    }

    public function testRenderString(): void
    {
        $this->assertEquals('<h1>Hello World!</h1>', $this->instance->renderString('<h1>Hello {{ who }}!</h1>', ['who' => 'World']));
    }

    /**
     * The full syntax tour, parsed the way MergeView does it - with the plugin
     * handler wired in. parseCallbackTags() hands it every leftover tag, not
     * just namespaced ones, so in the default lenient mode anything unresolved
     * (here {{who}} and the {{assignees}} loop on the second project, which
     * has contributors instead) comes back empty.
     */
    public function testParseTheFullSyntaxTour(): void
    {
        $merge = new Merge(['allow php' => false, 'plugin search paths' => []]);

        $template = file_get_contents(__DIR__ . '/support/mergeViews/basic.merge');
        $match = file_get_contents(__DIR__ . '/support/mergeMatch.merge');

        $this->assertEquals($match, $merge->parse($template, $this->sampleData, $merge->pluginCallBackHandler(...)));
    }

    /**
     * Same template, same missing plugin - but strict mode makes it an error
     * instead of an empty string.
     */
    public function testStrictPluginsThrowsOnMissingPlugin(): void
    {
        $merge = new Merge(['allow php' => false, 'plugin search paths' => [], 'strict plugins' => true]);

        $template = file_get_contents(__DIR__ . '/support/mergeViews/basic.merge');

        $this->expectException(MergeException::class);

        $merge->parse($template, $this->sampleData, $merge->pluginCallBackHandler(...));
    }

    /**
     * Lenient mode only covers the no-such-plugin case - a plugin that exists
     * is still called, and its return value used.
     */
    public function testResolvedPluginStillRuns(): void
    {
        $merge = new Merge(['allow php' => false, 'plugin search paths' => [__DIR__ . '/support/plugins']]);

        $parsed = $merge->parse('{{ shout name="world" }}', [], $merge->pluginCallBackHandler(...));

        $this->assertEquals('SHOUT: world', $parsed);
    }

    /* change() */

    /**
     * change() takes a config key, not the name of the validator that key maps
     * to - the guard used to search $changeable's values, so the one call the
     * class supports was the one call it rejected.
     */
    public function testChangeAcceptsAKnownConfigKey(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->assertInstanceOf(Merge::class, $merge->change('allow php', true));
    }

    public function testChangeRejectsAnUnknownName(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->expectException(MergeException::class);

        $merge->change('allow html', true);
    }

    /**
     * 'is_bool' is a *value* in $changeable, never a key - accepting it would
     * mean the guard is still looking at the wrong half of the array.
     */
    public function testChangeRejectsTheValidatorName(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->expectException(MergeException::class);

        $merge->change('is_bool', true);
    }

    public function testChangeRejectsAValueThatFailsItsValidator(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->expectException(MergeException::class);

        $merge->change('allow php', 'yes please');
    }

    /**
     * The property actually driving behaviour is $allowPhp; a spaced key has to
     * be camelCased on the way in or the assignment lands on a brand new
     * property named 'allow php' that nothing reads.
     */
    public function testChangeWritesTheCamelCasedProperty(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->assertFalse($this->getPrivatePublic('allowPhp', $merge));

        $merge->change('allow php', true);

        $this->assertTrue($this->getPrivatePublic('allowPhp', $merge));
    }

    /**
     * End to end: with php disallowed the tags are escaped, and change() has to
     * be able to switch that off after construction.
     */
    public function testChangeTakesEffectOnParse(): void
    {
        $merge = new Merge(['allow php' => false]);

        $this->assertEquals('&lt;? echo 1; ?&gt;', $merge->parse('<? echo 1; ?>'));

        $merge->change('allow php', true);

        $this->assertEquals('<? echo 1; ?>', $merge->parse('<? echo 1; ?>'));
    }
}
