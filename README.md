# 参数验证 validator
 使用PHP原生属性进行参数校验

# 使用示例
 - 定义实体文件 ```UserRequest.php```
```php
use Origin\Attributes\NotEmpty;
use Origin\Attributes\Email;

class UserRequest {
    #[NotEmpty(message: "Name cannot be empty.")]
    public string $name;

    #[Email(message: "Invalid email format.")]
    #[NotEmpty(message: "Email cannot be empty.")]
    public string $email;
}
```
- 赋值与验证
```php
require_once 'vendor/autoload.php';

use yuandian\Validator
use yuandian\exception\ValidateException;

$request = new UserRequest();
$request->name = '张三';
$request->email = 'zhangsan@Validator'
// 验证实体
try {
    $validator = new Validator();
   
    $validator->validate($userRequest);
    // 批量验证
    // $validator->batch(true)->validate($userRequest);
} catch (ValidateException $e) {
    echo "Validation errors: " . $e->getMessage() . "\n";
}
```

# 自动赋值&&自动验证
- 定义实体文件 ```UserRequest.php```
```php
use yuandian\attributes\NotEmpty;
use yuandian\attributes\Email;
use yuandian\BaseValidatorEntity;

class UserRequest extends BaseValidatorEntity {
    #[NotEmpty(message: "Name cannot be empty.")]
    public string $name;

    #[Email(message: "Invalid email format.")]
    #[NotEmpty(message: "Email cannot be empty.")]
    public string $email;
}
```

- 赋值与验证
```php
require_once 'vendor/autoload.php';

use yuandian\exception\ValidateException;

// 模拟请求数据
$requestData = [
    'name' => 'John Doe',
    'email' => 'invalid-email'
];

// 验证实体
try {
    $request = new UserRequest($requestData);
} catch (ValidateException $e) {
    echo "Validation errors: " . $e->getMessage() . "\n";
}
```

## 捐献

![](./wechat.png)
![](./alipay.png)