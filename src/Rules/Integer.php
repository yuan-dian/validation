<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/8/22
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

/**
 * 验证是否是整数
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Integer implements Rule
{
    public function __construct(public string $message = "The value should be integer")
    {
    }

    public function validate(mixed $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_INT);
    }
}