<?php
namespace app\index\model;

use think\Model;

class Download extends Model
{
	protected $autoWriteTimestamp = true; //自动写入
	protected $uploadtime = 'uploadtime';
    // 时间字段取出后的默认时间格式
    protected $dateFormat = 'Y年m月d日';
}
