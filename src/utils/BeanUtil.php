<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/9/10
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\utils;

use ReflectionClass;
use ReflectionException;
use ReflectionUnionType;
use yuandian\attributes\Trim;
use yuandian\exception\ParameterException;

/**
 * Bean 工具类
 */
class BeanUtil
{
    /***
     * 数组转对象
     *
     * @param array $array
     * @param string|object $object
     * @return object
     * @throws ParameterException|ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public static function arrayToObject(array $array, string|object $object): object
    {
        $reflectionClass = new ReflectionClass($object);
        // 如果$object是字符串，则创建一个新的实例
        if (is_string($object)) {
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }

        foreach ($array as $key => $value) {
            // 检查属性是否定义
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }
            $property = $reflectionClass->getProperty($key);
            $propertyType = $property->getType();

            // 自动去除空格的处理
            if (is_string($value) && !empty($property->getAttributes(Trim::class))) {
                $value = trim($value);
            }

            // 处理无类型或空值的情况
            if ($propertyType === null || (is_null($value) && $propertyType->allowsNull())) {
                $object->$key = $value;
                continue;
            }

            // 属性类型处理，支持联合类型处理
            $types = $propertyType instanceof ReflectionUnionType ? $propertyType->getTypes() : [$propertyType];

            // 优先匹配值的实际类型
            if (in_array(self::getPhpTypeName($value), array_map(fn($type) => $type->getName(), $types))) {
                $object->$key = $value;
                continue;
            }
            // 类型转换处理
            self::assignConvertedValue($object, $key, $value, $types);
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
    private static function getPhpTypeName($value): string
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
     * 根据类型转换值并赋值给对象
     *
     * @param object $object
     * @param string $key
     * @param mixed $value
     * @param array $types
     * @throws ParameterException|ReflectionException
     * @date 2024/8/28 11:23
     * @author 原点 467490186@qq.com
     */
    private static function assignConvertedValue(object $object, string $key, mixed $value, array $types): void
    {
        foreach ($types as $type) {
            $typeName = $type->getName();
            try {
                if (self::isConvertible(self::getPhpTypeName($value), $typeName) && settype($value, $typeName)) {
                    $object->$key = $value;
                    return;
                } elseif (class_exists($typeName) && is_array($value)) {
                    $object->$key = self::arrayToObject($value, $typeName);
                    return;
                }
            } catch (\Throwable $e) {
                // 类型转失败，尝试下一个类型
            }
        }
        throw new ParameterException("$key Type mismatch");
    }

    /**
     * 检查类型是否可以转换
     *
     * @param string $sourceType
     * @param string $targetType
     * @return bool
     * @date 2024/8/30 09:32
     * @author 原点 467490186@qq.com
     */
    private static function isConvertible(string $sourceType, string $targetType): bool
    {
        if ($sourceType === $targetType) {
            return true; // 同一类型可以转换
        }
        $conversionTable = [
            'int'    => ['float', 'string', 'bool'],
            'float'  => ['int', 'string', 'bool'],
            'string' => ['int', 'float', 'bool'],
            'bool'   => ['int', 'float', 'string'],
            'array'  => ['object'],
            'object' => ['array', 'string'],
            'null'   => ['int', 'float', 'string', 'bool', 'array', 'object'],
        ];

        return in_array($targetType, $conversionTable[$sourceType] ?? [], true);
    }
}