<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Event $event
 */

use App\Model\Table\EventsTable;
use Cake\Routing\Router;

    $userId = $this->request->getSession()->read('Auth.User.id');
    $userRole = $this->request->getSession()->read('Auth.User.role') ?: null;
    $canEdit = $userId && ($userRole == 'admin' || $userId == $event['user_id']);

    $eventUrl = Router::url([
        'controller' => 'events',
        'action' => 'view',
        'id' => $event['id']
    ], true);

    $this->Events = new EventsTable();
?>
<div class="actions">
    <!--?= $this->Facebook->likeButton([
        'href' => $eventUrl,
        'show_faces' => false,
        'layout' => 'button_count',
        'app_id' => '496726620385625'
    ]); ?-->
    <div class="export_options_container">
        <?php $alt = 'Export to another calendar application';
            echo $this->Html->link(
            $this->Html->image('/img/icons/calendar--arrow.png') . 'Export',
            "#" . $event['id'] . "_options",
            [
                'escape' => false,
                'title' => $alt,
                'alt' => $alt,
                'id' => 'export_event_'.$event['id'],
                'class' => 'export_options_toggler',
                'aria-expanded' => 'false',
                'aria-controls' => $event['id'] . '_options',
                'data-toggle' => 'collapse',
                'data-target' => '#'.$event['id'] . '_options'
            ]
        ); ?>
        <div class="export_options collapse" id="<?= $event['id'] ?>_options">
            <?php echo $this->Html->link(
                'iCal',
                [
                    'controller' => 'events',
                    'action' => 'ics',
                    $event['id']
                ],
                [
                    'title' => 'Download iCalendar (.ICS) file'
                ]
            ); ?>
            <?php
                $dst = $this->Events->getDaylightSavingOffsetPositive($event->date->format('Y-m-d'));
                $date = strtotime($event->time_start->i18nFormat('yyyyMMddHHmmss') . $dst);

                // Determine UTC "YYYYMMDDTHHMMSS" start/end values
                $start = date('Ymd', $date) . 'T'.date('His', $date) . 'Z';

                $endStamp = $date;
                if ($event->time_end) {
                    $endTime = strtotime($event->time_end->i18nFormat('yyyyMMddHHmmss') . $dst);
                    $endStamp = $endTime;
                }
                $end = date('Ymd', $date) . 'T' . date('His', $endStamp) . 'Z';

                // Clean up and truncate description
                $description = $event['description'];
                $description = strip_tags($description);
                $description = str_replace('&nbsp;', '', $description);
                $description = $this->Text->truncate(
                    $description,
                    1000,
                    [
                        'ellipsis' => "... (continued at $eventUrl)",
                        'exact' => false
                    ]
                );

                /* In parentheses after the location name, the address has
                 * 'Muncie, IN' tacked onto the end if 'Muncie' is not
                 * mentioned in it. */
                $address = trim($event['address']);
                if ($address == '') {
                    $address = 'Muncie, IN';
                } elseif (mb_stripos($address, 'Muncie') === false) {
                    $address .= ', Muncie, IN';
                }
                $location = $event['location'];
                if ($event['location_details']) {
                    $location .= ', '.$event['location_details'];
                }
                $location .= ' (' . $address . ')';

                $google_cal_url = 'http://www.google.com/calendar/event?action=TEMPLATE';
                $google_cal_url .= '&text=' . urlencode($event['title']);
                $google_cal_url .= '&dates=' . $start . '/' . $end;
                $google_cal_url .= '&details=' . urlencode($description);
                $google_cal_url .= '&location=' . urlencode($location);
                $google_cal_url .= '&trp=false';
                $google_cal_url .= '&sprop=Muncie%20Events';
                $google_cal_url .= '&sprop=name:http%3A%2F%2Fmuncieevents.com';

                echo $this->Html->link(
                    'Google',
                    $google_cal_url,
                    ['title' => 'Add to Google Calendar']
                );
            ?>
            <?php echo $this->Html->link(
                'Outlook',
                [
                    'plugin' => false,
                    'prefix' => false,
                    'controller' => 'Events',
                    'action' => 'ics',
                    $event['id']
                ],
                ['title' => 'Add to Microsoft Outlook']
            ); ?>
        </div>
    </div>
    <?php if ($userRole == 'admin' && !$event['approved_by']): ?>
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/tick.png', ['alt' => 'Approve this event']) . 'Approve',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'approve',
                'id' => $event['id']
            ],
            ['escape' => false]
        ); ?>
    <?php endif; ?>
    <?php if ($canEdit): ?>
        <?php echo $this->Html->link(
            $this->Html->image('/img/icons/pencil.png', ['alt' => 'Edit this event']) . 'Edit',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'edit',
                'id' => $event['id']
            ],
            ['escape' => false]
        ); ?>
        <?php echo $this->Form->postLink(
            $this->Html->image('/img/icons/cross.png', ['alt' => 'Delete this event']) . 'Delete',
            [
                'plugin' => false,
                'prefix' => false,
                'controller' => 'Events',
                'action' => 'delete',
                'id' => $event['id']
            ],
            [
                'confirm' => 'Are you sure you want to delete this event?',
                'escape' => false
            ]
        ); ?>
    <?php endif; ?>
</div>
