<?php
    /**
     * Подсчет переходов по ссылке
     *
     * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
     * @version    1.1
     * @package    deltashadow
     */

class filesDefaultAction extends noxAction
{
    public function execute()
    {
        $url = $this->getParam('url', '');
        if (!$url)
        {
            return 404;
        }

        $model = new filesFileModel();
        $ar = $model->getByField(array('url' => $url, 'enabled' => 1));

        if (!$ar)
        {
            return 404;
        }

        $model->updateById($ar['id'], array('downloads' => intval($ar['downloads'])+1));
        noxSystem::location($ar['filename']);
    }
}

?>