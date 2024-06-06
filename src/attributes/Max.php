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
class Max implements ValidateAttribute
{
    public function __construct(public int $rule, public string $message = '')
    {
        if (empty($message)) {
            $this->message = "must be less than or equal to {$this->rule}";
        }
    }

    public function validate(mixed $value): bool
    {
        if (is_array($value)) {
            $length = count($value);
        } else {
            $length = mb_strlen((string)$value);
        }

        return $length <= $this->rule;
    }

}