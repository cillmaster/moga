<?php
class systemAdministratorAtomFeedAction extends noxAction
{
    public $cache = false;
    private static $url;
    private static $media;
    private static $tab = "\t";
    private static $tab2 = "\t\t";

    public $caption = 'atom_feed.xml';

    public function __construct(){
        $config = noxConfig::getConfig();
        self::$url = $config['protocol'] . $config['host'];
        self::$media = new kafMedia();
        parent::__construct();
    }

    public function execute()
    {
        $step = 200;
        if($f = fopen(noxRealPath('atom_feed.xml'), 'w')){
            fwrite($f, '<?xml version="1.0"?>'. PHP_EOL);
            fwrite($f, '<feed xmlns="http://www.w3.org/2005/Atom" xmlns:g="http://base.google.com/ns/1.0">'. PHP_EOL);
            fwrite($f, self::$tab . '<title>Outlines</title>'. PHP_EOL);
            fwrite($f, self::$tab . '<link rel="self" href="' . self::$url . '"/>'. PHP_EOL);
            fwrite($f, self::$tab . '<updated>' . date('c', time()) . '</updated>'. PHP_EOL);

            $model = new printsVectorModel();
            $count = $model->count();
            $model->where(['prepay' => '0']);
            $i = 0;
            do {
                $res = $model->limit($i, $step)->fetchAll();
                foreach($res as $ar) {
                    fwrite($f, self::item($ar));
                }
                $i += $step;
            } while($i < $count);

            fwrite($f, '</feed>'. PHP_EOL);
            fclose($f);
        }
    }

    private static function item($v) {
        $prm = json_decode($v['prm'], 1);
        $img = self::$url . $v['preview'];
        $str = self::$tab . '<entry>' . PHP_EOL;
        $str .= self::$tab2 . '<g:id>' . $v['id'] . '</g:id>' . PHP_EOL;
        $str .= self::$tab2 . '<g:title>'
            . trim(join(' ', [$prm['year'], $prm['make'], $v['name']]))
            . ' blueprints</g:title>' . PHP_EOL;
        $str .= self::$tab2 . '<g:description>Get '
            . trim(join(' ', [$prm['year'], $prm['make'], $v['name'], $v['name_version'], $v['name_spec']]))
            . ' vector blueprints and editable templates.</g:description>' . PHP_EOL;
        $str .= self::$tab2 . '<g:link>' . self::$url . Prints::createUrlForItem($v, Prints::VECTOR, 'drawings') . '</g:link>' . PHP_EOL;
        $str .= self::$tab2 . '<g:image_link>' . $img . '</g:image_link>' . PHP_EOL;
        $str .= self::$tab2 . '<g:brand>Outlines</g:brand>' . PHP_EOL;
        $str .= self::$tab2 . '<g:mpn/>' . PHP_EOL;
        $str .= self::$tab2 . '<g:condition>new</g:condition>' . PHP_EOL;
        $str .= self::$tab2 . '<g:availability>in stock</g:availability>' . PHP_EOL;
        $str .= self::$tab2 . '<g:price>' . number_format($v['price'], 2) . ' USD</g:price>' . PHP_EOL;
        $str .= self::$tab . '</entry>' . PHP_EOL;
        return $str;
    }
}