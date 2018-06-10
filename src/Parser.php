<?php

namespace linkphp\router;

use Closure;

class Parser
{

    /**
     * router类实例
     * @var \linkphp\router\Router
     */
    private $_router;

    /**
     * 检测URL和规则路由是否匹配
     * @access private
     * @param string    $url URL地址
     * @param array    $rule 路由规则
     * @param array     $pattern 变量规则
     * @return string
     */
    private function match($url, array $rule, $pattern)
    {
        $curr_url = explode('/', $url);
        $router = $url;
        foreach ($rule as $key => $val) {
            if(preg_match('/^' . $val['rule'] . '$/', $url)){
                $router = $val['route'];
                if(!empty($val['var'])){
                    foreach ($val['var']['var'] as $varKey => $value){
                        $router .= '/' . $value . '/' . $curr_url[$val['var']['key'][$varKey]];
                    }
                }
            }
        }
        // 成功匹配后返回URL
        return $router;
    }

    public function parserPath(Router $router)
    {
        $this->_router = $router;
        $path = $this->_router->getPath();
        /**
         * 检测URL模式以及是否开启自定义路由配置
         */
        if($this->_router->getUrlModel() != '0' && $this->_router->getRouterOn()){
            $rule = $this->_router->getRule();
            /**
             * URL参数匹配
             */
            $this->parserParam($path, $rule);
            return;
        }
        /**
         * URL参数匹配
         */
        $this->parserParam($path);
    }

    public function parserParam($path, $rule = false)
    {
        if($path instanceof Closure){
            $parser_url = call_user_func($path,$this->_router);
        } elseif(is_array($path)) {
            $parser_url = preg_replace('/\.html$/','',$path);
        } else {
            $parser_url = preg_replace('/\.html$/','',$path);
        }
        if($rule){
            $parser_url = $this->match(trim($parser_url, '/'), array_merge($rule['get'],$rule['*']), []);
        }
        switch($this->_router->getUrlModel()){
            case 0:
                $this->initDispatchParamByNormal($parser_url);
                break;
            case 1:
                $dispatch = explode('/',trim($parser_url,'/'));
                if(in_array('index.php',$dispatch)){
                    $param['platform'] = isset($dispatch['1']) ? $dispatch['1'] : '';
                    $param['controller'] = isset($dispatch['2']) ? $dispatch['2'] : '';
                    $param['action'] = isset($dispatch['3']) ? $dispatch['3'] : '';
                    $this->getValue($parser_url,4);
                } else {
                    $param['platform'] = isset($dispatch['0']) ? $dispatch['0'] : '';
                    $param['controller'] = isset($dispatch['1']) ? $dispatch['1'] : '';
                    $param['action'] = isset($dispatch['2']) ? $dispatch['2'] : '';
                    $this->getValue($parser_url,3);
                }
                $this->_router->setUrl($param);
                $this->initDispatchParamByPathInfo();
                break;
            case 2:
                $this->initDispatchParamByNormal($parser_url);
                break;
        }
    }

    private function getValue($url,$start)
    {
        $get = explode('/',trim($url,'/'));
        if(count($get)>3){
            $param = array_slice($get,$start);
            for($i=0;$i<count($param);$i+=2){
                $_GET[$param[$i]] = $param[$i+1];
            }
            return $_GET;
        }
    }

    /**
     * 默认模式下初拼接分发参数
     */
    private function initDispatchParamByNormal($url){
        $get_param = $this->_router->getGetParam();
        //定义常量保存操作平台
        $this->_router->setPlatform(
            isset($get_param[$this->_router->getVarPlatform()])
                ?
                strtolower($get_param[$this->_router->getVarPlatform()])
                :
                $this->_router->getDefaultPlatform()
        );
        //定义常量保存控制器
        $this->_router->setController(
            isset($get_param[$this->_router->getVarController()])
                ?
                ucfirst($get_param[$this->_router->getVarController()])
                :
                $this->_router->getDefaultController()
        );
        //定义常量保存操作方法
        $this->_router->setAction(
            isset($get_param[$this->_router->getVarAction()])
                ?
                strtolower($get_param[$this->_router->getVarAction()])
                :
                $this->_router->getDefaultAction()
        );
    }

    /**
     * pathinfo 模式下拼接分发参数
     */
    private function initDispatchParamByPathInfo()
    {
        //定义常量保存操作平台
        $this->_router->setPlatform(
            $this->_router->getUrl('platform') == ''
                ?
                $this->_router->getDefaultPlatform()
                :
                strtolower($this->_router->getUrl('platform'))
        );
        //定义常量保存控制器
        $this->_router->setController(
            $this->_router->getUrl('controller') == ''
                ?
                $this->_router->getDefaultController()
                :
                ucfirst($this->_router->getUrl('controller'))
        );
        //定义常量保存操作方法
        $this->_router->setAction(
            $this->_router->getUrl('action') == ''
                ?
                $this->_router->getDefaultAction()
                :
                strtolower($this->_router->getUrl('action'))
        );
    }

}
