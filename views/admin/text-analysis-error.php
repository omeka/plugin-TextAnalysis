<?php if (isset($this->analysis['error'])): ?>
<h3>Service Error</h3>
<p class="error">The Natural Language Understanding service returned an error. No
analysis was performed. Below is the error response.</p>
<code><textarea rows="8"><?php echo json_encode($this->analysis, JSON_PRETTY_PRINT); ?></textarea></code>
<?php endif; ?>
