<?php
echo head(array('title' => 'Text Analysis: Analyzed Corpora'));
echo flash();
?>
<?php if ($taCorpora): ?>

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
<?php
foreach ($taCorpora as $taCorpus):
$corpus = $taCorpus->getCorpus();
$process = $taCorpus->getProcess();
$analyses = $taCorpus->getAnalyses();
if ($corpus->isSequenced()) {
    $sequenceMemberOptions = array();
    foreach ($analyses as $analysis) {
        $sequenceMemberOptions[] = $analysis->sequence_member;
    }
    $analysisField = 'view ' . $this->formSelect('sequence_member', null, array(), $sequenceMemberOptions);
} else {
    $analysisField = 'view';
}
?>
    <tr>
        <td><?php echo link_to($corpus, 'show', $corpus->name); ?></td>
        <td><?php echo $process->status; ?></td>
        <td>
            <?php if ('completed' === $process->status): ?>
            <?php echo $analysisField; ?>
            <?php endif; ?>
        </td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<?php else: ?>
<h2>No Ngram corpora have been analyzed.</h2>
<p>Get started by analyzing your first corpus.</p>
<a href="<?php echo url(array('action' => 'analyze')); ?>" class="add big green button">Analyze a Corpus</a>

<?php endif; ?>

