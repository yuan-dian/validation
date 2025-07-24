<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/11/29
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\Validation\Rules;

use Attribute;
use yuandian\Validation\Exception\ParameterException;
use yuandian\Validation\Rule;

#[Attribute(Attribute::TARGET_PROPERTY)]
class IsEnum implements Rule
{
    /**
     * @param string $enum enum class
     * @param array $only only enum attributes are supported
     * @param array $except except enum attributes
     * @param string $message message
     */
    public function __construct(
        private readonly string $enum,
        private readonly array $only = [],
        private readonly array $except = [],
        public string $message = 'The value must be a valid enum case',
    ) {
        if (!enum_exists($this->enum)) {
            throw new ParameterException('The enum parameter must be a valid enumeration type');
        }
    }

    private function check($value): bool
    {
        return match (true) {
            !empty($this->only) => in_array(needle: $value, haystack: $this->only, strict: true),
            !empty($this->except) => !in_array(needle: $value, haystack: $this->except, strict: true),
            default => true,
        };
    }

    public function validate(mixed $value): bool
    {
        if ($value instanceof $this->enum) {
            return $this->check($value);
        }
        return false;
    }
}