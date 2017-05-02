<?php

use Cake\Routing\Router;

?>
<div class="event">
    <h1 class="title">
        <?= $event->title; ?>
    </h1>
    <?php
        echo $this->element('events/actions', ['event' => $event, 'can_edit' => false]);
        $this->Js->buffer("setupEventActions('.event');");
    ?>
    <div class="header_details">
        <table class="details">
            <tr>
                <th>When</th>
                <td>
                    <?= $this->Calendar->date($event->date); ?>
                    <br />
                    <?= $this->Calendar->eventTime($event); ?>
                </td>
            </tr>
            <tr>
                <th>Where</th>
                <td>
                    <?= $event->location; ?>
                    <?php if ($event->location_details): ?>
                        <br />
                        <?= $event->location_details; ?>
                    <?php endif; ?>
                    <?php if ($event->address): ?>
                        <br />
                        <?= $event->address; ?>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <th>What</th>
                <td class="what">
                    <?php
                        echo $this->Icon->category($event->category->name).$event->category->name;
                        if (!empty($event->tags)) {
                            echo ': <span class="tags">';
                            $linked_tags = [];
                            foreach ($event->tags as $tag) {
                                $linked_tags[] = $tag['name'];
                                /*
                                $linked_tags[] = $this->Html->link($tag['name'], [
                                    'controller' => 'events',
                                    'action' => 'index',
                                    'tag' => $tag['id'].'_'.Inflector::slug($tag['name'])
                                ));
                                */
                            }
                            echo implode(', ', $linked_tags);
                            echo '</div>';
                        }
                    ?>
                </td>
            </tr>
            <?php if ($event->cost): ?>
                <tr>
                    <th>Cost</th>
                    <td><?= $event->cost; ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event->age_restriction): ?>
                <tr>
                    <th>Ages</th>
                    <td><?= $event->age_restriction; ?></td>
                </tr>
            <?php endif; ?>
            <?php if (!empty($event->images)): ?>
                <tr>
                    <th>Images</th>
                    <td>
                        <?php foreach ($event->images as $image): ?>
                            <?= $this->Calendar->thumbnail('tiny', [
                                'filename' => $image['Image']['filename'],
                                'caption' => $image['caption'],
                                'group' => 'event_view'.$event->id
                            ]); ?>
                        <?php endforeach; ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="description">
        <?= $this->Text->autolink($event->description, [
            'escape' => false
        ]); ?>
    </div>
    <div class="footer">
        <?php
            $url = Router::url([
                'controller' => 'events',
                'action' => 'view',
                'id' => $event->id
            ], true);
            echo $this->Html->link('Go to event page', $url);
        ?>
        <?php if ($event->source): ?>
            <br />
            Source:
            <?= $this->Text->autoLink($event->source); ?>
        <?php endif; ?>
    </div>
</div>
