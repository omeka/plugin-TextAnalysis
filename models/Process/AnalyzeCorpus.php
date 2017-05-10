<?php
class Process_AnalyzeCorpus extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {
        $taCorpusId = $args['text_analysis_corpus_id'];
        $itemCostOnly = isset($args['item_cost_only']) ? (bool) $args['item_cost_only'] : false;

        $db = get_db();
        $taCorpus = $db->getTable('TextAnalysisCorpus')->find($taCorpusId);
        $corpus = $taCorpus->getCorpus();

        // Limit analysis to the requested features.
        $features = array();
        if ($taCorpus->feature_entities) {
            $features['entities'] = array('sentiment' => true, 'emotion' => true, 'limit' => 50);
        }
        if ($taCorpus->feature_keywords) {
            $features['keywords'] = array('sentiment' => true, 'emotion' => true, 'limit' => 50);
        }
        if ($taCorpus->feature_categories) {
            $features['categories'] = array();
        }
        if ($taCorpus->feature_concepts) {
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
        $insertAnalysisSql = sprintf('
            INSERT INTO %s (
                text_analysis_corpus_id, sequence_member, analysis
            ) VALUES (
                %s, ?, ?
            )',
            $db->TextAnalysisCorpusAnalysis,
            $taCorpus->id
        );

        $db->beginTransaction();
        try {
            $itemCost = 0;
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
                    $text = implode(PHP_EOL, $itemTexts);
                    $itemCost += $watsonNlu->getItemCost($text, $features);
                    if (!$itemCostOnly) {
                        $response = $watsonNlu->combined($text, $features);
                        $analysis = json_encode(json_decode($response->getBody())); // remove unneeded whitespace
                        $db->query($insertAnalysisSql, array($sequenceMember, $analysis));
                    }
                }
            } else {
                // Process an unsequenced corpus.
                $itemTexts = array();
                foreach ($corpus->ItemsCorpus as $itemId) {
                    $itemTexts[] = $db->query($selectTextSql, $itemId)->fetchColumn(0);
                }
                $text = implode(PHP_EOL, $itemTexts);
                $itemCost += $watsonNlu->getItemCost($text, $features);
                if (!$itemCostOnly) {
                    $response = $watsonNlu->combined(implode(PHP_EOL, $itemTexts), $features);
                    $analysis = json_encode(json_decode($response->getBody())); // remove unneeded whitespace
                    $db->query($insertAnalysisSql, array(null, $analysis));
                }
            }
            $taCorpus->item_cost = $itemCost;
            $taCorpus->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
