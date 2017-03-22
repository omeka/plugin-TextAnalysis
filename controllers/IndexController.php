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
        $nluApi = new TextAnalysis_NluApi(
            get_option('text_analysis_username'),
            get_option('text_analysis_password')
        );
        $response = $nluApi->combined($text, array(
            'entities' => array('sentiment' => true, 'emotion' => true),
            'categories' => array(),
            'concepts' => array(),
            'keywords' => array('sentiment' => true, 'emotion' => true),
        ));
        $results = json_decode($response->getBody(), true);

        $this->view->item = $item;
        $this->view->element = $element;
        $this->view->text = $text;
        $this->view->words = $textObj->getWords();
        $this->view->totalWords = $textObj->getTotalWords();
        $this->view->results = $results;
    }
}
