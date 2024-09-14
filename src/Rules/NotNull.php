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

/**
 * 验证不是null
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class NotNull implements Rule
{
    public function __construct(public string $message = "This value should not be null.")
    {
    }

    public function validate(mixed $value): bool
    {
        if ($value === null) {
            return false;
        }
        return true;
    }

}