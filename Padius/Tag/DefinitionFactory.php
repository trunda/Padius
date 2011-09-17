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
 * Standard definition factory
 * Automaticaly adds tags for exposion of objects
 * 
 * @author Jakub Truneček
 */
class DefinitionFactory implements IDefinitionFactory
{
    /**
     * Returns array of definitions
     * @param  string $name
     * @param  array $options
     * @param  \Closure $closure
     * @return array
     */    
    public function defineTag($name, 
            array $options = array(), \Closure $closure = NULL)
    {
        $options = $this->prepareOptions($name, $options);
        $this->validateParams($name, $options, $closure);
        $result = array();
        $result[$name] = $this->constructTagSet($name, $options, $closure);
        $result += $this->exposePropertiesAsTags($name, $options);
        return $result;
    }
    
    /**
     * Prepares options
     * @param  string $name
     * @param  array $options
     * @return array 
     */
    protected function prepareOptions($name, array $options)
    {
        return $options;
    }
    
    /**
     * Validates params
     * 
     * @param  string $name
     * @param  array $options
     * @param \Closure $closure 
     */
    protected function validateParams($name, 
            array $options = array(), \Closure $closure = NULL)
    {
        if (!isset($options['for'])) {
            if(is_null($closure)) {
                throw new \InvalidArgumentException("Tag definition must contain a 'for' option or closure");
            }
            if (isset($options['expose'])) {
                throw new \InvalidArgumentException("Tag definition must contain a 'for' option when used with the 'expose' option");
            }
        }
    }
    
    /**
     * Prepares tag set
     * @param  string $name
     * @param  array $options
     * @param  \Closure $closure
     * @return \Closure 
     */
    protected function constructTagSet($name, 
            array $options = array(), \Closure $closure = NULL)
    {
        if ($closure) {
            return $closure;                   
        } else {            
            $lp = self::lastPart($name);
            return function($tag) use ($options, $lp) {
                if ($tag->isSingle()) {
                    return $options['for'];
                } else {
                    if (!is_null($options['for'])) {
                        $tag->locals->$lp = $options['for'];
                    }
                    return $tag->expand();
                }
            };
        }
    }
    
    /**
     * Exposes object properities as tag
     * @todo Exposition by reflection
     * 
     * @param  string $name
     * @param  array $options
     * @return array
     */
    protected function exposePropertiesAsTags($name, array $options)
    {
        $result = array();
        $expose = isset($options['expose']) 
            ? (array) $options['expose'] 
            : array();
        foreach($expose as $attribute) {
            $tagName = "$name:$attribute";
            $lp = self::lastPart($name);
            $result[$tagName] = function($tag) use ($lp, $attribute) {
                $object = $tag->locals->$lp;
                return $object->$attribute;
            };
        }
        return $result;
    }
    
    /**
     * Returns last part of name
     * @param  string $name
     * @return string 
     */
    protected static function lastPart($name)
    {
        $parts = explode(':', $name);
        return end($parts);
    }
}