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

namespace origin;

use ReflectionClass;

abstract class BaseEntity
{
    public function __construct(protected array $data = [])
    {
        $reflectionClass = new ReflectionClass($this);
        foreach ($reflectionClass->getProperties() as $property) {
            $name = $property->getName();
            if (array_key_exists($name, $data)) {
                $value = $data[$name];
                $type = $property->getType();
                if ($type !== null) {
                    $typeName = $type->getName();
                    if ($value !== null || $type->allowsNull()) {
                        // 根据类型进行转换
                        settype($value, $typeName);
                    }
                }
                $this->$name = $value;
            }
        }

        (new Validator())->validate($this);
    }

}