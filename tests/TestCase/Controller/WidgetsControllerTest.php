<?php
namespace App\Test\TestCase\Controller;

use App\Test\TestCase\ApplicationTest;

class WidgetsControllerTest extends ApplicationTest
{
    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
    }
    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        parent::tearDown();
    }

    /**
     * Test feed customizer method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testFeedCustomizer()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'feed',
            '?' => [
                'category' => 2,
                'location' => 'placeholdertown',
                'tags_included' => 'holding places',
                'tags_excluded' => ''
            ]
        ]);
        $this->assertResponseOk();
    }

    /**
     * Test month customizer method
     *
     * @return void
     * @throws \PHPUnit\Exception
     */
    public function testMonthCustomizer()
    {
        $this->get([
            'controller' => 'Widgets',
            'action' => 'month',
            '?' => [
                'hideGeneralEventsIcon' => 1,
                'category' => 2,
                'location' => 'placeholdertown',
                'tags_included' => 'holding places'
            ]
        ]);
        $this->assertResponseOk();
    }
}
