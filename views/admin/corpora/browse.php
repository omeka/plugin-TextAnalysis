<?php
echo head(array('title' => 'Text Analysis: Analyzed Corpora'));
echo flash();
?>
<?php if ($total_results): ?>

<?php if ($canAnalyze): ?>
<a href="<?php echo url(array('action' => 'analyze')); ?>" class="small green button">Analyze a Corpus</a>
<?php endif; ?>
<table>
<thead>
    <tr>
        <th>Name</th>
        <th>Process</th>
        <th>NLU Analysis</th>
        <th>MALLET Topic Model</th>
    </tr>
</thead>
<tbody>
<?php foreach (loop('text-analysis-corpus') as $taCorpus): ?>
<?php
$corpus = $taCorpus->getCorpus();
$process = $taCorpus->getProcess();
$analyses = $taCorpus->getAnalyses();
$docTopics = $taCorpus->getDocTopics();

$itemCostField = null;
$analysisField = '[not available]';
$topicModelField = '[not available]';

if ('completed' === $process->status) {
    if ($analyses) {
        if (1 === count($analyses)) {
            $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id));
            $analysisField = sprintf('<a href="%s">%s</a>', $url, 'View');
        } else {
            $options = array('Select to view');
            foreach ($analyses as $analysis) {
                $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $analysis->sequence_member));
                $options[$url] = $taCorpus->getSequenceMemberLabel($analysis->sequence_member);
            }
            $analysisField = $this->formSelect('sequence_member', null, array('class' => 'corpus_sequence_member'), $options);
        }
    }
    if (is_numeric($taCorpus->item_cost)) {
        $itemCostField = sprintf('~%s item cost*', number_format($taCorpus->item_cost));
    }
    if ($docTopics) {
        if (1 === count($docTopics)) {
            $url = url(array('action' => 'topic-model'), null, array('id' => $taCorpus->id));
            $topicModelField = sprintf('<a href="%s">%s</a>', $url, 'View');
        } else {
            $sequenceMembers = array_keys($docTopics);
            $options = array('Select to view');
            foreach ($sequenceMembers as $sequenceMember) {
                $url = url(array('action' => 'topic-model'), null, array('id' => $taCorpus->id, 'sequence_member' => $sequenceMember));
                $options[$url] = $taCorpus->getSequenceMemberLabel($sequenceMember);
            }
            $topicModelField = $this->formSelect('sequence_member', null, array('class' => 'corpus_sequence_member'), $options);
        }
    }
}
?>
    <tr>
        <td>
            <?php echo $corpus ? link_to($corpus, 'show', $corpus->name) : '[not available]'; ?>
            <ul class="action-links">
                <?php if ('completed' === $process->status): ?>
                <li><?php echo link_to($taCorpus, 'delete-confirm', 'Delete', array('class' => 'delete-confirm')); ?></li>
                <?php endif; ?>
            </ul>
        </td>
        <td><?php echo ucwords($process->status); ?></td>
        <td>
            <?php echo $analysisField; ?>
            <?php if ($itemCostField): ?>
            <br><?php echo $itemCostField; ?>
            <?php endif; ?>
        </td>
        <td><?php echo $topicModelField; ?></td>
    </tr>
<?php endforeach; ?>
</tbody>
</table>

<p>* <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Watson Natural Language Understanding</a>
(NLU) incurs a cost per item per feature: one item is one feature with up to 10,000
characters. This service uses up to four features: Entities, Keywords, Categories,
and Concepts.</p>

<script>
jQuery( document ).ready(function() {
    jQuery('.corpus_sequence_member').on('change', function(e) {
        window.location.href = this.value;
    });
});
</script>

<?php else: ?>

<h3>No Ngram corpora have been analyzed.</h3>
<?php if ($canAnalyze): ?>
<p>Get started by analyzing your first corpus.</p>
<a href="<?php echo url(array('action' => 'analyze')); ?>" class="add big green button">Analyze a Corpus</a>
<?php endif; ?>
<?php endif; ?>

<?php echo foot(); ?>
