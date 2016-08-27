<?php
/**
 * 默认展示页面
 */
namespace Shop\Controllers;

use Phalcon\DI;
use Shop\Models\Member;

class IndexController extends AbstractController
{
    public function indexAction()
    {
        /*
        $m_index = new member();
        $web_html = $m_index->getCodeList();
        foreach ($web_html as $v) {
            echo $v['username'], "\n";
        }

//        echo Di::getDefault ()->get('profiler')->getProfiles();
        $profile = $this->di->get('profiler')->getLastProfile();
        echo "SQL Statement: ", $profile->getSQLStatement(), "\n";
//        echo "Start Time: ", $profile->getInitialTime(), "\n";
//        echo "Final Time: ", $profile->getFinalTime(), "\n";
        echo "Total Elapsed Time: ", $profile->getTotalElapsedSeconds(), "\n";
        echo $this->di->get('profiler')->getLastProfile()->getSQLStatement();
//        pr($web_html);
        */
    }


    public function aclAction()
    {
        $member = new member();
        $member->iniAclTest();
    }


    public function testAction()
    {
        echo "test";
        $this->view->pick("upload");
    }


    public function cacheAction()
    {
        wkcache('my-cache-data11', array(1, 2, 3, 4, array("53ff65", "grr334", "655544")));

        //Get data
        $data = rkcache('my-cache-data11');
        pr($data);
//        $this->di->get("cacheData")->delete('my-cache-data');
        $this->view->disable();
    }


    public function mailAction()
    {
        $email = new \Phalcon\Mailer\Email();
        $result = $email->send_sys_email("zuxiso@qq.com", "测试", "测试测试测试");
    }


    public function uploadAction()
    {
        if ($this->request->hasFiles() == true) {
            $uploads = $this->request->getUploadedFiles();
            $isUploaded = false;
            foreach ($uploads as $upload) {
                $path = $this->di->get('config')->upload->path . md5(uniqid(rand(), true)) . '-' . strtolower($upload->getname());
                ($upload->moveTo($path)) ? $isUploaded = true : $isUploaded = false;
            }

            ($isUploaded) ? die('Files successfully uploaded .') : die('Some error ocurred .');
        } else {
            die('You must choose at least one file to send . Please try again .');
        }
    }


    public function smsAction()
    {
        $sms = new \Phalcon\Mailer\Sms();
        echo $sms->send("15001065733", "无界传媒欢迎你");
    }


    public function queueAction()
    {
        $queue = new \Phalcon\Queue\redis(array(
            'host' => '172.16.8.113',
            'port' => 6379,
            'persistent' => true,
        ));

        // $queue->push('test', serialize(array('processVideo' => 4871))); //入列
        // $queue->puts('test', 1, 2, 3, 4); // 批量入列
		// pr($queue->view('test', 1)); // 查看
        // $queues = $queue->scan('test'); // 扫描所有
        // $tget = $queue->get('test'); // 得到第一个值
        // pr($queue->size('test')); // 得到总数量
        // pr($queues);
        // exit;

        // Author ___Shies by 2015/06/17 20:30:49
		while ($queue->size('test'))
		{
			if (empty($queue->view('test', 0)))
			{
				break;
			}
			
			$content = $queue->pop('test', 30); // 出列
			echo $content;
			echo '<br />';
		}
    }

}