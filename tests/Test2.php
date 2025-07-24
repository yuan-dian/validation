<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/8/28
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Tests;

use yuandian\Tools\attribute\Trim;
use yuandian\Validation\BaseEntity;
use yuandian\Validation\Rules\Email;
use yuandian\Validation\Rules\NotEmpty;
use yuandian\Validation\Rules\Scene;

#[Scene("add", ['name', "email"])]
#[Scene("edit", ['account', "email"])]
class Test2 extends BaseEntity
{

//    public AA $aa;
    #[NotEmpty(message: "account cannot be empty.")]
    #[Trim]
    public array|string $account;
    #[NotEmpty(message: "Name cannot be empty.")]
    #[Trim]
    public string $name;

    #[Email(message: "Invalid email format---")]
    #[NotEmpty(message: "Email cannot be empty---")]
    #[Trim]
    public string $email;


//    #[Email(message: "Invalid email format")]
//    #[NotEmpty(message: "Email cannot be empty")]
//    public string $bb;
//
//    public function scenes(): array
//    {
//        return [
//            'add' => ['name', 'email'],
//        ];
//    }
}