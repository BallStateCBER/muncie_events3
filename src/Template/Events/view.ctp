<h1 class="page_title">
    <?= $event->title; ?>
</h1>

<div class="event">
    <?=
        $this->element('events/actions', compact('event'));
        $this->Js->buffer("setupEventActions('.event');");
    ?>

    <div class="header_details">
        <table class="details">
            <tr>
                <th>When</th>
                <td>
                    <?= $this->Calendar->date($event->date); ?>
                    <br />
                    <?= $this->Calendar->time($event); ?>
                </td>
            </tr>
            <tr>
                <th>Where</th>
                <td>
                    <?= $this->Html->link(
                       $event->location, [
                           'controller' => 'events',
                           'action' => 'location',
                           $event->location
                       ]
                    ); ?>
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
                        echo $this->Html->link(
                            $this->Icon->category($event->category->name).$event->category->name,
                            ['controller' => 'events', 'action' => 'category', $event->category->slug],
                            ['escape' => false, 'title' => 'View this category']
                        );
                        if (!empty($event->tag)) {
                            $linked_tags = [];
                            foreach ($event->tag as $tag) {
                                $linked_tags[] = $this->Html->link(
                                    $tag->name,
                                    [
                                        'controller' => 'events',
                                        'action' => 'tag',
                                        'slug' => $tag->id.'_'.Inflector::slug($tag->name)
                                    ],
                                    ['title' => 'View this tag']
                                );
                            }
                            echo '<span> - '.implode(', ', $linked_tags).'</span>';
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
            <?php if ($event->age_restrictions): ?>
                <tr>
                    <th>Ages</th>
                    <td><?= $event->age_restrictions; ?></td>
                </tr>
            <?php endif; ?>
            <?php if ($event->series_id && $event->eventSeries->title): ?>
                <tr>
                    <th>Series</th>
                    <td>
                        <?= $this->Html->link(
                            $event->eventSeries->title,
                            ['controller' => 'event_series', 'action' => 'view', 'id' => $event->series_id]
                        ); ?>
                    </td>
                </tr>
            <?php endif; ?>
        </table>
    </div>
    <div class="description">
        <?php if (!empty($event->eventsImage)): ?>
            <div class="images">
                <?php foreach ($event->eventsImage as $image): ?>
                    <?= $this->Calendar->thumbnail('small', [
                        'filename' => $image->filename,
                        'caption' => $image->caption,
                        'group' => 'event'.$event->id
                    ]); ?>
                    <?php if ($image->caption): ?>
                        <span class="caption">
                            <?= $image->caption; ?>
                        </span>
                    <?php endif; ?>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
        <?= $this->Text->autoLink($event->description, [
            'escape' => false
        ]); ?>
    </div>

    <div class="footer_details">
        <p>
            <?php if (!$event->user->id): ?>
                Added anonymously
            <?php elseif (!$event->user->name): ?>
                Added by a user whose account no longer exists
            <?php else: ?>
                Author: <?= $this->Html->link(
                    $event->user->name,
                    ['controller' => 'users', 'action' => 'view', 'id' => $event->user->id]
                ); ?>
            <?php endif; ?>

            <?php if ($event->source): ?>
                <br />
                Source:
                <?= $this->Text->autoLink($event->source); ?>
            <?php endif; ?>
        </p>
    </div>
</div>
