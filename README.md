# 通过定义PHP Doc规则生成API文档

[![GitHub stars](https://img.shields.io/github/stars/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/stargazers) [![GitHub forks](https://img.shields.io/github/forks/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/network) [![Packagist](https://img.shields.io/packagist/v/liluoao/api-doc.svg?style=for-the-badge)](https://packagist.org/packages/liluoao/api-doc) [![GitHub license](https://img.shields.io/github/license/liluoao/api-doc.svg?style=for-the-badge)](https://github.com/liluoao/api-doc/blob/master/LICENSE)

![](https://github.styleci.io/repos/120990562/shield?branch=master)
----

### 使用方法：

1. 引入本库
```
composer require liluoao/api-doc
```

2. 实例化核心类
>第一个参数是需要生成文档的文件夹路径
>
>第二个参数可选，保存生成文档的路径，默认为当前目录
```php
$apiDoc = new \Liluoao\ApiDoc('your-file-folder');
```

3. 配置
>包括你的文档名，和 `snake_case` 转换 `camelCase` 的配置
```php
$apiDoc->setName('doc-name');
$apiDoc->setCamel2SnakeConfig(false, false, 0, 0);
```

4. 执行
```php
$apiDoc->init();
```
### 规则:

>首行为空
>
>第二行为描述
>
>第三行以**api**开头，接上请求方式和URL
>
>参数备注中不能出现空格，建议替换成标点符号

### 格式示例：

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

### 联系我们：

  欢迎对本项目提**Issue**和**PR**

[![Email](https://img.shields.io/badge/%E9%82%AE%E7%AE%B1-liluoao%40qq.com-orange.svg?style=for-the-badge)](mailto:liluoao@qq.com)  [![SegmentFault](https://img.shields.io/badge/SegmentFault-李罗奥-brightgreen.svg?style=for-the-badge)](https://segmentfault.com/u/liluoao)  [![Juejin](https://img.shields.io/badge/掘金-李罗奥-blue.svg?style=for-the-badge)](https://juejin.im/user/5a19374cf265da4332274600)
