# 通过定义PHP Doc规则生成API文档

[![GitHub forks](https://img.shields.io/github/forks/liluoao/api-doc.svg?style=plastic)](https://github.com/liluoao/api-doc/network)
----

## 规则:

>首行为空
第二行为描述
第三行以**api**开头，接上请求方式和URL
参数备注中不能出现空格，建议替换成标点符号

```php
/**
 * 获取所有列表
 * api GET /index/all
 * @param array $condition 查询条件
 * @param int $page 页数
 * @param int $limit 每页个数
 * @return array 列表结果集
 */
public function all(array $condition, int $page, int $limit) :array {

}
```
