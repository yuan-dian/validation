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
 * 验证是否是url
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Url implements Rule
{
    public function __construct(public string $message = "The value should be in a URL format")
    {
    }

    public function validate(mixed $value): bool
    {
        return false !== filter_var($value, FILTER_VALIDATE_URL);
    }
}