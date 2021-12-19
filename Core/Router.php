<?php

namespace Core;

/**
 * Router
 * 
 * PHP version 8.0.12
 */
class Router {
    /**
     * Associative array of routes (= the routing table)
     * The route must contain both controller and action info
     * either in the regex or the param.
     * ['regex_for_route' => 'param_for_matched_route', ...]
     * ex.  [
     *          '/^(?P<controller>[a-z-]+)$/i' => ['action' => 'index'], 
     *          '/^password\/reset\/(?P<token>[\da-f]{32})$/' => [
     *              'controller' => 'Password', 
     *              'action'     => 'reset'
     *          ], 
     *          '/^(?P<controller>[a-z-]+)\/(?P<action>[a-z-]+)$/i' => [], 
     * 
     *          ...
     *      ]
     * 
     * @var array
     */
    private $route_table = [];

    /**
     * Matched route from the $_SERVER['QUERY_STRING']
     * Populated by parseQueryString()
     * ex. 'post/create'
     * 
     * @var string
     */
    private $route = '';

    /**
     * Associative array that pairs controller and action with their name
     * ex. ['controller' => 'Post', 'action' => 'create']
     * 
     * @var array
     */
    private $dispatch = [];

    /**
     * Associative array that pairs custom variables with their value
     * ex. ['token' => '3f786850e387550']
     * 
     * @var array
     */
    private $variables = [];

    /**
     * Add a new route to the route table ($route_table)
     * 
     * @param string    $route      The route URL (ex. '<controller>/<action>')
     * @param array     $params     Parameters (ex. ['controller' => 'Home', 'action' => 'index'])
     *                              Defaults to [] if not supplied.
     * 
     * @return void
     */
    public function add($route, $params = []) {
        // Escape forward slashes: '/' --> '\/'
        $route = preg_replace('/\//', '\\/', $route);

        // Convert variables e.g. <controller> --> (?P<controller>[a-z]+)
        $route = preg_replace('/\<([a-z]+)\>/', '(?P<\1>[a-z-]+)', $route);

        // Convert variables with custom regex e.g. <id:\d+> --> (?P<id>\d+)
        $route = preg_replace('/\<([a-z]+):([^\>]+)\>/', '(?P<\1>\2)', $route);

        // Add start and end delimiters
        $route = '/^' . $route . '$/';

        $this->route_table[$route] = $params;
    }

    /**
     * Dispatch the route, creating the controller object and running
     * the action method of the object.
     * 
     * @param string    $query_string   The query string given to index.php
     * 
     * @return void
     */
    public function dispatch($query_string) {
        $this->parseQueryString($query_string);     // populate $route

        // populate $dispatch and $variables using matchRoute()
        if ($this->matchRoute()) {
            $namespace = 'App\Controllers\\';
            $controller = $namespace . $this->dispatch['controller'];

            if (class_exists($controller)) {
                $controller_object = new $controller($this->variables);
                $action = $this->dispatch['action'];

                $controller_object->$action();
            } else {
                throw new \Exception("Controller class $controller not found");
            }
        } else {
            throw new \Exception('No route matched', 404);
        }

    }

    /**
     * Separate the route from the server query string and populate $route.
     * 
     * @param string    $query_string   The query string $_SERVER['QUERY_STRING']
     * 
     * @return void
     * 
     * ex. 
     * ==================================================================
     * URL                              ** QUERY_STRING **      Route    
     * localhost                        ''                      ''       
     * localhost?k1=v1&k2=v2            'k1=v1&k2=v2'           ''       
     * localhost/                       ''                      ''       
     * localhost/?k1=v1&k2=v2           'k1=v1&k2=v2'           ''       
     * localhost/abc                    'abc'                   'abc'    
     * localhost/abc?k1=v1&k2=v2        'abc&k1=v1&k2=v2'       'abc'    
     * localhost/abc/def                'abc/def'               'abc/def'
     * localhost/abc/def?k1=v1&k2=v2    'abc/def&k1=v1&k2=v2    'abc/def'
     * ==================================================================
     */
    private function parseQueryString($query_string) {
        $exploded = explode('&', $query_string);

        $this->route = '';

        // If the first element doesn't contain '=', then it is itself the route
        if (strpos($exploded[0], '=') === false) {
            $this->route = $exploded[0];
        }
    }

    /**
     * Populate $dispatch and $variables by matching $route to $route_table.
     * Return true on match, false otherwise.
     * ex.
     *      
     * 
     *      --> $params = ['controller' => 'post', 'action' => 'view', 'index' => '123']
     * 
     * @return boolean
     */
    private function matchRoute() {
        /**
         * ************************ $this->route_table ***************************
         *                     $route                               $params
         * '/^(?P<controller>[a-z-]+)\/(?P<index>\d+)$/'   => ['action' => 'view']
         * ***********************************************************************
         */
        foreach ($this->route_table as $route => $params) {

            // $this->route     'post/123'
            if (preg_match($route, $this->route, $parse)) {

                $params = array_merge($params, $parse);
                /** $params     [..., 'controller' => 'post', 
                 *                    'action' => 'view', 
                 *                    'index' => '123', ... ]
                 */
                
                foreach ($params as $key => $value) {
                    
                    if (is_string($key)) {
                        switch ($key) {
                            case 'controller':
                                $this->dispatch[$key] = $this->convertToStudlyCaps($value);
                                break;
                            case 'action':
                                $this->dispatch[$key] = $this->convertToCamelCase($value);
                                break;
                            default:
                                $this->variables[$key] = $value;
                        }
                    }
                }
                /**
                 * $this->dispatch      ['controller' => 'Post', 'action' => 'view']
                 * $this->variables     ['index' => '123']
                 */

                return true;
            }
        }
        return false;
    }

    /**
     * Convert the string with hyphens to StudlyCaps,
     * e.g. post-authors => PostAuthors
     *
     * @param string $string    The string to convert
     *
     * @return string
     */
    private function convertToStudlyCaps($string) {
        return str_replace(' ', '', ucwords(str_replace('-', ' ', $string)));
    }

    /**
     * Convert the string with hyphens to camelCase,
     * e.g. add-new => addNew
     *
     * @param string $string    The string to convert
     *
     * @return string
     */
    private function convertToCamelCase($string) {
        return lcfirst($this->convertToStudlyCaps($string));
    }
}