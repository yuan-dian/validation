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
 * 验证是否是纯字母
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Alpha implements Rule
{
    private const rule = '/^[A-Za-z]+$/';


    public function __construct(public string $message = "The value should be alpha")
    {
    }

    public function validate(mixed $value): bool
    {
        return is_scalar($value) && 1 === preg_match(self::rule, (string)$value);
    }
}