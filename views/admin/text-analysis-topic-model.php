<?php
$topicKeys = json_decode($taCorpus->topic_keys, true);
$docTopics = json_decode($taCorpus->doc_topics, true);
?>
<h3>Topic Model</h3>
<?php if ($topicKeys && $docTopics): ?>
<?php
$docTopicsSeq = $docTopics[$sequenceMember ? $sequenceMember : 'instance'];
arsort($docTopicsSeq);
$i = 1;
?>
<?php foreach ($docTopicsSeq as $key => $decimal): ?>
<?php $percent = round($decimal * 100); ?>
<h4><?php printf('Topic #%s (%s%%)', $i++, $percent); ?></h4>
<p style="font-family: monospace;"><?php echo $topicKeys[$key]; ?></p>
<div style="border: 1px solid #9d5b41">
    <div style="height: 10px; background-color: #9d5b41; width: <?php echo $percent; ?>%"></div>
</div>
<?php endforeach; ?>
<?php else: ?>
<p class="alert">No topic model generated.</p>
<?php endif; ?>
<p><a href="http://mallet.cs.umass.edu/index.php" target="_blank">Topic Modeling by MALLET: A Machine Learning for Language Toolkit</a></p>

