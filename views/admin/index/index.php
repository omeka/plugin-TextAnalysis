<?php
echo head(array('title' => 'Text Analysis'));
echo js_tag('tabs');
?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Tabs.initialize();
});
</script>

<ul id="section-nav" class="navigation tabs">
    <li><a href="#overview">Overview</a></li>
    <li><a href="#frequencies">Frequencies</a></li>
    <li><a href="#entities">Entities</a></li>
    <li><a href="#keywords">Keywords</a></li>
    <li><a href="#categories">Categories</a></li>
    <li><a href="#concepts">Concepts</a></li>
</ul>

<div id="overview">
    <h3>Overview</h3>
    <table>
        <tbody>
            <tr>
                <th>Item</th>
                <td><?php echo link_to_item(null, array(), 'show', $this->item); ?></td>
            </tr>
            <tr>
                <th>Element</th>
                <td><?php echo $this->element->name ?> (<?php echo $this->element->getElementSet()->name ?>)</td>
            </tr>
            <tr>
                <th>Words</th>
                <td><?php echo number_format($this->totalWords); ?></td>
            </tr>
            <tr>
                <th>Unique Words</th>
                <td><?php echo number_format($this->uniqueWords); ?></td>
            </tr>
            <tr>
                <th>Character Count</th>
                <td><?php echo number_format($this->characterCount); ?></td>
            </tr>
            <tr>
                <th>Text Size</th>
                <td><?php echo number_format($this->textBytes); ?> bytes (<?php echo number_format($this->textKilobytes, 2) ?> kilobytes)</td>
            </tr>
            <tr>
                <th>NLU Item Cost*</th>
                <td>~<?php echo number_format($this->itemCostEstimate); ?> items</td>
            </tr>
        </tbody>
    </table>
    <p>* <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Watson Natural Language Understanding</a>
    (NLU) incurs a cost per item per feature: one item is one feature with up to
    10,000 characters. This service uses up to four features: Entities, Keywords,
    Categories, and Concepts.</p>
    <h3>Text</h3>
    <div><?php echo nl2br($this->text); ?></div>
</div>

<div id="frequencies">
    <h3>Word Frequencies</h3>
    <table>
        <thead>
            <tr>
                <th>Word</th>
                <th>Count</th>
                <th>Relative Frequency</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->words as $word => $count): ?>
            <tr>
                <td><?php echo $word; ?></td>
                <td><?php echo $count; ?></td>
                <td><?php echo ($count / $this->totalWords) * 100; ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

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

<?php echo foot(); ?>
