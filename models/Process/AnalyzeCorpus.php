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
            $topicModel = new TextAnalysis_MalletTopicModel(
                '/home/jimsafley/Desktop/hack-to-learn/mallet-2.0.8/bin/mallet',
                realpath(sprintf('%s/../../mallet', __DIR__))
            );
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
                        $topicModel->addInstance($sequenceMember, $text);
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
                if ($features['topic_model']) {
                    $topicModel->addInstance('instance', $text);
                }
            }
            if ($features['topic_model']) {
                $topicModel->buildTopicModel();

                // Format results for better retrieval.
                $topicKeys = array();
                foreach ($topicModel->getTopicKeys() as $topicKey) {
                    $topicKeys[$topicKey[0]] = $topicKey[2];
                }
                $docTopics = array();
                foreach ($topicModel->getDocTopics() as $docTopic) {
                    $sequenceMember = basename($docTopic[1]);
                    $docTopics[$sequenceMember] = array_slice($docTopic, 2);
                }
                ksort($docTopics);

                $taCorpus->topic_keys = json_encode($topicKeys, JSON_FORCE_OBJECT);
                $taCorpus->doc_topics = json_encode($docTopics, JSON_FORCE_OBJECT);
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
