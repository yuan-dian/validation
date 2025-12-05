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
class When implements Rule
{
    public string $message = '';

    /**
     * @param string $field 触发条件的字段名
     * @param mixed $equals 等于某值时触发
     * @param Rule[]|Rule $rules 条件成立时执行的规则
     */
    public function __construct(
        public string $field,
        public mixed $equals,
        public array|Rule $rules = []
    ) {
    }

    /**
     * When 自身永远返回 true
     * 真正的校验在 Validator 中执行
     */
    public function validate(mixed $value): bool
    {
        return true;
    }
}