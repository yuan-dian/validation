<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/8/19
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\attributes;

use Attribute;

#[Attribute(Attribute::TARGET_PROPERTY)]
class Length implements ValidateAttribute
{
    public function __construct(public int $min, public int $max, public string $message = '')
    {
        if (empty($message)) {
            $this->message = "must be Length to {$this->min} ~ {$this->max}";
        }
    }

    public function validate(mixed $value): bool
    {
        if (is_numeric($value)) {
            $length = $value;
        } elseif (is_array($value)) {
            $length = count($value);
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length >= $this->min && $length >= $this->max;
    }
}