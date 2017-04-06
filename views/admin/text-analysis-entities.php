<h3>Named Entities</h3>
<p>Find people, places, events, and other types of entities mentioned in the text.</p>
<?php if ($this->entities): ?>
<a href="#glossary-entities">Glossary</a>
<table>
    <thead>
        <tr>
            <th>Entity</th>
            <th>Type</th>
            <th>Emotion</th>
            <th>Sentiment</th>
            <th>Count</th>
            <th>Relevance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->entities as $entity): ?>
        <tr>
            <td>
                <?php if (isset($entity['disambiguation'])): ?>
                <a target="_blank" href="<?php echo $entity['disambiguation']['dbpedia_resource']; ?>"><?php echo $entity['text']; ?></a>
                <?php else: ?>
                <?php echo $entity['text']; ?>
                <?php endif;  ?>
            </td>
            <td><?php echo $entity['type']; ?>
            <?php if (isset($entity['disambiguation']['subtype'])): ?>
            <ul>
                <?php foreach ($entity['disambiguation']['subtype'] as $subtype): ?>
                <li><?php echo $subtype; ?></li>
                <?php endforeach; ?>
            </ul>
            <?php endif; ?>
            </td>
            <td>
                <?php if (isset($entity['emotion'])): ?>
                <ul>
                    <li>Sadness: <?php echo $entity['emotion']['sadness']; ?></li>
                    <li>Joy: <?php echo $entity['emotion']['joy']; ?></li>
                    <li>Fear: <?php echo $entity['emotion']['fear']; ?></li>
                    <li>Disgust: <?php echo $entity['emotion']['disgust']; ?></li>
                    <li>Anger: <?php echo $entity['emotion']['anger']; ?></li>
                </ul>
                <?php endif; ?>
            </td>
            <td><?php echo $entity['sentiment']['score']; ?></td>
            <td><?php echo $entity['count']; ?></td>
            <td><?php echo $entity['relevance']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h4 id="glossary-entities">Glossary</h4>
<dl>
    <dt>Entity</dt>
    <dd>Entity text</dd>
    <dt>Type</dt>
    <dd>Entity type</dd>
    <dt>Emotion</dt>
    <dd>Emotion scores ranging from 0 to 1 for sadness, joy, fear, disgust, and anger. A 0 means the text doesn't convey the emotion, and a 1 means the text definitly carries the emotion.</dd>
    <dt>Sentiment</dt>
    <dd>Sentiment score for the concept ranging from -1 to 1. Negative scores indicate negative sentiment, and positive scores indicate positive sentiment.</dd>
    <dt>Count</dt>
    <dd>Number of times the entity is mentioned in the text</dd>
    <dt>Relevance</dt>
    <dd>Relevance score ranging from 0 to 1. A 0 means it's not relevant, and a 1 means it's highly relevant.</dd>
</dl>
<?php else: ?>
<p class="alert">No entities returned.</p>
<?php endif; ?>
<p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Text Analysis by IBM Watson Natural Language Understanding</a></p>
