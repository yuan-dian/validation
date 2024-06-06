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

namespace origin;

use origin\attributes\ValidateAttribute;
use origin\exception\ValidateException;
use ReflectionClass;
use ReflectionProperty;

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
     * @date 2024/6/6 上午10:28
     * @author 原点 467490186@qq.com
     */
    public function validate(object $entity): void
    {
        $reflectionClass = new ReflectionClass($entity);
        foreach ($reflectionClass->getProperties() as $property) {
            $this->validateProperty($entity, $property);
        }
    }

    public function validateProperty(object $entity, ReflectionProperty $property): void
    {
        $attributes = $property->getAttributes();
        foreach ($attributes as $attribute) {
            /**  @var ValidateAttribute $instance */
            $instance = $attribute->newInstance();
            $value = $property->isInitialized($entity) ? $property->getValue($entity) : null;
            if (!$instance->validate($value)) {
                if ($this->batch) {
                    $key = $property->getName();
                    $message = $this->error[$key] ?? '';
                    $this->error[$key] = (empty($message) ? '' : $message . ';') . $instance->message;
                } else {
                    throw new ValidateException($instance->message);
                }
            }
        }
        if (!empty($this->error)) {
            throw new ValidateException($this->error);
        }
    }
}