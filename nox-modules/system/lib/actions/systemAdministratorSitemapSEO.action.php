<?php
/**
 * Стартовая страница панели администратора
 *
 * @author     Сырчиков Виталий Евгеньевич <maddoger@gmail.com>
 * @version    1.1
 * @package    system
 */

class systemAdministratorSitemapAction extends noxAction
{
    public $cache = false;
    private static $url;
    private static $urlMedia;
    private static $tab = "\t";
    private static $tab2 = "\t\t";
    private static $tab3 = "\t\t\t";

    private static $minCatalogsDate = '2019-06-26T17:00:00+00:00';

    public $caption = 'sitemap.xml';

    public function __construct(){
        $config = noxConfig::getConfig();
        self::$url = $config['protocol'] . $config['host'];
        self::$urlMedia = $config['mediaSrc'];
        parent::__construct();
    }

    public function catalogs(){
        $minDate = strtotime(self::$minCatalogsDate);
        $fn = 'seo/Sitemap_Catalogs.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            fwrite($f, self::item('/blueprints', false, 'monthly', '0.1'));
            fwrite($f, self::item('/vector-drawings', false, 'monthly', '0.1'));

            $categoryModel = new printsCategoryModel();
            $cats = $categoryModel->fetchAll('id', 'url');
            foreach($cats as $c) {
                if($c == 'car'){
                    $changefreq = 'weekly';
                    $priority = '0.8';
                } else {
                    $changefreq = 'monthly';
                    $priority = '0.1';
                }
                fwrite($f, self::item('/' . $c . '-blueprints', false, $changefreq, $priority));
                fwrite($f, self::item('/' . $c . '-vector-drawings', false, $changefreq, $priority));
            }

            $requestVectorModel = new printsRequestVectorModel();
            $dt = $requestVectorModel->order('`request_date` DESC')->limit(1)->fetch('request_date');
            fwrite($f, self::item('/requests', date('c', strtotime($dt)), 'daily', '1.0'));

            $vectorModel = new printsVectorModel();
            $makeModel = new printsMakeModel();
            $makes = $makeModel->fetchAll('class_id', false, true);
            $carsModel = new noxModel(null, 'prints_class_car');
            foreach($cats as $cid=>$c) {
                if(isset($makes[$cid])) {
                    if($c == 'car'){
                        $changefreq = 'daily';
                        $priority = '1.0';
                    } else {
                        $changefreq = 'monthly';
                        $priority = '0.1';
                    }
                    foreach($makes[$cid] as $m) {
                        if(($m['url'] != 'other') || ($c == 'car')){
                            if($c == 'car') {
                                $dtR = $requestVectorModel->where(['category_id' => $cid, 'make_id' => $m['id']])
                                    ->order('`request_date` DESC')->limit(1)->fetch('request_date');
                                $dtR = $dtR ? strtotime($dtR) : 0;
                                $dtV = $vectorModel->where([
                                    'class_id' => 1,
                                    'item_id' => $carsModel->where(['make_id' => $m['id']])->fetchAll(false, 'id')
                                ])->order('`added_date` DESC')->limit(1)->fetch('added_date');
                                $dtV = $dtV ? strtotime($dtV) : 0;
                                $dt = max($dtR, $dtV, $minDate);
                                $lastmod = $dt ? date('c', $dt) : false;
                            } else {
                                $lastmod = false;
                            }
                            fwrite($f, self::item('/' . $c . '-blueprints/' . $m['url'], $lastmod, $changefreq, $priority));
                            fwrite($f, self::item('/' . $c . '-vector-drawings/' . $m['url'], $lastmod, $changefreq, $priority));
                        }
                    }
                }
            }

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function rasters(){
        $step = 200;
        $fn = 'seo/Sitemap_Rasters.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            $model = new printsBlueprintModel();
            $count = $model->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = Prints::createUrlForItem($ar, Prints::BLUEPRINT);
                    $lastmod = date('c', strtotime($ar['update_date']));
                    $changefreq = 'monthly';
                    $priority = '0.6';
                    fwrite($f, self::item($loc, $lastmod, $changefreq, $priority));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function rastersImg(){
        $step = 200;
        $fn = 'seo/Image_Rasters.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL);

            $model = new printsBlueprintModel();
            $count = $model->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = Prints::createUrlForItem($ar, Prints::BLUEPRINT);
                    $name = trim(str_replace('&', 'and', $ar['full_name']));
                        fwrite($f, self::image($loc, [
                        [
                            'loc' => $ar['filename'],
                            'title' => $name . ' blueprints free',
                            'caption' => 'Download ' . $name . ' blueprint free outline images of car.'
                        ]
                    ]));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function requests(){
        $step = 200;
        $fn = 'seo/Sitemap_Requests.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            $model = new printsRequestVectorModel();
            $count = $model->where(['vector_id' => null])->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = Prints::createUrlForItem($ar, Prints::REQUEST_VECTOR, 'drawings');
                    $lastmod = date('c', strtotime($ar['update_date']));
                    $changefreq = 'monthly';
                    $priority = '0.5';
                    fwrite($f, self::item($loc, $lastmod, $changefreq, $priority));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function sets(){
        $step = 200;
        $fn = 'seo/Sitemap_Sets.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            $model = new printsSetModel();
            $modelSetVector = new printsSetVectorModel();
            $modelVector = new printsVectorModel();
            $count = $model->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = '/sets/' . $ar['url'] . '-drawings';
                    $dt = $modelVector->where([
                        'id' => $modelSetVector->getVectorsIdBySet($ar['id'])
                    ])->order('`added_date` DESC')->limit(1)->fetch('added_date');
                    $lastmod = date('c', strtotime($dt));
                    $changefreq = 'daily';
                    $priority = '1.0';
                    fwrite($f, self::item($loc, $lastmod, $changefreq, $priority));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function stat(){
        $fn = 'seo/Sitemap_Static.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            fwrite($f, self::item('/', false, 'daily', '1.0'));
            fwrite($f, self::item('/nox-data/vector/free/opel-corsa-c-3-door-2000.pdf', false, 'monthly', '0.1'));
            fwrite($f, self::item('/search', false, 'monthly', '0.1'));

            $model = new pagesModel();
            $res = $model->where('`sitemap` != \'no\'')->fetchAll();
            foreach ($res as $ar){
                $prm = explode(' ', $ar['sitemap']);
                fwrite($f, self::item($ar['url'], false, $prm[1], $prm[2]));
            }

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function vectors(){
        $step = 200;
        $fn = 'seo/Sitemap_Vectors.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

            $model = new printsVectorModel();
            $count = $model->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = Prints::createUrlForItem($ar, Prints::VECTOR, 'drawings');
                    $lastmod = date('c', strtotime($ar['update_date']));
                    $changefreq = 'daily';
                    $priority = '0.6';
                    fwrite($f, self::item($loc, $lastmod, $changefreq, $priority));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public function vectorsImg(){
        $step = 200;
        $fn = 'seo/Image_Vectors.xml';
        if($f = fopen(noxRealPath($fn), 'w')) {
            fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
            fwrite($f, '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:image="http://www.google.com/schemas/sitemap-image/1.1">' . PHP_EOL);

            $model = new printsVectorModel();
            $count = $model->where(['prepay' => '0'])->count();
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    $loc = Prints::createUrlForItem($ar, Prints::VECTOR, 'drawings');
                    $prm = json_decode($ar['prm'], 1);
                    $nameArr = [];
                    if(isset($prm['year'])) $nameArr[] = $prm['year'];
                    if(isset($prm['make'])) $nameArr[] = $prm['make'];
                    if(isset($ar['name'])) $nameArr[] = $ar['name'];
                    if(isset($ar['name_version'])) $nameArr[] = $ar['name_version'];
                    if(isset($ar['name_spec'])) $nameArr[] = $ar['name_spec'];
                    if(isset($prm['body'])) $nameArr[] = $prm['body'];
                    $name = trim(str_replace('&', 'and', join(' ', $nameArr)));
                    fwrite($f, self::image($loc, [
                        [
                            'loc' => $ar['preview'],
                            'title' => $name . ' blueprint',
                            'caption' => $name . ' blueprints and vector line drawings. Templates for car wrap and 3d.'
                        ]
                    ]));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</urlset>'. PHP_EOL);
            fclose($f);
        }
    }

    public static function item($loc, $lastmod = false, $changefreq = false, $priority = false) {
        $str = self::$tab . '<url>' . PHP_EOL;
        $str .= self::$tab2 . '<loc>' . self::$url . $loc . '</loc>' . PHP_EOL;
        if($lastmod) $str .= self::$tab2 . '<lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
        if($changefreq) $str .= self::$tab2 . '<changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
        if($priority) $str .= self::$tab2 . '<priority>' . $priority . '</priority>' . PHP_EOL;
        $str .= self::$tab . '</url>' . PHP_EOL;
        return $str;
    }

    public static function image($loc, $images = []) {
        $str = self::$tab . '<url>' . PHP_EOL;
        $str .= self::$tab2 . '<loc>' . self::$url . $loc . '</loc>' . PHP_EOL;
        foreach ($images as $img){
            $str .= self::$tab2 . '<image:image>' . PHP_EOL;
            $str .= self::$tab3 . '<image:loc>' . self::$urlMedia . $img['loc'] . '</image:loc>' . PHP_EOL;
            $str .= self::$tab3 . '<image:title>' . $img['title'] . '</image:title>' . PHP_EOL;
            $str .= self::$tab3 . '<image:caption>' . $img['caption'] . '</image:caption>' . PHP_EOL;
            $str .= self::$tab2 . '</image:image>' . PHP_EOL;
        }
        $str .= self::$tab . '</url>' . PHP_EOL;
        return $str;
    }
}