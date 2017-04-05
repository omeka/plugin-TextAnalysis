<div id="save" class="panel">
    <h4>Text Analysis</h4>
    <?php if ($corpus->ItemsCorpus): ?>
    <form method="post" action="<?php echo url('text-analysis/ngram/analyze'); ?>">
        <p>Limit features to analyze:</p>
        <?php foreach ($features as $key => $value): ?>
        <label><?php echo $this->formCheckbox(sprintf('text_analysis_features[%s]', $key), null, array('checked' => true)); ?> <?php echo $value; ?></label>
        <br>
        <?php endforeach; ?>
        <?php echo $this->formHidden('text_analysis_corpus', $corpus->id) ?>
        <?php echo $this->formSubmit('text_analysis_submit', 'Analyze This Corpus', array('class' => 'big green button')); ?>
    </form>
    <?php else: ?>
    <p>Cannot analyze this corpus until it has been validated.</p>
    <?php endif; ?>
</div>
