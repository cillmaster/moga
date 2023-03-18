<?php
require_once(noxRealPath('nox-modules/3rdparty/Postmark/vendor/autoload.php'));
use Postmark\PostmarkClient;
use Postmark\Models\PostmarkException;

class postmarkMailer extends noxTemplate{

    private $type = '';

    private $prm = [
        'main' => [
            'account' => [
                'crm1' => [
                    'from' => 'requests@crm1.getoutlines.com',
                    'name' => 'Outlines Server'
                ],
                'crm2' => [
                    'from' => 'requests@crm2.getoutlines.com',
                    'name' => 'Outlines Server'
                ],
                'noreply' => [
                    'from' => 'noreply@getoutlines.com',
                    'name' => 'Outlines Server'
                ],
                'touch' => [
                    'from' => 'touch@crm.getoutlines.com',
                    'name' => 'Outlines Server'
                ],
            ]
        ],
        'confirm_email' => [
            'subject' => '[Action required] Confirm your email',
            'from' => 'noreply',
            'tag' => 'confirm-email'
        ],
        'invoice' => [
            'subject' => 'Invoice',
            'from' => 'noreply',
            'tag' => 'invoice'
        ],
        'registration' => [
            'subject' => 'Welcome to Outlines!',
            'from' => 'noreply',
            'tag' => 'welcome'
        ],
        'vector_link' => [
            'subject' => 'Vector link',
            'from' => 'noreply',
            'tag' => 'ready-vector'
        ],
        'vector_link_sorry' => [
            'subject' => 'Vector link',
            'from' => 'crm1',
            'tag' => 'ready-vector-sorry1'
        ],
        'vector_link_touch' => [
            'subject' => 'Vector link',
            'from' => 'touch',
            'tag' => 'ready-vector-touch'
        ],
        'vector_top_link' => [
            'subject' => 'Vector Top View link',
            'from' => 'noreply',
            'tag' => 'ready-vector-option-top'
        ],
        'prepay_link' => [
            'subject' => 'Prepay link',
            'from' => 'noreply',
            'tag' => 'prepay-link'
        ],
        'prepay_link_sorry' => [
            'subject' => 'Prepay link',
            'from' => 'crm1',
            'tag' => 'prepay-link-sorry1'
        ],
        'prepay_link_touch' => [
            'subject' => 'Prepay link',
            'from' => 'touch',
            'tag' => 'prepay-link-touch'
        ],
        'request_cant' => [
            'subject' => 'Can’t be produced',
            'from' => 'noreply',
            'tag' => 'request-cant'
        ],
        'request_cant_sorry' => [
            'subject' => 'Can’t be produced',
            'from' => 'crm1',
            'tag' => 'request-cant-sorry1'
        ],
        'reset_email' => [
            'subject' => 'Reset your password',
            'from' => 'noreply',
            'tag' => 'reset-password'
        ],
    ];

    public function __construct($type = ''){
        $this->type = $type;
        $filename = 'nox-system/mail/templates/' . $this->type . '.html';
        parent::__construct($filename);
    }

    public function mail($data){
        if(isset($data['name'])){
            $this->addVar('name', $data['name']);
        }
        switch ($this->type){
            case 'confirm_email':
            case 'reset_email':
                $this->addVar('confirmLink', $data['confirmLink']);
                $this->addVar('email', $data['to']);
                break;
            case 'invoice':
                $this->addVar('data', $data['data']);
                break;
            case 'vector_link':
            case 'vector_link_sorry':
            case 'vector_link_touch':
            case 'prepay_link':
            case 'prepay_link_sorry':
            case 'prepay_link_touch':
                $this->addVar('date', $data['date']);
                $this->addVar('car', $data['car']);
                $this->addVar('link', $data['link']);
                $this->addVar('title', $data['title']);
                break;
            case 'request_cant':
                $this->addVar('date', $data['date']);
                $this->addVar('car', $data['car']);
                break;
        }
        $_prm = $this->prm[$this->type];
        $from = isset($data['from']) ? $data['from'] : $_prm['from'];
        $from = $this->prm['main']['account'][$from]['from'];
        $subject = isset($data['subject']) ? $data['subject'] : $_prm['subject'];
        $body = trim($this->__toString());
        $tag = isset($data['tag']) ? $data['tag'] : $_prm['tag'];
        try{
            $client = new PostmarkClient(POSTMARK_TOKEN);
            $res = $client->sendEmail($from, $data['to'], $subject, $body, null, $tag, true,
                null, null, null, null, null, null, ['UserID' => $data['UserID']]);
        }catch(PostmarkException $ex){
            $res = join(' : ', [
                $ex->httpStatusCode,
                $ex->message,
                $ex->postmarkApiErrorCode
             ]);
        }catch(Exception $generalException){
            $res = 'postmarkGeneralException';
        }
        return $res;
    }
}