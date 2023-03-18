<?php
/**
 * Страница администрирования файлов
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    files
 */

class filesAdministratorActions extends noxThemeActions
{
	public $theme = 'administrator';

	public $cache = false;

    /**
     * @var filesFileModel
     */
    private $model;

	public function execute()
	{
		$this->model = new filesFileModel();

		return parent::execute();
	}

	/*
	 * Вывод списка
	 */
	public function actionDefault()
	{
		if (!$this->haveRight('control'))
		{
			return 401;
		}

		$this->caption = 'Файлы';
		$this->addVar('res', $this->model->getAll());
	}

	/*
	 * Добавление файла
	 */
	public function actionAdd()
	{
		if (!$this->haveRight('control'))
		{
			return 401;
		}

		$id = getParam(@$_GET['id']);
		if (!$id)
		{
			$ar = $this->model->getEmptyFields();
		} else
		{
			$ar = $this->model->getById($id);
		}

		//Сохраняем
		if (isset($_POST['submit']) && isset($_POST['new']))
		{
			unset($_POST['new']['id']);
			$_POST['new']['date'] = noxDate::toSql();
            $_POST['new']['url'] = substr(uniqid(md5(rand()), true), 5, 10);

			$this->model->insert($_POST['new']);
			noxSystem::location('?section=administrator');
		}

		$this->caption = 'Добавление файла';

		$this->addVar('ar', $ar);

		$this->templateFileName = $this->moduleFolder . '/templates/backend/administratorEdit.html';
	}

	/*
	 * Редактирование файла
	 */
	public function actionEdit()
	{
		if (!$this->haveRight('control'))
		{
			return 401;
		}

		$id = getParam(@$_GET['id']);
		if (!$id)
		{
			return 400;
		}

		//Активность
		if (isset($_GET['enabled']))
		{
			$enabled = getParam($_GET['enabled']);
			$this->model->updateById($id, array('enabled' => $enabled));

			if ($this->ajax())
			{
				return 200;
			} else
			{
				noxSystem::location('?section=administrator');
			}
		}

		//Сохраняем
		if (isset($_POST['submit']) && isset($_POST['new']))
		{
			$_POST['new']['date'] = noxDate::toSql();
			$this->model->updateById($id, $_POST['new']);
			noxSystem::location('?section=administrator');
		}

		$this->caption = 'Редактирование файла';

		$this->addVar('ar', $this->model->getById($id));

	}

	/*
	 * Удаление файла
	 */
	public function actionDelete()
	{
		if (!$this->haveRight('control'))
		{
			return 401;
		}

		$id = getParam($_GET['id']);
		if (!$id)
		{
			return 400;
		}

		$this->model->deleteById($id);
		if ($this->ajax())
		{
			return 200;
		} else
		{
			noxSystem::location('?section=administrator');
		}
	}
}

?>