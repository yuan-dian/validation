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

namespace yuandian\attributes;

use Attribute;

/**
 * 验证是否是数组
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IsArray
{
    public function __construct(public string $message = "The value should be a array")
    {
    }

    public function validate(mixed $value): bool
    {
        return is_array($value);
    }
}