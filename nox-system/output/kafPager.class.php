<?php

class kafPager extends noxTemplate
{
    private $url;

    public function __construct($filename = 'pager.html', $url = '', $get = true){
        $this->url = self::builtGet($url, $get);
        kafConsole::log($url);
        kafConsole::log($this->url);
        parent::__construct('nox-system/output/templates/' . $filename);
    }

    private static function builtGet($url, $get) {
        $preset = noxSystem::$params['get_preset'];
        $prm = [];
        foreach($_GET as $k => $v) {
            if(is_array($v)) {
                foreach($v as $kk => $vv) {
                    if(!isset($preset[$k . ' ' . $kk]) || $_GET[$k][$kk] != $preset[$k . ' ' . $kk])
                        $prm[] = $k . '[' . $kk . ']' . '=' . $vv;
                }
            }
            else {
                if((!isset($preset[$k]) || ($_GET[$k] !== $preset[$k])) && ($k !== 'page'))
                    $prm[] = $k . '=' . $v;
            }
        }
        if($get){
            $prm[] = 'page=###page###';
        }
        $prm = join('&', $prm);

        return $get ? ($url . '?' . $prm) : ($url . '/page###page###?' . $prm);
    }

    private function builtLink($page) {
        return '<a class="page" href="' . str_replace('###page###', $page, $this->url) . '" title="Go to Page ' . $page . '">' . $page . '</a>';
    }

    public function create($items, $step = 100, $depth = 1, $page = null, $anchor = false){
        $page = ($page === null || !is_numeric($page)) ? (int)$_GET['page'] : $page;
        $pages = ceil($items / $step);
        $res = array();
        noxSystem::$console->log($items);
        $index = 0;
        if($page > $depth + 1){
            $res[$index++] = array(
                'type' => 'link',
                'link' => $this->builtLink(1, $anchor)
            );
        };
        if($page > $depth + 2){
            $res[$index++] = array(
                'val' => '...',
                'type' => 'delimiter'
            );
        };
        if($page > 1){
            for ($i = min($page - 1, $depth); $i > 0; $i--){
                $res[$index++] = array(
                    'type' => 'link',
                    'link' => $this->builtLink($page - $i, $anchor)
                );
            }
        };
        $res[$index++] = array(
            'val' => $page,
            'type' => 'active'
        );
        if($pages - $page > 0){
            for ($i = 1; $i <= min($pages - $page, $depth); $i++) {
                $res[$index++] = array(
                    'type' => 'link',
                    'link' => $this->builtLink($page + $i, $anchor)
                );
            }
        };
        if($pages - $page > $depth + 1){
            $res[$index++] = array(
                'val' => '...',
                'type' => 'delimiter'
            );
        };
        if($pages - $page > $depth){
            $res[$index] = array(
                'type' => 'link',
                'link' => $this->builtLink($pages, $anchor)
            );
        };
        $this->addVar('pages', $pages);
        $this->addVar('res', $res);
        return $this->__toString();
    }

    public function create2($items, $step = 100, $depth = 1, $page = null){
        $page = ($page === null || !is_numeric($page)) ? (int)$_GET['page'] : $page;
        $pages = ceil($items / $step);
        if($page > 1){
            $prm = [
                'href' => str_replace('###page###', $page - 1, $this->url),
                'title' => 'Previous Page',
            ];
            $this->addVar('prev', $prm);
        }
        if($page < $pages){
            $prm = [
                'href' => str_replace('###page###', $page + 1, $this->url),
                'title' => 'Next Page',
            ];
            $this->addVar('next', $prm);
        }
        $chain = $depth * 2 + 1;
        $rest = $pages - $page;
        $res = array();
        noxSystem::$console->log($items);
        if($page > $depth + 1 && $pages > $chain){
            $res[] = [
                'type' => 'link',
                'link' => $this->builtLink(1)
            ];
        };
        if($page > $depth + 2 && $pages > $chain + 1){
            $res[] = [
                'val' => '...',
                'type' => 'delimiter'
            ];
        };
        if($page > 1){
            $length = min($page - 1, $depth + (($rest < $depth) ? $depth - $rest : 0));
            for ($i = $length; $i > 0; $i--){
                $res[] = [
                    'type' => 'link',
                    'link' => $this->builtLink($page - $i)
                ];
            }
        };
        $res[] = array(
            'val' => $page,
            'type' => 'active'
        );
        if($rest > 0){
            $length = min($rest, $depth + (($page < $depth) ? $depth - $page : 0));
            for ($i = 1; $i <= $length; $i++) {
                $res[] = array(
                    'type' => 'link',
                    'link' => $this->builtLink($page + $i)
                );
            }
        };
        if($rest > $depth + 1 && $pages > $chain + 1){
            $res[] = array(
                'val' => '...',
                'type' => 'delimiter'
            );
        };
        if($rest > $depth && $pages > $chain){
            $res[] = array(
                'type' => 'link',
                'link' => $this->builtLink($pages)
            );
        };
        $this->addVar('pages', $pages);
        $this->addVar('res', $res);
        return $this->__toString();
    }
}