# jsonrpc-client
laravel 5.8 swoft json rpc 2.0 客户端

## 快速上手
### 配置说明
1.把 配置文件 swoft_rpc_client.php 复制到Laravel 项目 config 目录下  

```php
    // 连接配置项 
    connection:[
        'default' => [
            'host' => '127.0.0.1',
            'port' => 18307,
            'setting' => [
                'timeout' => 2,
                'write_timeout' => 10,
            ]
        ]
    ],
     // 功能配置项
     'services' => [
        '服务名' => [
            'class' => '', //服务接口类名
            'version' => '1.0', //服务版本,默认: 1.0
            'connection' => '', //服务连接名，默认：default
        ]
     ]
    
```

### 使用说明 
```php
use XsKit\Swoft\Rpc\Client;

# 远程过程调用 
$cli = Client::usage('功能模块名','方法名',['参数'])->call()

# 判断调用是否成功
$cli->isSuccess();

# 获取调用失败信息
$cli->getErrorMessage();

# 获取调用结果
$cli->getResult();

# 获取调用结果，失败抛出 RuntimeException 异常
$cli->getResultOrFail()


```