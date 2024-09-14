<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/9/14
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Between implements Rule
{
    public function __construct(
        private int $min,
        private int $max,
        public string $message = ""
    ) {
        if (empty($message)) {
            $this->message = "The value should be be between {$this->min} and {$this->max}";
        }
    }

    public function validate(mixed $value): bool
    {
        if ($value >= $this->min && $value <= $this->max) {
            return true;
        }

        return false;
    }
}