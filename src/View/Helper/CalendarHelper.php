<?php
namespace App\View\Helper;

use App\Model\Entity\Event;
use App\Model\Table\EventsTable;
use Cake\Core\Configure;
use Cake\I18n\FrozenDate;
use Cake\I18n\FrozenTime;
use Cake\Utility\Text;
use Cake\View\Helper;

/**
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \CakeJs\View\Helper\JsHelper $Js
 */
class CalendarHelper extends Helper
{
    public $helpers = ['Html', 'Js'];

    /**
     * Return filter parameters to be used in 'previous' and 'next' links
     *
     * @param array $filter for params
     * @return array
     */
    private function getFilterUrlParamsPr($filter)
    {
        $filterUrlParams = [];
        if (isset($filter['tag'])) {
            $filterUrlParams['tag'] = $filter['tag']['id'] . '_' . Text::slug($filter['tag']['name']);
        }

        return $filterUrlParams;
    }

    /**
     * Returns an <hgroup> tag for the provided Y-m-d format date string
     *
     * @param string $date to be turned into a header
     * @return string
     */
    public function dayHeaders($date)
    {
        if ($date == date('Y-m-d')) {
            $day = 'Today';
            $thisWeek = true;
        } elseif ($date == date('Y-m-d', strtotime('+1 day'))) {
            $day = 'Tomorrow';
            $thisWeek = true;
        } elseif ($date != date('Y-m-d')) {
            $day = date('l', strtotime($date));
            $thisWeek = ($date > date('Y-m-d') && $date < date('Y-m-d', strtotime('today + 6 days')));
            if ($thisWeek) {
                $day = 'This ' . $day;
            }
        }
        $timestamp = strtotime($date);

        $pattern = 'M j, Y';
        $headerShortDate = '<h2 class="short_date">' . date($pattern, $timestamp) . '</h2>';
        $headerDay = '<h2 class="day">' . $day . '</h2>';
        $headers = $headerShortDate . $headerDay;
        $classes = 'event_accordion';
        if ($thisWeek) {
            $classes .= ' thisWeek';
        }

        return "<hgroup class='$classes'>$headers</hgroup>";
    }

    /**
     * Returns a linked list of tags
     *
     * @param array $event for these tags
     * @return string
     */
    public function eventTags($event)
    {
        $linkedTags = [];
        foreach ($event['tags'] as $tag) {
            $linkedTags[] = $this->Html->link(
                $tag['name'],
                [
                    'controller' => 'events',
                    'action' => 'tag',
                    'slug' => $tag['id'] . '_' . Text::slug($tag['name']),
                    'direction' => 'upcoming'
                ],
                [
                    'escape' => false
                ]
            );
        }

        return implode(', ', $linkedTags);
    }

    /**
     * Returns a formatted version of the date of the provided event
     *
     * @param Event $event Event entity
     * @return string
     */
    public function date($event)
    {
        return $event->date->format('l, F j, Y');
    }

    /**
     * Returns a string describing the start time (and end time, if applicable) of this event
     *
     * @param Event $event Event entity
     * @return string
     */
    public function time($event)
    {
        $start = $event->time_start;
        $end = $event->time_end;
        $retval = $start->format($this->getTimeFormat($start));
        if ($end) {
            $retval .= ' to ' . $end->format($this->getTimeFormat($end));
        }

        return $retval;
    }

    /**
     * Returns a time formatting string that leaves off the ":00" for times at the top of each hour, e.g. 4pm or 4:30pm
     *
     * @param FrozenTime $time Time object
     * @return string
     */
    private function getTimeFormat($time)
    {
        $isOnHour = substr($time->i18nFormat(), -5, 2) == '00';

        return $isOnHour ? 'ga' : 'g:ia';
    }

    /**
     * Returns a linked arrow to the previous day
     *
     * @param int $timestamp Of the previous day
     * @return string
     */
    public function prevDay($timestamp)
    {
        return $this->Html->link(
            '&larr; Previous Day',
            [
                'controller' => 'events',
                'action' => 'day',
                date('m', $timestamp),
                date('d', $timestamp),
                date('Y', $timestamp)
            ],
            ['escape' => false]
        );
    }

    /**
     * Returns a linked arrow to the next day
     *
     * @param int $timestamp Of the next day
     * @return string
     */
    public function nextDay($timestamp)
    {
        return $this->Html->link(
            'Next Day &rarr;',
            [
                'controller' => 'events',
                'action' => 'day',
                date('m', $timestamp),
                date('d', $timestamp),
                date('Y', $timestamp)
            ],
            ['escape' => false]
        );
    }

    /**
     * Returns a linked arrow to the previous month
     *
     * @param int $month of prev month
     * @param int $year of prev month
     * @return string
     */
    public function prevMonth($month, $year)
    {
        $newMonth = $month == 1 ? 12 : $month - 1;
        $newYear = $month == 1 ? $year - 1 : $year;

        return $this->Html->link(
            '&larr; Previous Month',
            [
                'controller' => 'events',
                'action' => 'month',
                $newMonth,
                $newYear
            ],
            ['escape' => false]
        );
    }

    /**
     * Returns a linked arrow to the next month
     *
     * @param int $month of next month
     * @param int $year of next month
     * @return string
     */
    public function nextMonth($month, $year)
    {
        $newMonth = $month == 12 ? 1 : $month + 1;
        $newYear = $month == 12 ? $year + 1 : $year;

        return $this->Html->link(
            'Next Month &rarr;',
            [
                'controller' => 'events',
                'action' => 'month',
                $newMonth,
                $newYear
            ],
            ['escape' => false]
        );
    }

    /**
     * Outputs either a thumbnail (square) image or a small (width-limited) image
     *
     * @param string $type 'small' or 'tiny'
     * @param array $params for the image
     * @return string
     */
    public function thumbnail($type, $params)
    {
        if (!isset($params['filename'])) {
            return '';
        }
        $filename = $params['filename'];
        $reducedPath = Configure::read('App.eventImagePath') . DS . $type . DS . $filename;
        if (!file_exists($reducedPath)) {
            return '';
        }
        $caption = isset($params['caption']) ?: $filename;
        $fullPath = Configure::read('App.eventImagePath') . DS . 'full' . DS . $filename;
        $class = "thumbnail tn_$type";
        if (isset($params['class'])) {
            $class .= ' ' . $params['class'];
        }

        // Reduced image
        $url = Configure::read('App.eventImageBaseUrl') . $type . '/' . $filename;
        $image = '<img src="' . $url . '" class="' . $class . '" alt="' . $caption . '"/>';

        if (!file_exists($fullPath)) {
            return $image;
        }

        // Link to full image
        $rel = "popup";
        if (isset($params['group'])) {
            $rel .= '[' . $params['group'] . ']';
        }
        $url = Configure::read('App.eventImageBaseUrl') . 'full/' . $filename;
        $link = "<a href='$url' rel='$rel' class='$class' alt='$filename'";
        if (isset($params['caption']) && !empty($params['caption'])) {
            $link .= ' title="' . $params['caption'] . '"';
        }
        $link .= ">$image</a>";

        return $link;
    }
}
