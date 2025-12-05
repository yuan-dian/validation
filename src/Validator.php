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

use yuandian\Tools\reflection\ClassReflector;
use yuandian\Tools\reflection\PropertyReflection;
use yuandian\Validation\Exception\ValidateException;
use yuandian\Validation\Rules\Each;
use yuandian\Validation\Rules\Scene;
use yuandian\Validation\Rules\When;

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
     * 场景缓存
     */
    private static array $sceneCache = [];

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
     * @throws \ReflectionException
     * @author 原点 467490186@qq.com
     */
    public function validate(object $entity, string $scene = ''): void
    {
        $reflectionClass = new ClassReflector($entity);

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
     * @param ClassReflector $reflectionClass
     * @param string $scene
     * @return array
     * @date 2024/9/6 14:14
     * @author 原点 467490186@qq.com
     */
    private function getProperties(ClassReflector $reflectionClass, string $scene = ''): array
    {
        $props = $reflectionClass->getPublicProperties();
        // 如果没有场景，直接返回所有属性
        if (empty($scene)) {
            return $props;
        }

        // 获取场景注解
        $sceneList = $this->getSceneList($reflectionClass);

        // 检查场景是否存在
        if (!isset($sceneList[$scene])) {
            throw new ValidateException("Invalid scene: {$scene}");
        }
        $allow = array_flip($sceneList[$scene]);

        return array_filter(
            $props,
            fn($prop) => isset($allow[$prop->getName()])
        );
    }

    /**
     * 获取对象场景列表
     * @param ClassReflector $reflectionClass
     * @return array
     * @date 2024/9/6 14:15
     * @author 原点 467490186@qq.com
     */
    private function getSceneList(ClassReflector $reflectionClass): array
    {
        $class = $reflectionClass->getName();

        if (isset(self::$sceneCache[$class])) {
            return self::$sceneCache[$class];
        }

        $sceneList = [];
        foreach ($reflectionClass->getAttributes(Scene::class) as $scene) {
            $sceneList[$scene->name] = $scene->properties;
        }

        return self::$sceneCache[$class] = $sceneList;
    }

    /**
     * 验证属性
     * @param object $entity
     * @param PropertyReflection $property
     * @date 2024/9/6 14:15
     * @throws \ReflectionException
     * @author 原点 467490186@qq.com
     */
    public function validateProperty(object $entity, PropertyReflection $property): void
    {
        $rules = $property->getAttributes(Rule::class);
        if (empty($rules)) {
            return;
        }
        $key = $property->getName();
        $value = $property->isInitialized($entity) ? $property->getValue($entity) : null;
        $is_validate = true;
        foreach ($rules as $rule) {
            // 拦截 Each
            if ($rule instanceof Each) {
                $this->validateEach($key, $value, $rule);
                continue;
            }
            // 拦截 Each
            if ($rule instanceof When) {
                $this->validateWhen($entity, $property, $value, $rule);
                continue;
            }
            if (!$rule->validate($value)) {
                $is_validate = false;
                if (!$this->batch) {
                    throw new ValidateException($rule->message);
                }
                $this->error[$key][] = $rule->message;
            }
        }
        // 如果子项本身是对象，递归验证
        if ($is_validate && is_object($value)) {
            $this->validate($value);
        }
    }

    /**
     * @param string $field
     * @param mixed $value
     * @param Each $each
     * @date 2025/12/5 下午3:18
     * @author 原点 467490186@qq.com
     */
    private function validateEach(string $field, mixed $value, Each $each): void
    {
        if (!array_is_list($value)) {
            $this->error[$field][] = "{$field} must be an list array";
            return;
        }

        foreach ($value as $index => $item) {
            // 支持 posts.*.title
            if ($each->field !== null) {
                $errorKey = "{$field}.{$index}.{$each->field}";
                if (!is_array($item) || !array_key_exists($each->field, $item)) {
                    if (!$this->batch) {
                        throw new ValidateException($errorKey . ": field not exists");
                    }
                    $this->error[$errorKey][] = $errorKey . ": field not exists";
                    continue;
                }
                $itemValue = $item[$each->field];
            } else {
                // 支持纯数组：tags.*
                $itemValue = $item;
                $errorKey = "{$field}.{$index}";
            }
            $rules = is_array($each->rules) ? $each->rules : [$each->rules];
            foreach ($rules as $rule) {
                if (!$rule->validate($itemValue)) {
                    if (!$this->batch) {
                        throw new ValidateException($errorKey . ": " . $rule->message);
                    }
                    $this->error[$errorKey][] = $errorKey . ": " . $rule->message;
                }
            }
        }
    }

    /**
     * 条件规则认证
     * @param object $entity
     * @param PropertyReflection $property
     * @param mixed $value
     * @param When $when
     * @date 2025/12/5 下午3:49
     * @author 原点 467490186@qq.com
     */
    private function validateWhen(object $entity, PropertyReflection $property, mixed $value, When $when): void
    {
        if (!property_exists($entity, $when->field)) {
            return;
        }

        $triggerValue = $entity->{$when->field};

        if ($triggerValue !== $when->equals) {
            return;
        }

        $key = $property->getName();
        $rules = is_array($when->rules) ? $when->rules : [$when->rules];
        foreach ($rules as $rule) {
            if (!$rule->validate($value)) {
                if (!$this->batch) {
                    throw new ValidateException($rule->message);
                }
                $this->error[$key][] = $rule->message;
            }
        }
    }
}