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
    <li><a href="#taxonomy">Taxonomy</a></li>
    <li><a href="#concepts">Concepts</a></li>
    <li><a href="#keywords">Keywords</a></li>
</ul>

<h2><?php echo metadata($this->item, array('Dublin Core', 'Title')); ?></h2>

<div id="overview">
    <h3>Overview</h3>
    <ul>
        <li><strong>Element</strong>: <?php echo $this->element->name ?> (<?php echo $this->element->getElementSet()->name ?>)</li>
        <li><strong>Words</strong>: <?php echo $this->totalWords; ?></li>
        <li><strong>Unique Words</strong>: <?php echo count($this->words); ?></li>
        <li><strong>Character Count</strong>: <?php echo mb_strlen($this->text); ?></li>
        <li><strong>Text Size</strong>: <?php echo strlen($this->text); ?> bytes</li>
    </ul>
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
    <h3>Named Entities</h3>
    <?php if (isset($this->results['entities']) && $this->results['entities']): ?>
    <table>
        <thead>
            <tr>
                <th>Entity</th>
                <th>Type</th>
                <th>Sentiment</th>
                <th>Count</th>
                <th>Relevance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['entities'] as $entity): ?>
            <tr>
                <td><?php echo $entity['text']; ?></td>
                <td><?php echo $entity['type']; ?></td>
                <td><?php echo $entity['sentiment']['type']; ?></td>
                <td><?php echo $entity['count']; ?></td>
                <td><?php echo $entity['relevance']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <pre><?php print_r($this->results['entities']); ?></pre>
    <?php else: ?>
    <p class="alert">No entities returned.</p>
    <?php endif; ?>
    <p><a href="http://www.alchemyapi.com/">Text Analysis by AlchemyAPI</a></p>
</div>

<div id="taxonomy">
    <h3>Taxonomy</h3>
    <?php if (isset($this->results['taxonomy']) && $this->results['taxonomy']): ?>
    <table>
        <thead>
            <tr>
                <th>Label</th>
                <th>Confident</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['taxonomy'] as $taxonomy): ?>
            <tr>
                <td><?php echo $taxonomy['label']; ?></td>
                <td><?php echo isset($taxonomy['confident']) ? $taxonomy['confident'] : null; ?></td>
                <td><?php echo $taxonomy['score']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <pre><?php print_r($this->results['taxonomy']); ?></pre>
    <?php else: ?>
    <p class="alert">No taxonomy returned.</p>
    <?php endif; ?>
    <p><a href="http://www.alchemyapi.com/">Text Analysis by AlchemyAPI</a></p>
</div>

<div id="concepts">
    <h3>Concepts</h3>
    <?php if (isset($this->results['concepts']) && $this->results['concepts']): ?>
    <table>
        <thead>
            <tr>
                <th>Concept</th>
                <th>Relevance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['concepts'] as $concept): ?>
            <tr>
                <td><?php echo $concept['text']; ?></td>
                <td><?php echo $concept['relevance']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <pre><?php print_r($this->results['concepts']); ?></pre>
    <?php else: ?>
    <p class="alert">No concepts returned.</p>
    <?php endif; ?>
    <p><a href="http://www.alchemyapi.com/">Text Analysis by AlchemyAPI</a></p>
</div>

<div id="keywords">
    <h3>Keywords</h3>
    <?php if (isset($this->results['keywords']) && $this->results['keywords']): ?>
    <table>
        <thead>
            <tr>
                <th>Keyword</th>
                <th>Sentiment</th>
                <th>Relevance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['keywords'] as $keyword): ?>
            <tr>
                <td><?php echo $keyword['text']; ?></td>
                <td><?php echo $keyword['sentiment']['type']; ?></td>
                <td><?php echo $keyword['relevance']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <pre><?php print_r($this->results['keywords']); ?></pre>
    <?php else: ?>
    <p class="alert">No keywords returned.</p>
    <?php endif; ?>
    <p><a href="http://www.alchemyapi.com/">Text Analysis by AlchemyAPI</a></p>
</div>

<?php echo foot(); ?>
