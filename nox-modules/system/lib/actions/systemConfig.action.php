<?php
/**
 * Действие для отображения и сохранения настроек
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    system
 * @subpackage config
 */

class systemConfigAction extends noxThemeAction
{
    public $cache = false;

    public $theme = 'administrator';

    public $caption = 'Настройки сайта';

    public function execute()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control'))
        {
            return 401;
        }

        //Получаем текущие настройки
        $config = noxConfig::getConfig();

        if (isset($_POST['new']))
        {
            //Сохраняем изменения
            $config = array_merge($config, $_POST['new']);
            //Сохраняем настройки
            noxConfig::saveConfig($config);
            noxSystem::location();
        }

        //Создаем форму
        $form = new noxForm(noxSystem::$baseUrl.noxSystem::$requestUrl);

        $form->addTextField('Заголовок сайта', 'new[defaultTitle]', @$config['defaultTitle'])->
            addTextArea('Ключевые слова', 'new[defaultKeywords]', @$config['defaultKeywords'])->
            addTextArea('Описание', 'new[defaultDescription]', @$config['defaultDescription'])->
            addTextField('Email администратора', 'new[defaultEmail]', @$config['defaultEmail']);

        $form->addRadioButtons('Кэширование (<a href="?section=config&action=clearcache" onclick="$.get(this.href, function(){ alert(\'Кэш очищен!\'); }); return false;">очистить кэш</a>)', 'new[cache]', @$config['cache']);

        //Получаем список всех шаблонов
        if ($templates = noxConfig::getThemes())
        {
            foreach ($templates as $value => $temp)
            {
                $templates_array[$value] = $temp['name'];
            }
            $form->addSelect('Тема по-умолчанию', 'new[defaultTheme]', @$config['defaultTheme'], $templates_array);
        }

        $form->addGroup('Почтовая рассылка (SMTP)')->
            addTextField('Сервер', 'new[smtp_server]', @$config['smtp_server'])->
            addTextField('Порт', 'new[smtp_port]', @$config['smtp_port'])->
            addTextField('Логин', 'new[smtp_login]', @$config['smtp_login'])->
            addTextField('Пароль', 'new[smtp_password]', @$config['smtp_password'])->
            closeGroup();


        //Список временных зон
        $timezones = array (
            'Pacific/Apia' => '[GMT-11:00] West Samoa Time (MIT)',
            'Pacific/Niue' => '[GMT-11:00] Niue Time (Pacific/ Niue)',
            'Pacific/Tahiti' => '[GMT-10:00] Tahiti Time (Pacific/ Tahiti)',
            'HST' => '[GMT-10:00] Hawaii Standard Time (HST)',
            'America/Adak' => '[GMT-10:00] Hawaii-Aleutian Standard Time (America/ Adak)',
            'Pacific/Fakaofo' => '[GMT-10:00] Tokelau Time (Pacific/ Fakaofo)',
            'Pacific/Rarotonga' => '[GMT-10:00] Cook Is. Time (Pacific/ Rarotonga)',
            'Pacific/Marquesas' => '[GMT-09:30] Marquesas Time (Pacific/ Marquesas)',
            'Pacific/Gambier' => '[GMT-09:00] Gambier Time (Pacific/ Gambier)',
            'America/Anchorage' => '[GMT-09:00] Alaska Standard Time (AST)',
            'Pacific/Pitcairn' => '[GMT-08:00] Pitcairn Standard Time (Pacific/ Pitcairn)',
            'America/Los_Angeles' => '[GMT-08:00] Pacific Standard Time (US & Canada)',
            'America/Phoenix' => '[GMT-07:00] Mountain Standard Time (US/ Arizona)',
            'MST7MDT' => '[GMT-07:00] Mountain Standard Time (US & Canada)',
            'America/Regina' => '[GMT-06:00] Central Standard Time (Canada/ Saskatchewan)',
            'America/Chicago' => '[GMT-06:00] Central Standard Time (US & Canada)',
            'Pacific/Easter' => '[GMT-06:00] Easter Is. Time (Pacific/ Easter)',
            'Pacific/Galapagos' => '[GMT-06:00] Galapagos Time (Pacific/ Galapagos)',
            'America/El_Salvador' => '[GMT-06:00] Central Standard Time (America/ El Salvador)',
            'EST' => '[GMT-05:00] Eastern Standard Time (US & Canada)',
            'America/Porto_Acre' => '[GMT-05:00] Acre Time (America/ Porto Acre)',
            'America/Guayaquil' => '[GMT-05:00] Ecuador Time (America/ Guayaquil)',
            'America/Lima' => '[GMT-05:00] Peru Time (America/ Lima)',
            'America/Bogota' => '[GMT-05:00] Colombia Time (America/ Bogota)',
            'America/Jamaica' => '[GMT-05:00] Eastern Standard Time (America/ Jamaica)',
            'America/Havana' => '[GMT-05:00] Central Standard Time (America/ Havana)',
            'America/Indianapolis' => '[GMT-05:00] Eastern Standard Time (US/ East-Indiana)',
            'America/Glace_Bay' => '[GMT-04:00] Atlantic Standard Time (America/ Glace Bay)',
            'America/Santiago' => '[GMT-04:00] Chile Time (America/ Santiago)',
            'America/Caracas' => '[GMT-04:00] Venezuela Time (America/ Caracas)',
            'Atlantic/Bermuda' => '[GMT-04:00] Atlantic Standard Time (Atlantic/ Bermuda)',
            'America/Asuncion' => '[GMT-04:00] Paraguay Time (America/ Asuncion)',
            'America/Cuiaba' => '[GMT-04:00] Amazon Standard Time (America/ Cuiaba)',
            'America/La_Paz' => '[GMT-04:00] Bolivia Time (America/ La Paz)',
            'Brazil/West' => '[GMT-04:00] Amazon Standard Time (Brazil/ West)',
            'PRT' => '[GMT-04:00] Atlantic Standard Time (PRT)',
            'America/Guyana' => '[GMT-04:00] Guyana Time (America/ Guyana)',
            'Atlantic/Stanley' => '[GMT-04:00] Falkland Is. Time (Atlantic/ Stanley)',
            'America/St_Johns' => '[GMT-03:30] Newfoundland Standard Time (America/ St Johns)',
            'America/Sao_Paulo' => '[GMT-03:00] Brazil Time (BET)',
            'America/Cayenne' => '[GMT-03:00] French Guiana Time (America/ Cayenne)',
            'America/Belem' => '[GMT-03:00] Brazil Time (America/ Belem)',
            'America/Argentina/Buenos_Aires' => '[GMT-03:00] Argentine Time (AGT)',
            'America/Paramaribo' => '[GMT-03:00] Suriname Time (America/ Paramaribo)',
            'America/Miquelon' => '[GMT-03:00] Pierre & Miquelon Standard Time (America/ Miquelon)',
            'America/Godthab' => '[GMT-03:00] Western Greenland Time (America/ Godthab)',
            'Antarctica/Rothera' => '[GMT-03:00] Rothera Time (Antarctica/ Rothera)',
            'America/Montevideo' => '[GMT-03:00] Uruguay Time (America/ Montevideo)',
            'America/Noronha' => '[GMT-02:00] Fernando de Noronha Time (America/ Noronha)',
            'Atlantic/South_Georgia' => '[GMT-02:00] South Georgia Standard Time (Atlantic/ South Georgia)',
            'America/Scoresbysund' => '[GMT-01:00] Eastern Greenland Time (America/ Scoresbysund)',
            'Atlantic/Azores' => '[GMT-01:00] Azores Time (Atlantic/ Azores)',
            'Atlantic/Cape_Verde' => '[GMT-01:00] Cape Verde Time (Atlantic/ Cape Verde)',
            'Europe/Lisbon' => '[GMT+00:00] Western European Time (Europe/ Lisbon)',
            'UTC' => '[GMT+00:00] Coordinated Universal Time (UTC)',
            'Africa/Casablanca' => '[GMT+00:00] Western European Time (Africa/ Casablanca)',
            'GMT' => '[GMT+00:00] Greenwich Mean Time (London/ Dublin)',
            'CET' => '[GMT+01:00] Central European Time (Brussels, Paris, Stockholm, Prague)',
            'Africa/Algiers' => '[GMT+01:00] Central European Time (Africa/ Algiers)',
            'Atlantic/Jan_Mayen' => '[GMT+01:00] Eastern Greenland Time (Atlantic/ Jan Mayen)',
            'Africa/Bangui' => '[GMT+01:00] Western African Time (Africa/ Bangui)',
            'Africa/Windhoek' => '[GMT+01:00] Western African Time (Africa/ Windhoek)',
            'Asia/Jerusalem' => '[GMT+02:00] Israel Standard Time (Asia/ Jerusalem)',
            'Africa/Johannesburg' => '[GMT+02:00] Central African Time (CAT)',
            'EET' => '[GMT+02:00] Eastern European Time (Athens, Beirut, Minsk, Istanbul)',
            'Africa/Tripoli' => '[GMT+02:00] Eastern European Time (Africa/ Tripoli)',
            'Africa/Johannesburg' => '[GMT+02:00] South Africa Standard Time (Africa/ Johannesburg)',
            'Europe/Moscow' => '[GMT+03:00] Moscow Standard Time (Europe/ Moscow)',
            'Asia/Baghdad' => '[GMT+03:00] Arabia Standard Time (Asia/ Baghdad)',
            'Antarctica/Syowa' => '[GMT+03:00] Syowa Time (Antarctica/ Syowa)',
            'Africa/Dar_es_Salaam' => '[GMT+03:00] Eastern African Time (EAT)',
            'Asia/Kuwait' => '[GMT+03:00] Arabia Standard Time (Asia/ Kuwait)',
            'Asia/Tehran' => '[GMT+03:30] Iran Standard Time (Asia/ Tehran)',
            'Indian/Reunion' => '[GMT+04:00] Reunion Time (Indian/ Reunion)',
            'Asia/Tbilisi' => '[GMT+04:00] Georgia Time (Asia/ Tbilisi)',
            'Asia/Dubai' => '[GMT+04:00] Gulf Standard Time (Asia/ Dubai)',
            'Asia/Baku' => '[GMT+04:00] Azerbaijan Time (Asia/ Baku)',
            'Asia/Oral' => '[GMT+04:00] Oral Time (Asia/ Oral)',
            'Indian/Mahe' => '[GMT+04:00] Seychelles Time (Indian/ Mahe)',
            'Europe/Samara' => '[GMT+04:00] Samara Time (Europe/ Samara)',
            'Asia/Yerevan' => '[GMT+04:00] Armenia Time (NET)',
            'Asia/Aqtau' => '[GMT+04:00] Aqtau Time (Asia/ Aqtau)',
            'Indian/Mauritius' => '[GMT+04:00] Mauritius Time (Indian/ Mauritius)',
            'Asia/Kabul' => '[GMT+04:30] Afghanistan Time (Asia/ Kabul)',
            'Asia/Karachi' => '[GMT+05:00] Pakistan Time (PLT)',
            'Indian/Kerguelen' => '[GMT+05:00] French Southern & Antarctic Lands Time (Indian/ Kerguelen)',
            'Asia/Aqtobe' => '[GMT+05:00] Aqtobe Time (Asia/ Aqtobe)',
            'Asia/Ashgabat' => '[GMT+05:00] Turkmenistan Time (Asia/ Ashgabat)',
            'Asia/Tashkent' => '[GMT+05:00] Uzbekistan Time (Asia/ Tashkent)',
            'Indian/Maldives' => '[GMT+05:00] Maldives Time (Indian/ Maldives)',
            'Asia/Yekaterinburg' => '[GMT+05:00] Yekaterinburg Time (Asia/ Yekaterinburg)',
            'Asia/Dushanbe' => '[GMT+05:00] Tajikistan Time (Asia/ Dushanbe)',
            'Asia/Bishkek' => '[GMT+05:00] Kirgizstan Time (Asia/ Bishkek)',
            'IST' => '[GMT+05:30] India Standard Time (IST)',
            'Asia/Katmandu' => '[GMT+05:45] Nepal Time (Asia/ Katmandu)',
            'Asia/Qyzylorda' => '[GMT+06:00] Qyzylorda Time (Asia/ Qyzylorda)',
            'Asia/Novosibirsk' => '[GMT+06:00] Novosibirsk Time (Asia/ Novosibirsk)',
            'BST' => '[GMT+06:00] Bangladesh Time (BST)',
            'Asia/Omsk' => '[GMT+06:00] Omsk Time (Asia/ Omsk)',
            'Asia/Thimbu' => '[GMT+06:00] Bhutan Time (Asia/ Thimbu)',
            'Asia/Almaty' => '[GMT+06:00] Alma-Ata Time (Asia/ Almaty)',
            'Antarctica/Vostok' => '[GMT+06:00] Vostok Time (Antarctica/ Vostok)',
            'Indian/Chagos' => '[GMT+06:00] Indian Ocean Territory Time (Indian/ Chagos)',
            'Asia/Colombo' => '[GMT+06:00] Sri Lanka Time (Asia/ Colombo)',
            'Antarctica/Mawson' => '[GMT+06:00] Mawson Time (Antarctica/ Mawson)',
            'Indian/Cocos' => '[GMT+06:30] Cocos Islands Time (Indian/ Cocos)',
            'Asia/Rangoon' => '[GMT+06:30] Myanmar Time (Asia/ Rangoon)',
            'Asia/Hovd' => '[GMT+07:00] Hovd Time (Asia/ Hovd)',
            'VST' => '[GMT+07:00] Indochina Time (VST)',
            'Indian/Christmas' => '[GMT+07:00] Christmas Island Time (Indian/ Christmas)',
            'Asia/Jakarta' => '[GMT+07:00] West Indonesia Time (Asia/ Jakarta)',
            'Antarctica/Davis' => '[GMT+07:00] Davis Time (Antarctica/ Davis)',
            'Asia/Krasnoyarsk' => '[GMT+07:00] Krasnoyarsk Time (Asia/ Krasnoyarsk)',
            'Asia/Kuala_Lumpur' => '[GMT+08:00] Malaysia Time (Asia/ Kuala Lumpur)',
            'Asia/Makassar' => '[GMT+08:00] Central Indonesia Time (Asia/ Makassar)',
            'Asia/Taipei' => '[GMT+08:00] Taipei Time (Asia/ Taipei)',
            'Asia/Shanghai' => '[GMT+08:00] Shanghai Time (Asia/ Shanghai)',
            'Asia/Singapore' => '[GMT+08:00] Singapore Time (Asia/ Singapore)',
            'Asia/Brunei' => '[GMT+08:00] Brunei Time (Asia/ Brunei)',
            'Asia/Irkutsk' => '[GMT+08:00] Irkutsk Time (Asia/ Irkutsk)',
            'Australia/Perth' => '[GMT+08:00] Western Standard Time (Australia) (Australia/ Perth)',
            'Asia/Manila' => '[GMT+08:00] Philippines Time (Asia/ Manila)',
            'Asia/Ulaanbaatar' => '[GMT+08:00] Ulaanbaatar Time (Asia/ Ulaanbaatar)',
            'Asia/Hong_Kong' => '[GMT+08:00] Hong Kong Time (Asia/ Hong Kong)',
            'Asia/Choibalsan' => '[GMT+09:00] Choibalsan Time (Asia/ Choibalsan)',
            'Asia/Dili' => '[GMT+09:00] East Timor Time (Asia/ Dili)',
            'Pacific/Palau' => '[GMT+09:00] Palau Time (Pacific/ Palau)',
            'Asia/Jayapura' => '[GMT+09:00] East Indonesia Time (Asia/ Jayapura)',
            'Asia/Yakutsk' => '[GMT+09:00] Yakutsk Time (Asia/ Yakutsk)',
            'Asia/Tokyo' => '[GMT+09:00] Japan Standard Time (JST)',
            'Asia/Seoul' => '[GMT+09:00] Korea Standard Time (Asia/ Seoul)',
            'Australia/Adelaide' => '[GMT+09:30] Central Standard Time (South Australia) (Australia/ Adelaide)',
            'Australia/Broken_Hill' => '[GMT+09:30] Central Standard Time (Australia/ Broken Hill)',
            'Australia/Darwin' => '[GMT+09:30] Central Standard Time (Northern Territory) (ACT)',
            'Australia/Hobart' => '[GMT+10:00] Eastern Standard Time (Tasmania) (Australia/ Hobart)',
            'Australia/Brisbane' => '[GMT+10:00] Eastern Standard Time (Queensland) (Australia/ Brisbane)',
            'Pacific/Port_Moresby' => '[GMT+10:00] Papua New Guinea Time (Pacific/ Port Moresby)',
            'Australia/Sydney' => '[GMT+10:00] Eastern Standard Time (New South Wales) (Australia/ Sydney)',
            'Asia/Vladivostok' => '[GMT+10:00] Vladivostok Time (Asia/ Vladivostok)',
            'Australia/Melbourne' => '[GMT+10:00] Eastern Standard Time (Victoria) (Australia/ Melbourne)',
            'Asia/Sakhalin' => '[GMT+10:00] Sakhalin Time (Asia/ Sakhalin)',
            'Pacific/Guam' => '[GMT+10:00] Chamorro Standard Time (Pacific/ Guam)',
            'Pacific/Truk' => '[GMT+10:00] Truk Time (Pacific/ Truk)',
            'Pacific/Yap' => '[GMT+10:00] Yap Time (Pacific/ Yap)',
            'Antarctica/DumontDUrville' => '[GMT+10:00] Dumont-d\'Urville Time (Antarctica/ DumontDUrville)',
            'Australia/Lord_Howe' => '[GMT+10:30] Load Howe Standard Time (Australia/ Lord Howe)',
            'Pacific/Ponape' => '[GMT+11:00] Ponape Time (Pacific/ Ponape)',
            'Pacific/Efate' => '[GMT+11:00] Vanuatu Time (Pacific/ Efate)',
            'Pacific/Noumea' => '[GMT+11:00] New Caledonia Time (Pacific/ Noumea)',
            'Pacific/Kosrae' => '[GMT+11:00] Kosrae Time (Pacific/ Kosrae)',
            'SST' => '[GMT+11:00] Solomon Is. Time (SST)',
            'Asia/Magadan' => '[GMT+11:00] Magadan Time (Asia/ Magadan)',
            'Pacific/Norfolk' => '[GMT+11:30] Norfolk Time (Pacific/ Norfolk)',
            'Pacific/Tarawa' => '[GMT+12:00] Gilbert Is. Time (Pacific/ Tarawa)',
            'Pacific/Fiji' => '[GMT+12:00] Fiji Time (Pacific/ Fiji)',
            'Pacific/Majuro' => '[GMT+12:00] Marshall Islands Time (Pacific/ Majuro)',
            'Asia/Kamchatka' => '[GMT+12:00] Petropavlovsk-Kamchatski Time (Asia/ Kamchatka)',
            'Pacific/Auckland' => '[GMT+12:00] New Zealand Standard Time (Pacific/ Auckland)',
            'Pacific/Wake' => '[GMT+12:00] Wake Time (Pacific/ Wake)',
            'Pacific/Funafuti' => '[GMT+12:00] Tuvalu Time (Pacific/ Funafuti)',
            'Pacific/Nauru' => '[GMT+12:00] Nauru Time (Pacific/ Nauru)',
            'Pacific/Wallis' => '[GMT+12:00] Wallis & Futuna Time (Pacific/ Wallis)',
            'Asia/Anadyr' => '[GMT+12:00] Anadyr Time (Asia/ Anadyr)',
            'Pacific/Chatham' => '[GMT+12:45] Chatham Standard Time (Pacific/ Chatham)',
            'Pacific/Tongatapu' => '[GMT+13:00] Tonga Time (Pacific/ Tongatapu)',
            'Pacific/Enderbury' => '[GMT+13:00] Phoenix Is. Time (Pacific/ Enderbury)',
            'Pacific/Kiritimati' => '[GMT+14:00] Line Is. Time (Pacific/ Kiritimati)',
        );

        $form->addGroup('Время')->
            addText('Y год, m номер месяца, d день (число), H час, i - минута, s - секунда, l название дня недели, F название месяца    ')->
            addSelect('Временная зона', 'new[timezoneSet]', @$config['timezoneSet'], $timezones)->
            addTextField('Формат времени', 'new[timeFormat]', @$config['timeFormat'])->
            addTextField('Формат даты', 'new[dateFormat]', @$config['dateFormat'])->
            addTextField('Формат дата - время', 'new[dateTimeFormat]', @$config['dateTimeFormat'])->
            addTextField('Формат время - дата', 'new[timeDateFormat]', @$config['timeDateFormat'])->
            closeGroup();

        $form->addGroup('Дополнительные параметры')->
            addRadioButtons('Production mode', 'new[is_production]', @$config['is_production'])->
            addRadioButtons('Режим отладки', 'new[debug]', @$config['debug'])->
            addRadioButtons('Отправлять администратору данные об ошибках', 'new[sendEmailOnException]', @$config['sendEmailOnException'])->
            addTextField('Авторизация для домена', 'new[cookie_domain]', @$config['cookie_domain'])->
            addTextField('Локали (через запятую без пробелов)', 'new[locale]', @$config['locale'])->
            addTextField('Локаль по-умолчанию', 'new[defaultLocale]', @$config['defaultLocale'])->
            addTextField('БД пользователей', 'new[userDb]', @$config['userDb'])->
            closeGroup()->
            addSubmitButton('Сохранить', 'save');

        echo (string)$form;
    }
}

?>