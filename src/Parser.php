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

    private $param;

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

            /**
             * 正则匹配路由规则
             */
            if(preg_match('/^' . $val['rule'] . '$/', $url)){

                $router = $val['route'];

                /**
                 * 检测是否为闭包
                 */
                if($router instanceof Closure){
                    $this->_router->setCallBack([$router,$curr_url, $val['var']]);
                    return false;
                }

                /**
                 * 检测是否有@
                 */
                if(0 != strpos($router, '@')){
                    $path = str_replace('@', '/', $router);
                    list($empty, $name, $module, $controller, $action) = explode('/' , $path);
                    $this->_router->setNamespace(strtolower($name));
                    $router = '/'. $module . '/' . $controller . '/' . $action;
                }

                if(!empty($val['var'])){
                    foreach ($val['var']['var'] as $varKey => $value){
                        $router .= '/' . $value . '/' . $curr_url[$val['var']['key'][$varKey]];
                    }
                }

                /**
                 * 检测是否有过滤器
                 */
                if(isset($val['option']['filter'])){

                    $this->parserFilter($val['option']['filter']);

                }

                continue;
            }
        }
        //触发路由中间件
        app()->event('routerMiddleware');
        // 成功匹配后返回URL
        return $router;
    }

    /**
     * 解析过滤器并执行
     * @param $filters
     * @return boolean
     */
    private function parserFilter($filters)
    {

        /**
         * 过滤器是否为数组
         */
        if(is_array($filters)){
            foreach ($filters as $filter){
                call_user_func([new $filter, 'handle']);
            }
            return true;
        }

        /**
         * 实例化过滤器
         */
        $filterObject = new $filters;

        if($filterObject instanceof RouterFilter){
            call_user_func([$filterObject, 'handle']);
        }
        return true;

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
        if(is_array($path)) {
            $parser_url = preg_replace('/\.html[\s\S]*/','',$path);
        } else {
            if(preg_match('/\.html/', $path)){
                $parser_url = preg_replace('/\.html[\s\S]*/','',$path);
            } else {
                $parser_url = preg_replace('/\?[\s\S]*/','',$path);
            }
        }
        if($rule){
            /**
             * 实例控制器,获取到匹配个的路由地址
             */
            $parser_url = $this->match(
                trim($parser_url, '/'),
                array_merge($rule['get'],$rule['*']),
                []);
            /**
             * 走闭包路由，不实例控制器
             */
            if(!$parser_url){
                return;
            }
        }
        switch($this->_router->getUrlModel()){
            case 0:
                $this->initDispatchParamByNormal();
                break;
            case 1:
                $dispatch = explode('/',trim($parser_url,'/'));
                if(preg_match('/^index.php$/', $dispatch[0])){
                    $this->param['platform'] = $this->assetNamespace($dispatch['1']);
                    $this->param['controller'] = isset($dispatch['2']) ? $dispatch['2'] : '';
                    $this->param['action'] = isset($dispatch['3']) ? $dispatch['3'] : '';
                    $this->getValue($parser_url,4);
                } else {
                    $this->param['platform'] = $this->assetNamespace($dispatch['0']);
                    $this->param['controller'] = isset($dispatch['1']) ? $dispatch['1'] : '';
                    $this->param['action'] = isset($dispatch['2']) ? $dispatch['2'] : '';
                    $this->getValue($parser_url,3);
                }
                $this->_router->setDispatch($this->param);
                $this->initDispatchParamByPathInfo();
                break;
            default :
                $this->initDispatchParamByNormal();
                break;
        }
    }

    private function assetNamespace($path)
    {
        if($path && false != strpos($path, '.')){
            list($namespace, $platform) = explode('.',$path);
            $this->_router->setNamespace(strtolower($namespace));
            return $platform;
        }

        return $path;
    }

    private function getValue($url,$start)
    {
        $get = explode('/',trim($url,'/'));
        if(count($get)>3){
            $param = array_slice($get,$start);
            for($i=0;$i<count($param);$i+=2){
                $_GET[$param[$i]] = $param[$i+1];
            }
            $this->_router->setGetParam($_GET);
            return $_GET;
        }
    }

    /**
     * 默认模式下初拼接分发参数
     */
    private function initDispatchParamByNormal(){
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
            $this->_router->getDispatch('platform') == ''
                ?
                $this->_router->getDefaultPlatform()
                :
                strtolower($this->_router->getDispatch('platform'))
        );
        //定义常量保存控制器
        $this->_router->setController(
            $this->_router->getDispatch('controller') == ''
                ?
                $this->_router->getDefaultController()
                :
                ucfirst($this->_router->getDispatch('controller'))
        );
        //定义常量保存操作方法
        $this->_router->setAction(
            $this->_router->getDispatch('action') == ''
                ?
                $this->_router->getDefaultAction()
                :
                strtolower($this->_router->getDispatch('action'))
        );
    }

}
