<?php
class TextAnalysis_CorpusController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $db = $this->_helper->db;
        $taCorpora = $db->getTable('TextAnalysisCorpus')->findAll();

        $this->view->taCorpora = $taCorpora;
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
}
