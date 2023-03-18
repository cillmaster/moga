<?php
/**
 * Страница просмотра вектора
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsVectorPrintOLDAction extends noxThemeAction
{
    public $print;

    public $vectorModel;

    public function execute()
    {
        $id = $this->getParam('vectorId', 0);
        $this->vectorModel = new printsVectorModel();

        $this->print = $this->vectorModel->getById($id);
        if(!$this->print) {
            return 404;
        }

        $newUrl = Prints::createUrlForItem($this->print, Prints::VECTOR);
        (new errorActions)->action301($newUrl);
    }
}
