<?php
namespace App\Test\TestCase\Model\Table;

use Cake\TestSuite\TestCase;

class EventsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\EventsTable
     */
    public $Events;

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

    public function testGetEventsOnDay()
    {
        $date = date('Y-m-d H:i:s', strtotime('Today 23:59:59'));
        $events = $this->Events->getEventsOnDay(date('Y'), date('m'), date('d'));
        foreach ($events as $event) {
            $this->assertEquals($event->time_start->format('Y-m-d H:i:s'), $date);
        }
    }

    /**
     * Test getUpcomingEvents method
     *
     * @return void
     */
    public function testGetUpcomingEvents()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Anonymous';
        $event->category_id = 1;
        $event->date = date('Y-m-d');
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1;
        $this->EventsTags->save($joinData);

        $events = $this->Events->getUpcomingEvents();
        $this->assertEquals(date('Y-m-d'), $events[0]->date->format('Y-m-d'));
        $this->Events->delete($event);
    }

    /**
     * Test getUpcomingFilteredEvents method
     *
     * @return void
     */
    public function testGetUpcomingFilteredEvents()
    {
        // the only event that should make it out of the filter
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->date = date('Y-m-d', strtotime('2020-01-01'));
        $event->time_start = date('H:i:s', strtotime('2020-01-01 10:29:37'));
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $event->published = 1;
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1;
        $this->EventsTags->save($joinData);

        // wrong category
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 2;
        $event->date = date('Y-m-d', strtotime('2020-01-28'));
        $event->time_start = date('H:i:s', strtotime('2020-01-28 10:29:37'));
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $event->published = 1;
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1;
        $this->EventsTags->save($joinData);

        // wrong location
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->date = date('Y-m-d', strtotime('2020-01-28'));
        $event->time_start = date('H:i:s', strtotime('2020-01-28 10:29:37'));
        $event->location = 'Placeholder parce';
        $event->description = 'Unit testing sure is boring';
        $event->published = 1;
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1;
        $this->EventsTags->save($joinData);

        // un-included tag
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->date = date('Y-m-d', strtotime('2020-01-28'));
        $event->time_start = date('H:i:s', strtotime('2020-01-28 10:29:37'));
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $event->published = 1;
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1012;
        $this->EventsTags->save($joinData);

        // excluded tag
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->date = date('Y-m-d', strtotime('2020-01-28'));
        $event->time_start = date('H:i:s', strtotime('2020-01-28 10:29:37'));
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $event->published = 1;
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = $event->id;
        $joinData->tag_id = 1013;
        $this->EventsTags->save($joinData);

        // the only event that meets these criteria should be on this date, specifically
        $date = date('Y-m-d H:i:s', strtotime('2019-12-31'));
        $options = [
            'category' => 1,
            'location' => 'Placeholder palace',
            'tags_included' => 'holding places',
            'tags_excluded' => 'delete'
        ];
        $nextStartDate = $date;
        $endDate = strtotime('2020-01-28');
        $events = $this->Events->getFilteredEvents($nextStartDate, $endDate, $options);
        $this->assertEquals(date('Y-m-d', strtotime('2020-01-01')), $events[0]->time_start->format('Y-m-d'));
    }

    public function testGetUnapproved()
    {
        $count = $this->Events->getUnapproved();
        $this->assertEquals(3, $count);
    }

    public function testGetNextStartDate()
    {
        $dates = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d'),
            '2020-01-31',
            '2020-02-01'
        ];
        $lastDate = $this->Events->getNextStartDate($dates);
        $this->assertEquals('20200202', $lastDate);
    }

    public function testGetPrevStartDate()
    {
        $dates = [
            date('Y-m-d'),
            date('Y-m-d'),
            date('Y-m-d'),
            '2020-01-31',
            '2020-02-01'
        ];
        $prevDate = $this->Events->getPrevStartDate($dates);
        $this->assertEquals(date('Ymd', strtotime('Yesterday')), $prevDate);
    }

    public function testGetLocations()
    {
        $locations = $this->Events->getLocations();
        $this->assertContains('Placeholder Place', $locations);
    }

    public function testGetPastLocations()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->time_start = date('H:i:s', strtotime('1992-01-28'));
        $event->location = 'Placeholder palace';
        $event->address = '1234 Counting St';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = 6;
        $joinData->tag_id = 1;
        $this->EventsTags->save($joinData);

        $locations = $this->Events->getPastLocations();
        $this->assertContains('1234 Counting St', $locations);
    }

    public function testGetAllUpcomingEventCounts()
    {
        $events = $this->Events->getAllUpcomingEventCounts();
        $artsyEvents = $this->Events->find()
            ->where(['category_id' => 2])
            ->andWhere(['date >=' => date('Y-m-d')])
            ->count();
        $this->assertEquals($artsyEvents, $events[2]);
    }

    public function testGetCountInDirectionWithTag()
    {
        $event = $this->Events->newEntity();
        $event->title = 'Best friend by Yelawolf on repeat';
        $event->category_id = 1;
        $event->time_start = date('H:i:s', strtotime('2019-01-28'));
        $event->location = 'Placeholder palace';
        $event->description = 'Unit testing sure is boring';
        $this->Events->save($event);

        $joinData = $this->EventsTags->newEntity();
        $joinData->event_id = 6;
        $joinData->tag_id = 1012;
        $this->EventsTags->save($joinData);

        $tags = $this->Events->getCountInDirectionWithTag('future', 1012);
        $this->assertEquals($tags, 1);
    }

    public function testGetCountPastWithTag()
    {
        $tags = $this->Events->getCountPastWithTag(1);

        $this->assertEquals($tags, 1);
    }

    public function testGetCountUpcomingWithTag()
    {
        $tags = $this->Events->getCountUpcomingWithTag(1);
        $this->assertEquals($tags, 1);
    }

    public function testGetPastEventIds()
    {
        $myBirthday = date('Y-m-d H:i:s', strtotime('-2 weeks 23:59:59'));
        $event = $this->Events->find()
            ->where(['title' => 'Placeholder Event From Long Ago'])
            ->andWhere(['date' => $myBirthday])
            ->first();
        $eventIds = $this->Events->getPastEventIds();
        $this->assertContains($event->id, $eventIds);
    }

    public function testGetFutureEventIdsAndGetFutureEvents()
    {
        $theFuture = date('Y-m-d', strtotime('tomorrow'));
        $event = $this->Events->find()
            ->where([
                'title' => 'Placeholder Event Regular',
                'date' => $theFuture
            ])
            ->first();
        $eventIds = $this->Events->getFutureEventIds();
        $this->assertContains($event->id, $eventIds);

        $theFuture = [
            0 => date('l', strtotime('Today')),
            1 => date('M', strtotime('Today')),
            2 => date('m', strtotime('Today')),
            3 => date('d', strtotime('Today')),
            4 => date('Y', strtotime('Today'))
        ];
        $events = $this->Events->getFuturePopulatedDates();
        $this->assertContains($theFuture, $events);
    }

    public function testGetIdsFromTag()
    {
        $eventIds = $this->Events->getIdsFromTag(1);
        $event = $this->Events->find()
            ->where(['id IN' => $eventIds])
            ->first();
        $this->assertEquals(1, $event->id);
    }

    public function testGetValidFilters()
    {
        $options = [
            'category' => 1,
            'location' => 'Placeholder palace',
            'tags_included' => 'acoustic music',
            'tags_excluded' => 'adult oriented'
        ];
        $filters = $this->Events->getValidFilters($options);
        $assumedFilters = [
            'location' => 'Placeholder palace',
            'tags_included' => [
                0 => 'acoustic music'
            ],
            'tags_excluded' => [
                0 => 'adult oriented'
            ]
        ];
        $this->assertEquals($filters, $assumedFilters);
    }
}
