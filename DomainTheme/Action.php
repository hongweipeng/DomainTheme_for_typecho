<?php
class DomainTheme_Action extends Typecho_Widget implements Widget_Interface_Do
{
	private $db;
	private $options;
	private $prefix;

	/**
     *判断用户是否已登录，未登录自动跳转到登录页面
     *@return void
     */
    public function checkLogin() {
        $user = Typecho_Widget::widget('Widget_User');
        if(! $user->hasLogin()) {
            Typecho_Widget::widget('Widget_Notice')->set(_t("未登录"), 'error');
            $this->response->redirect($this->options->adminUrl);
        }

    }

    public function get_data_once(&$arr, $key, $default = null) {
    	if(isset($arr[$key])) {
    		$default = $arr[$key];
    		unset($arr[$key]);
    	}
    	return $default;
    }
			
	public function insertDomainTheme()
	{
		$this->checkLogin();
		if (DomainTheme_Plugin::form('insert')->validate()) {
			$this->response->goBack();
		}
		/** 取出数据 */
		//$link = $this->request->from('name', 'domain', 'theme', 'user');
		$form_data = $this->request->from(array_keys($_POST));
		$data['id'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'id');
		$data['name'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'name');
		$data['domain'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'domain');
		$data['theme'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'theme');
		unset($form_data[DomainTheme_Plugin::$FORM_PRE.'do']);
		$data['user'] = json_encode($form_data);

		/** 插入数据 */
		$data['id'] = $this->db->query($this->db->insert($this->prefix.'domaintheme')->rows($data));

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=DomainTheme%2Fmanage-domaintheme.php', $this->options->adminUrl));
	}


	public function updateLink()
	{
		$this->checkLogin();
		if (DomainTheme_Plugin::form('update')->validate()) {
			$this->response->goBack();
		}

		/** 取出数据 */
		
		//$form_data = $this->request->from(array_keys($_POST));
		$form_data = $_POST;
		$data['id'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'id');
		$data['name'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'name');
		$data['domain'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'domain');
		$data['theme'] = $this->get_data_once($form_data, DomainTheme_Plugin::$FORM_PRE.'theme');
		unset($form_data[DomainTheme_Plugin::$FORM_PRE.'do']);
		$data['user'] = json_encode($form_data);

		/** 更新数据 */
		$this->db->query($this->db->update($this->prefix.'domaintheme')->rows($data)->where('id = ?', $data['id']));

		/** 设置高亮 */
		$this->widget('Widget_Notice')->highlight('link-'.$data['id']);

		/** 提示信息 */
		$this->widget('Widget_Notice')->set(_t('"%s" => %s 已经被更新',
		$data['domain'], $data['theme']), NULL, 'success');

		/** 转向原页 */
		$this->response->redirect(Typecho_Common::url('extending.php?panel=DomainTheme%2Fmanage-domaintheme.php', $this->options->adminUrl));
	}

    public function deleteLink()
    {
    	$this->checkLogin();
        $ids = $this->request->filter('int')->getArray('id');
        $deleteCount = 0;
        if ($ids && is_array($ids)) {
            foreach ($ids as $id) {
                if ($this->db->query($this->db->delete($this->prefix.'domaintheme')->where('id = ?', $id))) {
                    $deleteCount ++;
                }
            }
        }
        /** 提示信息 */
        $this->widget('Widget_Notice')->set($deleteCount > 0 ? _t('链接已经删除') : _t('没有链接被删除'), NULL,
        $deleteCount > 0 ? 'success' : 'notice');
        
        /** 转向原页 */
        $this->response->redirect(Typecho_Common::url('extending.php?panel=DomainTheme%2Fmanage-domaintheme.php', $this->options->adminUrl));
    }



	public function action()
	{
		$this->db = Typecho_Db::get();
		$this->prefix = $this->db->getPrefix();
		$this->options = Typecho_Widget::widget('Widget_Options');
		$this->on($this->request->is(DomainTheme_Plugin::$FORM_PRE.'do=insert'))->insertDomainTheme();
		$this->on($this->request->is(DomainTheme_Plugin::$FORM_PRE.'do=update'))->updateLink();
		$this->on($this->request->is(DomainTheme_Plugin::$FORM_PRE.'do=delete'))->deleteLink();
		//$this->response->redirect($this->options->adminUrl);
	}
}
