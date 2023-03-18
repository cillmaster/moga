<?php

class printsVectorModel extends noxModel
{
    public $table = 'prints_vector';

    public function getRelated($count, $where = [], $where2 = []) {
        $this->reset();
        $where['prepay'] = 0;
        $where2['prepay'] = 0;
        $res = $this->getRand($count, $where);
        $curCount = sizeof($res);
        if($curCount < $count) {
            $res = array_merge($res, $this->getRand($count - $curCount, $where2, array_column($res, 'id')));
        }
        $curCount = sizeof($res);
        if($curCount < $count) {
            $res = array_merge($res, $this->getRand($count - $curCount, [], array_column($res, 'id')));
        }
        foreach ($res as &$row)
            $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
        return $res;
    }

    public function getRelatedForItem($count, $for, $vector = true) {
        $res = $this->__getRelatedForItem($count, $for, $vector);
        foreach ($res as &$row) {
            $row['preview'] = isset($row['preview']) ? noxSystem::$media->srcMini($row['preview']) : '';
        }
        array_shift($res);
        return $res;
    }

    private function __getRelatedForItem($count, $for, $vector) {
        $categoryModelTable = 'prints_class_' . (new printsCategoryModel())->where('id', $for['class_id'])->fetch('db_table');

        if($vector) {
            $count++;
            $res = [$for];
        }
        else {
            $res = [];
        }
        /*
            0) set
            1) make + class + body
            2) make + class
            3) class + body
            4) make + body
            5) make
            6) body
            7) все cars
         */

        if(isset($for['set_id'])) {
            $rCount = $count - sizeof($res);
            $where = 'prepay=0 AND ' . sprintf('id IN(select vector_id from `%s` where set_id = %s)', 'prints_set_vector', $for['set_id']);
            $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
            if($pq) {
                $res = array_merge($res, $pq);
                if($count === sizeof($res)) {
                    return $res;
                }
            }
        }

        if(isset($for['make_id'])) {
            if(isset($for['body']) && isset($for['class'])) {
                $rCount = $count - sizeof($res);
                $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where make_id = %s AND class = "%s" AND body = "%s")', $categoryModelTable, $for['make_id'], $for['class'], $for['body']);
                $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
                if($pq) {
                    $res = array_merge($res, $pq);
                    if($count === sizeof($res)) {
                        return $res;
                    }
                }
            }

            if(isset($for['class'])) {
                $rCount = $count - sizeof($res);
                $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where make_id = %s AND class = "%s")', $categoryModelTable, $for['make_id'], $for['class']);
                $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
                if($pq) {
                    $res = array_merge($res, $pq);
                    if($count === sizeof($res)) {
                        return $res;
                    }
                }
            }
        }

        if(isset($for['body']) && isset($for['class'])) {
            $rCount = $count - sizeof($res);
            $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where class = "%s" AND body = "%s")', $categoryModelTable, $for['class'], $for['body']);
            $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
            if($pq) {
                $res = array_merge($res, $pq);
                if($count === sizeof($res)) {
                    return $res;
                }
            }
        }


        if(isset($for['make_id'])) {
            if(isset($for['body'])) {
                $rCount = $count - sizeof($res);
                $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where make_id = %s AND body = "%s")', $categoryModelTable, $for['make_id'], $for['body']);
                $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
                if($pq) {
                    $res = array_merge($res, $pq);
                    if($count === sizeof($res)) {
                        return $res;
                    }
                }
            }

            $rCount = $count - sizeof($res);
            $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where make_id = %s)', $categoryModelTable, $for['make_id']);
            $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
            if($pq) {
                $res = array_merge($res, $pq);
                if($count === sizeof($res)) {
                    return $res;
                }
            }
        }

        if(isset($for['body'])) {
            $rCount = $count - sizeof($res);
            $where = 'prepay=0 AND ' . sprintf('item_id IN(select id from `%s` where body = "%s")', $categoryModelTable, $for['body']);
            $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
            if($pq) {
                $res = array_merge($res, $pq);
                if($count === sizeof($res)) {
                    return $res;
                }
            }
        }

        //Category
        $rCount = $count - sizeof($res);
        $where = [
            'class_id' => (isset($for['class_id']) ? $for['class_id'] : $for['category_id']),
            'prepay' => 0
        ];
        $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
        if($pq) {
            $res = array_merge($res, $pq);
            if($count === sizeof($res)) {
                return $res;
            }
        }

        $rCount = $count - sizeof($res);
        $pq = $this->getRand($rCount, $where, array_column($res, 'id'));
        if($pq) {
            $res = array_merge($res, $pq);
            if($count === sizeof($res)) {
                return $res;
            }
        }

        $rCount = $count - sizeof($res);
        $pq = $this->getRand($rCount, [], array_column($res, 'id'));
        if($pq) {
            $res = array_merge($res, $pq);
        }

        return $res;
    }

    public function downloaded($id) {
        $this->exec('UPDATE ' . $this->table . ' SET downloads_count = downloads_count+1 WHERE id = ' . intval($id));
    }

    public static function viewVectorPreview($v, $urlType = false, $extra = '') {
        $url = Prints::createUrlForItem($v, Prints::VECTOR, $urlType);
        $turlType = str_replace('-', ' ', $urlType);
        $price = [(int)$v['price'], ($v['price']-(int)$v['price'])*100];
        $v['price'] = $price[0] . (($price[1]) ? '<sup>.' . $price[1] . '</sup>' : '');
        $prm = isset($v['prm']) ? json_decode($v['prm']) : new stdClass();
        $per = $prm->year . ' - ' . (!empty($prm->end) ? $prm->end : 'Present');
        $name = $prm->make . ' ' . $v['name'];
        $sub_name2 = $v['name_spec'] . ' ' . $prm->body;
        $prepaySize = preg_match('/large/', $extra) ? 448 : 208;

        $tmplReady = '<div class="block vector-block-preview %17$s">
                <div class="cont">
                    <a href="%1$s" title="%14$s %15$s blueprint and drawing">
                        <img src="%3$s" alt="%14$s %15$s blueprint">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s</div>
                        <div class="orange">$%4$s</div>
                    </div>
                </div>
                <div title="%14$s %15$s blueprint and drawing" class="vector-mark"></div>
                <div class="popup">
                    <div class="info">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s %11$s</div>
                    </div>
                    <cart-oper ind="%5$s" price="%4$s" mode="%19$s"></cart-oper>
                </div>
            </div>';

        $tmplPrepay = '<div class="block vector-block-preview %17$s">
                <div class="cont">
                    <a href="%1$s" title="%14$s %15$s blueprint and drawing" class="prepay-link-cont">
                        <img src="/nox-themes/default/images/prepay-preview%18$s.png" alt="%14$s %15$s blueprint" class="prepay-image">
                        <img src="/nox-themes/default/images/prepay-magic%18$s.png" alt="%14$s %15$s blueprint" class="prepay-image-hover">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s</div>
                        <div class="orange">$%4$s</div>
                    </div>
                </div>
                <div class="popup">
                    <div class="info">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s %11$s</div>
                    </div>
                    <cart-oper ind="%5$s" price="%4$s" mode="%19$s"></cart-oper>
                </div>
            </div>';

        return $v['prepay']
            ? sprintf($tmplPrepay,
                $url,
                $v['full_name'],
                $v['preview'],
                $v['price'],
                $v['id'],
                $turlType,
                'Make pre-payment',
                $per,
                $name,
                $v['name_version'],
                $sub_name2,
                $v['prepay'],
                time(),
                $prm->year,
                $v['sort_name'],
                noxSystem::$prepayMaxDays,
                $extra,
                $prepaySize,
                isset(noxSystem::$cartItems[$v['id']]) ? 'inCart' : ''
            )
            : sprintf($tmplReady,
                $url,
                $v['full_name'],
                $v['preview'],
                $v['price'],
                $v['id'],
                $turlType,
                'Download for',
                $per,
                $name,
                $v['name_version'],
                $sub_name2,
                $v['prepay'],
                time(),
                $prm->year,
                $v['sort_name'],
                noxSystem::$prepayMaxDays,
                $extra,
                $prepaySize,
                isset(noxSystem::$cartItems[$v['id']]) ? 'inCart' : ''
            );
    }

    public static function viewVectorPreviewPurchased($v, $urlType = false, $extra = '') {
        $url = Prints::createUrlForItem($v, Prints::VECTOR, $urlType);
        $turlType = str_replace('-', ' ', $urlType);
        $price = [(int)$v['price'], ($v['price']-(int)$v['price'])*100];
        $v['price'] = $price[0] . (($price[1]) ? '<sup>.' . $price[1] . '</sup>' : '');
        $prm = isset($v['prm']) ? json_decode($v['prm']) : new stdClass();
        $per = $prm->year . ' - ' . (!empty($prm->end) ? $prm->end : 'Present');
        $name = $prm->make . ' ' . $v['name'];
        $sub_name2 = $v['name_spec'] . ' ' . $prm->body;
        $tmplReady = '<div class="block vector-block-preview %17$s">
                <div class="cont">
                    <a href="%1$s" title="%14$s %15$s blueprint and drawing">
                        <img src="%3$s" alt="%14$s %15$s blueprint">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s</div>
                        <div class="orange">%4$s</div>
                    </div>
                </div>
                <div title="%14$s %15$s blueprint and drawing" class="vector-mark"></div>
                <div class="popup">
                    <div class="info">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s %11$s</div>
                    </div>
                    <div style="font-size: 14px;">
                        <a class="btn hover" href="/download/vector/%5$s?prm=%13$s">
                            <img src="/nox-themes/default/images/ui_icon-arrow.png" width="16" height="13">
                            &nbsp;&nbsp;<span>%7$s</span>
                        </a>
                    </div>
                    <div style="font-size: 14px; color: #f28d4f; line-height: 20px; padding: 8px 0;" class="makeTopView has-hint"
                        title="Your extra option - Top View - is under development now. Please wait for email notification when it\'s gone or visit this page to check order status.">
                        <img src="/nox-themes/default/images/under-development.gif" width="20" height="20" style="width: 20px; margin: 0;">
                        <span>We make Top View</span>
                    </div>
                </div>
            </div>';

        $tmplPrepay = '<div class="block vector-block-preview %17$s">
                <div class="cont">
                    <a href="%1$s" title="%14$s %15$s blueprint and drawing" class="prepay-link-cont">
                        <img src="/nox-themes/default/images/prepay-preview208.png" alt="%14$s %15$s blueprint">
                    </a>
                    <div class="info bottom">
                        <div class="period">%8$s</div>
                        <div class="name"><strong class="one-str">%9$s</strong></div>
                        <div class="subname">%10$s</div>
                        <div style="font-size: 14px; color: #f28d4f; line-height: 20px; padding: 8px 0;" class="has-hint" 
                            title="Your prepaid blueprint is under development now. It will be ready for sure. Estimated delivery date is up to %16$s work days. But sometimes delay may occur. Please contact us if you need it ASAP. Thank you.">
                            <img src="/nox-themes/default/images/under-development.gif" width="20" height="20" style="width: 20px; margin: 0;">
                            <span>%7$s</span>
                        </div>
                    </div>
                </div>
            </div>';

        return !$v['prepay']
            ? sprintf($tmplReady,
                $url,
                $v['full_name'],
                $v['preview'],
                '',
                $v['id'],
                $turlType,
                'Download now',
                $per,
                $name,
                $v['name_version'],
                $sub_name2,
                $v['prepay'],
                time(),
                $prm->year,
                $v['sort_name'],
                '',
                $extra
            )
            : sprintf($tmplPrepay,
                $url,
                $v['full_name'],
                $v['preview'],
                '',
                $v['id'],
                $turlType,
                'Under Development',
                $per,
                $name,
                $v['name_version'],
                $sub_name2,
                $v['prepay'],
                time(),
                $prm->year,
                $v['sort_name'],
                noxSystem::$prepayMaxDays,
                $extra
            );
    }
}
