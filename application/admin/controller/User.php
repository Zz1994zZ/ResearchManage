<?php
namespace app\admin\controller;

use think\Request;
use app\admin\model\User as UserModel;
use think\Session;

class User extends Base
{
    //登陆界面
    public function login()
    {
        $this -> alreadyLogin();
        $this -> view -> assign('title', '用户登录');
        $this -> view -> assign('desc', '网站资源后台管理系统登录');
        $this -> view -> assign('copyRight', '？？？？？？？？？？？？？？？？？？');
        return $this -> view -> fetch();
    }

    //登录验证
    public function checkLogin(Request $request)
    {
        $status = 0; //验证失败标志
        $result = '验证失败'; //失败提示信息
        $data = $request -> param();

        //验证规则
        $rule = [
            'name|姓名' => 'require',
            'password|密码'=>'require',
            'captcha|验证码' => 'require|captcha'
        ];

        //验证数据 $this->validate($data, $rule, $msg)
        $result = $this -> validate($data, $rule);

        //通过验证后,进行数据表查询
        //此处必须全等===才可以,因为验证不通过,$result保存错误信息字符串,返回非零
        if (true === $result) {

            //查询条件
            $map = [
                'name' => $data['name'],
                'password' => md5($data['password'])
            ];

            //数据表查询,返回模型对象
            $user = UserModel::get($map);
            if (null === $user) {
                $result = '用户名或密码错误';
            } else {
                $status = 1;
                $result = '验证通过,点击[确定]后进入后台';

                //创建2个session,用来检测用户登陆状态和防止重复登陆
                Session::set('user_id', $user -> id);
                Session::set('user_info', $user -> getData());

                //更新用户登录次数:自增1
                $user -> setInc('login_count');
            }
        }
        return ['status'=>$status, 'message'=>$result, 'data'=>$data];
    }

    //退出登陆
    public function logout()
    {
        //退出前先更新登录时间字段,下次登录时就知道上次登录时间了
        UserModel::update(['login_time'=>time()],['id'=> Session::get('user_id')]);
        Session::delete('user_id');
        Session::delete('user_info');

        $this -> success('注销登陆,正在返回',url('user/login'));
    }

    //管理员列表
    public function  adminList()
    {
        $this -> view -> assign('title', '管理员列表');
        $this -> view -> count = UserModel::count();

        //判断当前是不是admin用户
        //先通过session获取到用户登陆名
        $userName = Session::get('user_info.name');
        if ($userName == 'admin') {
            $list = UserModel::all();
        } else {
            $list = UserModel::all(['name'=>$userName]);
        }

        $this -> view -> assign('list', $list);
        return $this -> view -> fetch('admin_list');
    }

    //管理员状态变更
    public function setStatus(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        if($result->getData('status') == 1) {
            UserModel::update(['status'=>0],['id'=>$user_id]);
        } else {
            UserModel::update(['status'=>1],['id'=>$user_id]);
        }
    }

    //渲染编辑管理员界面
    public function adminEdit(Request $request)
    {
        $user_id = $request -> param('id');
        $result = UserModel::get($user_id);
        $this->view->assign('title','编辑管理员信息');
        $this->view->assign('user_info',$result->getData());
        return $this->view->fetch('admin_edit');
    }

    //更新数据操作
    public function editUser(Request $request)
    {
		$id = $request -> param('id');
		$name = $request -> param('name');
		$password = $request -> param('password');
		$status = $request -> param('status');
		$email = $request -> param('email');
		$role = $request -> param('role');
		
		$oldpassword = UserModel::get($id)->getData('password');
		if ($password != $oldpassword)
			$password = md5($password);
		
		$result = UserModel::update([
					'name' => $name,
					'password' => $password,
					'status' => $status,
					'email' => $email,
					'role' => $role,
				], ['id' => $id]);
        //如果是admin用户,更新当前session中用户信息user_info中的角色role,供页面调用
        if (Session::get('user_info.name') == 'admin') {
            Session::set('user_info.role', $role);
        }


        if (true == $result) {
            return ['status'=>1, 'message'=>'更新成功'];
        } else {
            return ['status'=>0, 'message'=>'更新失败,请检查'];
        }
    }

    //删除操作
    public function deleteUser(Request $request)
    {
        $user_id = $request -> param('id');
        UserModel::update(['is_delete'=>1],['id'=> $user_id]);
        UserModel::destroy($user_id);

    }

    //恢复删除操作
    public function unDelete()
    {
        UserModel::update(['delete_time'=>NULL],['is_delete'=>1]);
    }

    //添加操作的界面
    public function  adminAdd()
    {
        $this->view->assign('title','添加管理员');
        $this->view->assign('keywords','php.cn');
        $this->view->assign('desc','资源管理系统管理员添加');
        return $this->view->fetch('admin_add');
    }

    //添加操作
    public function addUser(Request $request)
    {
        $data = $request -> param();
        $status = 1;
        $message = '添加成功';

        $rule = [
            'name|用户名' => "require|min:3|max:15",
            'password|密码' => "require|min:3|max:15",
            'email|邮箱' => 'require|email'
        ];

        $result = $this -> validate($data, $rule);

        if ($result === true) {
            $user= UserModel::create($request->param());
            if ($user === null) {
                $status = 0;
                $message = '添加失败~~';
            }
        }
		
		else {
			$status = 0;
			$message = '格式错误~~';
		}

        return ['status'=>$status, 'message'=>$message];
    }

}