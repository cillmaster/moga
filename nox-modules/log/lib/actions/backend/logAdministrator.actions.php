<?php

class logAdministratorActions extends noxThemeActions
{
    public $theme = 'administrator';
    public $cache = false;

    public function actionXZ()
    {
        $model = new printsRequestVectorModel();
        $voteModel = new printsRequestVoteModel();

        $voteModel->exec('SELECT request_id, COUNT(*) as votes FROM `prints_request_vote` GROUP BY request_id');
        $votes = $voteModel->fetchAll('request_id', 'votes');
        $res = $model->fetchAll();
        global $data;
        $data = [];

        array_walk($res, function($b, $a, $votes) {
            global $data;
            $arName = explode(' ', $b['name']);
            $id = $b['id'];
            if(!isset($votes[$id])) $votes[$id] = 0;
            foreach($arName as $w) {
                if(isset($data[$w])) {
                    ++$data[$w]['count'];
                    $data[$w]['votes'] += $votes[$id];
                }
                else $data[$w] = [
                    'count' => 1,
                    'votes' => $votes[$id]
                ];
            }
        }, $votes);

        uasort($data, function($a, $b) {
            if($a['votes'] > $b['votes']) return -1;
            elseif($a['votes'] < $b['votes']) return 1;
            else {
                if($a['count'] > $b['count']) return -1;
                elseif($a['count'] < $b['count']) return 1;
            }

            return 0;
        });
        echo '<table><tr><td>Слово</td><td>Упоминаний</td><td>Голосов</td></tr>';
        array_walk($data, function($b, $a) {
            printf("<tr><td>%s</td><td>%s</td><td>%s</td></tr>", $a, $b['count'], $b['votes']);
        });
        echo '</table>';
    }

    public function actionRw(){
        $this->title = 'Слова в реквестах';
        $names = (new printsRequestVectorModel())->select('name')->fetchAll(false, 'name');
        $hash = [];
        for($i = count($names) - 1; $i > 0; $i--) {
            $words = explode(' ', $names[$i]);
            foreach($words as $w) {
                $hash[$w] = (isset($hash[$w])) ? $hash[$w]+1 : 1;
            }
        }

        arsort($hash);

        echo '<table><tr><td>Слово</td><td>Упоминаний</td></tr>';
        array_walk($hash, function($count, $word) {
            printf("<tr><td>%s</td><td>%s</td></tr>", $word, $count);
        });
        echo '</table>';
    }
}