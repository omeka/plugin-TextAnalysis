<div id="save" class="panel">
    <h4>Text Analysis</h4>
    <form method="post" action="<?php echo url('text-analysis'); ?>">
        <?php echo $this->formSelect('text_analysis_element', null, null, $elementOptions) ?>
        <?php echo $this->formHidden('text_analysis_item', $item->id) ?>
        <?php echo $this->formSubmit('text_analysis_submit', 'Analyze This Element', array('class' => 'big green button')); ?>
    </form>
</div>
