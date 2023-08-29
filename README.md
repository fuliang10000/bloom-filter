# 基于redis实现的布隆过滤器 #

## 安装 ##

```bash
composer require fuliang/bloom-filter
```

## 使用 ##

### 第一步 ###

```php
<?php
namespace Fuliang\BloomFilter;

use Redis;
/**
 * 重复内容过滤器
 * 该布隆过滤器总位数为2^32位, 判断条数为2^30条. hash函数最优为3个.(能够容忍最多的hash函数个数)
 * 使用的三个hash函数为
 * BKDR, SDBM, JSHash
 *
 * 注意, 在存储的数据量到2^30条时候, 误判率会急剧增加, 因此需要定时判断过滤器中的位为1的的数量是否超过50%, 超过则需要清空.
 */
class FilterRepeatedComments extends BloomFilterRedis
{
    /**
     * 表示判断重复内容的过滤器
     * @var string
     */
    protected string  $bucket = 'rptc';

    protected array $hashFunction = ['BKDRHash', 'SDBMHash', 'JSHash'];

    protected function getRedisClient(): Redis
    {
        return new YourRedis; // 假设这里你已经连接好了
    }
}
```

### 第二步 ###

```php
<?php
require_once 'vendor\autoload.php';
use Fuliang\BloomFilter\FilterRepeatedComments;

$bf = new FilterRepeatedComments();
$bf->add('item1');
$bf->add('item2');

$bf->exists('item1'); //true
$bf->exists('item2'); //true

$bf->delete('item1');
$bf->exists('item1'); //false
```