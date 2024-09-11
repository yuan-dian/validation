<?php
// +----------------------------------------------------------------------
// | 
// +----------------------------------------------------------------------
// | @copyright (c) 原点 All rights reserved.
// +----------------------------------------------------------------------
// | Author: 原点 <467490186@qq.com>
// +----------------------------------------------------------------------
// | Date: 2024/9/6
// +----------------------------------------------------------------------

declare (strict_types=1);

namespace yuandian\attributes;

use Attribute;

/**
 * Scene validate
 */
#[Attribute(Attribute::TARGET_CLASS | Attribute::TARGET_METHOD)]
class Scene
{
    /**
     * @param string $name The name of the scene
     * @param array $properties Verify the list of attributes
     */
    public function __construct(public string $name, public array $properties = [])
    {
    }
}