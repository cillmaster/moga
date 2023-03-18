<?php

class printsRequestVoteModel extends noxModel
{
    public $table = 'prints_request_vote';

    /**
     * @param $id int
     * @param $type int
     * @param bool $want_pay
     * @return void
     */
    public function vote($id, $type, $want_pay = -1) {
        if(noxSystem::authorization()){
            $this->replace([
                'user_id' => noxSystem::getUserId(),
                'request_id' => $id,
                'request_type' => $type,
                'want_pay' => $want_pay
            ]);
/*
            if($want_pay == 1){
                $details = (new printsRequestVectorModel())->where('id', $id)->fetch();
                (new kafMailer('want_pay'))->mail([
                    'from' => 'noreply',
                    'to' => 'hello@getoutlines.com',
                    'link' => 'http://' . noxSystem::$domain . Prints::createUrlForItem([
                        'id' => $id,
                        'url' => $details['url']
                    ], Prints::REQUEST_VECTOR),
                    'title' => $details['full_name']
                ]);
            }
*/
        }
    }

    public function getVotes($id, $type) {
        if(!is_array($id)) {
            $id = [$id];
        }
        $this->exec("SELECT request_id, count( request_id ) as votes, sum( want_pay ) as want_pay FROM `$this->table`
                WHERE request_type = $type AND request_id IN(" . implode(',', $id) . ")
                GROUP BY request_id");
        return  $this->fetchAll('request_id', 'votes');
    }

    public function getVoteAuthors($request_id)
    {
        return $this->select(['user_id', 'vote_datetime'])->where([
            'request_id' => $request_id,
            'want_pay' => 1
        ])->fetchAll('user_id', 'vote_datetime');
    }
}
