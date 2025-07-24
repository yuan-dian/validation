<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/12/2
// +----------------------------------------------------------------------

use yuandian\Tools\bean\BeanUtil;
use yuandian\Validation\Tests\Test;
use yuandian\Validation\Validator;

require_once 'vendor/autoload.php';

// 模拟请求数据
$requestData = [
    'account' => 'aa',
    'name'    => 'aa',
    'email'   => '   invalid-email@qq.com',
    'test'    => [
        'name'  => 'aa',
        'email' => '   invalid-email@qq.com',
    ],
];
//$requestData = [
//    [
//        'account' => 'aa',
//        'name'    => '   invalid-email@qq.com',
//        'email'   => '   invalid-email@qq.com',
//    ],
//    [
//        'account' => 'aa',
//        'name'    => '   invalid-email@qq.com',
//        'email'   => '   invalid-email@qq.com',
//    ],
//];

//$Collection = [
//    ['name' => "aa", "age" => 10],
//    ['name' => "bb", "age" => 20],
//    ['name' => "cc", "age" => 30],
//];

// 验证实体
try {
    $validator = new Validator();
//    $arrayToObject = \yuandian\Validation\Utils\BeanUtil::arrayToObject($Collection, Collection::class, true);
//    $request = new \yuandian\Validation\tests\Collection($Collection);
//    $validator->validate($request, 'add');
//    $request = new \yuandian\Validation\tests\Test($requestData);
    $data = BeanUtil::arrayToObject($requestData, Test::class);
    var_dump($data);
    $validator->validate($data);
//    var_dump($arrayToObject);
} catch (Throwable $e) {
    echo $e->getMessage() . "\n";
}
