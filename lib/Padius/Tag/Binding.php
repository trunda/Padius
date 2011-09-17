<?php
/**
 * Copyright (c) 2012 Jakub Truneček
 * 
 * Permission is hereby granted, free of charge, to any person
 * obtaining a copy of this software and associated documentation
 * files (the "Software"), to deal in the Software without
 * restriction, including without limitation the rights to use,
 * copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the
 * Software is furnished to do so, subject to the following
 * conditions:
 * 
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES
 * OF MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT
 * HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY,
 * WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR
 * OTHER DEALINGS IN THE SOFTWARE.
 */

namespace Padius\Tag;

use Padius;

/**
 * This object is passed to callback function, it is some kind
 * of hellper
 */
class Binding 
{
    /** @var Padius\Context */
    private $context;
    /** @var Padius\ArrayHash */
    private $locals;
    /** @var string */
    private $name;
    /** @var string */
    private $attrs;
    /** @var \Closure */
    private $closure;
    
    /**
     * @param Padius\Context $context
     * @param Padius\ArrayHash $locals
     * @param string $name
     * @param array $attrs
     * @param \Closure $closure 
     */
    public function __construct(Padius\Context $context, 
            Padius\ArrayHash $locals, $name, 
            array $attrs, \Closure $closure = NULL)
    {
        $this->context = $context;
        $this->locals = $locals;
        $this->closure = $closure;
        $this->name = $name;
        $this->attrs = $attrs;
        $this->expandAttributes();
    }

    /**
     * Helper, expands all attributes variables with locals
     */
    protected function expandAttributes()
    {
        foreach ($this->attrs as $key => $value) {
            preg_match_all('/\$([a-z1-9A-Z_]+)/', $value, $m);
            $toExpand = $m[1];
            foreach ($toExpand as $var) {
                if (isset($this->locals->$var)) {
                    $value = str_replace("$$var", $this->locals->$var, $value);
                }
            }
            $this->attrs[$key] = $value;            
        }
    }
    
    /**
     * @param \Closure $closure 
     */
    public function setClosure(\Closure $closure)
    {
        $this->closure = $closure;
    }
       
    /**
     * Helper for accessing attribtes
     * @param  string $name
     * @param  mixed $default
     * @return mixed
     */
    public function attr($name, $default = NULL) 
    {
        return isset($this->attrs[$name]) 
                ? $this->attrs[$name] 
                : $default;
    }
    
    /**
     * Expands tag's content
     * @return string
     */
    public function expand()
    {
        if ($this->isSingle()) {
            return '';
        } else {
            $closure = $this->closure;
            return $closure();
        }
    }
    
    /**
     * Contains any content?
     * @return boolean
     */
    public function isSingle()
    {
        return is_null($this->closure);
    }
    
    /**
     * Contains any content?
     * @return boolean 
     */
    public function isDouble()
    {
        return !$this->isSingle();
    }
    
    /**
     * Accessible variables
     * @param  strig $name
     * @return mixed 
     */
    public function __get($name)
    {
        $accessAllowed = array('locals', 'name');
        if (in_array($name, $accessAllowed)) {
            return $this->$name;
        }
    }
}