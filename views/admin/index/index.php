<?php
echo head(array('title' => 'Text Analysis'));
echo js_tag('tabs');
?>
<script type="text/javascript" charset="utf-8">
jQuery(window).load(function () {
    Omeka.Tabs.initialize();
});
</script>

<ul id="section-nav" class="navigation tabs">
    <li><a href="#overview">Overview</a></li>
    <li><a href="#frequencies">Frequencies</a></li>
    <li><a href="#entities">Entities</a></li>
    <li><a href="#keywords">Keywords</a></li>
    <li><a href="#categories">Categories</a></li>
    <li><a href="#concepts">Concepts</a></li>
</ul>

<div id="overview">
    <h3>Overview</h3>
    <table>
        <tbody>
            <tr>
                <th>Item</th>
                <td><?php echo link_to_item(null, array(), 'show', $this->item); ?></td>
            </tr>
            <tr>
                <th>Element</th>
                <td><?php echo $this->element->name ?> (<?php echo $this->element->getElementSet()->name ?>)</td>
            </tr>
            <tr>
                <th>Words</th>
                <td><?php echo number_format($this->totalWords); ?></td>
            </tr>
            <tr>
                <th>Unique Words</th>
                <td><?php echo number_format($this->uniqueWords); ?></td>
            </tr>
            <tr>
                <th>Character Count</th>
                <td><?php echo number_format($this->characterCount); ?></td>
            </tr>
            <tr>
                <th>Text Size</th>
                <td><?php echo number_format($this->textBytes); ?> bytes (<?php echo number_format($this->textKilobytes, 2) ?> kilobytes)</td>
            </tr>
            <tr>
                <th>NLU Item Cost*</th>
                <td>~<?php echo number_format($this->itemCostEstimate); ?> items</td>
            </tr>
        </tbody>
    </table>
    <p>* <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Watson Natural Language Understanding</a>
    (NLU) incurs a cost per item per feature: one item is one feature with up to
    10,000 characters. This service uses up to four features: Entities, Keywords,
    Categories, and Concepts.</p>
    <h3>Text</h3>
    <div><?php echo nl2br($this->text); ?></div>
</div>

<div id="frequencies">
    <h3>Word Frequencies</h3>
    <table>
        <thead>
            <tr>
                <th>Word</th>
                <th>Count</th>
                <th>Relative Frequency</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->words as $word => $count): ?>
            <tr>
                <td><?php echo $word; ?></td>
                <td><?php echo $count; ?></td>
                <td><?php echo ($count / $this->totalWords) * 100; ?>%</td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<div id="entities">
    <h3>Named Entities</h3>
    <p>Find people, places, events, and other types of entities mentioned in the text.</p>
    <?php if (isset($this->results['entities']) && $this->results['entities']): ?>
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
            <?php foreach ($this->results['entities'] as $entity): ?>
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
</div>

<div id="keywords">
    <h3>Keywords</h3>
    <p>Identify the important keywords in the text.</p>
    <?php if (isset($this->results['keywords']) && $this->results['keywords']): ?>
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
            <?php foreach ($this->results['keywords'] as $keyword): ?>
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
    <p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Text Analysis by IBM Watson Natural Language Understanding</a></p>
</div>

<div id="categories">
    <h3>Categories</h3>
    <p>Categorize the text using a five-level classification hierarchy.</p>
    <?php if (isset($this->results['categories']) && $this->results['categories']): ?>
    <a href="#glossary-categories">Glossary</a>
    <table>
        <thead>
            <tr>
                <th>Label</th>
                <th>Score</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['categories'] as $category): ?>
            <tr>
                <td><?php echo $category['label']; ?></td>
                <td><?php echo $category['score']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h4 id="glossary-categories">Glossary</h4>
    <dl>
        <dt>Label</dt>
        <dd>Category label. Forward slashes separate category hierarchy levels.</dd>
        <dt>Score</dt>
        <dd>Categorization score ranging from 0 to 1. A 0 means it's not confident in the categorization, and a 1 means it's highly confident.</dd>
    </dl>
    <?php else: ?>
    <p class="alert">No categories returned.</p>
    <?php endif; ?>
    <p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Text Analysis by IBM Watson Natural Language Understanding</a></p>
</div>

<div id="concepts">
    <h3>General Concepts</h3>
    <p>Identify high-level concepts that aren't necessarily directly referenced in the text.</p>
    <?php if (isset($this->results['concepts']) && $this->results['concepts']): ?>
    <a href="#glossary-concepts">Glossary</a>
    <table>
        <thead>
            <tr>
                <th>Concept</th>
                <th>Relevance</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($this->results['concepts'] as $concept): ?>
            <tr>
                <td><a target="_blank" href="<?php echo $concept['dbpedia_resource']; ?>"><?php echo $concept['text']; ?></td>
                <td><?php echo $concept['relevance']; ?></td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
    <h4 id="glossary-concepts">Glossary</h4>
    <dl>
        <dt>Concept</dt>
        <dd>Name of the concept</dd>
        <dt>Relevance</dt>
        <dd>Relevance score for the concept ranging from 0 to 1. A 0 means it's not relevant, and a 1 means it's highly relevant.</dd>
    </dl>

    <?php else: ?>
    <p class="alert">No concepts returned.</p>
    <?php endif; ?>
    <p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Text Analysis by IBM Watson Natural Language Understanding</a></p>
</div>

<?php echo foot(); ?>
