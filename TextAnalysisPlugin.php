<?php
class TextAnalysisPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'uninstall',
        'config_form',
        'config',
        'define_acl',
        'admin_items_show_sidebar',
    );

    public function hookUninstall()
    {
        delete_option('text_analysis_alchemyapi_key');
    }

    public function hookConfigForm()
    {
        $view = get_view();
        include 'config_form.php';
    }

    public function hookConfig($args)
    {
        set_option('text_analysis_alchemyapi_key', $args['post']['alchemyapi_key']);
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('TextAnalysis_Index');
        $acl->allow(null, 'TextAnalysis_Index');
    }

    public function hookAdminItemsShowSidebar($args)
    {
        $item = $args['item'];
        $elementTexts = $item->getAllElementTextsByElement();
        $elementOptions = array();
        foreach ($elementTexts as $elementId => $elementTexts) {
            $elementOptions[$elementId] = $item->getElementById($elementId)->name;
        }
        echo $args['view']->partial('text-analysis-sidebar.php', array(
            'item' => $item,
            'elementOptions' => $elementOptions
        ));
    }
}
