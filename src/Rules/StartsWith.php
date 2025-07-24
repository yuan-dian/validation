<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2025/7/24
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

/**
 * 验证是否已指定字符串开始
 */
#[Attribute(Attribute::TARGET_PROPERTY)]
class StartsWith implements Rule
{
    public function __construct(public string $needle, public string $message = '')
    {
        if (empty($message)) {
            $this->message = "Value should start with {$this->needle}";
        }
    }

    public function validate(mixed $value): bool
    {
        return str_starts_with($value, $this->needle);
    }
}