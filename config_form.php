<p>This plugin uses the <a href="https://www.ibm.com/watson/developercloud/natural-language-understanding.html">Watson Natural Language Understanding</a>
(NLU) service to analyze text. You must <a href="https://www.ibm.com/watson/developercloud/doc/natural-language-understanding/getting-started.html">create an IBM Bluemix account</a>
and enter your service credentials below to use this plugin. Given that usage may
incur real costs (depending on plan), text analysis is restricted to users with
super and admin permissions.</p>
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
