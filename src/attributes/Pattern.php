<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/6/5
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\attributes;

use Attribute;

/**
 * 验证正则
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Pattern implements ValidateAttribute
{
    public function __construct(public string $regexp = '', public string $message = "不符合正则表达式",)
    {
    }

    public function validate(mixed $value): bool
    {
        return is_scalar($value) && 1 === preg_match($this->regexp, (string)$value);
    }
}