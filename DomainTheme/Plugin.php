<?php
/**
 * 域名模板
 * 
 * @package DomainTheme
 * @author hongweipeng
 * @version 0.0.1
 * @link https://www.hongweipeng.com
 *
 */
class DomainTheme_Plugin implements Typecho_Plugin_Interface
{
    /**
     * 激活插件方法,如果激活失败,直接抛出异常
     * 
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function activate()
    {
		$info = DomainTheme_Plugin::install();
		Helper::addPanel(1, 'DomainTheme/manage-domaintheme.php', '域名模板', '管理域名模板', 'administrator');
		Helper::addAction('DomainTheme-edit', 'DomainTheme_Action');
        Typecho_Plugin::factory('Widget_Archive')->handleInit = array(__CLASS__, 'run');
		return _t($info);
    }

    public static function run($archive, $select) {
    	$db = Typecho_Db::get();
		$prefix = $db->getPrefix();
        $options = Helper::options();
        $domain = $_SERVER['HTTP_HOST'];
        $row = $db->fetchRow($db->select()->from($prefix.'domaintheme')->where('domain = ?', $domain)->limit(1));
        if($row) {
        	$options->theme = is_dir($options->themeFile($row['theme'])) ? $row['theme'] : 'default';;
        	$themeDir = rtrim($options->themeFile($options->theme), '/') . '/';

        	$archive->setThemeDir($themeDir);
        	if($row['user']) {
        		$themeOptions = json_decode($row['user'], true);
        		
        		if(is_array($themeOptions)) {
        			foreach ($themeOptions as $key => $value) {
        				$options->push(array('name'=>$key, 'value'=>$value));
        			}
        		}
        	}
        }
        
    }
    
    /**
     * 禁用插件方法,如果禁用失败,直接抛出异常
     * 
     * @static
     * @access public
     * @return void
     * @throws Typecho_Plugin_Exception
     */
    public static function deactivate()
	{
		Helper::removeAction('DomainTheme-edit');
		Helper::removePanel(1, 'DomainTheme/manage-domaintheme.php');
	}
    
    /**
     * 获取插件配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form 配置面板
     * @return void
     */
    public static function config(Typecho_Widget_Helper_Form $form) {}
    
    /**
     * 个人用户的配置面板
     * 
     * @access public
     * @param Typecho_Widget_Helper_Form $form
     * @return void
     */
    public static function personalConfig(Typecho_Widget_Helper_Form $form) {}

	public static function install()
	{
		$installDb = Typecho_Db::get();
        $prefix = $installDb->getPrefix();//获取表前缀
        $script = file_get_contents('usr/plugins/DomainTheme/typecho_domaintheme.sql');
        $script = trim(str_replace('typecho_', $prefix, $script));
        $script = str_replace('%charset%', 'utf8', $script);
        try {
            if($script) {
                $installDb->query($script, Typecho_Db::WRITE);
            }
            return '建立数据表，插件启用成功';
        } catch(Typecho_Db_Exception $e) {
            $code = $e->getCode();
            //throw new Typecho_Plugin_Exception('数据表建立失败，插件启用失败。错误号：'.$code);
        }
        return '插件启用成功';
	}
	

	public static function form($action = NULL)
	{
		/** 构建表格 */
		$options = Typecho_Widget::widget('Widget_Options');
		$form = new Typecho_Widget_Helper_Form(Typecho_Common::url('/action/DomainTheme-edit', $options->index),
		Typecho_Widget_Helper_Form::POST_METHOD);
		
		/** 名称 */
		$name = new Typecho_Widget_Helper_Form_Element_Text('name', NULL, NULL, _t('名称'));
		$form->addInput($name);
	
		/** 地址 */
		$url = new Typecho_Widget_Helper_Form_Element_Text('domain', NULL, "", _t('域名'));
		$form->addInput($url);
		
		/** 主题 */
        $themes = array_map('basename', glob(__TYPECHO_ROOT_DIR__ . __TYPECHO_THEME_DIR__ . '/*'));
        $themes = array_combine($themes, $themes);
		$theme = new Typecho_Widget_Helper_Form_Element_Select('theme', $themes, 'default', _t('主题名称'), _t('模板名称'));
		$form->addInput($theme);
		
		
		/** 自定义数据 */
		$user = new Typecho_Widget_Helper_Form_Element_Textarea('user', NULL, NULL, _t('自定义数据'), _t('该项用于用户自定义数据扩展(json格式)'));
		$form->addInput($user);
		
		/** 链接动作 */
		$do = new Typecho_Widget_Helper_Form_Element_Hidden('do');
		$form->addInput($do);
		
		/** 链接主键 */
		$id = new Typecho_Widget_Helper_Form_Element_Hidden('id');
		$form->addInput($id);
		
		/** 提交按钮 */
		$submit = new Typecho_Widget_Helper_Form_Element_Submit();
		$submit->input->setAttribute('class', 'btn primary');
		$form->addItem($submit);
		$request = Typecho_Request::getInstance();
        if (isset($request->id) && 'insert' != $action) {
            /** 更新模式 */
			$db = Typecho_Db::get();
			$prefix = $db->getPrefix();
            $link = $db->fetchRow($db->select()->from($prefix.'domaintheme')->where('id = ?', $request->id));
            if (!$link) {
                throw new Typecho_Widget_Exception(_t('链接不存在'), 404);
            }
            
            $name->value($link['name']);
            $url->value($link['domain']);
            $theme->value($link['theme']);
            $user->value($link['user']);
            $do->value('update');
            $id->value($link['id']);
            $submit->value(_t('编辑'));
            $_action = 'update';
        } else {
            $do->value('insert');
            $submit->value(_t('增加'));
            $_action = 'insert';
        }
        
        if (empty($action)) {
            $action = $_action;
        }

        /** 给表单增加规则 */
        if ('insert' == $action || 'update' == $action) {
			$name->addRule('required', _t('必须填写名称'));
			$url->addRule('required', _t('必须填写域名地址'));
            $theme->addRule('required', _t('必须填写模板名称'));
        }
        if ('update' == $action) {
            $id->addRule('required', _t('链接主键不存在'));
        }
        return $form;
	}


}
