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
 * 验证是否是浮点数
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class FloatNum implements ValidateAttribute
{
    public function __construct(public string $message = "The value should be float number")
    {
    }

    public function validate(mixed $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_FLOAT);
    }
}