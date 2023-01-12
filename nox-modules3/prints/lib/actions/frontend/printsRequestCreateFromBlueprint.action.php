<?php
/**
 * @author     <pa-nic@yandex.ru>
 * @version    1.0
 * @package    prints
 */

class printsRequestCreateFromBlueprintAction extends noxThemeAction
{
    public function execute()
    {
        $blueprintId = $this->getParam('blueprintId', 0);
        $blueprintModel = new printsBlueprintModel();

        $blueprint = $blueprintModel->getById($blueprintId);
        if(!$blueprint) {
            return 404;
        }

        $requestVectorModel = new printsRequestVectorModel();
        $categoryModel = new printsCategoryModel();
        $category = $categoryModel->getById($blueprint['class_id']);

        $categoryClassTable = 'prints_class_' . $category['db_table'];
        $dataCategoryModel = new noxModel(false, $categoryClassTable);
        $blueprint = array_merge($dataCategoryModel->getById($blueprint['item_id']), $blueprint);
        $blueprint['category_id'] = $blueprint['class_id'];
        //_d($blueprint);die;

        $fields = [
            'name',
            'year',
            'category_id',
            'make_id'
        ];

        $request = [];

        foreach($fields as $f) {
            if(isset($blueprint[$f])) {
                $request[$f] = $blueprint[$f];
            }
        }

        $requestCurrent = $requestVectorModel->where($request)->fetch();

        if(!$requestCurrent) {
            $request['full_name'] = Prints::generateFullName($blueprint, $blueprint);
            $request['sort_name'] = Prints::generateSortName($blueprint, $blueprint);
            $request['url'] = URLTools::string2url($request['full_name']);
            $request['request_date'] = noxDate::toSql();
            $request['description'] = json_encode([
                'blueprint_id' => $blueprintId
            ]);

            if($userId = noxSystem::getUserId()) {
                $request['user_id'] = $userId;
            }
            if(isset($_COOKIE['nox_utm'])) {
                $request['utm_value'] = $_COOKIE['nox_utm'];
            }

            $requestVectorModel->insert($request);
            $requestVectorId = $request['id'] = $requestVectorModel->insertId();
            $requestCurrent = $request;
        } else {
            $requestVectorId = $requestCurrent['id'];
        }

        if(!noxSystem::authorization() && $requestVectorId) {
            $this->addVar('action_registration_request');

            if(isset($_COOKIE['myrequests'])) {
                $myRequests = $_COOKIE['myrequests'] . '#.#' . $requestVectorId . '::' . printsRequestVectorModel::generateSecretCodeForRequest($requestVector);
            }
            else {
                $myRequests = $requestVectorId . '::' . printsRequestVectorModel::generateSecretCodeForRequest($request);
            }

            setcookie('myrequests', $myRequests, null, '/');
        }
        else {
            if($requestCurrent['status'] == 17){
                setcookie('vote_email', 'cant', time() + 1000, '/');
            } else {
                (new printsRequestVoteModel())->vote($requestVectorId, Prints::REQUEST_VECTOR, 0);
                setcookie('vote_email', 'want_free', time() + 1000, '/');
            }
        }

        $to = Prints::createUrlForItem($requestCurrent, Prints::REQUEST_VECTOR);
        if(!noxSystem::authorization()) {
            $to .= '#popup_registration';
        }
        noxSystem::location($to);
    }
}
