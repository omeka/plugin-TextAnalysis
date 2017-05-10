<?php
class TextAnalysis_CorporaController extends Omeka_Controller_AbstractActionController
{
    public function init()
    {
        $this->_helper->db->setDefaultModelName('TextAnalysisCorpus');
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
        $currentSequenceMember = $sequenceMember ? $taCorpus->getSequenceMemberLabel($sequenceMember) : 'n/a';

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
        $this->view->currentSequenceMember = $currentSequenceMember;
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
            $corpus = $db->getTable('NgramCorpus')->find($request->getPost('corpus_id'));
            if (!$corpus) {
                $this->_helper->redirector('analyze');
            }
            $features = $request->getPost('features');

            $taCorpus = $db->getTable('TextAnalysisCorpus')->findBy(array('corpus_id' => $corpus->id));
            if ($taCorpus) {
                $taCorpus[0]->delete(); // this cascade deletes all related analyses
            }

            $taCorpus = new TextAnalysisCorpus;
            $taCorpus->corpus_id = $corpus->id;
            $taCorpus->feature_entities = empty($features['entities']) ? 0 : 1;
            $taCorpus->feature_keywords = empty($features['keywords']) ? 0 : 1;
            $taCorpus->feature_categories = empty($features['categories']) ? 0 : 1;
            $taCorpus->feature_concepts = empty($features['concepts']) ? 0 : 1;
            $taCorpus->save(true);

            $process = Omeka_Job_Process_Dispatcher::startProcess(
                'Process_AnalyzeCorpus', null, array(
                    'text_analysis_corpus_id' => $taCorpus->id,
                    'item_cost_only' => $request->getPost('item_cost_only'),
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

        $corpora = $db->getTable('NgramCorpus')->findAll();
        $corporaOptions = array('Select Below');
        foreach ($corpora as $corpus) {
            if ($corpus->ItemsCorpus) {
                $corporaOptions[$corpus->id] = $corpus->name;
            }
        }

        $this->view->corporaOptions = $corporaOptions;
        $this->view->featureOptions = array(
            'entities' => 'Entities',
            'keywords' => 'Keywords',
            'categories' => 'Categories',
            'concepts' => 'Concepts',
        );
    }

    protected function getExportFilename($id, $sequenceMember = null)
    {
        return $sequenceMember
            ? sprintf('text-analysis-%s-%s.json', $id, $sequenceMember)
            : sprintf('text-analysis-%s.json', $id);
    }
}
