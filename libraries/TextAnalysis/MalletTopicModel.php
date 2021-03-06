<?php
class TextAnalysis_MalletTopicModel {

    protected $script;
    protected $tmpDir;
    protected $tmpInstanceDir;
    protected $extraStopwords;
    protected $topicKeys;
    protected $docTopics;

    /**
     * @param string $script Path to MALLET script
     * @param string $processingDir Path to processing directory
     */
    public function __construct($script, $processingDir, $extraStopwords = null)
    {
        if (!is_executable($script)) {
            throw new Exception('The MALLET script is not executable or cannot be found.');
        }
        if (!is_writable($processingDir)) {
            throw new Exception('The MALLET processing directory is not writable or cannot be found.');
        }

        $this->script = $script;
        $this->tmpDir = sprintf('%s/%s', $processingDir, md5(mt_rand()));
        $this->tmpInstanceDir = sprintf('%s/instances', $this->tmpDir);
        mkdir($this->tmpDir);
        mkdir($this->tmpInstanceDir);
    }

    /**
     * Set extra whitespace-separated stopwords.
     *
     * @param string $extraStopwords
     */
    public function setExtraStopwords($extraStopwords)
    {
        $extraStopwords = trim($extraStopwords);
        $this->extraStopwords = '' === $extraStopwords ? null : $extraStopwords ;
    }

    /**
     * Add a modeling instance.
     *
     * @param string $file The name of the instance file
     * @param string $text The text of the intance file
     */
    public function addInstance($file, $text)
    {
        file_put_contents(sprintf('%s/%s', $this->tmpInstanceDir, $file), $text);
    }

    /**
     * Build the topic model.
     *
     * Imports the instance directory, trains the topics, and retrieves the
     * topic keys and document topics.
     */
    public function buildTopicModel()
    {
        $inputFile = sprintf('%s/input.mallet', $this->tmpDir);
        $topicKeysFile = sprintf('%s/topic_keys', $this->tmpDir);
        $docTopicsFile = sprintf('%s/doc_topics', $this->tmpDir);

        // MALLET: import directory
        $argsImportDir = array(
            sprintf('--input %s', escapeshellarg($this->tmpInstanceDir)),
            sprintf('--output %s', escapeshellarg($inputFile)),
            '--keep-sequence',
            '--remove-stopwords',
        );
        if ($this->extraStopwords) {
            $extraStopwordsFile = sprintf('%s/extra_stopwords', $this->tmpDir);;
            file_put_contents($extraStopwordsFile, $this->extraStopwords);
            $argsImportDir[] = sprintf('--extra-stopwords %s', $extraStopwordsFile);
        }
        exec(sprintf(
            '%s import-dir %s',
            $this->script,
            implode(' ', $argsImportDir)
        ));

        // MALLET: train topics
        $argsTrainTopics = array(
            sprintf('--input %s', escapeshellarg($inputFile)),
            sprintf('--output-doc-topics %s', escapeshellarg($docTopicsFile)),
            sprintf('--output-topic-keys %s', escapeshellarg($topicKeysFile)),
        );
        exec(sprintf(
            '%s train-topics %s',
            $this->script,
            implode(' ', $argsTrainTopics)
        )   );

        // Extract topic keys and document topics.
        $strGetCsv = function ($value) {
            return str_getcsv($value, "\t");
        };
        $this->topicKeys = array_map($strGetCsv, file($topicKeysFile));
        $this->docTopics = array_map($strGetCsv, file($docTopicsFile));

        // Remove temporary files and directories.
        $files = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($this->tmpDir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($files as $fileinfo) {
            $todo = ($fileinfo->isDir() ? 'rmdir' : 'unlink');
            $todo($fileinfo->getRealPath());
        }
        rmdir($this->tmpDir);
    }

    /**
     * Get the topic keys.
     *
     * Must be called after calling self::buildTopicModel().
     *
     * @return array The array of topic keys
     */
    public function getTopicKeys()
    {
        return $this->topicKeys;
    }

    /**
     * Get the document topics.
     *
     * Must be called after calling self::buildTopicModel().
     *
     * @return array The array of document topics
     */
    public function getDocTopics()
    {
        return $this->docTopics;
    }
}
