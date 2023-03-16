<?php
    require_once('init.php');
    include noxRealPath('nox-config/' . (noxConfig::isProduction() ? 'production' : 'dev') . '.env.php');
    require_once(noxRealPath('nox-system/locale/noxLocale.class.php'));
    require_once(noxRealPath('nox-system/output/noxTemplate.class.php'));
    require_once(noxRealPath('nox-system/mail/postmarkMailer.class.php'));
    require_once(noxRealPath('nox-system/user/noxUser.model.php'));
    require_once(noxRealPath('nox-modules/log/lib/models/logEvents.model.php'));

    $logEvents = new logEventsModel();
    $tm = time();
    $items = $logEvents->where([
        'status' => 0,
        'type' => [1, 2],
        'date_check' => ['end' => $tm]
    ])->fetchAll();
    if($items){
        $users = new noxUserModel();
        foreach($items as $item){
            $prm = json_decode($item['prm'], true);
            $lastVisit = $users->where(['id' => $prm['UserID']])->fetch('last_visit_date');
            if(strtotime($lastVisit) < $item['date_create']){
                $prm['from'] = 'touch';
                $prm['tag'] .= '-touch';
                (new postmarkMailer($prm['email_template'] . '_touch'))->mail($prm);
            }
            $logEvents->updateByField('id', $item['id'], ['status' => 1]);
        }
    }
