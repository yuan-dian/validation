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
 * 验证是否是身份证
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class IdCard implements ValidateAttribute
{
     private const rule = '/(^[1-9]\d{5}(18|19|([23]\d))\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}[0-9Xx]$)|(^[1-9]\d{5}\d{2}((0[1-9])|(10|11|12))(([0-2][1-9])|10|20|30|31)\d{3}$)/';


    public function __construct(public string $message = "The ID card rules are incorrect")
    {
    }

    public function validate(mixed $value): bool
    {

        return is_scalar($value) && 1 === preg_match(self::rule, (string) $value);
    }
}