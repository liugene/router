<?php

namespace linkphp\router;

use framework\interfaces\RunInterface;

class Router implements RunInterface
{

    // 路由规则
    private $rules = [
        'get'     => [],
        'post'    => [],
        'put'     => [],
        'delete'  => [],
        'patch'   => [],
        'head'    => [],
        'options' => [],
        '*'       => [],
        'alias'   => [],
        'domain'  => [],
        'pattern' => [],
        'name'    => [],
    ];

    // REST路由操作方法定义
    private $rest = [
        'index'  => ['get', '', 'index'],
        'create' => ['get', '/create', 'create'],
        'edit'   => ['get', '/:id/edit', 'edit'],
        'read'   => ['get', '/:id', 'read'],
        'save'   => ['post', '', 'save'],
        'update' => ['put', '/:id', 'update'],
        'delete' => ['delete', '/:id', 'delete'],
    ];

    // 不同请求类型的方法前缀
    private $method_prefix = [
        'get'    => 'get',
        'post'   => 'post',
        'put'    => 'put',
        'delete' => 'delete',
        'patch'  => 'patch',
    ];

    /**
     * @param string $url_module
     * url模式
     * URL模式 0普通模式 1 pathinfo模式 2 rewrite模式
     */
    private $url_module = '1';

    /**
     * @param array $url
     * 封装后请求地址
     */
    private $dispatch = [];

    /**
     * @param array $path
     * 请求地址
     */
    private $path;

    /**
     * @param string $default_platform
     * 默认操作平台
     */
    private $default_platform = 'main';

    /**
     * @param string $default_controller
     * 默认控制器
     */
    private $default_controller = 'Home';

    /**
     * @param string $default_action
     * 默认操作方法
     */
    private $default_action = 'main';

    /**
     * @param string $var_platform
     * 默认模块传参变量
     */
    private $var_platform = 'm';

    /**
     * @param string $var_controller
     * 默认控制器传参变量
     */
    private $var_controller = 'c';

    /**
     * @param string $var_action
     * 默认方法传参变量
     */
    private $var_action = 'a';

    /**
     * @param bool $route_rules_on
     * 是否开启路由自定义配置
     */
    private $route_rules_on = true;

    /**
     * @param string $get_param
     * get参数
     */
    private $get_param;

    private $platform;

    private $controller;

    private $action;

    private $namespace;

    /**
     * 请求方式
     */
    private $method;

    /**
     * 返回的数据
     */
    private $return_data;

    /**
     * Parser
     * @var Parser
     */
    private $_parser;

    /**
     * Dispatch
     * @var Dispatch
     */
    private $_dispatch;

    private $callback;

    public function __construct(Parser $parser, Dispatch $dispatch)
    {
        $this->_parser = $parser;
        $this->_dispatch = $dispatch;
    }

    public function init()
    {
        return $this;
    }

    public function import(array $rules)
    {
        $this->rule($rules);
        return $this;
    }

    public function parser()
    {
        $this->_parser->parserPath($this);
        return $this;
    }

    public function dispatch()
    {
        $this->_dispatch->dispatch($this);
        return $this;
    }

    public function setReturnData($data)
    {
        $this->return_data = $data;
        return $this;
    }

    public function setUrlModel($model)
    {
        $this->url_module = $model;
        return $this;
    }

    public function setDispatch($dispatch)
    {
        $this->dispatch = $dispatch;
        return $this;
    }

    public function setPath($path)
    {
        $this->path = $path;
        return $this;
    }

    public function setMethod($method)
    {
        $this->method = $method;
        return $this;
    }

    public function setDefaultPlatform($platform)
    {
        $this->default_platform = $platform;
        return $this;
    }

    public function setDefaultController($controller)
    {
        $this->default_controller = $controller;
        return $this;
    }

    public function setDefaultAction($action)
    {
        $this->default_action = $action;
        return $this;
    }

    public function setVarPlatform($platform)
    {
        $this->var_platform = $platform;
        return $this;
    }

    public function setVarController($controller)
    {
        $this->var_controller = $controller;
        return $this;
    }

    public function setVarAction($action)
    {
        $this->var_action = $action;
        return $this;
    }

    public function setRouterOn($bool)
    {
        $this->route_rules_on = $bool;
        return $this;
    }

    public function setGetParam($param)
    {
        $this->get_param = $param;
        return $this;
    }

    public function setPlatform($platform)
    {
        $this->platform = $platform;
        return $this;
    }

    public function setController($controller)
    {
        $this->controller = $controller;
        return $this;
    }

    public function setAction($action)
    {
        $this->action = $action;
        return $this;
    }

    public function setNamespace($namespace)
    {
        $this->namespace = $namespace;
        return $this;
    }

    public function setCallBack($callback)
    {
        $this->callback = $callback;
        return $this;
    }


    /////////////////参数获取//////////////////////

    public function getReturnData()
    {
        return $this->return_data;
    }

    public function getUrlModel()
    {
        return $this->url_module;
    }

    public function getDispatch($key='')
    {
        return $key == '' ? $this->dispatch : $this->dispatch[$key];
    }

    public function getPath()
    {
        return $this->path;
    }

    public function getMethod()
    {
        return $this->method;
    }

    public function getDefaultPlatform()
    {
        return $this->default_platform;
    }

    public function getDefaultController()
    {
        return $this->default_controller;
    }

    public function getDefaultAction()
    {
        return $this->default_action;
    }

    public function getVarPlatform()
    {
        return $this->var_platform;
    }

    public function getVarController()
    {
        return $this->var_controller;
    }

    public function getVarAction()
    {
        return $this->var_action;
    }

    public function getRouterOn()
    {
        return $this->route_rules_on;
    }

    public function getRule()
    {
        return $this->rules;
    }

    public function getGetParam()
    {
        return $this->get_param;
    }

    public function getPlatform()
    {
        return $this->platform;
    }

    public function getController()
    {
        return $this->controller;
    }

    public function getAction()
    {
        return $this->action;
    }

    public function getNamespace()
    {
        return $this->namespace;
    }

    public function getCallBack()
    {
        return $this->callback;
    }

    /**
     * 注册路由
     * @access public
     * @param string|array    $rule 路由规则
     * @param string    $route 路由地址
     * @param string    $type 请求类型
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function rule($rule, $route='', $type = '*', $option = [], $pattern = [])
    {
        $type = strtolower($type);

        if (strpos($type, '|')) {
            $option['method'] = $type;
            $type             = '*';
        }

        if (is_array($rule) && empty($route)) {
            foreach ($rule as $key => $val) {
                if (is_numeric($key)) {
                    $key = array_shift($val);
                }
                if (is_array($val)) {
                    $route    = $val[0];
                    $option1  = array_merge($option, $val[1]);
                    $type = isset($val[1]['method']) ? $val[1]['method'] : $type;
                    $pattern1 = array_merge($pattern, isset($val[2]) ? $val[2] : []);
                } else {
                    $option1  = null;
                    $pattern1 = null;
                    $route    = $val;
                }
                $this->setRule($key, $route, $type, !is_null($option1) ? $option1 : $option, !is_null($pattern1) ? $pattern1 : $pattern);
            }
        } else {
            $this->setRule($rule, $route, $type, $option, $pattern);
        }
    }

    private function setRule($rule, $route, $type, $option, $pattern)
    {
//        dump($rule);
//        dump($route);
//        dump($type);
//        dump($option);
//        dump($pattern);die;
        if (is_array($rule)) {
            $rule = $rule[0];
            $route = $rule[1];
        }

        if ('$' == substr($rule, -1, 1)) {
            $rule = substr($rule, 0, -1);
        }

        if ('/' != $rule) {
            $rule = trim($rule, '/');
        }
        $vars = $this->parseVar($rule);
        $this->rules[$type][] = ['rule' => empty($vars['regex_route']) ?
            substr($vars['regex'],0, -2) :
            $vars['regex_route'] . '\/' . substr($vars['regex'],0, -2),
            'route' => $route, 'var' => $vars['var'], 'option' => $option, 'pattern' => $pattern];
    }

    // 分析路由规则中的变量
    private function parseVar($rule)
    {
        // 提取路由规则中的变量
        $var = [];
        $regex_route = '';
        $regex = '';
        foreach (explode('/', $rule) as $key => $val) {
            if (false !== strpos($val, '<') && preg_match_all('/<(\w+(\??))>/', $val, $matches)) {
                foreach ($matches[1] as $name) {
                    if (strpos($name, '?')) {
                        $name     = substr($name, 0, -1);
                    }
                    $var[] = $name;
                }
            }

            if (0 === strpos($val, '[:')) {
                // 可选参数
                $val      = substr($val, 1, -1);
            }
            if (0 === strpos($val, ':')) {
                // URL变量
                $name = substr($val, 1);
                $regex .= '\d*\/';
                $var['var'][] = $name;
                $var['key'][] = $key;
            } else {
                $regex_route = $val;
            }
        }
        return ['regex_route' => $regex_route, 'regex' => $regex, 'var' => $var];
    }


    /**
     * 注册路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function any($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, '*', $option, $pattern);
    }

    /**
     * 注册GET路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function get($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, 'GET', $option, $pattern);
    }

    /**
     * 注册POST路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function post($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, 'POST', $option, $pattern);
    }

    /**
     * 注册PUT路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function put($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, 'PUT', $option, $pattern);
    }

    /**
     * 注册DELETE路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function delete($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, 'DELETE', $option, $pattern);
    }

    /**
     * 注册PATCH路由
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function patch($rule, $route = '', $option = [], $pattern = [])
    {
        $this->rule($rule, $route, 'PATCH', $option, $pattern);
    }

    /**
     * 注册控制器路由 操作方法对应不同的请求后缀
     * @access public
     * @param string    $rule 路由规则
     * @param string    $route 路由地址
     * @param array     $option 路由参数
     * @param array     $pattern 变量规则
     * @return void
     */
    public function controller($rule, $route = '', $option = [], $pattern = [])
    {
        foreach ($this->method_prefix as $type => $val) {
            self::$type($rule . '/:action', $route . '/' . $val . ':action', $option, $pattern);
        }
    }

    /**
     * 注册别名路由
     * @access public
     * @param string|array  $rule 路由别名
     * @param string        $route 路由地址
     * @param array         $option 路由参数
     * @return void
     */
    public function alias($rule = null, $route = '', $option = [])
    {
        if (is_array($rule)) {
            $this->rules['alias'] = array_merge($this->rules['alias'], $rule);
        } else {
            $this->rules['alias'][$rule] = $option ? [$route, $option] : $route;
        }
    }

    /**
     * 设置不同请求类型下面的方法前缀
     * @access public
     * @param string    $method 请求类型
     * @param string    $prefix 类型前缀
     * @return void
     */
    public function setMethodPrefix($method, $prefix = '')
    {
        if (is_array($method)) {
            $this->method_prefix = array_merge($this->method_prefix, array_change_key_case($method));
        } else {
            $this->method_prefix[strtolower($method)] = $prefix;
        }
    }

    /**
     * rest方法定义和修改
     * @access public
     * @param string        $name 方法名称
     * @param array|bool    $resource 资源
     * @return void
     */
    public function rest($name, $resource = [])
    {
        if (is_array($name)) {
            $this->rest = $resource ? $name : array_merge($this->rest, $name);
        } else {
            $this->rest[$name] = $resource;
        }
    }

}