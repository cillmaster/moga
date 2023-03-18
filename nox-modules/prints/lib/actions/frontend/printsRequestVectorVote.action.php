<?php
/**
 * Страница голосования за запрос вектора
 *
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsRequestVectorVoteAction extends noxAction
{
    public $requestVectorModel;
    public $request;

    public $model;

    public function execute()
    {
        $id = $this->getParam('id', 0);
        $this->requestVectorModel = new printsRequestVectorModel();
        $this->request = $this->requestVectorModel->where(['id' => $id])->fetch();

        if(!$this->request) {
            return 404;
        }

        if(!noxSystem::authorization()) {
            return 404;
        }
        else {
            $this->model = new printsRequestVoteModel();
            $pay = (int)$_GET['want_pay'];
            $this->model->vote($id, Prints::REQUEST_VECTOR, $pay);
            setcookie('vote_email', $pay ? 'want_pay' : 'want_free', time() + 1000, '/');
            noxSystem::locationBack();
        }
    }
}
