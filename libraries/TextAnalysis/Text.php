<?php
class TextAnalysis_Text
{
    /**
     * @var array
     */
    protected $words = array();

    protected $totalWords = 0;

    /**
     * Constructor
     *
     * @param string $text
     * @param string $locale
     */
    public function __construct($text, $locale = null)
    {
        if (!$locale) {
            $locale = ini_get('intl.default_locale');
        }
        $iterator = \IntlBreakIterator::createWordInstance($locale);
        $iterator->setText($text);
        foreach($iterator->getPartsIterator() as $part) {
            if (\IntlBreakIterator::WORD_NONE !== $iterator->getRuleStatus()) {
                $word = mb_strtolower($part);
                if (isset($this->words[$word])) {
                    $this->words[$word]++;
                } else {
                    $this->words[$word] = 1;
                }
                $this->totalWords++;
            }
        }
    }

    /**
     * Get all words in the text.
     *
     * @return array
     */
    public function getWords()
    {
        arsort($this->words);
        return $this->words;
    }

    /**
     * Get the total work count.
     *
     * @return int
     */
    public function getTotalWords()
    {
        return $this->totalWords;
    }
}
