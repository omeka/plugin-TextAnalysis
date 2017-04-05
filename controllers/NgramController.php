<?php
class TextAnalysis_NgramController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $table = $this->_helper->db;
        $this->view->corpora = $table->getTable('NgramCorpus')->findAll();
    }

    public function analyzeAction()
    {
        $request = $this->getRequest();
        $corpusId = $request->getPost('text_analysis_corpus');
        $features = $request->getPost('text_analysis_features');

        if (!($request->isPost() && $corpusId)) {
            $this->_helper->redirector('index', 'index', 'index');
        }

        $db = get_db();
        $corpus = $db->getTable('NgramCorpus')->find($corpusId);
        if (!$corpus) {
            $this->_helper->redirector('index', 'index', 'index');
        }

        $process = Omeka_Job_Process_Dispatcher::startProcess(
            'Process_AnalyzeNgramCorpus', null, array(
                'corpus_id' => $corpus->id,
                'features' => $features,
            )
        );
        $this->_helper->flashMessenger(
        'Analyzing the corpus. This may take some time. '
        . 'Feel free to navigate away from this page and close your browser. '
        . 'Refresh this page to see if the process is complete.', 'success');
        $this->_helper->redirector('index');
    }
}
