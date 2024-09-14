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
class Timestamp implements Rule
{
    public function __construct(public string $message = "The value should be a valid timestamp")
    {
    }

    public function validate(mixed $value): bool
    {
        if (!filter_var($value, FILTER_VALIDATE_INT)) {
            return false;
        }

        return (bool)date('Y-m-d H:i:s', $value);
    }
}