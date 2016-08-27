<?php
namespace Shop\Controllers;

use
    Phalcon\Mvc\Controller,
    Engine\View;

/**
 * 加载 Phalcon\Tag 函数
 */
load_functions ('tag');

/**
 * 基础控制器类
 */
class AbstractController extends Controller
{
    public $ajax = null;

    /**
     * Action 完成前执行
     */
    public function beforeExecuteRoute ($dispatcher)
    {
        //$this->ajax = new \AJAX($this->response);

        return true; // false 时停止执行
    }

    /**
     * 初始化方法
     *
     */
    public function initialize ()
    {
    }

    /**
     * Action 完成后执行
     */
    public function afterExecuteRoute ($dispatcher)
    {
    }

    /**
     * 关闭视图渲染
     */
    public function viewNoRender ()
    {
        $this->view->setRenderLevel (View::LEVEL_NO_RENDER);
    }
}
