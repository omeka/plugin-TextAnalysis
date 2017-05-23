<?php echo head(array('title' => 'Text Analysis: Analyze Corpus')); ?>

<form method="post">
<section class="seven columns alpha">
    <div class="field">
        <div class="two columns alpha">
            <label for="corpus_id">Corpus</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select an Ngram corpus to analyze. Corpora that have not been validated cannot be analyzed.</p>
            <?php echo $this->formSelect('corpus_id', null, array('id' => 'corpus_id'), $corporaOptions); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="features">Features</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Limit the features to analyze. Limiting NLU features will reduce the item cost.</p>
            <?php foreach ($featureOptions as $key => $value): ?>
            <label><?php echo $this->formCheckbox(sprintf('features[%s]', $key), null, array('id' => 'features', 'checked' => false)); ?> <?php echo $value; ?></label>
            <?php endforeach; ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="item_cost_only">Item cost only?</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Calculate the NLU item cost but do not run any NLU analysis.</p>
            <?php echo $this->formCheckbox('item_cost_only', null, array('id' => 'item_cost_only')); ?>
        </div>
    </div>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" name="submit" id="submit" value="Analyze Corpus" class="submit big green button">
    </div>
</section>
</form>

