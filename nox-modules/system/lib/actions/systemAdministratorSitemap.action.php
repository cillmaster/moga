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
    private static $tab = "\t";

    public $caption = 'sitemap.xml';

    public function __construct(){
        $config = noxConfig::getConfig();
        self::$url = $config['protocol'] . $config['host'];
        parent::__construct();
    }

    public function execute()
    {

        $tm = date('c', time());
        $step = 200;
        if($F = fopen(noxRealPath('sitemap.xml'), 'w')){
            fwrite($F, '<?xml version="1.0" encoding="UTF-8"?>'. PHP_EOL);
            fwrite($F, '<sitemapindex xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd">'. PHP_EOL);
            $list = [];

            $fn = 'sitemap_0.xml';
            if($f = fopen(noxRealPath($fn), 'w')) {
                $list[] = $fn;
                fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
                fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

                fwrite($f, self::item('/', false, 'daily', '1.0'));
                fwrite($f, self::item('/requests', false, 'daily', '1.0'));
                fwrite($f, self::item('/blueprints', false, 'monthly', '1.0'));
                fwrite($f, self::item('/vector-drawings', false, 'monthly', '1.0'));
                fwrite($f, self::item('/search', false, 'monthly', '0.5'));

                $categoryModel = new printsCategoryModel();
                $cats = $categoryModel->fetchAll('id', 'url');
                foreach($cats as $c) {
                    fwrite($f, self::item('/' . $c . '-blueprints', false, 'weekly', '1.0'));
                    fwrite($f, self::item('/' . $c . '-vector-drawings', false, 'weekly', '1.0'));
                }

                $makeModel = new printsMakeModel();
                $requestVectorModel = new printsRequestVectorModel();
                $makes = $makeModel->fetchAll('class_id', false, true);
                foreach($cats as $cid=>$c) {
                    if(isset($makes[$cid])) {
                        foreach($makes[$cid] as $m) {
                            if($m['vectors_count']) {
                                fwrite($f, self::item('/' . $c . '-blueprints/' . $m['url'], false, 'daily', '1.0'));
                                fwrite($f, self::item('/' . $c . '-vector-drawings/' . $m['url'], false, 'daily', '1.0'));
                            }
                            elseif($m['blueprints_count']) {
                                fwrite($f, self::item('/' . $c . '-blueprints/' . $m['url'], false, 'daily', '1.0'));
                            }
                            else {
                                if($requestVectorModel->reset()->where(['category_id' => $cid, 'make_id' => $m['id']])->count()) {
                                    fwrite($f, self::item('/' . $c . '-blueprints/' . $m['url'], false, 'daily', '1.0'));
                                    fwrite($f, self::item('/' . $c . '-vector-drawings/' . $m['url'], false, 'daily', '1.0'));
                                }
                            }
                        }
                    }
                }

                $model = new printsSetModel();
                $count = $model->count();
                $i = 0;
                do {
                    $res = $model->limit($i, $step)->fetchAll();
                    foreach($res as $ar) {
                        fwrite($f, self::item('/sets/' . $ar['url'] . '-drawings', false, 'daily', '1.0'));
                    }
                    $i += $step;
                } while($i < $count);

                $model = new printsVectorModel();
                $count = $model->count();
                $i = 0;
                do {
                    $res = $model->limit($i, $step)->fetchAll();
                    foreach($res as $ar) {
                        fwrite($f, self::item(Prints::createUrlForItem($ar, Prints::VECTOR, 'drawings'), false, 'weekly', '0.75'));
                    }
                    $i += $step;
                } while($i < $count);

                fwrite($f, '</urlset>'. PHP_EOL);
                fclose($f);
            }

            $fn = 'sitemap_1.xml';
            if($f = fopen(noxRealPath($fn), 'w')) {
                $list[] = $fn;
                fwrite($f, '<?xml version="1.0" encoding="UTF-8"?>' . PHP_EOL);
                fwrite($f, '<urlset xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xsi:schemaLocation="http://www.sitemaps.org/schemas/sitemap/0.9">' . PHP_EOL);

                $model = new printsRequestVectorModel();
                $count = $model->count();
                $i = 0;
                do {
                    $res = $model->limit($i, $step)->fetchAll();
                    foreach($res as $ar) {
                        fwrite($f, self::item(Prints::createUrlForItem($ar, Prints::REQUEST_VECTOR, 'drawings'), false, 'weekly', '0.5'));
                    }
                    $i += $step;
                } while($i < $count);

                $model = new printsBlueprintModel();
                $count = $model->count();
                $i = 0;
                do {
                    $res = $model->limit($i, $step)->fetchAll();
                    foreach($res as $ar) {
                        fwrite($f, self::item(Prints::createUrlForItem($ar, Prints::BLUEPRINT), false, 'weekly', '0.25') . PHP_EOL);
                    }
                    $i += $step;
                } while($i < $count);

                fwrite($f, '</urlset>'. PHP_EOL);
                fclose($f);
            }

            foreach ($list as $fn){
                fwrite($F, self::$tab . '<sitemap>' . PHP_EOL);
                fwrite($F, self::$tab . self::$tab . '<loc>' . self::$url . '/' . $fn . '</loc>' . PHP_EOL);
                fwrite($F, self::$tab . self::$tab . '<lastmod>' . $tm . '</lastmod>' . PHP_EOL);
                fwrite($F, self::$tab . '</sitemap>' . PHP_EOL);
            }

            fwrite($F, '</sitemapindex>'. PHP_EOL);
            fclose($F);
        }
    }

    public static function item($loc, $lastmod = false, $changefreq = false, $priority = false) {
        $str = self::$tab . '<url>' . PHP_EOL;
        $str .= self::$tab . self::$tab . '<loc>' . self::$url . $loc . '</loc>' . PHP_EOL;
        if($lastmod) $str .= self::$tab . self::$tab . '<lastmod>' . $lastmod . '</lastmod>' . PHP_EOL;
        if($changefreq) $str .= self::$tab . self::$tab . '<changefreq>' . $changefreq . '</changefreq>' . PHP_EOL;
        if($priority) $str .= self::$tab . self::$tab . '<priority>' . $priority . '</priority>' . PHP_EOL;
        $str .= self::$tab . '</url>' . PHP_EOL;
        return $str;
    }
}