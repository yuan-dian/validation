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

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class NotEmpty implements Rule
{
    public function __construct(public string $message = "This value should not be blank.")
    {
    }

    public function validate(mixed $value): bool
    {
        return !empty($value) || '0' == $value;
    }

}