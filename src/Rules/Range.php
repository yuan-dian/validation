<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2025/10/10
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

/**
 * 验证范围
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class Range implements Rule
{
    public function __construct(public int $min, public int $max, public string $message = '')
    {
        if (empty($message)) {
            $this->message = "must be range to {$this->min} ~ {$this->max}";
        }
    }

    public function validate(mixed $value): bool
    {
        return $value >= $this->min && $value <= $this->max;
    }

}