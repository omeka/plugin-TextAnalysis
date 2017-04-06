<?php
echo head(array('title' => 'Text Analysis: Corpus Analysis'));
echo js_tag('tabs');
?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Tabs.initialize();
});
</script>

<ul id="section-nav" class="navigation tabs">
    <li><a href="#entities">Entities</a></li>
    <li><a href="#keywords">Keywords</a></li>
    <li><a href="#categories">Categories</a></li>
    <li><a href="#concepts">Concepts</a></li>
</ul>

<div id="entities">
    <?php echo $this->partial('text-analysis-entities.php', array('entities' => $this->entities)); ?>
</div>

<div id="keywords">
    <?php echo $this->partial('text-analysis-keywords.php', array('keywords' => $this->keywords)); ?>
</div>

<div id="categories">
    <?php echo $this->partial('text-analysis-categories.php', array('categories' => $this->categories)); ?>
</div>

<div id="concepts">
    <?php echo $this->partial('text-analysis-concepts.php', array('concepts' => $this->concepts)); ?>
</div>
