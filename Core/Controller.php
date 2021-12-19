<?php

namespace Core;

/**
 * Core controller
 * 
 * The base controller of all controllers
 */
abstract class Controller {
    /**
     * Custom variables defined in index.php (ex. <index:\d+>)
     * @var array   ex. ['index' => '123']
     */
    protected $variables = [];

    /**
     * Constructor
     * Register custom variables as elements of $this->variables
     * 
     * @param array     $variables  Variables passed from router's dispatch()
     * 
     * @return void
     */
    public function __construct($variables) {
        $this->variables = $variables;
    }

    /**
     * Called when a non-existent or inaccessible method is called on an object
     * of this class. Used to execute before() and after() filter methods. All 
     * actions which directly serve the web should be declared as PROTECTED.
     * 
     * @param string    $method     The private method to be called
     * @param array     $args       Arguments passed to the method
     * 
     * @return void
     */
    public function __call($method, $args) {
        if (method_exists($this, $method)) {
            if ($this->before() !== false) {
                call_user_func_array([$this, $method], $args);
                $this->after();
            }
        } else {
            throw new \Exception("Method $method not found in controller " . get_class($this));
        }
    }

    /**
     * Before filter    - called before an action
     * 
     * @return boolean  True if the action being called can be executed, false otherwise.
     */
    protected function before() {}

    /**
     * After filter     - called after an action
     * 
     * @return void
     */
    protected function after() {}

    /**
     * Redirect to a different page
     * 
     * @param string    $url    The relative URL
     * 
     * @return void
     */
    protected function redirect($url) {
        header('Location: http://' . $_SERVER['HTTP_HOST'] . $url, true, 303);
        exit;
    }
}