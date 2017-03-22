<p>This plugin uses IBM's <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Natural Language Understanding</a>
service to analyze text. You must <a href="https://www.ibm.com/watson/developercloud/doc/natural-language-understanding/getting-started.html">create an IBM Bluemix account</a>
and enter your service credentials below to use this plugin.</p>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="username">Username</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('username', get_option('text_analysis_username'), array('id' => 'username')); ?>
        </div>
    </div>
</section>
<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="password">Password</label>
        </div>
        <div class="inputs five columns omega">
            <?php echo $view->formText('password', get_option('text_analysis_password'), array('id' => 'password')); ?>
        </div>
    </div>
</section>
