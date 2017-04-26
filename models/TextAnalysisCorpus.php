<?php
class TextAnalysisCorpus extends Omeka_Record_AbstractRecord
{
    public $id;
    public $corpus_id;
    public $process_id;
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
        switch ($this->getCorpus()->sequence_type) {
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
