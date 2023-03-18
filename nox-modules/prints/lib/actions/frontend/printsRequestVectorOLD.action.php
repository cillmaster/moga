<?php
/**
 * Страница запроса чертежа
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsRequestVectorOLDAction extends noxThemeAction
{
    public $requestVectorModel;
    public $request;

    public function execute()
    {
        $id = $this->getParam('id', 0);
        $this->requestVectorModel = new printsRequestVectorModel();
        $this->request = $this->requestVectorModel->getById($id);

        if(!$this->request) {
            return 404;
        }
        else {
            (new ErrorActions)->action301(Prints::createUrlForItem($this->request, Prints::REQUEST_VECTOR));
        }
    }
}
