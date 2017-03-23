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

        $item = $itemTable->find($itemId);
        $element = $elementTable->find($elementId);

        $elementTexts = $item->getElementTextsByRecord($element);
        $texts = array();
        foreach ($elementTexts as $elementText) {
            $texts[] = $elementText->text;
        }
        $text = implode(' ', $texts); // consolidate all texts

        $textObj = new TextAnalysis_Text($text);
        $nluApi = new TextAnalysis_WatsonNlu(
            get_option('text_analysis_username'),
            get_option('text_analysis_password')
        );
        $response = $nluApi->combined($text, array(
            'entities' => array('sentiment' => true, 'emotion' => true, 'limit' => 100),
            'categories' => array(),
            'concepts' => array(),
            'keywords' => array('sentiment' => true, 'emotion' => true, 'limit' => 100),
        ));
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
        // feature with up to 10,000 characters. This plugin uses four features.
        // @see https://www.ibm.com/watson/developercloud/natural-language-understanding.html#pricing-block
        $this->view->itemCostEstimate = ceil(4 * ($this->view->characterCount / 10000));
        $this->view->results = $results;
    }
}
