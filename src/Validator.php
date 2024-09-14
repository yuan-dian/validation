<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/6/6
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use yuandian\attributes\Scene;
use yuandian\attributes\ValidateAttribute;
use yuandian\exception\ValidateException;

class Validator
{
    /**
     * 是否批量验证
     * @var bool
     */
    protected bool $batch = false;
    /**
     * 验证失败错误信息
     * @var array
     */
    protected array $error = [];

    /**
     * 设置批量验证
     * @param bool $batch
     * @return $this
     * @date 2024/6/6 上午10:14
     * @author 原点 467490186@qq.com
     */
    public function batch(bool $batch = true): static
    {
        $this->batch = $batch;

        return $this;
    }

    /**
     * 参数校验
     * @param object $entity
     * @param string $scene 场景
     * @date 2024/6/6 上午10:28
     * @author 原点 467490186@qq.com
     */
    public function validate(object $entity, string $scene = ''): void
    {
        $reflectionClass = new ReflectionClass($entity);

        // 获取属性
        $properties = $this->getProperties($reflectionClass, $scene);
        // 验证属性
        foreach ($properties as $property) {
            $this->validateProperty($entity, $property);
        }

        // 检查错误
        if (!empty($this->error)) {
            throw new ValidateException($this->error);
        }
    }

    /**
     * 获取对象属性
     * @param ReflectionClass $reflectionClass
     * @param string $scene
     * @return array
     * @date 2024/9/6 14:14
     * @author 原点 467490186@qq.com
     */
    private function getProperties(ReflectionClass $reflectionClass, string $scene): array
    {
        // 如果没有场景，直接返回所有属性
        if (empty($scene)) {
            return $reflectionClass->getProperties(ReflectionProperty::IS_PUBLIC);
        }

        // 获取场景注解
        $scene_list = $this->getSceneList($reflectionClass);

        // 检查场景是否存在
        if (!isset($scene_list[$scene])) {
            throw new ValidateException("Invalid scene");
        }

        // 根据场景返回属性
        $properties = [];
        foreach ($scene_list[$scene] as $key) {
            if ($reflectionClass->hasProperty($key)) {
                $reflectionProperty = $reflectionClass->getProperty($key);
                if ($reflectionProperty->isPublic()) {
                    $properties[] = $reflectionProperty;
                }
            }
        }

        return $properties;
    }

    /**
     * 获取对象场景列表
     * @param ReflectionClass $reflectionClass
     * @return array
     * @date 2024/9/6 14:15
     * @author 原点 467490186@qq.com
     */
    private function getSceneList(ReflectionClass $reflectionClass): array
    {
        $scene_list = [];
        $attributes = $reflectionClass->getAttributes(Scene::class);

        foreach ($attributes as $attribute) {
            /**
             * @var Scene $newInstance
             */
            $newInstance = $attribute->newInstance();
            $scene_list[$newInstance->name] = $newInstance->properties;
        }

        return $scene_list;
    }

    /**
     * 验证属性
     * @param object $entity
     * @param ReflectionProperty $property
     * @date 2024/9/6 14:15
     * @author 原点 467490186@qq.com
     */
    public function validateProperty(object $entity, ReflectionProperty $property): void
    {
        $attributes = $property->getAttributes(ValidateAttribute::class, ReflectionAttribute::IS_INSTANCEOF);
        $key = $property->getName();
        foreach ($attributes as $attribute) {
            /**  @var ValidateAttribute $instance */
            $instance = $attribute->newInstance();
            $value = $property->isInitialized($entity) ? $property->getValue($entity) : null;
            if (!$instance->validate($value)) {
                if ($this->batch) {
                    $this->error[$key][] = $instance->message;
                } else {
                    throw new ValidateException($instance->message);
                }
            }
        }
        if (isset($this->error[$key])) {
            $this->error[$key] = implode(' & ', $this->error[$key]);
        }
    }
}