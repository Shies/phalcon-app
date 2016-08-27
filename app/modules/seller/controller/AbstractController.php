<?php
namespace Seller\Controllers;

use
    Phalcon\Mvc\Controller,
    Engine\View;

/**
 * 加载 Phalcon\Tag 函数
 */
load_functions('tag');

/**
 * 基础控制器类
 */
class AbstractController extends Controller
{

    public $ajax = null;

    /**
     * 输出模板内容的数组，其他的变量不允许从程序中直接输出到模板
     */
    private static $output_value = array();

    /**
     * 初始化方法
     *
     */
    public function initialize()
    {
    }

    /**
     * Action 完成前执行
     */
    public function beforeExecuteRoute($dispatcher)
    {
        //$this->ajax = new \AJAX($this->response);

        return true; // false 时停止执行
    }


    /**
     * Action 完成后执行
     */
    public function afterExecuteRoute($dispatcher)
    {
    }

    /**
     * 关闭视图渲染
     */
    public function viewNoRender()
    {
        $this->view->setRenderLevel(View::LEVEL_NO_RENDER);
    }
}
