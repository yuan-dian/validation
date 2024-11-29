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

namespace yuandian\Validation;

use ReflectionAttribute;
use ReflectionClass;
use ReflectionProperty;
use yuandian\Validation\Exception\ValidateException;
use yuandian\Validation\Rules\Scene;

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
     * 缓存的反射类列表
     * @var array
     */
    private static array $reflectionCache = [];

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
        $reflectionClass = $this->getReflectionClass($entity);

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
     * 获取反射类实例，缓存反射信息，避免重复实例化
     * @param object $entity
     * @return ReflectionClass
     */
    private function getReflectionClass(object $entity): ReflectionClass
    {
        $className = get_class($entity);
        if (!isset($this->reflectionCache[$className])) {
            self::$reflectionCache[$className] = new ReflectionClass($entity);
        }
        return self::$reflectionCache[$className];
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
        $sceneList = $this->getSceneList($reflectionClass);

        // 检查场景是否存在
        if (!isset($sceneList[$scene])) {
            throw new ValidateException("Invalid scene");
        }

        // 根据场景返回属性
        $properties = [];
        foreach ($sceneList[$scene] as $key) {
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
        $sceneList = [];
        $attributes = $reflectionClass->getAttributes(Scene::class);

        foreach ($attributes as $attribute) {
            /** @var Scene $sceneInstance */
            $newInstance = $attribute->newInstance();
            $sceneList[$newInstance->name] = $newInstance->properties;
        }

        return $sceneList;
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
        $attributes = $property->getAttributes(Rule::class, ReflectionAttribute::IS_INSTANCEOF);
        $key = $property->getName();
        foreach ($attributes as $attribute) {
            /**  @var Rule $instance */
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