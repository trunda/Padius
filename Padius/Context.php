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
namespace Padius;

/**
 * Context definition class
 * @author Jakub Truneček
 */
final class Context 
{
    /** @var array */
    public $definitions = array();
    /** @var array */
    private $globals = array();
    /** @var strig */
    private $factoryClass;
    /** @var array */
    private $bindingStack = array();
    
    /**
     * Construtor
     * @param type $factoryClass Default definition factory
     */
    public function __construct($factoryClass = 'Padius\Tag\DefinitionFactory')
    {
        $this->factoryClass = $factoryClass;
    }
   
    /**
     * Defines new tag (creates all his definitions by definition factory)
     * @param  string $name
     * @param  array $options
     * @param  \Closure $closure Tag's behaviour
     * @return Context 
     */
    public function defineTag($name)
    {
        $args = func_get_args();
        // Arguemnts 
        $name = $args[0];
		if (!is_string($name) || $name === '') {
			throw new \InvalidArgumentException(
                    "Tag name must be a non-empty string, " 
                    . gettype($name) . " given."
            );
		}                        
        if (count($args) == 3) {
            if (!is_array($args[1])) {
                throw new \InvalidArgumentException("Options must be an array");
            }
            if (!$args[2] instanceof \Closure) {
                throw new \InvalidArgumentException(
                        "Tag behavior is defined by closure"
                );
            }
            $options = $args[1];
            $closure = $args[2];
        } elseif (count($args) == 2) {
            if (!is_array($args[1]) && !$args[1] instanceof \Closure) {
                throw new \InvalidArgumentException(
                        "Tag behavior is defined by closure or options must be array"
                );                
            }            
            if (is_array($args[1])) {
                $options = $args[1];
                $closure = NULL;
            } else {
                $options = array();
                $closure = $args[1];
            }
        }
        
        // Tag definition
        $factory = isset($options['factory']) 
            ? $options['factory'] 
            : $this->factoryClass;
        
        $object = new $factory($this);
        if (!$object instanceof Tag\IDefinitionFactory) {
            throw new \InvalidArgumentException(
                    "Class '$factory' have to implement Padius\Tag\IDefinitionFactory interface"
            );
        }        
        $this->definitions += $object->defineTag($name, $options, $closure);
        return $this;
    }
    
    /**
     * Renders tag, used by parser. User should never call this method
     * directly
     * 
     * @internal
     * @param  string $name
     * @param  array $attrs
     * @param  \Closure $b
     * @return string
     */
    public function renderTag($name, array $attrs, \Closure $b = NULL) 
    {
        if (preg_match('/^(.+?):(.+)$/', $name, $m)) {
            $that = $this;
            return $this->renderTag($m[1], array(), function() use ($that, $m, $name, $attrs, $b) {
                return $that->renderTag($m[2], $attrs, $b);
            });
        } else {
            $qname = $this->qualifiedTagName($name);
            if (isset($this->definitions[$qname])) {
                $definition = $this->definitions[$qname];
                return $this->stack($definition, $name, $attrs, $b);                
            } else {
                throw \Exception("Neznam");
            }
        }
    }
    
    /**
     * Prepares stag and binding for tag evaulatinon
     * 
     * @param  \Closure $definition
     * @param  string $name
     * @param  array $attrs
     * @param  \Closure $b
     * @return string
     */
    protected function stack(\Closure $definition, $name, array $attrs, \Closure $b = NULL)
    {
        $previous = end($this->bindingStack);
        $previousLocals = $previous ? $previous->locals : $this->globals;
        $locals = is_array($previousLocals) ? ArrayHash::from($previousLocals) : $previousLocals;
        $binding = new Tag\Binding($this, $locals, $name, $attrs, $b);
        array_push($this->bindingStack, $binding);
        $result = $definition($binding);
        array_pop($this->bindingStack);
        return $result;
    }
    
    /**
     * Returns current nesting by binding stack
     * @return string 
     */
    protected function currentNesting()
    {
        return implode(':', array_map(function($item) {
            return $item->name;
        }, $this->bindingStack));
    }
    
    /**
     * Determines name by nesting (specifing name is the most important) and
     * by numeric specifity (like CSS class resolving)
     * 
     * @param  string $name
     * @return string 
     */
    protected function qualifiedTagName($name)
    {
        $nestingParts = array_map(function($item) {
            return $item->name;
        }, $this->bindingStack);
        if (end($nestingParts) !== $name) {
            $nestingParts[] = $name;
        }
        $specificName = implode(':', $nestingParts);
        if (!isset($this->definitions[$specificName])) {
            $possibleMatches = preg_grep("/(^|:)$name$/", 
                    array_keys($this->bindingStack));
            if (empty($possibleMatches)) {
                return $name;
            }
            $specifity = array();
            foreach ($possibleMatches as $tag) {
                $specifity[$this->numericSpecifity($tag, $nestingParts)] = $value;
            }            
            $max = max(array_keys($specifity));
            return $max != 0 ? $specifity[$max] : $name;
        }
        return $specificName;
    }
    
    /**
     * Counts numeric specifity for given name by current nesting
     * @param  string $name
     * @param  array $nestingparts
     * @return int 
     */
    protected function numericSpecifity($name, $nestingparts)
    {
        $nameParts = explode(':', $name);
        $specifity = 0;
        $value = 1;
        if (end($nestingparts) == end($nameParts)) {
            while(count($nestingparts) > 0) {
                if (end($nestingparts) == end($nameParts)) {
                    $specifity += $value;
                    array_pop($nameParts);
                }                    
                array_pop($nameParts);
                $value *= 0.1;
            }
            if (count($nameParts) > 0) {
                $specifity = 0;
            }
        }
        return $specifity;
    }

    /**
     * Sets the current global context for tags
     * @param  array $globals 
     * @return Context Fluent
     */
    public function setGlobals(array $globals)
    {
        $this->globals = $globals;
        return $this;
    }
}