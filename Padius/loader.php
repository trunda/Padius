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
 * Load Padius template framework
 */
define('PADIUS', TRUE);
define('PADIUS_DIR', __DIR__);
define('PADIUS_VERSION_ID', 05000); // v0.5.0

/**
 * Loader
 * @author Jakub Truneček
 */
final class Loader
{

    private $list = array(
        'padius\utils' => '/Utils.php',
        'padius\parser' => '/Parser.php',
        'padius\context' => '/Context.php',
        'padius\arrayhash' => '/ArrayHash.php',
        'padius\tag\binding' => '/Tag/Binding.php',
        'padius\tag\definitionfactory' => '/Tag/DefinitionFactory.php',
        'padius\tag\idefinitionfactory' => '/Tag/IDefinitionFactory.php',
        'padius\parser\scanner' => '/Parser/Scanner.php',
        'padius\parser\iscanner' => '/Parser/IScanner.php',
        'padius\parser\node' => '/Parser/Node.php',
        'padius\parser\containernode' => '/Parser/ContainerNode.php',
    );

    /**
     * Try to load the requested class.
     * @param  string  class/interface name
     * @return void
     */
    final public static function load($type)
    {
        foreach (func_get_args() as $type) {
            if (!class_exists($type)) {
                throw new \InvalidArgumentException("Unable to load class or interface '$type'.");
            }
        }
    }

    /**
     * Register autoloader.
     * @return void
     */
    public function register()
    {
        if (!function_exists('spl_autoload_register')) {
            throw new \Exception('spl_autoload does not exist in this PHP installation.');
        }

        spl_autoload_register(array($this, 'tryLoad'));
    }

    /**
     * Unregister autoloader.
     * @return bool
     */
    public function unregister()
    {
        return spl_autoload_unregister(array($this, 'tryLoad'));
    }

    /**
     * Handles autoloading of classes or interfaces.
     * @param  string
     * @return void
     */
    public function tryLoad($type)
    {        
        $type = ltrim(strtolower($type), '\\');
        if (isset($this->list[$type])) {
            require_once (PADIUS_DIR . $this->list[$type]);
        }
    }

}

$loader = new Loader;
$loader->register();