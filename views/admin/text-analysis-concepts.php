<h3>General Concepts</h3>
<p>Identify high-level concepts that aren't necessarily directly referenced in the text.</p>
<?php if ($this->concepts): ?>
<h4>Glossary</h4>
<dl>
    <dt>Concept</dt>
    <dd>Name of the concept</dd>
    <dt>Relevance</dt>
    <dd>Relevance score for the concept ranging from 0 to 1. A 0 means it's not relevant, and a 1 means it's highly relevant.</dd>
</dl>
<table>
    <thead>
        <tr>
            <th>Concept</th>
            <th>Relevance</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->concepts as $concept): ?>
        <tr>
            <td><a target="_blank" href="<?php echo $concept['dbpedia_resource']; ?>"><?php echo $concept['text']; ?></td>
            <td><?php echo $concept['relevance']; ?></td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>
<?php else: ?>
<p class="alert">No concepts returned.</p>
<?php endif; ?>
<p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html" target="_blank">Text Analysis by IBM Watson Natural Language Understanding</a></p>
