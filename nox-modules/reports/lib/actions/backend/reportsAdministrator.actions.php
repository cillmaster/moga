<?php
class reportsAdministratorActions extends noxThemeActions
{
    public $cache = false;

    public $theme = 'administrator';

    public function actionDefault()
    {
        //Проверяем, есть ли у пользователя право
        if (!$this->haveRight('control')){
            return 401;
        }

        $tm = time();
        $id = $_GET['id'];
        $rid = $_GET['report_id'];

        $this->templateFileName = $this->moduleFolder . '/templates/report_' . $rid . '.html';

        function daysInMonth($y, $m, $tm){
            $mapDays = [31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31];
            if($y . $m == date('Yn', $tm)){
                $res = intval(date('j', $tm)) - 1 + intval(date('G', $tm)) / 24;
            }else if($m == 2 && $y % 4 == 0){
                $res = 29;
            }else{
                $res = $mapDays[$m - 1];
            }
            return $res;
        }

        function nf($v, $fix = 0, $d = 1, $empty = ''){
            return ((float)$v && (float)$d) ? number_format($v / $d, $fix, '.', '') : $empty;
        }

        $noxDbQuery = new noxDbQuery();

        $group = 'day';
        $from = noxDate::toSql(1);
        if(in_array($rid, [1, 5, 7, 8])){
            $fieldD = '`registration_date`';
            $fieldU = '`nox_user`.`id`';
        } else {
            $fieldD = '`datetime`';
            $fieldU = '`payment`.`user_id`';
        }
        $selectD = 'DATE(' . $fieldD . ') as d';
        $startD = '`d`';
        $groupD = 'GROUP BY d DESC';
        $devUsers = [1, 2, 3, 4, 5, 17, 37, 797, 12752, 26270, 31964, 70860];
        $whereU = '(' . $fieldU . ' NOT IN(' . join(',', $devUsers) . '))';

        if(in_array($rid, [1, 4, 5, 6, 7, 8])){
            switch ($_GET['period']){
                case 0:
                    $from = noxDate::toSql(date('d m Y', time() - 30 * 86400) . ' 3 0 0 ');
                    break;
                case 1:
                    $from = noxDate::toSql(date('d m Y', time() - 90 * 86400) . ' 3 0 0 ');
                    break;
                case 2:
                    $from = noxDate::toSql(mktime(3, 0, 0, date('n'), 1, (date('Y') - 1)));
                    $group = 'month';
                    break;
                case 3:
                    $group = 'month';
                    break;
            }
            if($group == 'month'){
                $selectD = 'MONTH(' . $fieldD . ') as m, YEAR(' . $fieldD . ') as y';
                $startD = 'CONCAT_WS(\'-\', `y`, `m`, 1)';
                $groupD = 'GROUP BY y DESC, m DESC';
            }
        }
        $whereD = '(' . $fieldD . ' >= "' . $from . '")';
        $where = join(' AND ', [$whereD, $whereU]);
        $this->addVar('month', $group == 'month');

        switch ($rid){
            case 1:
                $this->caption = 'Visitors / Users / Active Users';
                $summary = ['g' => 0, 'f' => 0, 's' => 0, 'w' => 0, 'd' => 0];
                $avg = ($_GET['order'] == '1') && ($group == 'month');
                $noxDbQuery->exec('SELECT ' . $selectD . ', 
                        SUM(case WHEN `user_type`="google" then 1 else 0 end) g, 
                        SUM(case WHEN `user_type`="facebook" then 1 else 0 end) f, 
                        SUM(case WHEN `user_type`="email" AND `registration_status`="success_confirm" then 1 else 0 end) s, 
                        SUM(case WHEN `user_type`="email" AND `registration_status`="wait_confirm" then 1 else 0 end) w 
                    FROM `nox_user` WHERE ' . $where . ' ' . $groupD);
                $res = $noxDbQuery->fetchAll();
                $days = 1;
                foreach ($res as &$item){
                    $item['d'] = ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m']));
                    $item['mT'] = $item['s'] + $item['w'];
                    $item['sPr'] = nf($item['s'] * 100, 1, $item['mT']);
                    $item['wPr'] = nf($item['w'] * 100, 1, $item['mT']);
                    $item['aT'] = $item['g'] + $item['f'] + $item['s'];
                    $item['uT'] = $item['aT'] + $item['w'];
                    $item['aPr'] = nf($item['aT'] * 100, 1, $item['uT']);
                    $summary['d'] += $days;
                    $summary['g'] += $item['g'];
                    $summary['f'] += $item['f'];
                    $summary['s'] += $item['s'];
                    $summary['w'] += $item['w'];
                    if($avg){
                        $days = daysInMonth($item['y'], $item['m'], $tm);
                        foreach (['g', 'f', 's', 'w', 'mT', 'aT', 'uT'] as $f){
                            $item[$f] = nf($item[$f], 2, $days);
                        }
                    }
                }
                $this->addVar('res', $res);
                $mT = $summary['s'] + $summary['w'];
                $summary['sPr'] = nf($summary['s'] * 100, 2, $mT);
                $summary['wPr'] = nf($summary['w'] * 100, 2, $mT);
                $summary['mT'] = nf($mT);
                $aT = $summary['g'] + $summary['f'] + $summary['s'];
                $uT = $aT + $summary['w'];
                $summary['aPr'] = nf($aT * 100, 2, $uT);
                $summary['aT'] = nf($aT);
                $summary['uT'] = nf($uT);
                if($avg){
                    foreach (['g', 'f', 's', 'w', 'mT', 'aT', 'uT'] as $f){
                        $summary[$f] = nf((float)$summary[$f], 2, $summary['d']);
                    }
                }
                $this->addVar('summary', $summary);
                break;
            case 2:
                $per = 90;
                $this->caption = 'Отчет по добавленным векторам за последние ' . $per . ' дней';
                $where_time = '`added_date` >= "' . noxDate::toSql(time() - $per * 86400) . '"';
                $noxDbQuery->exec('SELECT `add_user_id`, `login` FROM `prints_vector` 
                    LEFT JOIN `nox_user` ON `prints_vector`.`add_user_id` = `nox_user`.`id` 
                    WHERE ' . $where_time . ' AND `add_user_id` IS NOT NULL GROUP BY `add_user_id`');
                $res = $noxDbQuery->fetchAll('add_user_id');
                $editors = array();
                foreach ($res as $row)
                    $editors[$row['add_user_id']] = $row['login'];
                $this->addVar('editors', $editors);
                $noxDbQuery->exec('SELECT DATE(`added_date`) as d, COUNT(`id`) as c 
                    FROM `prints_vector` WHERE ' . $where_time .
                    ($id > 0 ? ' AND `add_user_id`=' . $id : '') .
                    ' GROUP BY d ORDER BY d DESC');
                $res = $noxDbQuery->fetchAll();
                $this->addVar('res', $res);
                break;
            case 3:
                $this->caption = 'Список Paying Sets';
                $order = $_GET['order'] == 0 ? 'c' : 's';
                $noxDbQuery->exec('SELECT COUNT(`purchase_id`) AS c, SUM(`price`) AS s, `prints_set`.`name_full` AS n 
                    FROM `payment` LEFT JOIN `prints_set_vector` ON `payment`.`purchase_id` = `prints_set_vector`.`vector_id` 
                    LEFT JOIN `prints_set` ON `prints_set`.`id` = `prints_set_vector`.`set_id` 
                    WHERE `payment`.`status` = "approved" AND `set_id` IS NOT NULL GROUP BY `prints_set`.`id` ORDER BY ' . $order . ' DESC');
                $res = $noxDbQuery->fetchAll();
                foreach ($res as &$row){
                    $row['s'] = round($row['s'], 2);
                }
                $this->addVar('res', $res);
                break;
            case 4:
                $this->caption = 'Sales / Items / AvPrice';
                $tbl = [];
                $summary = ['d' => 0, 's' => 0, 'c' => 0];
                $year = [];
                $yearAvg = [];
                $avg = $_GET['prm1'] == '0';
                $fld = $_GET['prm2'] == '0' ? 's' : 'c';
                $list = $_GET['prm3'] == '0';
                $ds = (($list || !$avg) && ($fld == 'c')) ? 0 : 2;
                $noxDbQuery->exec('SELECT ' . $selectD . ', SUM(`price`) s, COUNT(`price`) c
                    FROM `payment` 
                    WHERE ' . $where . ' AND (`status` = "approved") '. $groupD);
                $res = $noxDbQuery->fetchAll();
                foreach ($res as &$item){
                    $days = ($group == 'day') ? 1 : daysInMonth($item['y'], $item['m'], $tm);
                    if($list){
                        $tbl[] = [
                            'd' => ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m'])),
                            'sAvg' => nf($item['s'], 2, $days),
                            'sTotal' => nf($item['s'], 2),
                            'cAvg' => nf($item['c'], 2, $days),
                            'cTotal' => $item['c'],
                            'prAvg' => nf($item['s'], 2, $item['c']),
                        ];
                        $summary['d'] += $days;
                        $summary['c'] += $item['c'];
                        $summary['s'] += $item['s'];
                    } else {
                        $tbl_days[$item['m']][$item['y']] = $days;
                        $tbl_s[$item['m']][$item['y']] = $item[$fld];
                        $year[] = $item['y'];
                    }
                }
                if(!$list) {
                    $year = array_unique($year, SORT_NUMERIC);
                    sort($year);
                    foreach ($year as $y) {
                        $s = 0;
                        $d = 0;
                        for ($i = 1; $i <= 12; $i++) {
                            if (isset($tbl_s[$i][$y])) {
                                if ($avg) {
                                    $tbl[$i][$y] = nf($tbl_s[$i][$y] / $tbl_days[$i][$y], 2);
                                } else {
                                    $tbl[$i][$y] = nf($tbl_s[$i][$y], $ds);
                                }
                                $s += $tbl_s[$i][$y];
                                $d += $tbl_days[$i][$y];
                            } else {
                                $tbl[$i][$y] = '';
                            }
                        }
                        if ($avg) {
                            $yearAvg[$y] = nf($s / $d, 2);
                        } else {
                            $yearAvg[$y] = nf($s / $d * 365 / 12, 2);
                            $yearTotal[$y] = nf($s, $ds);
                        }
                    }
                }
                $this->addVar('tbl', $tbl);
                if($list){
                    $summary['prAvg'] = nf($summary['s'], 2, $summary['c']);
                    $summary['sAvg'] = nf($summary['s'], 2, $summary['d']);
                    $summary['cAvg'] = nf($summary['c'], 2, $summary['d']);
                    $summary['sTotal'] = nf($summary['s'], 2);
                    $summary['cTotal'] = nf($summary['c']);
                    $this->addVar('summary', $summary);
                } else {
                    $this->addVar('year', $year);
                    $this->addVar('yearAvg', $yearAvg);
                    if(isset($yearTotal)){
                        $this->addVar('yearTotal', $yearTotal);
                    }
                }
                break;
            case 5:
                $this->caption = 'Paying Users';
                $tbl = [];
                $summary = ['uPay' => 0, 'uAct' => 0];
                $noxDbQuery->exec('SELECT ' . $selectD .', COUNT(DISTINCT `nox_user`.`id`) u, 
                    COUNT(DISTINCT IF(`status` = "approved",`payment`.`user_id`,NULL)) c
                    FROM `nox_user` LEFT JOIN `payment` ON `nox_user`.`id` = `payment`.`user_id`
                    WHERE ' . $where . ' AND (`registration_status` = "success_confirm") '. $groupD);
                $res = $noxDbQuery->fetchAll();
                foreach ($res as &$item){
                    $tbl[] = [
                        'd' => ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m'])),
                        'uAct' => nf($item['u']),
                        'uPay' => nf($item['c']),
                        'payAvg' => nf($item['c'] * 100, 2, $item['u']),
                    ];
                    $summary['uPay'] += $item['c'];
                    $summary['uAct'] += $item['u'];
                }
                $this->addVar('tbl', $tbl);
                $summary['payAvg'] = nf($summary['uPay'] * 100, 2, $summary['uAct']);
                $summary['uPay'] = nf($summary['uPay']);
                $summary['uAct'] = nf($summary['uAct']);
                $this->addVar('summary', $summary);
                break;
            case 6:
                $this->caption = '1S / 2S Sales';
                $tbl = [];
                $summary = ['s' => 0, 's1' => 0, 'c1' => 0, 'c2' => 0];
                $noxDbQuery->exec('SELECT ' . $selectD .', `user_id` u,  
                    COUNT(0) c, SUM(`price`) s,
                    (SELECT `price` FROM `payment` WHERE (`user_id` = `u`) AND (' . $fieldD . ' > ' . $startD . ') 
                    AND (`status` = "approved") ORDER BY ' . $fieldD . ' ASC LIMIT 1) s1
                    FROM `payment` 
                    WHERE ' . $where . ' AND (`status` = "approved") '. $groupD . ', u');
                $res = $noxDbQuery->fetchAll();
                $pD = 'no';
                foreach ($res as &$item){
                    $d = ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m']));
                    if(!isset($tbl[$d])){
                        if(isset($tbl[$pD])){
                            $tbl[$pD]['s1Pr'] = nf($tbl[$pD]['s1'] * 100, 2, $tbl[$pD]['s']);
                            $tbl[$pD]['s2Pr'] = nf($tbl[$pD]['s2'] * 100, 2, $tbl[$pD]['s']);
                        }
                        $tbl[$d] = [
                            'd' => $d,
                            's' => nf($item['s'], 2),
                            's1' => nf($item['s1'], 2),
                            'c1' => 1,
                            's2' => nf($item['s'] - $item['s1'], 2),
                            'c2' => nf($item['c'] - 1),
                        ];
                    } else {
                        $tbl[$d]['s'] = nf((float)$tbl[$d]['s'] + $item['s'], 2);
                        $tbl[$d]['s1'] = nf((float)$tbl[$d]['s1'] + $item['s1'], 2);
                        $tbl[$d]['c1'] = nf((int)$tbl[$d]['c1'] + 1);
                        $tbl[$d]['s2'] = nf((float)$tbl[$d]['s2'] + $item['s'] - $item['s1'], 2);
                        $tbl[$d]['c2'] = nf((int)$tbl[$d]['c2'] + $item['c'] - 1);
                    }
                    $summary['s'] += $item['s'];
                    $summary['s1'] += $item['s1'];
                    $summary['c1']++;
                    $summary['c2'] += ($item['c'] - 1);
                    $pD = $d;
                }
                if(isset($tbl[$pD])){
                    $tbl[$pD]['s1Pr'] = nf($tbl[$pD]['s1'] * 100, 2, $tbl[$pD]['s']);
                    $tbl[$pD]['s2Pr'] = nf($tbl[$pD]['s2'] * 100, 2, $tbl[$pD]['s']);
                }
                $this->addVar('tbl', $tbl);
                $s2 = $summary['s'] - $summary['s1'];
                $summary['s1Pr'] = nf($summary['s1'] * 100, 2, $summary['s']);
                $summary['s1'] = nf($summary['s1'], 2);
                $summary['s2Pr'] = nf($s2 * 100, 2, $summary['s']);
                $summary['s2'] = nf($s2, 2);
                $summary['s'] = nf($summary['s'], 2);
                $summary['c1'] = nf($summary['c1']);
                $summary['c2'] = nf($summary['c2']);
                $this->addVar('summary', $summary);
                break;
            case 7:
                $this->caption = '1S / 2S Users / APC';
                $tbl = [];
                $summary = ['c' => 0, 'u1' => 0, 'u2' => 0];
                $noxDbQuery->exec('SELECT ' . $selectD .', 
                    SUM(`c`) c, 
                    SUM(case WHEN `c` < 2 then 1 else 0 end) u1, 
                    SUM(case WHEN `c` > 1 then 1 else 0 end) u2 
                    FROM `nox_user` LEFT JOIN `user_stats` ON `nox_user`.`id` = `user_stats`.`uid`
                    WHERE ' . $where . ' '. $groupD);
                $res = $noxDbQuery->fetchAll();
                foreach ($res as &$item){
                    $u = $item['u1'] + $item['u2'];
                    $tbl[] = [
                        'd' => ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m'])),
                        'u' => nf($u),
                        'u1' => nf($item['u1']),
                        'u1Pr' => nf($item['u1'] * 100, 2, $u),
                        'u2' => nf($item['u2']),
                        'u2Pr' => nf($item['u2'] * 100, 2, $u),
                        'cAvg' => nf($item['c'], 4, $u),
                    ];
                    $summary['c'] += $item['c'];
                    $summary['u1'] += $item['u1'];
                    $summary['u2'] += $item['u2'];
                }
                $this->addVar('tbl', $tbl);
                $u = $summary['u1'] + $summary['u2'];
                $summary['u'] = nf($u);
                $summary['u1Pr'] = nf($summary['u1'] * 100, 2, $u);
                $summary['u1'] = nf($summary['u1']);
                $summary['u2Pr'] = nf($summary['u2'] * 100, 2, $u);
                $summary['u2'] = nf($summary['u2']);
                $summary['cAvg'] = nf($summary['c'], 4, $u);
                $this->addVar('summary', $summary);
                break;
            case 8:
                $this->caption = 'ARPU / ARPPU';
                $tbl = [];
                $summary = ['s' => 0, 'u' => 0, 'p' => 0];
                $noxDbQuery->exec('SELECT ' . $selectD . ', 
                    COUNT(DISTINCT `nox_user`.`id`) u, 
                    COUNT(DISTINCT IF(`status` = "approved",`nox_user`.`id`,NULL)) p,
                    SUM(IF(`status` = "approved",`payment`.`price`,NULL)) s
                    FROM `nox_user` LEFT JOIN `payment` ON `nox_user`.`id` = `payment`.`user_id`
                    WHERE ' . $where . ' '. $groupD);
                $res = $noxDbQuery->fetchAll();
                foreach ($res as &$item){
                    $tbl[] = [
                        'd' => ($group == 'day') ? $item['d'] : ($item['y'] . '-' . sprintf("%02d", $item['m'])),
                        'uAvg' => nf($item['s'], 2, $item['u']),
                        'pAvg' => nf($item['s'], 2, $item['p']),
                    ];
                    $summary['s'] += $item['s'];
                    $summary['u'] += $item['u'];
                    $summary['p'] += $item['p'];
                }
                $this->addVar('tbl', $tbl);
                $summary['uAvg'] = nf($summary['s'], 2, $summary['u']);
                $summary['pAvg'] = nf($summary['s'], 2, $summary['p']);
                $this->addVar('summary', $summary);
                break;
            case 'ip':
                $this->caption = 'IP Location';
                $ip = $_SERVER['HTTP_X_REAL_IP'];
                $details = json_decode(file_get_contents("http://ipinfo.io/{$ip}/json"));
                $this->addVar('res', $details);
                $this->addVar('res2', $_SERVER);
                break;
            case 'map':
                $this->caption = 'Отчеты Stats';
                break;
            default:
                $this->templateFileName = $this->moduleFolder . '/templates/report_0.html';
                $this->caption = 'Отчет не найден';
                break;
        }
    }
}

?>