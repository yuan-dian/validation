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

    // 缓存已反射的类，以避免重复创建
    private static array $reflectionCache = [];

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
     * @return object|array
     * @throws ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public static function arrayToObject(array $from, string|object $object): object|array
    {
        // 集合类型处理
        if (array_is_list($from)) {
            return array_map(
                fn(mixed $item) => self::arrayToObject($item, $object),
                $from
            );
        }

        // 从缓存获取反射类，避免重复创建
        $reflectionClass = self::getReflectionClass($object);
        $object = is_string($object) ? $reflectionClass->newInstanceWithoutConstructor() : $object;

        foreach ($from as $key => $value) {
            // 检查属性是否定义
            if (!$reflectionClass->hasProperty($key)) {
                continue;
            }
            $property = $reflectionClass->getProperty($key);
            $propertyType = $property->getType();

            // 判断是否支持自动去除空格的处理
            if (self::hasTrimAttribute($property, $value)) {
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
     * 检查是否存在去除空格的属性注解
     *
     * @param ReflectionProperty $property
     * @param mixed $value
     * @return bool
     */
    private static function hasTrimAttribute(ReflectionProperty $property, mixed $value): bool
    {
        return is_string($value) && !empty($property->getAttributes(Trim::class));
    }

    /**
     * 从缓存获取反射类，如果缓存中不存在则创建
     *
     * @param string|object $object
     * @return ReflectionClass
     * @throws ReflectionException
     */
    private static function getReflectionClass(string|object $object): ReflectionClass
    {
        $className = is_object($object) ? get_class($object) : $object;

        if (!isset(self::$reflectionCache[$className])) {
            self::$reflectionCache[$className] = new ReflectionClass($className);
        }

        return self::$reflectionCache[$className];
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
                // 处理基础类型转换
                if (self::isConvertible(self::getPhpTypeName($value), $typeName) && settype($value, $typeName)) {
                    $property->setValue($object, $value);
                    return;
                }
                // 处理对象类型
                if (class_exists($typeName) && is_array($value)) {
                    $property->setValue($object, self::arrayToObject($value, $typeName));
                    return;
                }

                // 处理枚举类型
                if (enum_exists($typeName)) {
                    self::handleEnumType($property, $object, $value, $typeName);
                    return;
                }
            } catch (\Throwable $e) {
                // 类型转换失败，跳过，尝试下一个类型
                continue;
            }
        }
        throw new ParameterException("Property '{$property->getName()}' type mismatch.");
    }

    /**
     * 处理枚举类型的赋值
     *
     * @param ReflectionProperty $property
     * @param object $object
     * @param mixed $value
     * @param string $typeName
     */
    private static function handleEnumType(
        ReflectionProperty $property,
        object $object,
        mixed $value,
        string $typeName
    ): void {
        // 如果值已经是枚举类型，直接赋值
        if ($value instanceof $typeName) {
            $property->setValue($object, $value);
            return;
        }
        if (!is_string($value) && !is_int($value)) {
            throw new ParameterException("$typeName 枚举值不合法");
        }
        // 处理基础枚举类型（BackedEnum）
        if (is_subclass_of($typeName, \BackedEnum::class)) {
            foreach ($typeName::cases() as $case) {
                if ($case->value === $value) {
                    $property->setValue($object, $case);
                    return;
                }
            }
        }
        // 处理无值枚举（UnitEnum）
        if (is_subclass_of($typeName, \UnitEnum::class) && is_string($value)) {
            foreach ($typeName::cases() as $case) {
                if ($case->name === $value) {
                    $property->setValue($object, $case);
                    return;
                }
            }
        }
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