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
use ReflectionUnionType;
use yuandian\attributes\Trim;
use yuandian\exception\ValidateException;

abstract class BaseEntity
{
    /**
     * @throws \Exception
     */
    public function __construct(array $data = [])
    {
        try {
            $this->arrayToObject($data, $this);
        } catch (\ReflectionException $e) {
            throw new \Exception($e->getMessage());
        }
    }

    /***
     * @param array $array
     * @param string|object $object
     * @return object
     * @throws \ReflectionException
     * @date 2024/8/22 15:14
     * @author 原点 467490186@qq.com
     */
    public function arrayToObject(array $array, string|object $object): object
    {
        $reflectionClass = new ReflectionClass($object);
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
            // 判断是否有Trim注解，自动去除空格
            if (is_string($value)) {
                $attributes = $property->getAttributes();
                foreach ($attributes as $attribute) {
                    if ($attribute->getName() === Trim::class) {
                        $value = trim($value);
                    }
                }
            }
            // 没有指定类型，直接赋值
            if ($propertyType === null) {
                $object->$key = $value;
                continue;
            }
            // 检查是否为可空类型
            $isNullable = $propertyType->allowsNull();

            // 如果值为 null 且属性允许 null，则直接赋值
            if (is_null($value) && $isNullable) {
                $object->$key = null;
                continue;
            }
            // 属性类型处理，支持联合类型处理
            $types = $propertyType instanceof ReflectionUnionType ? $propertyType->getTypes() : [$propertyType];

            $typeNames = [];
            foreach ($types as $type) {
                $typeNames[] = $type->getName();
            }

            $valueTypeName = $this->getPhpTypeName($value);

            if (in_array($valueTypeName, $typeNames)) {
                $object->$key = $value;
                continue;
            }
            foreach ($types as $type) {
                $typeName = $type->getName();
                if ($type->isBuiltin()) {
                    try {
                        settype($value, $typeName);
                        $object->$key = $value;
                    } catch (\Exception) {
                        throw new ValidateException("$key 类型不匹配");
                    }
                    break;
                } elseif (class_exists($typeName)) {
                    if (!is_array($value)) {
                        throw new ValidateException("$key 类型不匹配");
                    }
                    $object->$key = $this->arrayToObject($value, $typeName);
                    break;
                }
            }
        }

        return $object;
    }

    /**
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

}