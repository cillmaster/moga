<?php

class kafMailer extends noxMail{

    private $type = '';

    private $prm = array(
        'main' => array(
            'account' => array(
                'hello' => array(
                    'from' => 'hello@getoutlines.com',
                    'name' => 'Outlines'
                ),
                'pay' => array(
                    'from' => 'pay@getoutlines.com',
                    'name' => 'Outlines'
                ),
                'noreply' => array(
                    'from' => 'noreply@getoutlines.com',
                    'name' => 'Outlines Server'
                )
            )
        ),
        'exception' => array(
            'subject' => 'Outlines Exception!',
            'from' => 'noreply'
        ),
        'want_pay' => array(
            'subject' => 'Want pay!',
            'from' => 'pay'
        ),
        'new_preorder' => array(
            'subject' => 'Приоритетный чертеж!',
            'from' => 'pay'
        )
    );

    public function __construct($type = ''){
        $this->type = $type;
        $filename = 'nox-system/mail/templates/' . $this->type . '.html';
        parent::__construct($filename);
    }

    public function mail($data){
        switch ($this->type){
            case 'exception':
                $this->addVar('domain', $data['domain']);
                $this->addVar('exception', $data['exception']);
                break;
            case 'new_preorder':
                $this->addVar('deadline', $data['deadline']);
                $this->addVar('option', $data['option']);
            case 'want_pay':
                $this->addVar('link', $data['link']);
                $this->addVar('title', $data['title']);
                break;
        }
        $subject = isset($data['subject']) ? $data['subject'] : $this->prm[$this->type]['subject'];
        $this->to($data['to'])
            ->subject($subject)
            ->from($this->prm['main']['account'][$this->prm[$this->type]['from']])
            ->html(true)
            ->send();
    }

}