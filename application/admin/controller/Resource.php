<?php
namespace app\admin\controller;
use think\Request;
use think\Session;
use think\Db;
use app\admin\model\Download as DownloadModel;

class Resource extends Base
{
	public function resourceList()
	{
		$keywords = $this->request->param('keywords');
		$logmin = $this->request->param('logmin');
		$logmax = $this->request->param('logmax');
		$selected = $this->request->param('selected');
		if (is_null($logmin))
			$logmin = '1900-01-01';
		if (is_null($logmax))
			$logmax = date("Y-m-d", time());
		if (is_null($selected))
			$selected = '%';
		$pageParam['query']['keywords'] = $keywords;
		$arrylist = Db::table('download')
			->where('filename', 'like', '%'.$keywords.'%')
			->where('category', 'like', $selected)
			->where('uploadtime', '>=', $logmin)
			->where('uploadtime', '<=', $logmax)
			->select();
		$this -> view -> logmin = $logmin;
		$this -> view -> logmax = $logmax;
		$this -> view -> keywords = $keywords;
		$this -> view -> assign('arrylist', $arrylist);
        return $this -> view -> fetch('resource_list');  //渲染首页模板
	}
	
	// 从数据库中删除资源信息
	public function deleteResource(Request $request)
	{
		$resource_id = $request -> param('id');
		DownloadModel::destroy($resource_id);
	}
	
	//渲染编辑界面
	public function ResourceEdit(Request $request)
    {
        $resource_id = $request -> param('id');
        $result = DownloadModel::get($resource_id);
        $this->view->assign('title','编辑资源信息');
        $this->view->assign('resource_info',$result->getData());
        return $this->view->fetch('resource_edit');
    }
	
	//更新数据操作
    public function editResource(Request $request)
    {
        //获取表单返回的数据
        $param = $request -> param();

        //去掉表单中为空的数据,即没有修改的内容
        foreach ($param as $key => $value ){
            if (!empty($value)){
                $data[$key] = $value;
            }
        }
		
        $result = Db::table('download')
			->where('id', '=', $data['id'])
			->update($data);

        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }
}