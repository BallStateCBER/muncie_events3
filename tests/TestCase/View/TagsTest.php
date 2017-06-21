<?php
namespace App\Test\TestCase\Controller;

use App\Controller\CategoriesController;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestCase;

/**
 * App\Controller\CategoriesController Test Case
 */
class TagsViewTest extends IntegrationTestCase
{
    /**
     * Test that ALL previously used tags are accessible
     *
     * @return void
     */
    public function testTagsIndex()
    {
        $this->get("tags/past");
        $this->assertResponseOk();

        $this->Tags = TableRegistry::get('Tags');
        $tags = $this->Tags->getAllWithCounts(['date <' => date('Y-m-d')]);

        foreach ($tags as $tag) {
            // irritatingly, we're replacing characters with their ascii codes
            $htmlTag = str_replace("&", "&amp;", $tag['name']);
            $htmlTag = str_replace("'", "&#039;", $htmlTag);
            $this->assertResponseContains($htmlTag);
        }
    }
}