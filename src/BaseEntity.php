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

namespace yuandian;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionUnionType;
use yuandian\attributes\Trim;
use yuandian\exception\ValidateException;

abstract class BaseEntity
{
    /**
     * @param array $data
     * @throws ReflectionException|ValidateException
     */
    public function __construct(array $data = [])
    {
        $this->arrayToObject($data, $this);
    }

    /***
     * @param array $array
     * @param string|object $object
     * @return object
     * @throws ValidateException|ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public function arrayToObject(array $array, string|object $object): object
    {
        $reflectionClass = new ReflectionClass($object);
        // $object如果是字符串，创建对象
        if (is_string($object)) {
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }

        foreach ($array as $key => $value) {
            // 判断属性是否定义
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }
            $property = $reflectionClass->getProperty($key);
            $propertyType = $property->getType();

            // 自动去除空格的处理
            if (is_string($value) && $this->hasTrimAttribute($property)) {
                $value = trim($value);
            }

            // 无类型或空值处理
            if ($propertyType === null || (is_null($value) && $propertyType->allowsNull())) {
                $object->$key = $value;
                continue;
            }

            // 属性类型处理，支持联合类型处理
            $types = $propertyType instanceof ReflectionUnionType ? $propertyType->getTypes() : [$propertyType];

            // 优先匹配值的实际类型
            if (in_array($this->getPhpTypeName($value), array_map(fn($type) => $type->getName(), $types))) {
                $object->$key = $value;
                continue;
            }
            // 类型转换处理
            $this->assignConvertedValue($object, $key, $value, $types);
        }

        return $object;
    }

    /**
     * 获取PHP的原生类型名称
     *
     * @param $value
     * @return string
     * @date 2024/8/23 11:57
     * @author 原点 467490186@qq.com
     */
    private function getPhpTypeName($value): string
    {
        $type = gettype($value);
        return match ($type) {
            'integer' => 'int',
            'double' => 'float',
            'boolean' => 'bool',
            'NULL' => 'null',
            default => $type,
        };
    }

    /**
     * 判断属性是否有Trim注解
     *
     * @param ReflectionProperty $property
     * @return bool
     * @date 2024/8/28 11:30
     * @author 原点 467490186@qq.com
     */
    private function hasTrimAttribute(ReflectionProperty $property): bool
    {
        return count(
                array_filter(
                    $property->getAttributes(),
                    fn($attr) => $attr->getName() === Trim::class
                )
            ) > 0;
    }

    /**
     * 根据类型转换值并赋值给对象
     *
     * @param object $object
     * @param string $key
     * @param mixed $value
     * @param array $types
     * @throws ValidateException|ReflectionException
     * @date 2024/8/28 11:23
     * @author 原点 467490186@qq.com
     */
    private function assignConvertedValue(object $object, string $key, mixed $value, array $types): void
    {
        foreach ($types as $type) {
            $typeName = $type->getName();
            if ($type->isBuiltin()) {
                if ($this->trySetType($value, $typeName)) {
                    $object->$key = $value;
                    return;
                }
            } elseif (class_exists($typeName)) {
                if (is_array($value)) {
                    $object->$key = $this->arrayToObject($value, $typeName);
                    return;
                }
                throw new ValidateException("$key 类型不匹配");
            }
        }
        throw new ValidateException("$key 类型不匹配");
    }

    /**
     * 尝试转换并设置类型
     *
     * @param mixed $value
     * @param string $typeName
     * @return bool
     * @date 2024/8/28 11:30
     * @author 原点 467490186@qq.com
     */
    private function trySetType(mixed &$value, string $typeName): bool
    {
        return settype($value, $typeName);
    }

}