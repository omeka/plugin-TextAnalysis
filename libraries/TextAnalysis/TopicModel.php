<?php
class TextAnalysis_TopicModel {

    protected $cmd;
    protected $tmpDir;
    protected $tmpInstanceDir;
    protected $topicKeys;
    protected $docTopics;

    /**
     * @param string $cmdDir Path to mallet command
     * @param string $tmpDir Path to directory that will contain temporary files
     */
    public function __construct($cmd, $dir)
    {
        $this->cmd = $cmd;
        $this->tmpDir = sprintf('%s/%s', $dir, md5(mt_rand()));
        $this->tmpInstanceDir = sprintf('%s/instances', $this->tmpDir);
        mkdir($this->tmpDir);
        mkdir($this->tmpInstanceDir);
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

        $cmdImportDir = sprintf(
            '%s import-dir --input %s --output %s --keep-sequence --remove-stopwords',
            $this->cmd,
            escapeshellarg($this->tmpInstanceDir),
            escapeshellarg($inputFile)
        );
        $cmdTrainTopics = sprintf(
            '%s train-topics --input %s --output-doc-topics %s --output-topic-keys %s',
            $this->cmd,
            escapeshellarg($inputFile),
            escapeshellarg($docTopicsFile),
            escapeshellarg($topicKeysFile)
        );

        exec($cmdImportDir);
        exec($cmdTrainTopics);

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
