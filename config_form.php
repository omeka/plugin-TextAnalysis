<section class="seven columns alpha">
    <div class="field">
        <div class="two column alpha">
            <label for="alchemyapi_key">AlchemyAPI Key</label>
        </div>
        <div class="inputs five columns omega">
            <p class="explanation">You must register for an <a href="http://www.alchemyapi.com/api/register.html">AlchemyAPI API key</a> to use this plugin.</p>
            <?php echo $view->formText(
                'alchemyapi_key',
                get_option('text_analysis_alchemyapi_key'),
                array('id' => 'alchemyapi_key')
            ) ?>
        </div>
    </div>
</section>
