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
and enter the path (from root) to the directory containing the MALLET executable.</p>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="mallet-path">MALLET path</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('mallet_path', get_option('text_analysis_mallet_path'), array('id' => 'mallet-path')); ?>
        </div>
    </div>
</section>
