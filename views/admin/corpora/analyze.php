<?php echo head(array('title' => 'Text Analysis: Analyze Corpus')); ?>

<form method="post">
<section class="seven columns alpha">
    <div class="field">
        <div class="two columns alpha">
            <label for="corpus_id">Corpus</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select an Ngram corpus to analyze. Note that corpora
            that have not been validated cannot be analyzed.</p>
            <?php echo $this->formSelect('corpus_id', null, array('id' => 'corpus_id'), $corporaOptions); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="features">Features</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Select which features to analyze. Limiting NLU
            features will reduce the item cost.</p>
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
            <p class="explanation">Applies only to the "NLU" features. Calculate
            the estimated item cost of the selected NLU features but do not process
            them.</p>
            <?php echo $this->formCheckbox('item_cost_only', null, array('id' => 'item_cost_only')); ?>
        </div>
    </div>
    <div class="field">
        <div class="two columns alpha">
            <label for="stopwords">Stopwords</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">Applies only to the "Topic Model" feature. In
            addition to common English stopwords, remove these whitespace-separated
            words from the corpus.</p>
            <?php echo $this->formTextarea('stopwords', null, array('id' => 'stopwords', 'rows' => '6')); ?>
        </div>
    </div>
</section>
<section class="three columns omega">
    <div id="save" class="panel">
        <input type="submit" name="submit" id="submit" value="Analyze Corpus" class="submit big green button">
    </div>
</section>
</form>

