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

namespace yuandian\Validation;

use ReflectionException;
use yuandian\Validation\Exception\ParameterException;
use yuandian\Validation\Utils\BeanUtil;

abstract class BaseEntity
{
    /**
     * @param array $data
     * @throws ParameterException|ReflectionException
     */
    public function __construct(array $data = [])
    {
        if (!empty($data)) {
            BeanUtil::arrayToObject($data, $this);
        }
    }

    public function toArray(): array
    {
        return get_object_vars($this);
    }


}