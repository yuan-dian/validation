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

namespace origin\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Mobile implements ValidateAttribute
{
     private const rule = '/^1[3-9]\d{9}$/';


    public function __construct(public string $message = "The mobile phone number is incorrect")
    {
    }

    public function validate(mixed $value): bool
    {

        return is_scalar($value) && 1 === preg_match(self::rule, (string) $value);
    }
}