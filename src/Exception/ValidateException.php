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

namespace yuandian\Validation\Exception;

/**
 * 数据验证异常
 */
class ValidateException extends \RuntimeException
{
    protected string|array $error;

    public function __construct($error)
    {
        $this->error = $error;
        $this->message = is_array($error) ? $this->formatErrors($error) : $error;
        parent::__construct($this->message);
    }

    /**
     * 获取验证错误信息
     * @access public
     * @return array|string
     */
    public function getError(): array|string
    {
        return $this->error;
    }

    /**
     * 格式化错误信息
     * @param array $errors
     * @return string
     * @date 2025/12/5 下午5:21
     * @author 原点 467490186@qq.com
     */
    protected function formatErrors(array $errors): string
    {
        $result = [];

        foreach ($errors as $field => $messages) {
            if (is_array($messages)) {
                $result[] = $field . ': ' . implode(', ', $messages);
            } else {
                $result[] = $field . ': ' . $messages;
            }
        }

        return implode('; ', $result);
    }
}
