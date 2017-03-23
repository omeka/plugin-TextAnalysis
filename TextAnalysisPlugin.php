<?php
class TextAnalysisPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'uninstall',
        'upgrade',
        'config_form',
        'config',
        'define_acl',
        'admin_items_show_sidebar',
    );

    public function hookUninstall()
    {
        delete_option('text_analysis_alchemyapi_key');
        delete_option('text_analysis_username');
        delete_option('text_analysis_password');
    }

    public function hookUpgrade($args)
    {
        if (version_compare($args['old_version'], '1.1', '<=')) {
            delete_option('text_analysis_alchemyapi_key');
        }
    }

    public function hookConfigForm()
    {
        $view = get_view();
        include 'config_form.php';
    }

    public function hookConfig($args)
    {
        set_option('text_analysis_username', $args['post']['username']);
        set_option('text_analysis_password', $args['post']['password']);
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('TextAnalysis_Index');
        // Given that usage may incur real costs, restrict text analysis
        // features to super and admin users.
        $acl->allow(array('super', 'admin'), 'TextAnalysis_Index');
    }

    public function hookAdminItemsShowSidebar($args)
    {
        if (!is_allowed('TextAnalysis_Index', null)) {
            return;
        }
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
