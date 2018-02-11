# 通过定义PHP Doc规则生成API文档

[![GitHub stars](https://img.shields.io/github/stars/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/stargazers) [![GitHub forks](https://img.shields.io/github/forks/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/network) [![Packagist](https://img.shields.io/packagist/v/liluoao/api-doc.svg?style=for-the-badge)](https://packagist.org/packages/liluoao/api-doc) [![GitHub license](https://img.shields.io/github/license/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/blob/master/LICENSE)
----

### 规则:

>首行为空
>
>第二行为描述
>
>第三行以**api**开头，接上请求方式和URL
>
>参数备注中不能出现空格，建议替换成标点符号

### 使用方法：

1. 引入本库
```
composer require liluoao/api-doc
```
或直接下载源码
```php
use your-namespace\ApiDoc;
//or
require 'src/ApiDoc.php';
```

2. 实例化核心类
>第一个参数是需要生成文档的文件夹路径
>
>第二个参数可选,保存生成文档的路径,默认为当前目录
```php
$apiDoc = new ApiDoc('test');
```

3. 配置（*可选*）
>包括你的文档名，和 `snake_case` 转换 `camelCase` 的配置
```php
$apiDoc->setName('example');
$apiDoc->setCamel2SnakeConfig(false, false, 0, 0);
```

4. 执行
```php
$apiDoc->init();
```

### 示例：

需要生成的文件：
>示例代码 `test/index.php`
```php
/**
 * 组合一句问候
 * api GET /index/hello
 * @param string $name 你想问候的人
 * @param string $say 问候语
 * @return string 组合后的话
 */
public function hello(string $name, string $say): string {
    return "Hello,{$name},{$say}";
}
```
#### 结果：
![example](https://raw.githubusercontent.com/liluoao/api-doc/master/test/example.png)

>注：生成后引入LayUI的路径需根据你生成路径修改
