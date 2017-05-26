<p>This plugin uses the <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Watson Natural Language Understanding</a>
(NLU) service to analyze text. You must <a href="https://www.ibm.com/watson/developercloud/doc/natural-language-understanding/getting-started.html">create an IBM Bluemix account</a>
and enter your service credentials below. Given that usage may incur real costs
(depending on plan), text analysis is restricted to users with super and admin
permissions.</p>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="username">Bluemix Username</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('username', get_option('text_analysis_username'), array('id' => 'username')); ?>
        </div>
    </div>
</section>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="password">Bluemix Password</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('password', get_option('text_analysis_password'), array('id' => 'password')); ?>
        </div>
    </div>
</section>
<p>This plugin uses <a href="http://mallet.cs.umass.edu/index.php">MALLET: A Machine Learning for Language Toolkit</a>
to generate topic models. You must <a href="http://mallet.cs.umass.edu/download.php">download and install MALLET </a>
and enter the path (from root) to the directory containing the MALLET script.</p>
<?php
$malletScriptDir = get_option('text_analysis_mallet_script_dir');
$malletScript = realpath(sprintf('%s/mallet', $malletScriptDir));
$malletProcessingDir = realpath(sprintf('%s/mallet_processing', __DIR__));
?>
<?php if (!is_executable($malletScript)): ?>
<p class="error">WARNING: The MALLET script directory "<?php echo $malletScriptDir; ?>"
is invalid. To generate topic models the directory must contain the MALLET script
and the script must be executable by the web server.</p>
<?php endif; ?>
<?php if (!is_writable($malletProcessingDir)): ?>
<p class="error">WARNING: The MALLET processing directory "<?php echo $malletProcessingDir; ?>"
is invalid. To generate topic models it must be writable by the web server.</p>
<?php endif; ?>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="mallet_script_dir">MALLET script directory</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('mallet_script_dir', get_option('text_analysis_mallet_script_dir'), array('id' => 'mallet_script_dir')); ?>
        </div>
    </div>
</section>
