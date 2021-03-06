<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Category $category
 */
?>
<h1 class="page_title">
    <?php echo $category->name; ?>
    <?php echo $this->Icon->category($category->name); ?>
</h1>

<?php
    $this->Js->buffer("
        muncieEvents.requestEventFilters.category = '$category->slug';
    ");
    echo $this->element('events/accordion_wrapper');
?>
