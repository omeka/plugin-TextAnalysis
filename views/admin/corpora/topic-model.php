<?php
echo head(array('title' => 'Text Analysis: MALLET Topic Model'));
$i = 1;
?>
<?php foreach ($docTopics as $key => $decimal): ?>
<?php $percent = round($decimal * 100); ?>
<h4><?php printf('Topic #%s (%s%%)', $i++, $percent); ?></h4>
<p style="font-family: monospace;"><?php echo $topicKeys[$key]; ?></p>
<div style="border: 1px solid #9d5b41">
    <div style="height: 10px; background-color: #9d5b41; width: <?php echo $percent; ?>%"></div>
</div>
<?php endforeach; ?>
<p><a href="http://mallet.cs.umass.edu/index.php" target="_blank">Topic Modeling by MALLET: A Machine Learning for Language Toolkit</a></p>
<?php echo foot(); ?>
