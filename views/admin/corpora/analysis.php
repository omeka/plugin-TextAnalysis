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
    <li><a href="#overview">Overview</a></li>
    <li><a href="#entities">Entities</a></li>
    <li><a href="#keywords">Keywords</a></li>
    <li><a href="#categories">Categories</a></li>
    <li><a href="#concepts">Concepts</a></li>
</ul>

<?php if (isset($analysis['code'])): ?>
<h3>Service Error</h3>
<p class="error">The Natural Language Understanding service returned an error. No
analysis was performed. Below is the error response.</p>
<code><textarea rows="8"><?php echo json_encode($analysis, JSON_PRETTY_PRINT); ?></textarea></code>
<?php endif; ?>

<div id="overview">
    <h3>Overview</h3>
    <table>
        <tbody>
            <tr>
                <th>Corpus</th>
                <td><?php echo link_to($corpus, 'show', $corpus->name); ?></td>
            </tr>
            <tr>
                <th>Previous</th>
                <td><?php echo $prevLink; ?></td>
            </tr>
            <tr>
                <th>Current</th>
                <td><?php echo $currentSequenceMember; ?></td>
            </tr>
            <tr>
                <th>Next</th>
                <td><?php echo $nextLink; ?></td>
            </tr>
        </tbody>
    </table>
</div>

<div id="entities">
    <?php echo $this->partial(
        'text-analysis-entities.php',
        array('entities' => isset($analysis['entities']) ? $analysis['entities'] : null)
    ); ?>
</div>

<div id="keywords">
    <?php echo $this->partial(
        'text-analysis-keywords.php',
        array('keywords' => isset($analysis['keywords']) ? $analysis['keywords'] : null)
    ); ?>
</div>

<div id="categories">
    <?php echo $this->partial(
        'text-analysis-categories.php',
        array('categories' => isset($analysis['categories']) ? $analysis['categories'] : null)
    ); ?>
</div>

<div id="concepts">
    <?php echo $this->partial(
        'text-analysis-concepts.php',
        array('concepts' => isset($analysis['concepts']) ? $analysis['concepts'] : null)
    ); ?>
</div>
