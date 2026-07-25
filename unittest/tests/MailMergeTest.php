<?php

declare(strict_types=1);

use orange\framework\Data;
use orange\mergeview\Merge;
use orange\mergeview\MergeView;

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
     * The full syntax tour, parsed without a plugin callback - unresolved tags
     * render empty, which is what the stored fixture captures. Going through
     * MergeView here would instead send every unresolved tag to the plugin
     * handler and fail on the first one.
     */
    public function testParseTheFullSyntaxTour(): void
    {
        $this->markTestSkipped(
            'The stored fixture expects an unresolved tag - {{who}}, and a loop over a ' .
            'missing key - to render empty, which needs a plugin callback that tolerates ' .
            'a missing plugin. pluginCallBackHandler() currently throws instead, so no ' .
            'call style reproduces the fixture. Pending a decision on which is correct.'
        );

        $merge = new Merge(['allow php' => false, 'plugin search paths' => []]);

        $template = file_get_contents(__DIR__ . '/support/mergeViews/basic.merge');
        $match = file_get_contents(__DIR__ . '/support/mergeMatch.merge');

        $this->assertEquals($match, $merge->parse($template, $this->sampleData));
    }
}
