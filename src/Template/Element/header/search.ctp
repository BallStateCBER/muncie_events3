<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div>
    <?php
        $formTemplate = [
            'inputContainer' => '{{content}}',
            'submitContainer' => '{{content}}'
        ];
        $this->Form->setTemplates($formTemplate);
    ?>
    <?= $this->Form->create('Event', [
        'id' => 'EventSearchForm',
        'url' => array_merge(['controller' => 'events', 'action' => 'search'], $this->request->getParam('pass'))
    ]) ?>
    <img src="/img/loading_small_dark.gif" id="search_autocomplete_loading" alt="Loading..." />
    <label class="sr-only" for="EventFilter">
        Search events
    </label>
    <?= $this->Form->Control('filter', [
        'label' => false,
        'class' => 'form-control',
        'id' => 'EventFilter'
    ]) ?>
    <div class="input-group-btn">
        <div class="btn-group">
            <?= $this->Form->submit('Search', [
                'class' => 'btn btn-default btn-sm'
            ]) ?>
            <button id="search_options_toggler" class="dropdown-toggle btn btn-secondary btn-sm" type="button" data-toggle="collapse" aria-haspopup="true" aria-expanded="false" data-target="#search_options">
                <span class="caret"></span>
                <span class="sr-only">Search options</span>
            </button>
            <div id="search_options" class="collapse" aria-labelledby="search_options_toggler">
                <div>
                    <label class="sr-only" for="direction">
                        Direction of events
                    </label>
                    <?= $this->Form->control('direction', [
                        'options' => [
                            'upcoming' => 'Upcoming Events',
                            'past' => 'Past Events',
                            'all' => 'All Events'
                        ],
                        'default' => 'upcoming',
                        'type' => 'radio',
                        'label' => false,
                        'legend' => false,
                        'separator' => '<br />'
                    ]) ?>
                </div>
            </div>
        </div>
    </div>

    <?= $this->Form->end() ?>
    <?php $this->Js->buffer("setupSearch();"); ?>
</div>
