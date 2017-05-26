<?php
class TextAnalysis_CorporaController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('TextAnalysisCorpus');
    }

    public function browseAction()
    {
        parent::browseAction();
        $this->view->canAnalyze = (bool) $this->getAvailableFeatures();
    }

    public function analysisAction()
    {
        $db = $this->_helper->db;
        $request = $this->getRequest();

        $id = $request->getQuery('id');
        $sequenceMember = $request->getQuery('sequence_member');

        $taCorpus = $db->getTable('TextAnalysisCorpus')->find($id);
        $corpus = $taCorpus->getCorpus();
        $corpusAnalyses = $taCorpus->getAnalyses();
        $corpusAnalysis = $taCorpus->getAnalyses($sequenceMember);
        $corpusAnalysis = $corpusAnalysis[0];
        $analysis = $corpusAnalysis->getAnalysis();

        $prevLink = 'n/a';
        $nextLink = 'n/a';

        if ($corpus && $corpus->isSequenced()) {
            $prevSeqMem = $corpusAnalysis->getPreviousSequenceMember();
            if ($prevSeqMem) {
                $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $prevSeqMem));
                $prevLink = sprintf('<a href="%s">%s</a>', $url, $taCorpus->getSequenceMemberLabel($prevSeqMem));
            }
            $nextSeqMem = $corpusAnalysis->getNextSequenceMember();
            if ($nextSeqMem) {
                $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $nextSeqMem));
                $nextLink = sprintf('<a href="%s">%s</a>', $url, $taCorpus->getSequenceMemberLabel($nextSeqMem));
            }
        }

        $url = url(array('action' => 'export'), null, array('id' => $taCorpus->id, 'sequence_member' => $sequenceMember));
        $exportLink = sprintf('<a href="%s">%s</a>', $url, $this->getExportFilename($id, $sequenceMember));

        $this->view->taCorpus = $taCorpus;
        $this->view->corpus = $corpus;
        $this->view->corpusAnalysis = $corpusAnalysis;
        $this->view->analysis = $analysis;
        $this->view->prevLink = $prevLink;
        $this->view->nextLink = $nextLink;
        $this->view->exportLink = $exportLink;
        $this->view->sequenceMember = $sequenceMember;
    }

    public function topicModelAction()
    {
        $db = $this->_helper->db;
        $request = $this->getRequest();

        $id = $request->getQuery('id');
        $sequenceMember = $request->getQuery('sequence_member');

        $taCorpus = $db->getTable('TextAnalysisCorpus')->find($id);
        $topicKeys = $taCorpus->getTopicKeys();
        $docTopicsAll = $taCorpus->getDocTopics();
        $docTopics = $docTopicsAll[$sequenceMember ? $sequenceMember : 'instance'];
        arsort($docTopics);

        $prevLink = 'n/a';
        $nextLink = 'n/a';

        $seqMems = array_keys($docTopicsAll);
        foreach ($seqMems as $key => $seqMem) {
            if ($seqMem == $sequenceMember) {
                if (isset($seqMems[$key - 1])) {
                    $prevSeqMem = $seqMems[$key - 1];
                    $url = url(array('action' => 'topic-model'), null, array('id' => $taCorpus->id, 'sequence_member' => $prevSeqMem));
                    $prevLink = sprintf('<a href="%s">%s</a>', $url, $taCorpus->getSequenceMemberLabel($prevSeqMem));
                }
                if (isset($seqMems[$key + 1])) {
                    $nextSeqMem = $seqMems[$key + 1];
                    $url = url(array('action' => 'topic-model'), null, array('id' => $taCorpus->id, 'sequence_member' => $nextSeqMem));
                    $nextLink = sprintf('<a href="%s">%s</a>', $url, $taCorpus->getSequenceMemberLabel($nextSeqMem));
                }
                break;
            }
        }

        $this->view->taCorpus = $taCorpus;
        $this->view->sequenceMember = $sequenceMember;
        $this->view->docTopics = $docTopics;
        $this->view->topicKeys = $topicKeys;
        $this->view->prevLink = $prevLink;
        $this->view->nextLink = $nextLink;
    }

    public function exportAction()
    {
        $db = $this->_helper->db;
        $request = $this->getRequest();

        $id = $request->getQuery('id');
        $sequenceMember = $request->getQuery('sequence_member');

        $taCorpus = $db->getTable('TextAnalysisCorpus')->find($id);
        $corpusAnalysis = $taCorpus->getAnalyses($sequenceMember);
        $corpusAnalysis = $corpusAnalysis[0];

        $this->getResponse()
            ->setHeader('Content-Type', 'application/json')
            ->setHeader('Content-Disposition', sprintf('attachment; filename="%s"', $this->getExportFilename($id, $sequenceMember)))
            ->setHeader('Content-Length', strlen($corpusAnalysis->analysis))
            ->setBody($corpusAnalysis->analysis)
            ->sendResponse();
        exit;
    }

    public function analyzeAction()
    {
        $db = $this->_helper->db;
        $request = $this->getRequest();

        if ($request->isPost()) {
            $corpusId = $request->getPost('corpus_id');
            $features = $request->getPost('features');
            $stopwords = $request->getPost('stopwords');
            $itemCostOnly = (bool) $request->getPost('item_cost_only');

            $corpus = $db->getTable('NgramCorpus')->find($corpusId);
            if (!$corpus) {
                $this->_helper->redirector('browse');
            }

            $features = array(
                'entities' => !empty($features['entities']),
                'keywords' => !empty($features['keywords']),
                'categories' => !empty($features['categories']),
                'concepts' => !empty($features['concepts']),
                'topic_model' => !empty($features['topic_model']),
            );

            $taCorpus = $db->getTable('TextAnalysisCorpus')->findBy(array('corpus_id' => $corpus->id));
            $taCorpus = $taCorpus[0];
            if ($taCorpus) {
                if (!$itemCostOnly && ($features['entities'] || $features['keywords'] || $features['categories'] || $features['concepts'])) {
                    // User requested to analyze at least one NLU feature for an
                    // existing corpus. Delete all existing NLU analyses before
                    // reanalyzing.
                    foreach ($taCorpus->getAnalyses() as $analysis) {
                        $analysis->delete();
                    }
                }
                if ($features['topic_model']) {
                    // User requested the topic model feature for an existing
                    // corpus. Remove all existing topic model data before
                    // reanalyzing.
                    $taCorpus->topic_keys = null;
                    $taCorpus->doc_topics = null;
                }
            } else {
                $taCorpus = new TextAnalysisCorpus;
                $taCorpus->corpus_id = $corpus->id;
            }
            $taCorpus->save(true);

            $process = Omeka_Job_Process_Dispatcher::startProcess(
                'Process_AnalyzeCorpus', null, array(
                    'text_analysis_corpus_id' => $taCorpus->id,
                    'features' => $features,
                    'stopwords' => $stopwords,
                    'item_cost_only' => $itemCostOnly,
                )
            );

            $taCorpus->process_id = $process->id;
            $taCorpus->save(true);

            $this->_helper->flashMessenger(
            'Analyzing the corpus. This may take some time. '
            . 'Feel free to navigate away from this page and close your browser. '
            . 'Refresh this page to see if the process is complete.', 'success');
            $this->_helper->redirector('index');
        }

        $featureOptions = $this->getAvailableFeatures();
        if (!$featureOptions) {
            $this->_helper->redirector('browse');
        }

        $corpora = $db->getTable('NgramCorpus')->findAll();
        $corporaOptions = array('Select Below');
        foreach ($corpora as $corpus) {
            if ($corpus->ItemsCorpus) {
                $corporaOptions[$corpus->id] = $corpus->name;
            }
        }

        $this->view->corporaOptions = $corporaOptions;
        $this->view->featureOptions = $featureOptions;
    }

    protected function getExportFilename($id, $sequenceMember = null)
    {
        return $sequenceMember
            ? sprintf('text-analysis-%s-%s.json', $id, $sequenceMember)
            : sprintf('text-analysis-%s.json', $id);
    }

    protected function getAvailableFeatures() {
        $features = array();
        if (get_option('text_analysis_username') && get_option('text_analysis_password')) {
            $features = array_merge($features, array(
                'entities' => 'Entities (NLU)',
                'keywords' => 'Keywords (NLU)',
                'categories' => 'Categories (NLU)',
                'concepts' => 'Concepts (NLU)',
            ));
        }
        if (get_option('text_analysis_mallet_script_dir')) {
            $features['topic_model'] = 'Topic Model (MALLET)';
        }
        return $features;
    }
}
