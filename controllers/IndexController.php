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

        $apiKey = get_option('text_analysis_alchemyapi_key');
        $alchemyApi = new TextAnalysis_AlchemyApi($apiKey);
        $response = $alchemyApi->combined($text, array('extract' => 'entity,taxonomy,concept,keyword'));

        $this->view->item = $item;
        $this->view->element = $element;
        $this->view->text = $text;
        $this->view->words = $textObj->getWords();
        $this->view->totalWords = $textObj->getTotalWords();
        $this->view->results = json_decode($response->getBody(), true);
    }
}
