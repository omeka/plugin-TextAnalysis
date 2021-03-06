<?php
class TextAnalysisPlugin extends Omeka_Plugin_AbstractPlugin
{
    protected $_hooks = array(
        'install',
        'uninstall',
        'upgrade',
        'config_form',
        'config',
        'define_acl',
    );

    protected $_filters = array(
        'admin_navigation_main',
    );

    public function hookInstall()
    {
        $db = get_db();
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}text_analysis_corpus` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) UNSIGNED DEFAULT NULL,
  `process_id` int(10) UNSIGNED DEFAULT NULL,
  `item_cost` int(10) UNSIGNED DEFAULT NULL,
  `topic_keys` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  `doc_topics` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_id` (`corpus_id`),
  UNIQUE KEY `process_id` (`process_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}text_analysis_corpus_analyses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `text_analysis_corpus_id` int(10) UNSIGNED NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `analysis` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`),
  KEY `text_analysis_corpus_id` (`text_analysis_corpus_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
        );
        $db->query(<<<SQL
ALTER TABLE `{$db->prefix}text_analysis_corpus_analyses`
ADD CONSTRAINT `text_analysis_corpus_analyses`
FOREIGN KEY (`text_analysis_corpus_id`)
REFERENCES `{$db->prefix}text_analysis_corpus` (`id`)
ON DELETE CASCADE;
SQL
        );
    }

    public function hookUninstall()
    {
        $db = get_db();
        $db->query('SET FOREIGN_KEY_CHECKS=0;');
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}text_analysis_corpus`");
        $db->query("DROP TABLE IF EXISTS `{$db->prefix}text_analysis_corpus_analyses`");
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        delete_option('text_analysis_alchemyapi_key');
        delete_option('text_analysis_username');
        delete_option('text_analysis_password');
        delete_option('text_analysis_mallet_script');
    }

    public function hookUpgrade($args)
    {
        $db = get_db();

        if (version_compare($args['old_version'], '1.1', '<=')) {
            delete_option('text_analysis_alchemyapi_key');
        }

        if (version_compare($args['old_version'], '2.0', '<=')) {
            $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}text_analysis_corpus` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `corpus_id` int(10) UNSIGNED DEFAULT NULL,
  `process_id` int(10) UNSIGNED DEFAULT NULL,
  `feature_entities` tinyint(1) DEFAULT NULL,
  `feature_keywords` tinyint(1) DEFAULT NULL,
  `feature_categories` tinyint(1) DEFAULT NULL,
  `feature_concepts` tinyint(1) DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `corpus_id` (`corpus_id`),
  UNIQUE KEY `process_id` (`process_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
            );
            $db->query(<<<SQL
CREATE TABLE IF NOT EXISTS `{$db->prefix}text_analysis_corpus_analyses` (
  `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `text_analysis_corpus_id` int(10) UNSIGNED NOT NULL,
  `sequence_member` varchar(40) COLLATE utf8_unicode_ci DEFAULT NULL,
  `analysis` mediumtext COLLATE utf8_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL
            );
            $db->query(<<<SQL
ALTER TABLE `{$db->prefix}text_analysis_corpus_analyses`
ADD CONSTRAINT `text_analysis_corpus_analyses`
FOREIGN KEY (`text_analysis_corpus_id`)
REFERENCES `{$db->prefix}text_analysis_corpus` (`id`)
ON DELETE CASCADE;
SQL
            );
        }

        if (version_compare($args['old_version'], '2.1', '<=')) {
            $db->query(<<<SQL
ALTER TABLE `{$db->prefix}text_analysis_corpus`
ADD `item_cost` INT UNSIGNED NULL DEFAULT NULL AFTER `process_id`;
SQL
            );
        }

        if (version_compare($args['old_version'], '2.2', '<=')) {
            $db->query(<<<SQL
ALTER TABLE `{$db->prefix}text_analysis_corpus`
  DROP `feature_entities`,
  DROP `feature_keywords`,
  DROP `feature_categories`,
  DROP `feature_concepts`;
SQL
            );
        }

        if (version_compare($args['old_version'], '2.3', '<=')) {
            $db->query(<<<SQL
ALTER TABLE `{$db->prefix}text_analysis_corpus`
  ADD `topic_keys` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL,
  ADD `doc_topics` mediumtext COLLATE utf8_unicode_ci DEFAULT NULL;
SQL
            );
        }

        if (version_compare($args['old_version'], '2.4', '<=')) {
            $malletCmdDir = get_option('text_analysis_mallet_cmd_dir');
            if ($malletCmdDir) {
                delete_option('text_analysis_mallet_cmd_dir');
                set_option('text_analysis_mallet_script_dir', $malletCmdDir);
            }
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

        $malletScriptDir = trim($args['post']['mallet_script_dir']);
        if ($malletScriptDir) {
            $malletScript = sprintf('%s/mallet', $malletScriptDir);
            if (is_executable($malletScript)) {
                set_option('text_analysis_mallet_script_dir', $malletScriptDir);
            } else {
                delete_option('text_analysis_mallet_script_dir');
                throw new Omeka_Validate_Exception('The MALLET script directory is invalid. Make sure the directory contains the MALLET script and that the script is executable by the web server.');
            }
        } else {
            delete_option('text_analysis_mallet_script_dir');
        }
    }

    public function hookDefineAcl($args)
    {
        $acl = $args['acl'];
        $acl->addResource('TextAnalysis_Corpora');
        // Given that usage may incur real costs, restrict text analysis
        // features to super and admin users.
        $acl->allow(array('super', 'admin'), array('TextAnalysis_Corpora'));
    }

    public function filterAdminNavigationMain($nav)
    {
        if (plugin_is_active('Ngram')) {
            $nav[] = array(
                'label' => __('Text Analysis'),
                'uri' => url('text-analysis/corpora'),
                'resource' => ('TextAnalysis_Corpora'),
            );
        }
        return $nav;
    }
}
