<?php
class TextAnalysis_IndexController extends Omeka_Controller_AbstractActionController
{
    public function indexAction()
    {
        $request = $this->getRequest();

        if (!$request->isPost()) {
            $this->_helper->redirector('index', 'index', 'index');
        }

        $table = $this->_helper->db;
        $itemTable = $table->getTable('Item');
        $elementTable = $table->getTable('Element');

        $itemId = $request->get('text_analysis_item');
        $elementId = $request->get('text_analysis_element');
        $requestedFeatures = $request->get('text_analysis_features');

        $item = $itemTable->find($itemId);
        $element = $elementTable->find($elementId);

        $elementTexts = $item->getElementTextsByRecord($element);
        $texts = array();
        foreach ($elementTexts as $elementText) {
            $texts[] = $elementText->text;
        }
        $text = implode(' ', $texts); // consolidate all texts

        $textObj = new TextAnalysis_Text($text);
        $watsonNlu = new TextAnalysis_WatsonNlu(
            get_option('text_analysis_username'),
            get_option('text_analysis_password')
        );
        // Limit analysis to the requested features.
        $features = array();
        if (!empty($requestedFeatures['entities'])) {
            $features['entities'] = array('sentiment' => true, 'emotion' => true, 'limit' => 100);
        }
        if (!empty($requestedFeatures['keywords'])) {
            $features['keywords'] = array('sentiment' => true, 'emotion' => true, 'limit' => 100);
        }
        if (!empty($requestedFeatures['categories'])) {
            $features['categories'] = array();
        }
        if (!empty($requestedFeatures['concepts'])) {
            $features['concepts'] = array();
        }
        $response = $watsonNlu->combined($text, $features);
        $results = json_decode($response->getBody(), true);

        $this->view->item = $item;
        $this->view->element = $element;
        $this->view->text = $text;
        $this->view->words = $textObj->getWords();
        $this->view->totalWords = $textObj->getTotalWords();
        $this->view->uniqueWords = count($this->view->words);
        $this->view->characterCount = mb_strlen($this->view->text);
        $this->view->textBytes = strlen($this->view->text);
        $this->view->textKilobytes = $this->view->textBytes / 1024;
        // Watson NLU incurs a cost per item per feature: one item is one
        // feature with up to 10,000 characters.
        // @see https://www.ibm.com/watson/developercloud/natural-language-understanding.html#pricing-block
        $this->view->itemCostEstimate = ceil(count($features) * ($this->view->characterCount / 10000));
        $this->view->results = $results;
    }
}
