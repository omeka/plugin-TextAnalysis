<?php
class Process_AnalyzeCorpus extends Omeka_Job_Process_AbstractProcess
{
    public function run($args)
    {
        $taCorpusId = $args['text_analysis_corpus_id'];
        $features = $args['features'];
        $itemCostOnly = isset($args['item_cost_only']) ? (bool) $args['item_cost_only'] : false;

        $db = get_db();
        $taCorpus = $db->getTable('TextAnalysisCorpus')->find($taCorpusId);
        $corpus = $taCorpus->getCorpus();

        // Limit analysis to the requested features.
        $nluFeatures = array();
        if ($features['entities']) {
            $nluFeatures['entities'] = array('sentiment' => true, 'emotion' => true, 'limit' => 50);
        }
        if ($features['keywords']) {
            $nluFeatures['keywords'] = array('sentiment' => true, 'emotion' => true, 'limit' => 50);
        }
        if ($features['categories']) {
            $nluFeatures['categories'] = array();
        }
        if ($features['concepts']) {
            $nluFeatures['concepts'] = array();
        }
        if ($features['topic_model']) {
            $malletTmpStr = md5(mt_rand());
            $malletTmpDir = sprintf('%s/%s', realpath(sprintf('%s/../../mallet', __DIR__)), $malletTmpStr);
            $malletTmpCorpusDir = sprintf('%s/corpus', $malletTmpDir);
            mkdir($malletTmpDir);
            mkdir($malletTmpCorpusDir);
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
                    if ($nluFeatures) {
                        $itemCost += $watsonNlu->getItemCost($text, $nluFeatures);
                        if (!$itemCostOnly) {
                            $response = $watsonNlu->combined($text, $nluFeatures);
                            $analysis = json_encode(json_decode($response->getBody())); // remove unneeded whitespace
                            $db->query($insertAnalysisSql, array($sequenceMember, $analysis));
                        }
                    }
                    if ($features['topic_model']) {
                        $filename = sprintf('%s/%s', $malletTmpCorpusDir, $sequenceMember);
                        file_put_contents($filename, $text);
                    }
                }
            } else {
                // Process an unsequenced corpus.
                $itemTexts = array();
                foreach ($corpus->ItemsCorpus as $itemId) {
                    $itemTexts[] = $db->query($selectTextSql, $itemId)->fetchColumn(0);
                }
                $text = implode(PHP_EOL, $itemTexts);
                if ($nluFeatures) {
                    $itemCost += $watsonNlu->getItemCost($text, $nluFeatures);
                    if (!$itemCostOnly) {
                        $response = $watsonNlu->combined(implode(PHP_EOL, $itemTexts), $nluFeatures);
                        $analysis = json_encode(json_decode($response->getBody())); // remove unneeded whitespace
                        $db->query($insertAnalysisSql, array(null, $analysis));
                    }
                }
            }
            if ($features['topic_model']) {
                $malletCmd = '/home/jimsafley/Desktop/hack-to-learn/mallet-2.0.8/bin/mallet';
                $malletInputFile = sprintf('%s/input.mallet', $malletTmpDir, $malletTmpStr);
                $malletDocTopicsFile = sprintf('%s/doc_topics', $malletTmpDir);
                $malletTopicKeysFile = sprintf('%s/topic_keys', $malletTmpDir);

                $cmdImportDir = sprintf(
                    '%s import-dir --input %s --output %s --keep-sequence --remove-stopwords',
                    $malletCmd,
                    escapeshellarg($malletTmpCorpusDir),
                    escapeshellarg($malletInputFile)
                );
                $cmdTrainTopics = sprintf(
                    '%s train-topics --input %s --output-doc-topics %s --output-topic-keys %s',
                    $malletCmd,
                    escapeshellarg($malletInputFile),
                    escapeshellarg($malletDocTopicsFile),
                    escapeshellarg($malletTopicKeysFile)
                );

                exec($cmdImportDir);
                exec($cmdTrainTopics);

                // Remove temporary files and directory.
                //~ $files = glob(sprintf('%s/*', $malletTmpDir));
                //~ foreach ($files as $file) {
                    //~ if (is_file($file)) {
                        //~ unlink($file);
                    //~ }
                //~ }
                //~ rmdir($malletTmpDir);
            }
            if ($nluFeatures) {
                $taCorpus->item_cost = $itemCost;
            }
            $taCorpus->save();
            $db->commit();
        } catch (Exception $e) {
            $db->rollBack();
            throw $e;
        }
    }
}
