<?php
return array(
    'title'       => 'Модуль файлового обмена',
    'version'     => '1.0',
    'description' => 'Позволяет генерировать короткие ссылки на файлы и вести подсчет скачиваний.',
    'author'      => 'Сырчиков Виталий Евгеньевич <maddoger@gmail.com>',
	'rights'      => array('control' => 'Управление модулем'),
    'frontend'    => true,
    'backend'     => true,
	'menu'  => array(
		array(
			'title' => 'Файлы',
			'link' => '/administrator/files',
		),
	),
);
?>