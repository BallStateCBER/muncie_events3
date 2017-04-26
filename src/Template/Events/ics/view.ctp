<?php
use Cake\Utility\Inflector;
use Eluceo\iCal;

$date = strtotime($event->date->i18nFormat('yyyyMMddHHmmss'));
$startTime = strtotime($event->time_start->i18nFormat('yyyyMMddHHmmss'));
if ($event->time_end) {
    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss'));
}

$start = gmdate('Ymd', $date).'T'.gmdate('His', $startTime).'Z';

$endStamp = $startTime;
if ($event->time_end) {
    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss'));
    $endStamp = $endTime;
}
$end = gmdate('Ymd', $date).'T'.gmdate('His', $endStamp).'Z';

$vCalendar = new \Eluceo\iCal\Component\Calendar('www.muncieevents.com');

$vEvent = new \Eluceo\iCal\Component\Event();
$vEvent
    ->setDtStart(new \Datetime($start))
    ->setDtEnd(new \Datetime($end))
    ->setDescription('Hell yes, this new plugin is great, and everything works, and all I have to do is set everything!')
    ->setUseTimezone('US/Eastern');

$vCalendar
    ->addComponent($vEvent);

echo $vCalendar->render();
