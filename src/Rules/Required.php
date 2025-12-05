<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2025/12/5
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Required implements Rule
{
    public function __construct(public string $message = 'The field cannot be empty')
    {
    }

    public function validate(mixed $value): bool
    {
        //  null 一定非法
        if ($value === null) {
            return false;
        }

        // 字符串：去除空白后不能为空
        if (is_string($value)) {
            return trim($value) !== '';
        }

        // 数组：不能为空数组
        if (is_array($value)) {
            return !empty($value);
        }

        // 数字：0 是合法值
        if (is_int($value) || is_float($value)) {
            return true;
        }

        // 布尔：false 也是合法值
        if (is_bool($value)) {
            return true;
        }

        // 对象：只要不是 null 就认为存在
        if (is_object($value)) {
            return true;
        }

        // 其余类型（资源等）默认非法
        return false;
    }

}