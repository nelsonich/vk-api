#!/usr/bin/php
<?php

use Dotenv\Dotenv;
use VK\Client\Enums\VKLanguage;
use VK\Client\VKApiClient;
use VK\OAuth\VKOAuth;
use VK\OAuth\VKOAuthDisplay;
use VK\OAuth\Scopes\VKOAuthUserScope;
use VK\OAuth\VKOAuthResponseType;

use System\Database;

require '../vendor/autoload.php';

define('_VK_API_LANG_', VKLanguage::ENGLISH);

$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->safeLoad();

$params = getopt('null', ["groupId:"]);

$mongo = new Database($_ENV['DATABASE_NAME'], 'vk_members');
$data = $mongo->fetchData([
    'group_id' => intval($params['groupId'])
]);

$vk = new VKApiClient(
    $_ENV['VK_API_VERSION'],
    _VK_API_LANG_
);

// $oauth = new VKOAuth();
// $redirect_uri = 'https://oauth.vk.com/blank.html';
// $display = VKOAuthDisplay::PAGE;
// $scope = [
//     VKOAuthUserScope::WALL,
//     VKOAuthUserScope::GROUPS,
// ];
// $state = 'secret_state_code';

// $browser_url = $oauth->getAuthorizeUrl(
//     VKOAuthResponseType::CODE,
//     $_ENV['VK_API_CLIENT_ID'],
//     $redirect_uri,
//     $display,
//     $scope,
//     $state
// );

// var_dump($browser_url);
// die;

// ACCESS TOKEN

// $oauth = new VK\OAuth\VKOAuth();
// $code = '3e22c458a34d464112';

// $response = $oauth->getAccessToken(
//     $_ENV['VK_API_CLIENT_ID'],
//     $_ENV['VK_API_CLIENT_SECRET'],
//     $_ENV['VK_API_REDIRECT_URI'],
//     $code
// );

// $access_token = $response['access_token'];

// var_dump($access_token);
// die;

$membersCount = count($data->toArray());
$members = [];

while (true) {
    $response = $vk->groups()->getMembers($_ENV['VK_API_ACCESS_TOKEN'], array(
        'group_id' => $params['groupId'],
        'fields' => ['bdate', 'city', 'country'],
        'count' => $_ENV['VK_API_LIMIT'],
        'offset' => $membersCount,
    ));

    $totalCount = $response['count'];
    $membersChunk = $response['items'];

    if (count($membersChunk) === 0) {
        break;
    }

    $membersChunk = $response['items'];
    $members = array_merge($members, $membersChunk);

    $membersChunkCount = count($membersChunk);
    $membersCount += $membersChunkCount;

    if ($membersCount === $totalCount) {
        break;
    }

    foreach ($membersChunk as $key => $member) {
        $membersChunk[$key]['group_id'] = intval($params['groupId']);
        $membersChunk[$key]['vk_member_id'] = $member['id'];

        unset($membersChunk[$key]['id']);

        if (isset($member['bdate']) && !is_null($member['bdate'])) {
            $arr = explode('.', $member['bdate']);

            if (count($arr) === 3) {
                $membersChunk[$key]['age'] = (date("Y") - $arr[2]);
            }
        }
    }

    $data = $mongo->insertMany($membersChunk);

    // $mongo->updateMany(
    //     ['_id' => ['$in' => $data->getInsertedIds()]],
    //     ['group_id' => $params['groupId']]
    // );

    printf(
        "Loaded: %-8s | Total: %-8s | From %-8s\r",
        number_format($membersChunkCount, 0, '.', ','),
        number_format($membersCount, 0, '.', ','),
        number_format($totalCount, 0, '.', ',')
    );

    usleep($_ENV['VK_API_SLEEP']);
}

var_dump($members);
