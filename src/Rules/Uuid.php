<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/9/14
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Uuid implements Rule
{
    public function __construct(public string $message = "The value should contain a valid UUID")
    {
    }

    public function validate(mixed $value): bool
    {
        return boolval(preg_match('/^[a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8}$/i', $value));
    }
}