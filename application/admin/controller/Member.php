<?php
namespace app\admin\controller;
use think\Request;
use think\Session;
use think\Db;

class Member extends Base
{
	public function memberList()
	{
		$arrylist = Db::table('member')->select();
		$this -> view -> assign('arrylist', $arrylist);
        return $this -> view -> fetch('member_list');  //渲染首页模板
	}
	
	// 渲染添加界面
	public function memberAdd()
	{
		return $this->view->fetch('member_add');
	}
	
	// 从数据库中删除成员信息
	public function delMember(Request $request)
	{
		$member_id = $request -> param('id');
		Db::table('member') -> delete($member_id);
	}
	
	// 渲染编辑界面
	public function memberEdit(Request $request)
	{
        $member_id = $request -> param('id');
        $result = Db::table('member') -> find($member_id);
        $this->view->assign('title','编辑资源信息');
        $this->view->assign('member_info',$result);
        return $this->view->fetch('member_edit');
	}
	
	public function editMember()
	{
		$filename=$_FILES['myFile']['name'];
		$type=$_FILES['myFile']['type'];
		$tmp_name=$_FILES['myFile']['tmp_name'];
		$size=$_FILES['myFile']['size'];
		$error=$_FILES['myFile']['error'];
		
		// 获取文件名、后缀、作者、上传者
		$houzhui = substr(strrchr($filename, '.'), 1);
		$result = basename($filename,".".$houzhui);
		$name = $_POST['name'];
		$sex = $_POST['sex'];
		$direction = $_POST['direction'];
		$intro = $_POST['intro'];
		$id = $_POST['id'];
		
		// 检查是否为图片
		$filetype = ['image/jpg', 'image/jpeg', 'image/gif', 'image/bmp', 'image/png'];
		if (!in_array($type, $filetype))
		{
			echo "<script>alert('不是图片类型！')</script>";
			return;
		}
		// 查看是否已存在相同的
		$findid = Db::table('member')
			->where('photoname', '=', $filename)
			->find();
		if (is_null($findid))
		{
			move_uploaded_file($tmp_name, "img/".$filename);
		}
		if ($error==0) {
			date_default_timezone_set('Asia/Shanghai');
			$data = [
				'name' => $name,
				'sex' => $sex,
				'direction' => $direction,
				'intro' => $intro,
				'photoname' => $filename,
				'updatetime' => date('Y-m-d', time()),
			];
			Db::table('member')
				->where('id', '=', $id)
				->update($data);
			echo "<script>alert('更新成功！')</script>";
		}
		else {
			switch ($error){
				case 1:
					echo "<script>alert('超过了上传文件的最大值，请上传1G以下文件！')</script>";
					break;
				case 2:
					echo "<script>alert('上传文件过多，请一次上传20个及以下文件！')</script>";
					break;
				case 3:
					echo "<script>alert('文件并未完全上传，请再次尝试！')</script>";
					break;
				case 4:
					echo "<script>alert('未选择上传文件！')</script>";
					break;
				case 5:
					echo "<script>alert('上传文件为0！')</script>";
					break;
			}
		}
	}
	
	// 上传成员信息
	public function doaction()
	{
		$filename=$_FILES['myFile']['name'];
		$type=$_FILES['myFile']['type'];
		$tmp_name=$_FILES['myFile']['tmp_name'];
		$size=$_FILES['myFile']['size'];
		$error=$_FILES['myFile']['error'];
		
		// 获取文件名、后缀、作者、上传者
		$houzhui = substr(strrchr($filename, '.'), 1);
		$result = basename($filename,".".$houzhui);
		$name = $_POST['name'];
		$sex = $_POST['sex'];
		$direction = $_POST['direction'];
		$intro = $_POST['intro'];
		
		// 检查是否为图片
		$filetype = ['image/jpg', 'image/jpeg', 'image/gif', 'image/bmp', 'image/png'];
		if (!in_array($type, $filetype))
		{
			echo "<script>alert('不是图片类型！')</script>";
			return;
		}
		// 查看是否已存在相同的
		$findid = Db::table('member')
			->where('photoname', '=', $filename)
			->find();
		if (!is_null($findid))
		{
			echo "<script>alert('您上传的图片已存在！')</script>";
			return;
		}

		//将服务器上的临时文件移动到指定位置
		//方法一move_upload_file($tmp_name,$destination)
		//move_uploaded_file($tmp_name, "uploads/".$filename);//文件夹应提前建立好，不然报错
		//方法二copy($src,$des)
		//以上两个函数都是成功返回真，否则返回false
		//copy($tmp_name, "copies/".$filename);
		//注意，不能两个方法都对临时文件进行操作，临时文件似乎操作完就没了，我们试试反过来
		//copy($tmp_name, "upload/".$filename);
		move_uploaded_file($tmp_name, "img/".$filename);
		//能够实现，说明move那个函数基本上相当于剪切；copy就是copy，临时文件还在

		//另外，错误信息也是不一样的，遇到错误可以查看或者直接报告给用户
		if ($error==0) {
			date_default_timezone_set('Asia/Shanghai');
			$data = [
				'name' => $name,
				'sex' => $sex,
				'direction' => $direction,
				'intro' => $intro,
				'photoname' => $filename,
				'updatetime' => date('Y-m-d', time()),
			];
			Db::table('member')
				->insert($data);
			echo "<script>alert('添加成功！')</script>";
		}
		else {
			switch ($error){
				case 1:
					echo "<script>alert('超过了上传文件的最大值，请上传1G以下文件！')</script>";
					break;
				case 2:
					echo "<script>alert('上传文件过多，请一次上传20个及以下文件！')</script>";
					break;
				case 3:
					echo "<script>alert('文件并未完全上传，请再次尝试！')</script>";
					break;
				case 4:
					echo "<script>alert('未选择上传文件！')</script>";
					break;
				case 5:
					echo "<script>alert('上传文件为0！')</script>";
					break;
			}
		}
	}
}