<?php
echo head(array('title' => 'Text Analysis: Analyzed Corpora'));
echo flash();
?>
<?php if ($corpusRows): ?>

<a href="<?php echo url(array('action' => 'analyze')); ?>" class="small green button">Analyze a Corpus</a>

<table>
<thead>
    <tr>
        <th>Name</th>
        <th>Process</th>
        <th>Analysis</th>
    </tr>
</thead>
<tbody>
<?php foreach ($corpusRows as $corpusRow): ?>
    <tr>
        <td><?php echo $corpusRow['name']; ?></td>
        <td><?php echo $corpusRow['process']; ?></td>
        <td><?php echo $corpusRow['analysis']; ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<script>
jQuery( document ).ready(function() {
    jQuery('.corpus_sequence_member').on('change', function(e) {
        window.location.href = this.value;
    });
});
</script>

<?php else: ?>

<h2>No Ngram corpora have been analyzed.</h2>
<p>Get started by analyzing your first corpus.</p>
<a href="<?php echo url(array('action' => 'analyze')); ?>" class="add big green button">Analyze a Corpus</a>

<?php endif; ?>

