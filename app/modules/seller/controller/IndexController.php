<?php
/**
 * 默认展示页面
 */
namespace Seller\Controllers;

use Phalcon\DI;
use Phalcon\Mvc\Model\Criteria;
use Phalcon\Paginator\Adapter\Model as Paginator;
use Phalcon\Mvc\View\Simple as SimpleView;
use Seller\Models\News;
use Seller\Models\Badword;
use Seller\Models\Comment_data_1;


class IndexController extends AbstractController
{

    public function initialize()
    {
        parent::initialize();
    }


    public function indexAction()
    {
		# exclude strict notice
        # error_reporting(E_ALL | E_STRICT);

        $badword = new Badword();
        $badlist = $badword->getBadWord();

        $news = new News();
        $article = $news->getArticleInfo(mt_rand(1, 15));
		
        $this->view->setVar('article', $article);
        $this->view->setVar('badword', $badlist);
        $this->view->setVar('comment_search', '/index/search');

        $this->view->pick('index/comment');
    }


    public function commentAction()
    {
        if (!$this->request->isPost())
        {
            return $this->dispatcher->forward([
                'controller' => 'index',
                'action' => 'index'
            ]);
        }

        !session_get('last_time') and  
         session_set('last_time', 0);

        $content = $this->request->getPost('content');
        $content = htmlspecialchars(trim($content));
        if (empty($content) ||
            strlen($content) > 200)
        {
            $this->flash->error('The content length gt maximum');
            exit;
        }

        $cmt = new Comment_data_1();
        $comment = array(
            'commentid' => 'content_6-11-1', // eg cmt_obj_id
            'siteid'    => 1,
            'userid'    => 1,
            'username'  => 'zuxiso',
            'creat_at'  => time(),
            'status'    => 1,
            'content'   => $content,
            'ip'        => $_SERVER['REMOTE_ADDR'],
			'reply'		=> 0
        );
        $status = $cmt->saveComment($comment, 'INSERT', NULL);
        if (empty($status))
        {
            $this->flash->error('save fail');
            exit;
        }

        $this->flash->success('save success');
        return $this->dispatcher->forward([
            'controller' => 'index',
            'action' => 'index'
        ]);
    }


    public function asyncCommentAction()
    {
        $result = ['msg' => '', 'err' => 0, 'cont' => ''];
        if (!$this->request->isPost())
        {
            $result['err'] = 1;
            $result['msg'] = 'invalid request';
            die(json_encode($result));
        }

        $content = htmlspecialchars(trim($this->request->getPost('content')));
        $content = $this->checkContent($content);

        if (empty($content) ||
            strlen($content) > 200)
        {
            $result['err'] = 1;
            $result['msg'] = 'The content length gt maximum';
            die(json_encode($result));
        }

        $cmt = new Comment_data_1();
        $comment = array(
            'commentid' => 'content_6-11-1', // eg cmt_obj_id
            'siteid'    => 1,
            'userid'    => 1,
            'username'  => 'zuxiso',
            'creat_at'  => time(),
            'status'    => 1,
            'content'   => $content,
            'ip'        => $_SERVER['REMOTE_ADDR'],
            'reply'		=> 0
        );

        $queue = new \Phalcon\Queue\redis(array(
            'host' => '172.16.8.113',
            'port' => 6379,
            'persistent' => true,
        ));

        $count = intval(null);
        $queue->push('comment', serialize($comment));
        while ($queue->size('comment'))
        {
            // if concurrency gt 100, auto flush redis db, here is just a temporary test
			if ($queue->size('comment') > 100)
            {
				$queue->remove('comment');
                $result['err'] = 1;
                $result['msg'] = 'The queue length already gt maxinum';
                die(json_encode($result));
            }
			
			if (!$queue->view('comment', 0))
			{
				break;
			}

            $content = $queue->pop('comment', 30);
            if (empty($content) ||
                is_array($content))
            {
                continue;
            }

            $comment = unserialize($content);
            $cmt->saveComment($comment) AND $count++;
            usleep(100);
        }

        if (!$count)
        {
			$queue->remove('comment');
            $result['err'] = 1;
            $result['msg'] = 'save fail';
            die(json_encode($result));
        }
        else
        {
            $result['msg'] = 'save success';
            die(json_encode($result));
        }
    }


    public function asyncSearchAction()
    {
        $result = ['err' => 0, 'msg' => '', 'cont' => ''];
		if (!$this->request->isPost())
		{
			$result['err'] = 1;
			$result['msg'] = 'invalid request';
			die(json_encode($result));
		}
		
		// if is async request
		// $query = Criteria::formInput($this->di, "News", $_POST);		
		// $this->persistent->parameters = $query->getParams();
		
		$parameters = $this->persistent->parameters;
		if (!is_array($parameters))
		{
			$parameters = array();
		}
		$parameters['page'] = (int) $this->request->getPost('page');
		$parameters['where'] = ['commentid' => 'content_6-11-1'];

		// pr($parameters)
		
		$cmt = new Comment_data_1();
		$lists = $cmt->getCommentList($parameters, true);
		
		
		// Render a view passing parameters formated
		$output = $this->view->getRender(
			"index", // controller
			'cmt_list', // filename
			[
				'pager'   => $lists['pager'],
				'where'   => $lists['where'],
				'result'  => $lists['result']
			]
		);
		
		$result['cont'] = $output;
		die(json_encode($result));
    }


    private function checkCNContent($content)
    {
        $badword = NULL;
        if (class_exists('Seller\\Models\\Badword'))
        {
            $badword = new Badword();
        }
		
        if (!is_object($badword))
        {
            throw new \Exception('The badword must is object');
        }

        $badlist = $badword->getBadWord();
        if (empty($badlist))
        {
            return $content;
        }

        $search = $replace = [];
        foreach ($badlist AS $key => $value)
        {
            $search[] = $value['badword'];
            $replace[] = $value['replaceword'];
        }

        return str_replace(
            (array) $search,
            (array) $replace,
                    $content
                );
    }


    private function checkContent($content)
    {
        $filter = NULL;
        if (class_exists('\\Engine\\Filter'))
        {
            $filter = new \Engine\Filter();
        }

        if (is_null($filter))
        {
            throw new \Exception('The filter must is object');
        }

        // filter zh_cn for cms backend manage
        $content = $this->checkCNContent($content);

        $filter->setWord('fuck');
        $filter->setWord('shit');
        $filter->setWord('dick');
        $filter->setWord('rubbish');

        // var_dump($filter->isWord('fuck'));
        // var_dump($filter->isWord('a'));
        // var_dump($filter->isWord('db'));
        // var_dump($filter->isWord('comeon'));
        // var_dump($filter->isWord('you'));

        $badlist = $filter->search($content);
        if (empty($badlist))
        {
            return $content;
        }

        $find = [];
        foreach ($badlist AS $key => $value)
        {
            if (is_array($value))
            {
                list($word, $pos) = array_values($value);
                $find[] = substr($content, $pos, strlen($word));
            }
        }

        return str_replace(
                    (array) $find,
                    '*', $content
                );
    }


    public function searchAction()
    {
		$numberPage = 1;
		if ($this->request->isPost())
		{
			// if is async request
			$query = Criteria::formInput($this->di, "Users", $_POST);
			$this->persistent->parameters = $query->getParams();
		}
		else
		{
			// else direct request
			$numberPage = (int) $this->request->getQuery('page');
        }
		
		$parameters = $this->persistent->parameters;
		if (!is_array($parameters))
		{
			$parameters = array();
		}
		$parameters['page'] = $numberPage;
		$parameters['where'] = array('commentid' => 'content_6-11-1');
		
		
		$cmt = new Comment_data_1();
		$list = $cmt->getCommentList($parameters);
		
		$news = new News();
        $article = $news->getArticleInfo(mt_rand(1, 15));
		
		
		$this->view->setVar('article', $article);
		$this->view->setVar("result", $list['result']);
		$this->view->setVar("where",  $list['where']);
		$this->view->setVar('pager',  $list['pager']);
        $this->view->setVar('comment_search', '/index/search');
		
		
		$this->view->pick('index/comment');
    }

}



