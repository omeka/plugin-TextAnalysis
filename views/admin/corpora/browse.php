<?php
echo head(array('title' => 'Text Analysis: Analyzed Corpora'));
echo flash();
?>
<?php if ($total_results): ?>

<a href="<?php echo url(array('action' => 'analyze')); ?>" class="small green button">Analyze a Corpus</a>

<table>
<thead>
    <tr>
        <th>Name</th>
        <th>Process</th>
        <th>NLU Item Cost</th>
        <th>Analysis</th>
    </tr>
</thead>
<tbody>
<?php foreach (loop('text-analysis-corpus') as $taCorpus): ?>
<?php
$corpus = $taCorpus->getCorpus();
$process = $taCorpus->getProcess();
$analyses = $taCorpus->getAnalyses();

$itemCostField = '[not available]';
$analysisField = '[not available]';

if ($analyses && 'completed' === $process->status) {
    if ($corpus->isSequenced()) {
        $options = array('Select to view');
        foreach ($analyses as $analysis) {
            $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $analysis->sequence_member));
            $options[$url] = $taCorpus->getSequenceMemberLabel($analysis->sequence_member);
        }
        $analysisField = $this->formSelect('sequence_member', null, array('class' => 'corpus_sequence_member'), $options);

    } else {
        $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id));
        $analysisField = sprintf('<a href="%s">%s</a>', $url, 'View');
    }
}
if (is_numeric($taCorpus->item_cost)) {
    $itemCostField = '~' . number_format($taCorpus->item_cost);
}

?>
    <tr>
        <td>
            <?php echo link_to($corpus, 'show', $corpus->name); ?>
            <ul class="action-links">
                <?php if ('completed' === $process->status): ?>
                <li><?php echo link_to($taCorpus, 'delete-confirm', 'Delete', array('class' => 'delete-confirm')); ?></li>
                <?php endif; ?>
            </ul>
        </td>
        <td><?php echo ucwords($process->status); ?></td>
        <td><?php echo $itemCostField; ?></td>
        <td><?php echo $analysisField; ?></td>
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

<?php echo foot(); ?>
