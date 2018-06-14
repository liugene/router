<?php

// +----------------------------------------------------------------------
// | LinkPHP [ Link All Thing ]
// +----------------------------------------------------------------------
// | Copyright (c) 2016-2017 http://linkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Latham <liujun2199@vip.qq.com>
// +----------------------------------------------------------------------
// |               路由分发
// +----------------------------------------------------------------------

namespace linkphp\router;

use framework\Application;
use framework\Exception;
use ReflectionFunction;

class Dispatch
{

    private $app;

    public function __construct(Application $application)
    {
        $this->app = $application;
    }

    //分发方法
    public function dispatch(Router $router)
    {
        $this->app->hook('modelMiddleware');
        if($router->getCallBack()){
            $callback = $router->getCallBack();
            $reflectorFunc =  new ReflectionFunction($callback[0]);
            /**
             * 获得实例后的参数
             */
            $reflectorParam = $reflectorFunc->getParameters();
            /**
             * true
             * 循环遍历参数
             */
            $paramName = [];
            foreach($reflectorParam as $paramIndex => $parameter){
                /**
                 * 获取类名
                 */
                $dependentClass = $parameter->getClass();
                if($dependentClass){
                    /**
                     * 执行get方法取得实例
                     * 并注入
                     */
                    $paramName[] = app()->get($dependentClass);
                } else {
                    /**
                     * 获取请求参数值
                     */
                    if(!empty($callback[2]['var'])){
                        foreach ($callback[2]['var'] as $varKey => $value){
                            $paramName[$value] = $callback[1][$callback[2]['key'][$varKey]];
                        }
                    }
//                    dump($paramName);die;
//                    $paramName[] = $callback[1][$parameter->getName()];
                }
            }
            $response = call_user_func_array($callback[0], $paramName);
            $router->setReturnData($response);
        } else {
            //实例化控制器类
            $controller_name = $router->getNamespace()  . '\\' . $router->getPlatform() . '\controller\\' . $router->getController();
            $this->app->bind($this->app->definition()
                ->setAlias($controller_name)
                ->setIsSingleton(true)
                ->setClassName($controller_name));
            //调用方法
            $action_name = $router->getAction();
            $this->app->hook('controllerMiddleware');
            $controller = $this->app->get($controller_name);
            if(method_exists($controller,$action_name)){
                $this->app->hook('actionMiddleware');
                /**
                 * 方法注入
                 */
                $router->setReturnData($this->app->injection($controller, $action_name, $router->getGetParam()));
            } else {
                //抛出异常
                throw new Exception("无法加载方法");
            }
        }
    }
}