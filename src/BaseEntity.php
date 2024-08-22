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
            if ($reflectionClass->hasProperty($key)) {
                $property = $reflectionClass->getProperty($key);

                // 获取属性类型
                $propertyType = $property->getType();
                if ($propertyType !== null) {
                    $propertyClassName = $propertyType->getName();

                    if ($propertyType->isBuiltin()) {
                        // 对于内建类型，进行类型转换
                        settype($value, $propertyClassName);
                        $object->$key = $value;
                    } elseif (class_exists($propertyClassName)) {
                        // 对于类类型，递归转换
                        $object->$key = $this->arrayToObject($value, $propertyClassName);
                    }
                } else {
                    // 没有类型提示，直接赋值
                    $object->$key = $value;
                }
            }
        }

        return $object;
    }

}