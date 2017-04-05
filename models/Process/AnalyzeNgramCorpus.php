<?php
class Process_AnalyzeNgramCorpus extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {
        $db = get_db();
        $corpus = $this->_db->getTable('NgramCorpus')->find($args['corpus_id']);

        // Limit analysis to the requested features.
        $features = array();
        if (!empty($args['features']['entities'])) {
            $features['entities'] = array('sentiment' => true, 'emotion' => true, 'limit' => 100);
        }
        if (!empty($args['features']['keywords'])) {
            $features['keywords'] = array('sentiment' => true, 'emotion' => true, 'limit' => 100);
        }
        if (!empty($args['features']['categories'])) {
            $features['categories'] = array();
        }
        if (!empty($args['features']['concepts'])) {
            $features['concepts'] = array();
        }

        $watsonNlu = new TextAnalysis_WatsonNlu(
            get_option('text_analysis_username'),
            get_option('text_analysis_password')
        );

        $selectTextSql = sprintf('
            SELECT et.text
            FROM %s i JOIN %s et
            ON i.id = et.record_id
            WHERE et.record_id = ?
            AND et.element_id = %s',
            $db->Item,
            $db->ElementText,
            $db->quote($corpus->text_element_id, Zend_Db::INT_TYPE)
        );
        $textAnalysisNgramCorpusSql = sprintf('
            INSERT INTO %s (
                corpus_id, sequence_member, analysis
            ) VALUES (
                %s, ?, ?
            )',
            $db->TextAnalysisNgramCorpus,
            $corpus->id
        );

        $db->beginTransaction();
        try {
            if ($corpus->isSequenced()) {
                // Process a sequenced corpus.
                $items = array();
                foreach ($corpus->ItemsCorpus as $itemId => $sequenceMember) {
                    $items[$sequenceMember][] = $itemId;
                }
                ksort($items);
                foreach ($items as $sequenceMember => $itemIds) {
                    $itemTexts = array();
                    foreach ($itemIds as $itemId) {
                        $itemTexts[] = $db->query($selectTextSql, $itemId)->fetchColumn(0);
                    }
                    $response = $watsonNlu->combined(implode(PHP_EOL, $itemTexts), $features);
                    $analysis = $response->getBody();
                    $db->query($textAnalysisNgramCorpusSql, array($sequenceMember, $analysis));
                }
            } else {
                // Process an unsequenced corpus.
                $itemTexts = array();
                foreach ($corpus->ItemsCorpus as $itemId) {
                    $itemTexts[] = $db->query($selectTextSql, $itemId)->fetchColumn(0);
                }
                $response = $watsonNlu->combined(implode(PHP_EOL, $itemTexts), $features);
                $analysis = $response->getBody();
                $db->query($textAnalysisNgramCorpusSql, array(null, $analysis));
            }
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
