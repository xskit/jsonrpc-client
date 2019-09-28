# jsonrpc-client
laravel 5.8 swoft json rpc 2.0 客户端

## 快速上手
### 配置说明
1.把 配置文件 swoft_rpc_client.php 复制到Laravel 项目 config 目录下

> connection : 连接配置项  
> functions : 功能配置项
```php
     'functions' => [
            '功能模块名' => [
                'class' => '', //服务接口类名
                'version' => '1.0' //服务版本
            ]
        ]
    
```

### 使用说明 
```php
use XsKit\Swoft\Rpc\Client;

# 远程过程调用 
$cli = Client::usage('功能模块名','方法名',['参数'])->call()

# 获取调用结果
$cli->getResult();

# 判断调用是否成功
$cli->isSuccess();

# 获取调用失败信息
$cli->getErrorMessage();
```