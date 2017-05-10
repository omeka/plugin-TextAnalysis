<?php
class TextAnalysisCorpusAnalysis extends Omeka_Record_AbstractRecord
{
    public $id;
    public $text_analysis_corpus_id;
    public $sequence_member;
    public $analysis;

    /**
     * Get the related TextAnalysis corpus.
     *
     * @return TextAnalysisCorpus
     */
    public function getTextAnalysisCorpus()
    {
        return $this->getTable('TextAnalysisCorpus')->find($this->text_analysis_corpus_id);
    }

    /**
     * Get the Watson Natural Language Understanding analysis of the text.
     *
     * @return array
     */
    public function getAnalysis()
    {
        return json_decode($this->analysis, true);
    }

    public function getPreviousSequenceMember()
    {
        $db = get_db();
        $sql = sprintf('
        SELECT sequence_member
        FROM %s
        WHERE sequence_member < ?
        ORDER BY sequence_member DESC
        LIMIT 1',
        $db->TextAnalysisCorpusAnalysis
        );
        return $db->fetchOne($sql, array($this->sequence_member));
    }

    public function getNextSequenceMember()
    {
        $db = get_db();
        $sql = sprintf('
        SELECT sequence_member
        FROM %s
        WHERE sequence_member > ?
        ORDER BY sequence_member
        LIMIT 1',
        $db->TextAnalysisCorpusAnalysis
        );
        return $db->fetchOne($sql, array($this->sequence_member));
    }
}
