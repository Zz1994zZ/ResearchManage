<?php
namespace app\index\controller;

use think\Request;
use think\Session;
use think\Db;
use app\index\model\Download as DownloadModel;

class Download extends Base
{
    public function download()
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
        return $this -> view -> fetch('download');  //渲染首页模板
    }
	
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
		$author = $_POST['author'];
		$uploader = $_POST['uploader'];
		$source = $_POST['source'];
		$decsription = $_POST['decsription'];
		$category = $_POST['category'];
		// 查看是否已存在相同的(文件名+扩展名)
		$findid = Db::table('download')
			->where('filename', '=', $result)
			->where('filetype', '=', $houzhui)
			->find();
		if (!is_null($findid))
		{
			echo "<script>alert('您上传的文件已存在！')</script>";
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
		move_uploaded_file($tmp_name, "upload/".$filename);
		//能够实现，说明move那个函数基本上相当于剪切；copy就是copy，临时文件还在

		//另外，错误信息也是不一样的，遇到错误可以查看或者直接报告给用户
		if ($error==0) {
			date_default_timezone_set('Asia/Shanghai');
			$data = [
				'filename' => $result,
				'filesize' => $size,
				'filetype' => $houzhui,
				'author' => $author,
				'uploader' => $uploader,
				'decsription' => $decsription,
				'category' => $category,
				'source' => $source,
				'uploadtime' => date('Y-m-d', time()),
			];
			Db::table('download')
				->insert($data);
			echo "<script>alert('上传成功！')</script>";
		}else{
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
	
	public function downloadfile()
	{
		$filename = $_GET['filename'];
		if(file_exists($filename))
		{
			header('content-disposition:attachment;filename='.basename($filename));
			header('content-length:'.filesize($filename));
			readfile($filename);
		}
		else
		{
			echo "<img src='/static/img/notexist-img.png'>";
		}
	}
	
	public function downloadfiles()
	{
		$data = $_POST['id']; // 获取传来的id
		
		$zipName = 'upload/tmp.zip';
		$zip = new \ZipArchive;
		$zip->open($zipName, \ZIPARCHIVE::OVERWRITE | \ZIPARCHIVE::CREATE);
		//以下是需要下载的图片数组信息，将需要下载的图片信息转化为类似即可
		
		$hasfile = false;
		foreach ($data as $value){
			$filename = "upload/".$value;
			if(file_exists($filename))
			{
				$zip->addFile($filename, basename($filename));
				$hasfile = true;
			}
			// 添加打包的图片，第一个参数是图片内容，第二个参数是压缩包里面的显示的名称, 可包含路径
			// 或是想打包整个目录 用 $zip->add_path($image_path);
		}
		if ($hasfile == false)
			$zip->addFile('upload/favicon.ico', 'favicon.ico');
		$zip->close();

/*      header("Cache-Control: public");
		header("Content-Description: File Transfer");
        header('Content-disposition: attachment; filename='.basename($zipName)); //文件名        
		header("Content-Type: application/zip"); //zip格式的        
		header("Content-Transfer-Encoding: binary"); //告诉浏览器，这是二进制文件        
		header('Content-Length: '. filesize($zipName)); //告诉浏览器，文件大小        
		@readfile($zipName); */
	}
	
	public function fileAdd()
    {
        return $this->view->fetch('file-add');
    }
}
