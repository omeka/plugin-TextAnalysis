<?php
class TextAnalysis_CorporaController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $db = $this->_helper->db;
        $taCorpora = $db->getTable('TextAnalysisCorpus')->findAll();

        $corpusRows = array();
        foreach ($taCorpora as $taCorpus) {
            $corpus = $taCorpus->getCorpus();
            $process = $taCorpus->getProcess();
            $analyses = $taCorpus->getAnalyses();

            if ('completed' === $process->status) {
                if ($corpus->isSequenced()) {
                    $options = array('Select to view');
                    foreach ($analyses as $analysis) {
                        $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $analysis->sequence_member));
                        $options[$url] =  $this->_getSequenceMemberLabel($corpus->sequence_type, $analysis->sequence_member);
                    }
                    $analysisField = $this->view->formSelect('sequence_member', null, array('class' => 'corpus_sequence_member'), $options);

                } else {
                    $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id));
                    $analysisField = sprintf('<a href="%s">%s</a>', $url, 'View');
                }
            } else {
                $analysisField = '[not available]';
            }

            $corpusRows[] = array(
                'name' => link_to($corpus, 'show', $corpus->name),
                'process' => ucwords($process->status),
                'analysis' => $analysisField,
            );
        }

        $this->view->corpusRows = $corpusRows;
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
        $currentSequenceMember = $sequenceMember ? $this->_getSequenceMemberLabel($corpus->sequence_type, $sequenceMember) : 'n/a';

        if ($corpus->isSequenced()) {
            $prevSeqMem = $corpusAnalysis->getPreviousSequenceMember();
            if ($prevSeqMem) {
                $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $prevSeqMem));
                $prevLink = sprintf('<a href="%s">%s</a>', $url, $this->_getSequenceMemberLabel($corpus->sequence_type, $prevSeqMem));
            }
            $nextSeqMem = $corpusAnalysis->getNextSequenceMember();
            if ($nextSeqMem) {
                $url = url(array('action' => 'analysis'), null, array('id' => $taCorpus->id, 'sequence_member' => $nextSeqMem));
                $nextLink = sprintf('<a href="%s">%s</a>', $url, $this->_getSequenceMemberLabel($corpus->sequence_type, $nextSeqMem));
            }
        }

        $this->view->taCorpus = $taCorpus;
        $this->view->corpus = $corpus;
        $this->view->corpusAnalysis = $corpusAnalysis;
        $this->view->analysis = $analysis;
        $this->view->prevLink = $prevLink;
        $this->view->nextLink = $nextLink;
        $this->view->currentSequenceMember = $currentSequenceMember;
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

    protected function _getSequenceMemberLabel($sequenceType, $sequenceMember)
    {
        switch ($sequenceType) {
            case 'month':
                $dateTime = DateTime::createFromFormat('Ym', $sequenceMember);
                return $dateTime->format('Y F');
            case 'day':
                $dateTime = DateTime::createFromFormat('Ymd', $sequenceMember);
                return $dateTime->format('Y F j');
            case 'year':
            case 'numeric':
            default:
                return $sequenceMember;
        }
    }
}
