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

abstract class BaseValidatorEntity extends BaseEntity
{
    /**
     * @param array $data
     * @throws \ReflectionException
     */
    public function __construct(array $data = [])
    {
        parent::__construct($data);
        (new Validator())->validate($this);
    }
}