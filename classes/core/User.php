<?php
namespace core;

/**
 * Класс для работы с данными пользователя
 * @author qwe
 */
class User extends Session
{
    public $role = null;
    
    public $userName = null;
    
    public $startSessionLikesCount;
    
    public $userLikesCount;

    /**
    * Вернёт объект юзера
    * 
    * @staticvar type $instance
    * @return \static
    */
    public static function get()
    {
        static $instance = null; // статическая переменная
        if (null === $instance) { // проверка существования
            $instance = new static();
        }
        return $instance;
    }
    
    /** 
     * Скрываем конструктор для того чтобы класс нельзя было создать в обход getInstance 
     */
    protected function __construct()
    {
        if (!empty(Session::get()->session['user']['role'])
                && !empty(Session::get()->session['user']['userName'])) {
            $this->role = Session::get()->session['user']['role'];
            $this->userName = Session::get()->session['user']['userName'];
        }
        else {
            Session::get()->session['user']['role'] = 'guest';
            Session::get()->session['user']['userName'] = 'guest';
            $this->role = 'guest';
            $this->userName = 'guest';
        }
    }
        
    /**
     * Присваивает данной сессии имя пользователя и роль в соответствии с полученными данными
     * @param type $userName
     * @param type $pass
     * @return boolean
     */
    public function login($login, $pass)
    {
        if ($this->checkAuthData($login, $pass)) {
            
            $role = $this->getRoleByUserName($login); 
            $this->role =  $role; 
            $this->userName = $login;
            Session::get()->session['user']['role'] = $role; 
            Session::get()->session['user']['userName'] = $login; 
            Session::get()->session['user']['userSessionLikesCount'] = 0; 
            
            
//            Session::get()->session['user']['startSessionLikesCount'] = (new \application\models\Article)->getAllLikesCount();
        }
        return true;
    }
    
    /**
     * Получить роль по имени пользователя
     * @param type $userName
     * @return type
     */
    private function getRoleByUserName($userName)
    {
        $siteAuthData = \Config::$users;
        if (isset($siteAuthData[$userName])) {
            return $siteAuthData[$userName]['role'];
        }
    }
    
    /**
     * Проверяет, зарегистрирован ли данный пользователь
     * @param type $login
     * @param type $pass
     */
    private function checkAuthData($login, $pass)
    {
        $result = false;
        $siteAuthData = \Config::$users;
        if (isset($siteAuthData[$login])) {
            if ($siteAuthData[$login]['pass'] == $pass) {
                $result = true;
            }
        }
        return $result;
    }
    
    /**
     * Удаляет из Userа и Сессии данные об актуальной роли и мени пользователя
     */
    public function logout()
    {
        
        $this->role = "";
        $this->userName = "";
        Session::get()->session['user'] = null;
//        session_destroy();
        return true;
    }
    
    /**
     * 
     * @param type $route
     */
    public function isAllowed($route)
    {
        $result = false;
        $controllerClassName = "application\\controllers\\" . \Router::getControllerClassName($route);
        $controller = new $controllerClassName();
        $rules = $controller->getControllerRules();
        $action = $controller->getControllerActionName($route);
        
//        echo "<br>Контроллер: " .  $controllerClassName . "<br> Действие: " . $action;
        
        if ($controller->isEnabled($route, $action)) {
            $result = true;
        }
//        echo "<br>Результат: " . $result;
        return $result;
    }
 
    /**
     * 
     * @param type $route
     * @param type $elementHTML
     */
    public function returnIfAllowed($route, $elementHTML) 
    {
        if($this->isAllowed($route)) {
            echo $elementHTML;
        }
        else echo "";
    }
    
}
