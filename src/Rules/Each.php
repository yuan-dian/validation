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

/**
 * 对数组中“每一个元素的某个字段”应用同一组 Rule
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Each implements Rule
{
    public string $message = '';

    /**
     * @param Rule[]|Rule $rules
     * @param string|null $field
     */
    public function __construct(public array|Rule $rules, public ?string $field = null)
    {
    }

    public function validate(mixed $value): bool
    {
        // Each 本身永远返回 true
        // 真正的错误由 Validator 逐项收集
        return true;
    }

}