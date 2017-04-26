<h3>Keywords</h3>
<p>Identify the important keywords in the text.</p>
<?php if ($this->keywords): ?>
<a href="#glossary-keywords">Glossary</a>
<table>
    <thead>
        <tr>
            <th>Keyword</th>
            <th>Emotion</th>
            <th>Sentiment</th>
            <th>Relevance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->keywords as $keyword): ?>
        <tr>
            <td><?php echo $keyword['text']; ?></td>
            <td>
                <?php if (isset($keyword['emotion'])): ?>
                <ul>
                    <li>Sadness: <?php echo $keyword['emotion']['sadness']; ?></li>
                    <li>Joy: <?php echo $keyword['emotion']['joy']; ?></li>
                    <li>Fear: <?php echo $keyword['emotion']['fear']; ?></li>
                    <li>Disgust: <?php echo $keyword['emotion']['disgust']; ?></li>
                    <li>Anger: <?php echo $keyword['emotion']['anger']; ?></li>
                </ul>
                <?php endif; ?>
            </td>
            <td><?php echo $keyword['sentiment']['score']; ?></td>
            <td><?php echo $keyword['relevance']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<h4 id="glossary-keywords">Glossary</h4>
<dl>
    <dt>Keyword</dt>
    <dd>Keyword text</dd>
    <dt>Emotion</dt>
    <dd>Emotion scores ranging from 0 to 1 for sadness, joy, fear, disgust, and anger. A 0 means the text doesn't convey the emotion, and a 1 means the text definitly carries the emotion.</dd>
    <dt>Sentiment</dt>
    <dd>Sentiment score for the concept ranging from -1 to 1. Negative scores indicate negative sentiment, and positive scores indicate positive sentiment.</dd>
    <dt>Relevance</dt>
    <dd>Keyword relevance score. A 0 means it's not relevant, and a 1 means it's highly relevant.</dd>
</dl>
<?php else: ?>
<p class="alert">No keywords returned.</p>
<?php endif; ?>
<p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html" target="_blank">Text Analysis by IBM Watson Natural Language Understanding</a></p>
