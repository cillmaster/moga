<?php

class application extends noxApplication {

    public function run() {
        if(noxSystem::$urlArray[0] === 'administrator') {
            // Backend
            noxLocale::setLocale('ru');
        }
        else {
            // Frontend
            require noxRealPath('\nox-modules\utm\lib\models\utm.model.php');
            $utmModel = new utmModel();
            $utmModel->hookUtm();
        }

        parent::run();
    }
}
class URLTools {
    public static function builtGet($get) {
        $str = '';

        if(!$get || !is_array($get)) return $str;
        foreach($get as $k=>$v) {
            if(is_array($v)) {
                foreach($v as $kk=>$vv) {
                    $str .= $k . '[' . $kk . ']' . '=' . $vv . '&';
                }
            }
            else {
                $str .= $k . '=' . $v . '&';
            }
        }

        return '?' . substr($str, 0, -1);
    }

    public static function string2url($str) {
        $str = str_replace(['(', ')'], '', $str);
        return urlencode(strtolower(str_replace([' ', '_', '/'], '-', rtrim($str))));
    }
}
class Views {
    private static $VIEWS = [
        'Front' => 1,
        'Top' => 2,
        'Rear' => 4,
        'Side' => 8,
    ];

    /**
     * @param $views int
     * @return string
     */
    public static function getViewsInHtml($views, $fullNames = false) {
        $html = '';
        foreach(self::$VIEWS as $v=>$val) {
            $textValue = ($fullNames) ? $v : $v[0];
            $html .= '<span class="' . (($views & $val) ? 'ui-view_active' : 'ui-view_unactive') . '">' . $textValue . '</span> ';
        }
        return '<!--googleoff: all--><!--noindex-->' . $html . '<!--/noindex--><!--googleon: all-->';
    }

    public static function getViewsForAdmin($views, $id = false) {
        $views = (int)$views;
        $html = '';
        if($id) {
            foreach(self::$VIEWS as $v=>$val) {
                $html .= '<label>' . $v[0] . '&nbsp;<input type="checkbox" name="new[' . $id . '][views][' . $v . ']"' . (($views & $val) ? ' checked' : '') . '></label>&nbsp;&nbsp;&nbsp;';
            }
        }
        else {
            foreach(self::$VIEWS as $v=>$val) {
                $html .= '<label>' . $v[0] . '&nbsp;<input type="checkbox" name="new[views][' . $v . ']"' . (($views & $val) ? ' checked' : '') . '></label>&nbsp;&nbsp;&nbsp;';
            }
        }
        return $html;
    }

    public static function array2int($ar) {
        $i = 0;
        if(is_array($ar)) {
            foreach(self::$VIEWS as $v=>$val) {
                if(isset($ar[$v]) && $ar[$v]) $i += $val;
            }
        }

        return $i;
    }
}

class Prints {
    const BLUEPRINT = 1;
    const VECTOR = 2;
    const PREPAY = 5;
    const REQUEST_VECTOR = 9;
    const SET_VECTOR = 20;

    const HTML_ICON_FREE = '<span class="icon-free">FREE</span>';
    const HTML_ICON_VECTOR = '<span class="icon-vector">VECTOR</span>';
    const HTML_ICON_VECTOR_FILL = '<span class="icon-vector-fill">VECTOR</span>';
    const HTML_ICON_PREPAY = '<span class="icon-vector">PREPAY</span>';
    const HTML_ICON_VECTOR_REQUEST = '<span class="icon-vector-request">REQUESTED</span>';
    const HTML_ICON_VECTOR_REQUEST_LOCKED = '<span class="icon-vector-request">LOCKED REQUEST</span>';

    public static $sectionUrl = [
        1 => 'blueprints',
        2 => 'vector-drawings',
        5 => 'vector-drawings',
        9 => 'requests',
        20 => 'sets'
    ];

    public static $lastUrl = [
        1 => 'blueprints',
        2 => 'drawings',
        5 => 'drawings',
        9 => 'drawings',
        20 => 'drawings'
    ];

    public static $vectorFormats = [
        'PDF' => 'Portable Document Format 1.5 (Acrobat 6)',
        'EPS' => 'Encapsulated Postscript',
        'AI'  => 'Compatible with Adobe Illustrator CS4 / CS5 / CS6 and newer',
        'SVG' => 'Scalable Vector Graphics 1.1',
        'DWG' => 'Compatible with AutoCAD 2004 / 2005 / 2006 and newer',
        'DXF' => 'Drawing Exchange Format (AutoCAD 2004 / 2005 / 2006 and newer)'
    ];

    public static function getVectorUrlFromRaw($vectorUrl) {

        static $vectorLastUrls = [
            'drawings'          => 'drawings',
            'blueprints'        => 'blueprints',
            'templates-wrap'    => 'templates wrap',
            'vinyl-signwrites'  => 'vinyl signwrites',
            'vector-clip-art'   => 'vector clip-art'
        ];

        foreach($vectorLastUrls as $vu=>$vt) {
            $vuLength = strlen($vu);
            if($vu === substr($vectorUrl, strlen($vectorUrl) - $vuLength, $vuLength)) {
                return [
                    'vectorUrl' => substr($vectorUrl, 0, -$vuLength-1), // -1 чтобы удалить дефис-разделитель
                    'typeUrl' => $vu,
                    'typeTitle' => $vt
                ];
            }
        }

        return false;
    }

    public static $requestTypes = [
        1 => 'Новый',
        7 => 'Свой',
        8 => 'Делаем',
        12 => 'Сделан',
        17 => 'Не можем'
    ];

    public static $requestVoteTypes = [
        1 => 'Новый',
        12 => 'Email',
        9 => 'Facebook0',
        10 => 'Facebook1',
        2 => 'Пропускаем',
        3 => 'Не подходит',
        13 => 'Прошел срок',
        14 => 'Интересно',
        15 => 'Купил',
        16 => 'Linked',
        17 => 'Не можем',
        18 => 'Не машина',
        19 => 'Дублер'
    ];

    public static function getSetOptions() {
        return (new printsSetModel())->getAllNames();
    }
    /**
     *
     * @param $item array
     * @param $type int
     * @param $urlType bool
     * @return string
     */
    public static function createUrlForItem($item, $type, $urlType = false) {
        switch($type) {
            case Prints::SET_VECTOR:
                return '/' . self::$sectionUrl[$type] . '/' . $item['url']  . '-' . self::$lastUrl[$type];
            default:
                return '/' . self::$sectionUrl[$type] . '/' . $item['id'] . '/' . $item['url'] . '-'
                . (($urlType) ? $urlType : self::$lastUrl[$type]);
        }
     }

    /**
     *
     * @param $ar mixed
     * @param $catData mixed
     * @param $type int
     * @return string
     */
    public static function generateSEOUrl($ar, $catData = false, $type = Prints::BLUEPRINT) {
        if(!is_string($ar)) {
            $url = '';

            if(isset($catData['year']) && $catData['year']) {
                $url .= $catData['year'] . '-';
            }
            if(isset($catData['end']) && $catData['end']) {
                $url .= $catData['end'] . '-';
            }
            if(isset($catData['make_id']) && $catData['make_id']) {
                static $makeModel;
                if(!$makeModel) $makeModel = new printsMakeModel();

                $makeUrl = $makeModel->select('url')->where('id', $catData['make_id'])->fetch('url');
                $url .= $makeUrl . '-';
            }
            if($ar['name']) {
                $url .= $ar['name'] . '-';
            }
            if(isset($catData['body']) && $catData['body'] && ($type !== Prints::VECTOR)) {
                $url .= $catData['body'] . '-';
            }

            if($url !== '') {
                $url = substr($url, 0, -1);
            }
        }
        else {
            $url = &$ar;
        }

        return URLTools::string2url($url);
    }

    public static function generateFullName($ar, $catData) {
        $fullname = '';
        if(isset($catData['year'])) {
            if($catData['year'] != '0000') {
                $fullname .= $catData['year'] . ' ';
            }
        }
        if(isset($catData['end'])) {
            if($catData['end'] != '0000' AND $catData['end'] != '') {
                $fullname .= '- ' . $catData['end'] . ' ';
            }
        }
        if(isset($catData['make_id']) && $catData['make_id']) {
            static $makeModel;
            if(!$makeModel) {
                $makeModel = new printsMakeModel();
            }
            $fullname .= $makeModel->where('id', $catData['make_id'])->fetch('name') . ' ';
        }
        if(isset($ar['name']) && $ar['name']) {
            $fullname .= $ar['name'] . ' ';
        }
        if(isset($ar['name_version']) && $ar['name_version']) {
            $fullname .= $ar['name_version'] . ' ';
        }
        if(isset($ar['name_spec']) && $ar['name_spec']) {
            $fullname .= $ar['name_spec'] . ' ';
        }
        if(isset($catData['body']) && $catData['body']) {
            $fullname .= $catData['body'] . ' ';
        }
        if($fullname) {
            $fullname = substr($fullname, 0, -1);
        }

        return $fullname;
    }

    public static function generatePrm($catData){
        $prm = array();
        $prm['year'] = (isset($catData['year']) && $catData['year'] != '0000') ? $catData['year'] : '';
        $prm['end'] = (isset($catData['end']) && $catData['end'] != '0000') ? $catData['end'] : '';
        $prm['body'] = (isset($catData['body']) && $catData['body']) ? $catData['body'] : '';
        if(isset($catData['make_id']) && $catData['make_id']) {
            static $makeModel;
            if(!$makeModel) {
                $makeModel = new printsMakeModel();
            }
            $prm['make'] = $makeModel->where('id', $catData['make_id'])->fetch('name');
        }else{
            $prm['make'] = '';
        }
        return json_encode($prm);
    }

    public static function generateSortName($ar, $catData) {
        $fullname = '';

        if(isset($catData['make_id']) && $catData['make_id']) {
            static $makeModel;
            if(!$makeModel) {
                $makeModel = new printsMakeModel();
            }
            $fullname .= $makeModel->where('id', $catData['make_id'])->fetch('name') . ' ';
        }
        if(isset($ar['name']) && $ar['name']) {
            $fullname .= $ar['name'] . ' ';
        }
        if(isset($ar['name_version']) && $ar['name_version']) {
            $fullname .= $ar['name_version'] . ' ';
        }
        if(isset($ar['name_spec']) && $ar['name_spec']) {
            $fullname .= $ar['name_spec'] . ' ';
        }
        if(isset($catData['body']) && $catData['body']) {
            $fullname .= $catData['body'] . ' ';
        }
        if($fullname) {
            $fullname = substr($fullname, 0, -1);
        }

        return $fullname;
    }
}

class Users {
    const businessGroupId = 4;
}

function _headerMovedPermanently($url) {
    header("HTTP/1.1 301 Moved Permanently");
    header("Location: " . $url);
    exit();
}

if(!function_exists('array_column')) {
    /**
     * @param Array $ar
     * @param String $col
     * @param bool $unique
     * @return array
     */
    function array_column($ar, $col, $unique = true) {
        $res = [];

        if(!isset(reset($ar)[$col])) return $res;
        if($unique) {
            foreach($ar as $arr) {
                $res[$arr[$col]] = $arr[$col];
            }
            $res = array_values($res);
        }
        else {
            foreach($ar as $arr) {
                $res[] = $arr[$col];
            }
        }
        return $res;
    }
}
/**
 * @param $categoryId int
 * @param $model noxModel
 * @return array
 */
function getLatinAlphabet($categoryId, $model) {
    if($model instanceof printsBlueprintModel) {
        $model->exec('SELECT UPPER(SUBSTRING( name, 1, 1 )) AS "letter" FROM `prints_blueprint`  WHERE class_id = ' . $categoryId . ' GROUP BY `letter`');
        $a = $model->fetchAll('letter', 'letter');

        $model->exec('SELECT UPPER(SUBSTRING( name, 1, 1 )) AS "letter" FROM `prints_vector`  WHERE class_id = ' . $categoryId . ' GROUP BY `letter`');
        $ar = $model->fetchAll('letter', 'letter');
        $a = $a + $ar;

        $model->exec('SELECT UPPER(SUBSTRING( name, 1, 1 )) AS "letter" FROM `prints_request_vector`  WHERE category_id = ' . $categoryId . ' GROUP BY `letter`');
        $ar = $model->fetchAll('letter', 'letter');
        $a = $a + $ar;
    }
    elseif($model instanceof printsVectorModel) {
        $model->exec('SELECT UPPER(SUBSTRING( name, 1, 1 )) AS "letter" FROM `prints_vector`  WHERE class_id = ' . $categoryId . ' GROUP BY `letter`');
        $a = $model->fetchAll('letter', 'letter');

        $model->exec('SELECT UPPER(SUBSTRING( name, 1, 1 )) AS "letter" FROM `prints_request_vector`  WHERE category_id = ' . $categoryId . ' GROUP BY `letter`');
        $ar = $model->fetchAll('letter', 'letter');
        $a = $a + $ar;
    }
    else {
        $a = [];
    }

    return $a;
}
$GLOBALS['nox'] = [
    'ui-windows' => []
];

function addWindows($wnds) {
    $wnds = explode(',', $wnds);
    foreach($wnds as $name) {
        $GLOBALS['nox']['ui-windows'][trim($name)] = true;
    }
}
function requireWindows() {
    $source = '';
    foreach($GLOBALS['nox']['ui-windows'] as $name=>$_) {
        $source .= file_get_contents(noxRealPath('/nox-modules/users/templates/windows/' . $name . '.html'));
    }
    return $source;
}
if(!isset($GLOBALS['cron'])) {
    noxSystem::authorization() || addWindows('auth, reg_main, reg_fb, reg_email, reset_pass_1, reset_pass_2');
}
