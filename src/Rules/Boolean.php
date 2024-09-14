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
class Boolean implements Rule
{
    public function __construct(public string $message = "The value should represent a boolean value")
    {
    }

    public function validate(mixed $value): bool
    {
        return (
            $value === false || $value === 'false' || $value === 0 || $value === '0' ||
            $value === true || $value === 'true' || $value === 1 || $value === '1'
        );
    }
}