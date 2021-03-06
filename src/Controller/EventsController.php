<?php
namespace App\Controller;

use App\Model\Entity\Category;
use App\Model\Entity\Event;
use App\Model\Entity\Image;
use App\Model\Entity\Tag;
use App\Model\Entity\User;
use App\Slack;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\ORM\TableRegistry;

/**
 * Events Controller
 *
 * @property \App\Model\Table\EventsTable $Events
 * @property \Search\Controller\Component\PrgComponent $Prg
 */
class EventsController extends AppController
{
    public $name = 'Events';
    public $helpers = ['Tag', 'Calendar'];
    public $components = [
        'Search.Prg',
        'RequestHandler'
    ];
    public $uses = ['Event'];
    public $eventFilter = [];

    /**
     * Initialization hook method.
     *
     * @return void
     * @throws \Exception
     */
    public function initialize()
    {
        parent::initialize();
        $this->Auth->allow([
            'add',
            'category',
            'datepickerPopulatedDates',
            'day',
            'exportFor14Eleven',
            'getAddress',
            'ics',
            'index',
            'location',
            'month',
            'pastLocations',
            'search',
            'searchAutoComplete',
            'tag',
            'today',
            'tomorrow',
            'view'
        ]);
        $this->loadComponent('Search.Prg', [
            'actions' => ['search']
        ]);
        if ($this->request->getParam('action') === 'add') {
            $this->loadComponent('Recaptcha.Recaptcha');
        }
    }

    /**
     * Determines whether or not the user is authorized to make the current request
     *
     * @param User|null $user User entity
     * @return bool
     */
    public function isAuthorized($user = null)
    {
        if (isset($user)) {
            if ($user['role'] == 'admin') {
                return true;
            }
            $authorPages = [
                'delete',
                'edit',
                'editSeries'
            ];
            $action = $this->request->getParam('action');

            /* If the request isn't for an author-accessible page,
             * then it's for an admin-only page, and this user isn't an admin */
            if (!in_array($action, $authorPages)) {
                return false;
            }

            // Grant access only if this user is the event/series's author
            $entityId = $this->request->getParam('pass')[0];
            $entity = ($action == 'editSeries')
                ? $this->Events->EventSeries->get($entityId)
                : $this->Events->get($entityId);

            $id = php_sapi_name() != 'cli' ? $user['id'] : $this->Auth->user('id');

            return $entity->user_id === $id;
        }

        return false;
    }

    /**
     * setCustomTags method
     *
     * @param Event|\Cake\Datasource\EntityInterface $event Event entity
     * @return void
     */
    private function setCustomTags($event)
    {
        if (!isset($event->customTags)) {
            return;
        }

        $customTags = trim($event->customTags);
        if (empty($customTags)) {
            return;
        }

        $customTags = explode(',', $customTags);

        // Force lowercase, trim whitespace, remove duplicates
        foreach ($customTags as &$customTag) {
            $customTag = strtolower(trim($customTag));
        }
        unset($customTag);
        $customTags = array_unique($customTags);

        $dataFieldName = 'tags._ids.' . count($this->request->getData('tags._ids'));
        foreach ($customTags as $customTag) {
            if ($customTag == '') {
                continue;
            }

            /** @var Tag $existingTag */
            $existingTag = $this->Events->Tags->find()
                ->select(['id', 'selectable'])
                ->where(['name' => $customTag])
                ->first();

            // Include this tag if it exists
            if ($existingTag) {
                // Ignore this custom tag if it matches an unselectable tag
                if (!$existingTag->selectable) {
                    continue;
                }

                $this->request = $this->request->withData($dataFieldName, $existingTag->id);
                $event->tags[] = $this->Events->Tags->get($existingTag->id);
                continue;
            }

            // Create the custom tag if it does not already exist
            $newTag = $this->Events->Tags->newEntity([
                'name' => $customTag,
                'user_id' => $this->Auth->user('id'),
                'parent_id' => 1012, // 'Unlisted' group
                'listed' => $this->Auth->user('role') == 'admin' ? 1 : 0,
                'selectable' => 1
            ]);
            $this->Events->Tags->save($newTag);

            $this->request = $this->request->withData($dataFieldName, $newTag->id);
            $event->tags[] = $newTag;
        }

        $uniqueTagIds = array_unique($this->request->getData('tags._ids'));
        $this->request = $this->request->withData('tags._ids', $uniqueTagIds);
        $this->request = $this->request->withData('customTags', '');
        $event->customTags = '';
    }

    /**
     * Sends the variables $dateFieldValues, $defaultDate, and $preselectedDates to the view
     *
     * @param Event|\Cake\Datasource\EntityInterface $event Event entity
     * @return void
     */
    private function setDatePicker($event)
    {
        // Prepare date picker
        if ($this->request->getParam('action') == 'add') {
            $dateFieldValues = [];
            $preselectedDates = '[]';
            $defaultDate = 0; // Today
        }
        if ($this->request->getParam('action') == 'edit') {
            $today = $event->date->format('Y-m-d');
            $dst = $this->Events->getDaylightSavingOffsetNegative($today);
            $event->date = date('m/d/Y', strtotime($today . $dst));
            $start = date('h:i a', strtotime($event->time_start->format('h:i a') . $dst));
            $event->time_start = $start;
            if ($event->time_end) {
                $end = date('h:i a', strtotime($event->time_end->format('h:i a') . $dst));
                $event->time_end = $end;
            }
        }
        if ($this->request->getParam('action') == 'editSeries') {
            $dateFieldValues = [];
            foreach ($event->time_start as $date) {
                list($year, $month, $day) = explode('-', $date);
                if (!isset($defaultDate)) {
                    $defaultDate = "$month/$day/$year";
                }
                $dateFieldValues[] = date_create("$month/$day/$year");
            }
            $preselectedDates = [];
            $eventSelectedDates = [];
            foreach ($dateFieldValues as $date) {
                $preselectedDates[] = "'" . date_format($date, 'm/d/Y') . "'";
                $eventSelectedDates[] = date_format($date, 'm/d/Y');
            }
            $preselectedDates = implode(',', $preselectedDates);
            $eventSelectedDates = implode(',', $eventSelectedDates);
            $event->date = $eventSelectedDates;
            $preselectedDates = '[' . $preselectedDates . ']';
        }
        $this->set(compact('dateFieldValues', 'defaultDate', 'preselectedDates'));
    }

    /**
     * Sets various variables used in the event form
     *
     * @param Event|\Cake\Datasource\EntityInterface $event Event entity
     * @return void
     */
    private function setEventFormVars($event)
    {
        $this->setDatePicker($event);

        $hasSeries = false;
        $hasEndTime = false;
        if (in_array($this->request->getParam('action'), ['add', 'editSeries'])) {
            $hasSeries = count($event['date']) > 1;
            $hasEndTime = isset($event['time_end']);
        } elseif ($this->request->getParam('action') == 'edit') {
            $hasSeries = isset($event['series_id']);
            $hasEndTime = isset($event['time_end']) && $event['time_end'];
        }

        $userId = $this->Auth->user('id');
        $this->set([
            'has' => [
                'series' => $hasSeries,
                'end_time' => $hasEndTime,
                'address' => isset($event['address']) && $event['address'],
                'cost' => isset($event['cost']) && $event['cost'],
                'ages' => isset($event['age_restriction']) && $event['age_restriction'],
                'source' => isset($event['source']) && $event['source']
            ],
            'categories' => $this->Events->Categories->find('list'),
            'previousLocations' => $this->Events->getPastLocations(),
            'userId' => $userId
        ]);
    }

    /**
     * Creates and/or removes associations between this event and its new/deleted images
     *
     * @param Event|\Cake\Datasource\EntityInterface $event Event entity
     * @return void
     */
    private function saveImageData($event)
    {
        $place = 0;
        $imageData = $this->request->getData('data.Image');
        if ($imageData) {
            foreach ($imageData as $imageId => $caption) {
                /** @var Image $newImage */
                $newImage = $this->Images->get($imageId);
                $this->Events->Images->unlink($event, [$newImage]);
                $delete = $this->request->getData("delete.$imageId");
                if ($delete == '1') {
                    continue;
                }
                if ($delete == 0) {
                    $event->images[$place] = $newImage;
                    $newImage->_joinData = $this->EventsImages->newEntity();
                    $newImage->_joinData->weight = $place + 1;
                    $newImage->_joinData->caption = $caption;
                    $newImage->_joinData->created = $newImage->created;
                    $newImage->_joinData->modified = $newImage->modified;
                    $this->Events->Images->link($event, [$newImage]);
                }
                $place++;
            }
        }
        $event->dirty('images', true);
    }

    /**
     * Returns a boolean indicating whether or not the current user has passed bot detection
     *
     * @return bool
     */
    private function passedBotDetection()
    {
        return php_sapi_name() == 'cli' || $this->Auth->user() || $this->Recaptcha->verify();
    }

    /**
     * Adds a new event
     *
     * @return \Cake\Http\Response|null
     */
    public function add()
    {
        /**  @var Event $event */
        $event = $this->Events->newEntity();
        $event->time_start = new FrozenTime('12:00pm');
        $event->time_end = new FrozenTime('1:00pm');
        $user = $this->Auth->user();
        $autoPublish = $this->Users->getAutoPublish($user['id']);

        // Prepare form
        $this->setEventFormVars($event);
        $this->set([
            '_serialize' => ['event'],
            'autoPublish' => $autoPublish,
            'event' => $event,
            'titleForLayout' => 'Submit an Event',
        ]);

        if (!$this->request->is(['patch', 'post', 'put'])) {
            return null;
        }

        if (!$this->passedBotDetection()) {
            $this->Flash->error('Please log in or check your Recaptcha box.');

            return null;
        }

        $data = $this->request->getData() + [
            'tags' => [],
            'time_end' => null
        ];
        $dates = explode(',', $this->request->getData('date'));
        if (count($dates) < 2) {
            return $this->addSingleEvent($data);
        }

        return $this->addEventSeries($data);
    }

    /**
     * Redirects the user after successfully adding an event
     *
     * @param bool $autoPublish Whether or not the event was automatically published
     * @param int $eventId The event's ID
     *
     * @return \Cake\Http\Response
     */
    private function redirectAfterAdd($autoPublish, $eventId)
    {
        $url = ['controller' => 'Events'];
        if ($autoPublish) {
            $url['action'] = 'view';
            $url[] = $eventId;
        } else {
            $url['action'] = 'index';
        }

        return $this->redirect($url);
    }

    /**
     * Displays events in the specified category
     *
     * @param string $slug Category entity slug
     * @param string|null $nextStartDate param for Events
     * @return void
     */
    public function category($slug, $nextStartDate = null)
    {
        if ($nextStartDate == null) {
            $nextStartDate = date('Y-m-d');
        }
        /** @var Category $category */
        $category = $this->Events->Categories->find('all')
            ->where(['slug' => $slug])
            ->first();

        $options = ['category_id' => $category->id];
        $endDate = strtotime($nextStartDate . ' + 2 weeks');
        $events = $this->Events->getStartEndEvents($nextStartDate, $endDate, $options);
        if ($events) {
            $this->indexEvents($events);
        }
        $this->set([
            'category' => $category,
            'titleForLayout' => $category->name
        ]);
    }

    /**
     * Produces a view with JS used by the datepicker in the header
     *
     * @return void
     */
    public function datepickerPopulatedDates()
    {
        $this->viewbuilder()->setLayout('blank');
        $results = $this->Events->getPopulatedDates();
        $calDates = [];
        foreach ($results as $result) {
            list($year, $month, $day) = explode('-', $result);
            $calDates["$month-$year"][] = $day;
        }
        $this->set(compact('calDates'));
    }

    /**
     * Shows the events taking place on the specified day
     *
     * @param string|null $month param for Events
     * @param string|null $day param for Events
     * @param string|null $year param for Events
     * @return \Cake\Http\Response|null
     */
    public function day($month = null, $day = null, $year = null)
    {
        if (! $year || ! $month || ! $day) {
            return $this->redirect('/');
        }

        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $day = str_pad($day, 2, '0', STR_PAD_LEFT);
        $events = $this->Events->getEventsOnDay($year, $month, $day);
        if ($events) {
            $this->indexEvents($events);
        }
        $timestamp = mktime(0, 0, 0, $month, $day, $year);
        $dateString = date('F j, Y', $timestamp);
        $this->set(compact('month', 'year', 'day'));
        $this->set([
            'titleForLayout' => 'Events on ' . $dateString,
            'displayedDate' => date('l F j, Y', $timestamp)
        ]);

        return null;
    }

    /**
     * Deletes an event
     *
     * @param int|null $id id for series
     * @return \Cake\Http\Response
     */
    public function delete($id = null)
    {
        $event = $this->Events->get($id);
        if ($this->Events->delete($event)) {
            $this->Flash->success(__('The event has been deleted.'));

            return $this->redirect('/');
        }
        $this->Flash->error(__('The event could not be deleted. Please, try again.'));

        return $this->redirect(['action' => 'index']);
    }

    /**
     * Edits an event
     *
     * @param int $id id for series
     * @return \Cake\Http\Response|null
     */
    public function edit($id = null)
    {
        /** @var Event $event */
        $event = $this->Events->get($id, [
            'contain' => ['EventSeries', 'Images', 'Tags']
        ]);

        // Prepare form
        $this->setEventFormVars($event);
        $this->set([
            '_serialize' => ['event'],
            'event' => $event,
            'titleForLayout' => 'Edit Event',
        ]);

        if ($this->request->is(['patch', 'post', 'put'])) {
            $data = $this->request->getData();
            if (!$this->request->getData('tags')) {
                $data['tags'] = [];
            }
            if (!$this->request->getData('has_end_time')) {
                $data['time_end'] = null;
            }

            $event = $this->Events->patchEntity($event, $data);
            $user = $this->Auth->user();
            $event->autoApprove($user);
            $event->autoPublish($user);
            $this->setCustomTags($event);
            $this->date = date('Y-m-d', strtotime($data['date']));
            $this->saveImageData($event);
            $saved = $this->Events->save($event, [
                'associated' => ['EventSeries', 'Images', 'Tags']
            ]);
            if ($saved) {
                $this->Flash->success('Event updated');

                return $this->redirect([
                    'controller' => 'Events',
                    'action' => 'view',
                    $event->id
                ]);
            }
            $msg = 'The event could not be updated. Please correct any indicated errors and try again, or contact an ' .
                'administrator if you need assistance.';
            $this->Flash->error($msg);

            return null;
        }

        return null;
    }

    /**
     * Edits the basic information about an event series
     *
     * @param int $seriesId id for series
     * @return void
     */
    public function editSeries($seriesId)
    {
        $eventSeries = $this->EventSeries->get($seriesId);
        if (!$eventSeries) {
            $msg = 'Sorry, it looks like you were trying to edit an event series that doesn\'t exist anymore.';
            $this->Flash->error($msg);

            return;
        }
        $events = $this->Events->find()
            ->where(['series_id' => $seriesId])
            ->contain(['EventSeries'])
            ->order([
                'date' => 'ASC',
                'time_start' => 'ASC'
            ])
            ->toArray();
        $dates = [];
        $dateString = '';
        foreach ($events as $event) {
            $dateString = $event->date->format('Y-m-d');
            $dates[] = $dateString;
        }
        $eventId = $events[0]->id;
        $event = $this->Events->get($eventId, [
            'contain' => ['EventSeries']
        ]);
        $dst = $this->Events->getDaylightSavingOffsetNegative($dateString);
        $timeStart = date_format($event->time_start, 'H:i:s');
        $event->time_start = date('h:i a', strtotime($timeStart . $dst));
        $event->time_start = $dates;
        $this->setEventFormVars($event);
        $this->Flash->error('Warning: All events in this series will be overwritten.');
        $categories = $this->Categories->find('list');
        $this->set([
            'titleForLayout' => 'Edit Event Series: ' . $eventSeries['title']
        ]);
        $this->set(compact('categories', 'dates', 'event', 'events', 'eventSeries'));
        $this->render('/Element/events/form');
        if ($this->request->is('put') || $this->request->is('post')) {
            $newDates = explode(',', $this->request->getData('date'));
            foreach ($dates as $date) {
                $oldDate = date('m/d/Y', strtotime($date));
                if (!in_array($oldDate, $newDates)) {
                    $deleteEvent = $this->Events->getEventsByDateAndSeries($date, $seriesId);
                    if ($this->Events->delete($deleteEvent)) {
                        $this->Flash->success("Event '$deleteEvent->title' has been deleted.");
                    }
                }
            }
            foreach ($newDates as $date) {
                $oldEvent = $this->Events->getEventsByDateAndSeries($date, $seriesId);
                if (isset($oldEvent->id)) {
                    $event = $this->Events->get($oldEvent->id);
                }
                if (!isset($oldEvent->id)) {
                    $event = $this->Events->newEntity();
                }
                $event->category_id = $this->request->getData('category_id');
                $event->description = $this->request->getData('description');
                $event->location = $this->request->getData('location');
                $optional = ['age_restriction', 'cost', 'source', 'address', 'location_details'];
                foreach ($optional as $option) {
                    if ($this->request->getData($option)) {
                        $event->$option = $this->request->getData($option);
                    }
                }
                $event->series_id = $seriesId;
                $event->title = $this->request->getData('title');
                $this->setCustomTags($event);
                $event['date'] = $date;
                if ($this->Events->save($event, [
                    'associated' => ['EventSeries', 'Images', 'Tags']
                ])) {
                    $this->Flash->success("Event '$event->title' has been saved.");
                    continue;
                }
                if (!$this->Events->save($event)) {
                    $this->Flash->error("The event '$event->title' (#$event->id) could not be saved.");
                }
            }
            $series = $this->EventSeries->get($seriesId);
            $series = $this->EventSeries->patchEntity($series, $this->request->getData());
            $series->title = $this->request->getData('series_title');
            if ($this->EventSeries->save($series)) {
                $this->Flash->success("The event series '$series->title' was saved.");

                return;
            }
            if (!$this->EventSeries->save($series)) {
                $this->Flash->error("The event series '$series->title' was not saved.");

                return;
            }
        }
    }

    /**
     * Returns an address associated with the specified location name
     *
     * @param string $location we need address
     * @return void
     */
    public function getAddress($location = '')
    {
        $this->viewBuilder()->setLayout('blank');
        $this->set('address', $this->Events->getAddress($location));
    }

    /**
     * Shows a page of events
     *
     * @param string|null $nextStartDate next start date for Event entity
     * @return void
     */
    public function index($nextStartDate = null)
    {
        $nextStartDate = $nextStartDate ?? date('Y-m-d');
        $endDate = strtotime($nextStartDate . ' + 2 weeks');
        $events = $this->Events->getStartEndEvents($nextStartDate, $endDate, null);
        $this->indexEvents($events);
    }

    /**
     * Shows events taking place at the specified location, optionally limited to past or upcoming events
     *
     * @param string|null $slug location_slug of Event entity
     * @param string|null $direction of index
     * @return void
     */
    public function location($slug = null, $direction = null)
    {
        $dir = $direction == 'past' ? 'ASC' : 'DESC';
        $comparison = $direction == 'past' ? '<' : '>=';
        $comparisonReverse = $direction == 'past' ? '>=' : '<';
        $opposite = $direction == 'past' ? 'upcoming' : 'past';
        $direction = ucwords($direction);

        $listing = $this->Events
            ->find('all', [
                'conditions' => [
                    'location_slug' => $slug,
                    "date $comparison" => date('Y-m-d'),
                    'Events.published' => 1
                ],
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => [
                    'date' => $dir,
                    'time_start' => $dir
                ]
            ]);
        $listing = $this->paginate($listing)->toArray();
        $location = $this->Events->getLocationFromSlug($slug);
        $this->indexEvents($listing);
        $count = $this->Events
            ->find('all')
            ->where([
                'location_slug' => $slug,
                "Events.date $comparison" => date('Y-m-d')
            ])
            ->count();
        $oppCount = $this->Events
            ->find('all')
            ->where([
                'location_slug' => $slug,
                "Events.date $comparisonReverse" => date('Y-m-d')
            ])
            ->count();
        $this->set(compact('count', 'direction', 'location', 'oppCount', 'opposite'));
        $this->set('multipleDates', true);
        $this->set(['slug' => $slug]);
        $this->set('titleForLayout', '');
    }

    /**
     * Shows all events for the specified month
     *
     * @param string|null $month month of Event
     * @param string|null $year year of Event
     * @return \Cake\Http\Response|null
     */
    public function month($month = null, $year = null)
    {
        if (!$month || !$year) {
            return $this->redirect('/');
        }
        // Zero-pad day and month numbers
        $month = str_pad($month, 2, '0', STR_PAD_LEFT);
        $events = $this->Events
            ->find('all', [
                'conditions' => [
                    'MONTH(date)' => $month,
                    'YEAR(date)' => $year
                ],
                'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags'],
                'order' => [
                    'date' => 'ASC',
                    'time_start' => 'ASC'
                ]
            ])
            ->toArray();
        if ($events) {
            $this->indexEvents($events);
        }
        $timestamp = mktime(0, 0, 0, $month, 01, $year);
        $dateString = date('F, Y', $timestamp);
        $this->set(compact('month', 'year'));
        $this->set([
            'titleForLayout' => 'Events in ' . $dateString,
            'displayedDate' => date('F, Y', $timestamp)
        ]);

        return null;
    }

    /**
     * Shows all of the locations associated with past events
     *
     * @return void
     */
    public function pastLocations()
    {
        $locations = $this->Events->getPastLocationsWithSlugs();
        $alpha = range('a', 'z');
        $locsByFirstLetter = array_fill_keys($alpha, []);
        foreach ($locations as $name => $tag) {
            $firstLetter = ctype_alpha($tag[0]) ? $tag[0] : '#';
            $locsByFirstLetter[$firstLetter][] = [
                $tag => $name
            ];
        }
        $this->set([
            'locsByFirstLetter' => $locsByFirstLetter,
            'titleForLayout' => 'Locations of Past Events',
            'pastLocations' => $locations,
            'listPastLocations' => true
        ]);
    }

    /**
     * Shows events that match a provided search term
     *
     * @return void
     */
    public function search()
    {
        $filter = $this->request->getQuery();
        // Determine the direction (past or upcoming)
        $direction = $filter['direction'];
        $dateQuery = ($direction == 'upcoming') ? 'date >=' : 'date <';
        if ($direction == 'all') {
            $dateQuery = 'date !=';
        };
        $dir = ($direction == 'upcoming') ? 'ASC' : 'DESC';
        $dateWhen = ($direction == 'all') ? '1900-01-01 00:00:00' : date('Y-m-d H:i:s');
        $events = $this->Events->find('search', [
            'search' => $filter,
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ])
            ->where([$dateQuery => $dateWhen])
            ->order([
                'date' => $dir,
                'time_start' => $dir
            ]);
        $events = $this->paginate($events)->toArray();
        if ($events) {
            $this->indexEvents($events);
        }
        if ($direction == 'all') {
            $currentDate = date('Y-m-d');
            $counts = ['upcoming' => 0, 'past' => 0];
            foreach ($events as $date => $dateEvents) {
                if ($date >= $currentDate) {
                    $counts['upcoming']++;
                }
                if ($date < $currentDate) {
                    $counts['past']++;
                }
            }
            $this->set(compact('counts'));
        }
        // Determine if there are events in the opposite direction
        if ($direction == 'past' || $direction = 'upcoming') {
            $whereKey = ($direction == 'upcoming') ? 'start <' : 'start >=';
            $oppositeCount = $this->Events->find('search', ['search' => $filter])
                ->where([$whereKey => date('Y-m-d H:i:s')])
                ->count();
            $this->set('oppositeEvents', $oppositeCount);
        }
        $tags = $this->Events->Tags->find('search', [
            'search' => $filter
        ]);
        $tagCount = null;
        foreach ($tags as $tag) {
            if ($tag->id) {
                $tagCount = true;
            }
        }
        $this->set([
            'titleForLayout' => 'Search Results',
            'direction' => $direction,
            'directionAdjective' => ($direction == 'upcoming') ? 'upcoming' : $direction,
            'filter' => $filter,
            'dateQuery' => $dateQuery,
            'tags' => $tags,
            'tagCount' => $tagCount
        ]);
    }

    /**
     * Provides an auto complete suggestion for a partial search term
     *
     * @return void
     */
    public function searchAutoComplete()
    {
        $stringToComplete = filter_input(INPUT_GET, 'term');
        $limit = 10;
        // The search term will be compared via LIKE to each of these, in order, until $limit tags are found
        $likeConditions = [
            $stringToComplete,
            $stringToComplete . ' %',
            $stringToComplete . '%',
            '% ' . $stringToComplete . '%',
            '%' . $stringToComplete . '%'
        ];
        // Collect tags up to $limit
        $tags = [];
        foreach ($likeConditions as $like) {
            if (count($tags) == $limit) {
                break;
            }
            $newLimit = $limit - count($tags);
            $results = $this->Tags->find()
                ->limit($newLimit)
                ->where(['name LIKE' => $like])
                ->andWhere(['listed' => 1])
                ->andWhere(['selectable' => 1])
                ->select(['id', 'name'])
                ->contain(false)
                ->toArray();
            if (!empty($results)) {
                foreach ($results as $result) {
                    if (!in_array($result->name, $tags)) {
                        $tagId = $result->id;
                        $tags[$tagId] = $result->name;
                    }
                }
            }
        }
        $x = 0;
        foreach ($tags as $tag) {
            $this->set([
                $x => $tag
            ]);
            $x = $x + 1;
        }
        $this->viewBuilder()->setLayout('blank');
    }

    /**
     * sends Slack a notification for events to be moderated
     *
     * @param string $type of event
     * @param int $id of event
     * @return null
     */
    private function sendSlackNotification($type, $id)
    {
        if (php_sapi_name() == 'cli') {
            return null;
        }
        $this->Slack = new Slack();
        $grahamDays = ['Sun', 'Tue', 'Thu', 'Sat'];
        if (in_array(date('D'), $grahamDays)) {
            $admin = 'Graham';
        } else {
            $admin = 'Erica';
        }
        $introMsg = ", a new $type has been posted to Muncie Events. The $type ";
        $event = '';
        if ($type == 'series') {
            $event = $this->EventSeries->get($id);
        } elseif ($type == 'event') {
            $event = $this->Events->get($id);
        }
        if ($event->user_id != null) {
            $user = $this->Users->get($event->user_id);
            $user = 'by ' . $user->name;
        } else {
            $user = 'anonymously';
        }
        $page = $type == 'series' ? '-series' : '';
        $msg = "'$event->title' has been posted $user: https://muncieevents.com/events/edit$page/$event->id";
        $this->Slack->addLine($admin . $introMsg . $msg);
        $this->Slack->send();

        return null;
    }

    /**
     * setLocationSlug method
     *
     * @param string $location to slug
     * @return string
     */
    public function setLocationSlug($location)
    {
        $locationSlug = strtolower($location);
        $locationSlug = substr($locationSlug, 0, 20);
        $locationSlug = str_replace('/', ' ', $locationSlug);
        $locationSlug = preg_replace("/[^A-Za-z0-9 ]/", '', $locationSlug);
        $locationSlug = str_replace("   ", ' ', $locationSlug);
        $locationSlug = str_replace("  ", ' ', $locationSlug);
        $locationSlug = str_replace(' ', '-', $locationSlug);
        if (substr($locationSlug, -1) == '-') {
            $locationSlug = substr($locationSlug, 0, -1);
        }

        return $locationSlug;
    }

    /**
     * Shows the events with a specified tag
     *
     * @param string|null $slug tag slug
     * @param string|null $direction of results
     * @return null|\Cake\Http\Response
     */
    public function tag($slug = '', $direction = null)
    {
        $str = filter_input(INPUT_SERVER, 'QUERY_STRING', FILTER_SANITIZE_STRING);
        $pos = strpos($str, 'slug');

        if ($pos !== false) {
            $slugUrl = explode('=', $str);
            $slu = $slugUrl[1];

            return ($this->redirect("/events/tag/$slu"));
        }

        $dir = $direction == 'past' ? 'ASC' : 'DESC';
        $comparison = $direction == 'past' ? '<' : '>=';
        $comparisonReverse = $direction == 'past' ? '>=' : '<';
        $opposite = $direction == 'past' ? 'upcoming' : 'past';
        $direction = ucwords($direction);

        $tagId = $this->Tags->getIdFromSlug($slug);

        /** @var Tag $tag */
        $tag = $this->Events->Tags->find('all', [
            'conditions' => ['id' => $tagId],
            'fields' => ['id', 'name'],
            'contain' => false
        ])->first();
        if (empty($tag)) {
            $this->Flash->error("Sorry, but we couldn't find that tag ($slug)");

            return null;
        }
        $eventId = $this->Events->getIdsFromTag($tagId);
        $listing = $this->Events->find()
            ->where([
                'Events.id IN' => $eventId,
                "Events.time_start $comparison" => date('Y-m-d'),
                'Events.published' => 1
            ])
            ->contain(['Users', 'Categories', 'EventSeries', 'Images', 'Tags'])
            ->order([
                'date' => $dir,
                'time_start' => $dir
            ]);
        $listing = $this->paginate($listing)->toArray();

        $this->indexEvents($listing);
        $count = $this->Events->find()
            ->where([
                'Events.id IN' => $eventId,
                "Events.date $comparison" => date('Y-m-d')
            ])
            ->count();
        $oppCount = $this->Events->find()
            ->where([
                'Events.id IN' => $eventId,
                "Events.date $comparisonReverse" => date('Y-m-d')
            ])
            ->count();
        $this->set(compact('count', 'direction', 'eventId', 'oppCount', 'opposite', 'slug', 'tag'));
        $this->set([
            'titleForLayout' => 'Tag: ' . ucwords($tag->name)
        ]);

        return null;
    }

    /**
     * Shows the events taking place today
     *
     * @return \Cake\Http\Response
     */
    public function today()
    {
        return $this->redirect('/events/day/' . date('m') . '/' . date('d') . '/' . date('Y'));
    }

    /**
     * Shows the events taking place tomorrow
     *
     * @return \Cake\Http\Response
     */
    public function tomorrow()
    {
        $tomorrow = date('m-d-Y', strtotime('+1 day'));
        $tomorrow = explode('-', $tomorrow);

        return $this->redirect('/events/day/' . $tomorrow[0] . '/' . $tomorrow[1] . '/' . $tomorrow[2]);
    }

    /**
     * Shows a specific event
     *
     * @param string|null $id Event id.
     * @return void
     * @throws \Cake\Datasource\Exception\RecordNotFoundException When record not found.
     */
    public function view($id = null)
    {
        $event = $this->Events->get($id, [
            'contain' => ['Users', 'Categories', 'EventSeries', 'Images', 'Tags']
        ]);
        $this->set('event', $event);
        $this->set('_serialize', ['event']);
        $this->set('titleForLayout', $event['title']);
    }

    /**
     * Processes request data and adds a single event (not connected to a series)
     *
     * @param array $data Request data
     * @return \Cake\Http\Response|null
     */
    private function addSingleEvent(array $data)
    {
        $user = $this->Auth->user();
        $data['user_id'] = $user['id'];
        $dates = explode(',', $this->request->getData('date'));
        $data['date'] = new FrozenDate($dates[0]);
        $event = $this->Events->newEntity($data);
        $event->autoApprove($user);
        $event->autoPublish($user);
        $this->setCustomTags($event);
        $saved = (bool)$this->Events->save($event, [
            'associated' => ['Images', 'Tags']
        ]);
        if (!$saved) {
            $msg = 'The event could not be submitted. Please correct any errors and try again. If you need ' .
                'assistance, please contact an administrator.';
            $this->Flash->error($msg);

            return null;
        }

        // Notify
        $autoPublish = $this->Users->getAutoPublish($user['id']);
        $msg = 'Your event has been ' . ($autoPublish ? 'posted' : 'submitted for publishing');
        $this->Flash->success($msg);
        $this->sendSlackNotification('event', $event->id);

        return $this->redirectAfterAdd($autoPublish, $event->id);
    }

    private function addEventSeries($data)
    {
        // Create empty series
        $seriesTable = TableRegistry::getTableLocator()->get('EventSeries');
        $user = $user = $this->Auth->user();
        $series = $seriesTable->newEntity([
            'title' => $data['title'],
            'user_id' => $user['id'],
            'published' => (new Event())->userIsAutoPublishable($this->Auth->user())
        ]);
        $seriesErrorMsg = 'There was an error submitting your event series. Please correct any error ' .
            'messages indicated below and contact an administrator if you need assistance.';
        if ($series->hasErrors()) {
            $this->Flash->error($seriesErrorMsg);

            return null;
        }

        // Create events
        $firstEventId = null;
        $events = [];
        $dates = explode(',', $this->request->getData('date'));
        foreach ($dates as $date) {
            $data['date'] = new FrozenDate($date);
            $event = $this->Events->newEntity($data);
            $event->autoApprove($user);
            $event->autoPublish($user);
            $this->setCustomTags($event);
            if ($event->hasErrors()) {
                $seriesErrorMsg .= ' Details: ' . print_r($event->getErrors(), true);
                $this->Flash->error($seriesErrorMsg);

                return null;
            }
            $events[] = $event;
        }

        // Save series
        $this->Events->EventSeries->save($series);

        // Save events
        foreach ($events as $event) {
            /** @var Event $event */
            $event = $this->Events->patchEntity($event, [
                'series_id' => $series->id
            ]);
            $this->Events->save($event, [
                'associated' => ['EventSeries', 'Images', 'Tags']
            ]);
            if (!$firstEventId) {
                $firstEventId = $event->id;
            }
            $this->saveImageData($event);
        }

        // Notify
        $autoPublish = $this->Users->getAutoPublish($user['id']);
        $msg = 'Your event series has been ' . ($autoPublish ? 'posted' : 'submitted for publishing');
        $this->Flash->success($msg);
        $this->sendSlackNotification('series', $series->id);

        return $this->redirectAfterAdd($autoPublish, $firstEventId);
    }
}
