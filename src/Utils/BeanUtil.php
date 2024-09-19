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

namespace yuandian\Validation\Utils;

use ReflectionClass;
use ReflectionException;
use ReflectionProperty;
use ReflectionUnionType;
use yuandian\Validation\Exception\ParameterException;
use yuandian\Validation\Rules\Trim;

/**
 * Bean 工具类
 */
class BeanUtil
{
    /**
     * 对象转对象
     * 【只处理公共属性】
     * @param object $from
     * @param string|object $object
     * @return object
     * @throws ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public static function objectToObject(object $from, string|object $object): object
    {
        return self::arrayToObject(get_object_vars($from), $object);
    }

    /**
     * 数组转对象
     *
     * @param array $from
     * @param string|object $object
     * @param bool $isCollection
     * @return object|array
     * @throws ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public static function arrayToObject(array $from, string|object $object, bool $isCollection = false): object|array
    {
        // 集合类型处理
        if ($isCollection) {
            return array_map(
                fn(mixed $item) => self::arrayToObject($item, $object),
                $from
            );
        }

        $reflectionClass = new ReflectionClass($object);
        // 如果$object是字符串，则创建一个新的实例
        if (is_string($object)) {
            $object = $reflectionClass->newInstanceWithoutConstructor();
        }

        foreach ($from as $key => $value) {
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
                $property->setValue($object, $value);
                continue;
            }

            // 属性类型处理，支持联合类型处理
            $types = $propertyType instanceof ReflectionUnionType ? $propertyType->getTypes() : [$propertyType];

            // 优先匹配值的实际类型
            if (in_array(self::getPhpTypeName($value), array_map(fn($type) => $type->getName(), $types))) {
                $property->setValue($object, $value);
                continue;
            }
            // 类型转换处理
            self::assignConvertedValue($property, $object, $value, $types);
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
     * @param ReflectionProperty $property
     * @param object $object
     * @param mixed $value
     * @param array $types
     * @date 2024/8/28 11:23
     * @author 原点 467490186@qq.com
     */
    private static function assignConvertedValue(
        ReflectionProperty $property,
        object $object,
        mixed $value,
        array $types
    ): void {
        foreach ($types as $type) {
            $typeName = $type->getName();
            try {
                if (self::isConvertible(self::getPhpTypeName($value), $typeName) && settype($value, $typeName)) {
                    $property->setValue($object, $value);
                    return;
                } elseif (class_exists($typeName) && is_array($value)) {
                    $property->setValue($object, self::arrayToObject($value, $typeName));
                    return;
                }
            } catch (\Throwable $e) {
                // 类型转失败，尝试下一个类型
            }
        }
        throw new ParameterException("$property->name Type mismatch");
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