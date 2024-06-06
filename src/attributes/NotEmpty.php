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

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty implements ValidateAttribute
{
    public function __construct(public string $message = "This value should not be blank.")
    {
    }

    public function validate(mixed $value): bool
    {
        if (empty($value)) {
            return false;
        }
        return true;
    }

}