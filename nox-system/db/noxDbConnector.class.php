<?php
/**
 * noxDbConnector
 *
 * Класс соединения с базой данных
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.0
 * @package    nox-system
 * @subpackage db
 */

class noxDbConnector
{
    /**
     * Адаптеры для баз данных
     *
     * @var array
     */
    protected static $adapters = array();

    /**
     * Возвращает адаптер базы данных. Если соединение не установлено, устанавливает его
     *
     * @param string $name название БД из файла конфигурации
     * @return noxDbAdapter
     * @throws noxException
     */
    public static function getConnection($name = 'default')
    {
        //Соединение уже есть?
        if (isset(self::$adapters[$name]))
        {
            return self::$adapters[$name];
        } else
        {
            //Получаем настройки баз данных сайта
            $config = noxConfig::getDb();
            //Проверяем, существует ли база данных в настройках
            if (!isset($config[$name]))
            {
                throw new noxException('Настройки для базы данных &quot;' . $name . '&quot; не найдены!');
            }
            //Получаем адаптер в соответствии с типом
            $adapter_name = 'noxDb' . $config[$name]['type'] . 'Adapter';
            if (!class_exists($adapter_name, true))
            {
                return false;
            }
            $adapter = new $adapter_name();
            //Соединяемся с БД
            if (!$adapter->connect($config[$name]))
            {
                throw new noxException('Ошибка соединения с базой данных &quot;' . $name . '&quot;!');
            }
            //Сохраняем адаптер
            self::$adapters[$name] = $adapter;
            //Возвращаем адаптер
            return $adapter;
        }
    }

    /**
     * Закрывает все открытые соединения с БД
     */
    public static function closeAll()
    {
        //Для каждого соединения
        foreach (self::$adapters as $name => $adapter)
        {
            $adapter->close();
            unset($adapter);
            unset(self::$adapters[$name]);
        }
    }
}

?>