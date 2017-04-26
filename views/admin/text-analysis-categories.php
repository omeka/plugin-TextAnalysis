<h3>Categories</h3>
<p>Categorize the text using a five-level classification hierarchy.</p>
<?php if ($this->categories): ?>
<a href="#glossary-categories">Glossary</a>
<table>
    <thead>
        <tr>
            <th>Label</th>
            <th>Score</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($this->categories as $category): ?>
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
<p><a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html" target="_blank">Text Analysis by IBM Watson Natural Language Understanding</a></p>
