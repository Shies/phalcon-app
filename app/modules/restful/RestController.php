<?php
/**
 * Created by PhpStorm.
 * User: kevin
 * Date: 2015/4/24
 * Time: 10:45
 */
namespace Rest;
use \Phalcon\Mvc\Controller;
use \Phalcon\Http\Response as PhResponse;

class RsetController extends Controller
{

    protected $statusCode = 200;
    protected $headers    = array();
    protected $payload    = '';
    protected $format     = '';

    /**
     * Getters
     */

    /**
     * @return int
     */
    public function getStatusCode()
    {
        return $this->statusCode;
    }

    /**
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * @return string
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @return string
     */
    public function getFormat()
    {
        return !empty($this->format) ? $this->format : $this->config->app_format;
    }

    /**
     * Setters
     */
    public function setStatusCode($code)
    {
        $this->statusCode = $code;
    }

    public function setHeaders($key, $value)
    {
        $this->headers[$key] = $value;
    }

    public function setPayload($payload)
    {
        $this->payload = $payload;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }

    protected function getResultset($data, $node, $class)
    {
        return resultsToArray($data, $node, $class);
    }

    protected function initResponse()
    {
        $this->statusCode = 200;
        $this->headers    = array();
        $this->payload    = '';
    }

    protected function render()
    {
        $format      = $this->getFormat();
        $payload     = $this->getPayload();
        $status      = $this->getStatusCode();
        $description = $this->getResponseDescription($status);
        $headers     = $this->getHeaders();

        switch ($format)
        {
            case 'json':
                $contentType = 'application/json';
                $content     = json_encode($payload);
                break;
            case 'xml':
                $contentType = 'application/xml';
                /**
                 * @todo implement arrayToXML
                 */
                $content     = arrayToXML($payload);
                break;
            default:
                $contentType = 'text/plain';
                $content     = $payload;
                break;
        }

        $response = new PhResponse();

        $response->setStatusCode($status, $description);
        $response->setContentType($contentType, 'UTF-8');
        $response->setHeader('Access-Control-Allow-Origin', '*');
        $response->setHeader('Access-Control-Allow-Headers', 'X-Requested-With');
        $response->setContent($content);

        // Set the additional headers
        foreach ($headers as $key => $value) {
            $response->setHeader($key, $value);
        }

        $this->view->disable();

        return $response;
    }

    public function getAction($field, $value)
    {
        // implement some functionality to get the class name
        // and the method from the get_called_class()
        $class  = controllerToModel(get_called_class());
        $node   = controllerToClass(get_called_class());
        $method = 'fetchBy' . ucfirst($field);
        $node   = strtolower($node);

        $data = $class::$method($value);

        return $data;
    }

    public function postAction()
    {
        if ($this->request->isPost()) {

            $payload = $_POST;
            $class   = controllerToModel(get_called_class());

            $object = new $class();

            foreach ($payload as $field => $value) {
                $object->$field = $value;
            }

            $status = $object->save();

            $data           = array();
            $data['status'] = $status;

            if (!$status) {

                $data['errno'] = -1;

                foreach ($object->getMessages() as $message) {
                    $data['error'][] = $message;
                }
            }

            // @todo Check for errors here
            return $data;
        }
    }

    public function putAction()
    {
        if ($this->request->isPost()) {

            $payload = $_POST;
            $class   = controllerToModel(get_called_class());

            $object = new $class();

            foreach ($payload as $field => $value) {

                $object->$field = $value;

            }

            $status = $object->save();

            $data           = array();
            $data['status'] = $status;

            if (!$status) {

                $data['errno'] = -1;

                foreach ($object->getMessages() as $message) {
                    $data['error'][] = $message;
                }
            }

            // @todo Check for errors here
            return $data;
        }
    }

    public function listAction($page = null)
    {
        // Some manipulation needed to check what we need to call
        // i.e. which model. This comes from the controller name
        $class = controllerToModel(get_called_class());
        $node  = controllerToClass(get_called_class());
        $node  = strtolower($node);

        $data = $class::fetchAll();

        // Here you can implement some sort of pagination for your data
        return $data;
    }

    public function countAction()
    {
        // Some manipulation needed to check what we need to call
        // i.e. which model. This comes from the controller name
        $model = controllerToModel(get_called_class());

        $data['count'] = $model::fetchCount();

        $this->initResponse();
        $this->setPayload($data);

        return $this->render();
    }

    protected function schema($class)
    {
        $output = $class::fetchSchema();

        $this->setFormat('xml');
        $this->setPayload($output);

        return $this->render();
    }

    protected function getResponseDescription($code)
    {
        $codes = array(

            // Informational 1xx
            100 => 'Continue',
            101 => 'Switching Protocols',

            // Success 2xx
            200 => 'OK',
            201 => 'Created',
            202 => 'Accepted',
            203 => 'Non-Authoritative Information',
            204 => 'No Content',
            205 => 'Reset Content',
            206 => 'Partial Content',

            // Redirection 3xx
            300 => 'Multiple Choices',
            301 => 'Moved Permanently',
            302 => 'Found',  // 1.1
            303 => 'See Other',
            304 => 'Not Modified',
            305 => 'Use Proxy',
            // 306 is deprecated but reserved
            307 => 'Temporary Redirect',

            // Client Error 4xx
            400 => 'Bad Request',
            401 => 'Unauthorized',
            402 => 'Payment Required',
            403 => 'Forbidden',
            404 => 'Not Found',
            405 => 'Method Not Allowed',
            406 => 'Not Acceptable',
            407 => 'Proxy Authentication Required',
            408 => 'Request Timeout',
            409 => 'Conflict',
            410 => 'Gone',
            411 => 'Length Required',
            412 => 'Precondition Failed',
            413 => 'Request Entity Too Large',
            414 => 'Request-URI Too Long',
            415 => 'Unsupported Media Type',
            416 => 'Requested Range Not Satisfiable',
            417 => 'Expectation Failed',

            // Server Error 5xx
            500 => 'Internal Server Error',
            501 => 'Not Implemented',
            502 => 'Bad Gateway',
            503 => 'Service Unavailable',
            504 => 'Gateway Timeout',
            505 => 'HTTP Version Not Supported',
            509 => 'Bandwidth Limit Exceeded'
        );

        $result = (isset($codes[$code])) ?
            $codes[$code]          :
            'Unknown Status Code';

        return $result;
    }
}