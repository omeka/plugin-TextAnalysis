<?php
class TextAnalysisCorpus extends Omeka_Record_AbstractRecord
{
    public $id;
    public $corpus_id;
    public $process_id;
    public $item_cost;
    public $feature_entities;
    public $feature_keywords;
    public $feature_categories;
    public $feature_concepts;

    /**
     * Get the related Ngram corpus.
     *
     * @return NgramCorpus
     */
    public function getCorpus()
    {
        return $this->getTable('NgramCorpus')->find($this->corpus_id);
    }

    /**
     * Get the process responsible for analyzing this corpus.
     *
     * @return Process
     */
    public function getProcess()
    {
        return $this->getTable('Process')->find($this->process_id);
    }

    public function getAnalyses($sequenceMember = null)
    {
        $query = array('text_analysis_corpus_id' => $this->id);
        if ($sequenceMember) {
            $query['sequence_member'] = $sequenceMember;
        }
        return $this->getTable('TextAnalysisCorpusAnalysis')->findBy($query);
    }

    public function getSequenceMemberLabel($sequenceMember)
    {
        $corpus = $this->getCorpus();
        $sequenceType = $corpus ? $corpus->sequence_type : null;
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

    public function getRecordUrl($action = 'show')
    {
        return array(
            'module' => 'text-analysis',
            'controller' => 'corpora',
            'action' => $action,
            'id' => $this->id,
        );
    }
}
