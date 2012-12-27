<?php
/**
 * Imbo
 *
 * Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to
 * deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or
 * sell copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * * The above copyright notice and this permission notice shall be included in
 *   all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS
 * IN THE SOFTWARE.
 *
 * @package TestSuite\UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imbo
 */

namespace Imbo\UnitTest;

use Imbo\Router;

/**
 * @package TestSuite\UnitTests
 * @author Christer Edvartsen <cogo@starzinger.net>
 * @copyright Copyright (c) 2011-2012, Christer Edvartsen <cogo@starzinger.net>
 * @license http://www.opensource.org/licenses/mit-license MIT License
 * @link https://github.com/imbo/imbo
 * @covers Imbo\Router
 */
class RouterTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Router
     */
    private $router;

    private $event;
    private $request;

    /**
     * Set up the router instance
     */
    public function setUp() {
        $this->router = new Router();
        $this->request = $this->getMock('Imbo\Http\Request\RequestInterface');
        $this->event = $this->getMock('Imbo\EventManager\EventInterface');
        $this->event->expects($this->any())->method('getRequest')->will($this->returnValue($this->request));
    }

    /**
     * Tear down the router instance
     */
    public function tearDown() {
        $this->router = null;
        $this->request = null;
        $this->event = null;
    }

    /**
     * @covers Imbo\Router::getDefinition
     */
    public function testReturnsACorrectDefinition() {
        $definition = $this->router->getDefinition();
        $this->assertInternalType('array', $definition);

        foreach ($definition as $d) {
            $this->assertInstanceOf('Imbo\EventListener\ListenerDefinition', $d);
        }
    }

    /**
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage I'm a teapot
     * @expectedExceptionCode 418
     * @covers Imbo\Router::route
     */
    public function testCanBeATeaPot() {
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('BREW'));
        $this->router->route($this->event);
    }

    /**
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Unsupported HTTP method
     * @expectedExceptionCode 501
     * @covers Imbo\Router::route
     */
    public function testThrowsExceptionOnUnsupportedHttpMethod() {
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('TRACE'));
        $this->router->route($this->event);
    }

    /**
     * Return invalid routes for the resolve method
     *
     * @return array[]
     */
    public function getInvalidRoutes() {
        return array(
            array('/foobar'),
            array('/status.json/'),
            array('/users/Christer'),
            array('/users/christer.json/'),
            array('/users/Christer.json/'),
            array('/users/christer/images.json/'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c.gif/'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta.json/'),
        );
    }

    /**
     * @dataProvider getInvalidRoutes
     * @expectedException Imbo\Exception\RuntimeException
     * @expectedExceptionMessage Not Found
     * @expectedExceptionCode 404
     * @covers Imbo\Router::route
     */
    public function testThrowsExceptionWhenNoRouteMatches($route) {
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));
        $this->request->expects($this->once())->method('getPath')->will($this->returnValue($route));
        $this->router->route($this->event);
    }

    /**
     * Returns valid routes for the router
     *
     * @return array[]
     */
    public function getValidRoutes() {
        return array(
            // Status resource
            array('/status', 'status'),
            array('/status/', 'status'),
            array('/status.json', 'status', null, null, 'json'),
            array('/status.xml', 'status', null, null, 'xml'),
            array('/status.html', 'status', null, null, 'html'),

            // User resource
            array('/users/christer', 'user', 'christer'),
            array('/users/christer/', 'user', 'christer'),
            array('/users/christer.json', 'user', 'christer', null, 'json'),
            array('/users/christer.xml', 'user', 'christer', null, 'xml'),
            array('/users/christer.html', 'user', 'christer', null, 'html'),
            array('/users/user_name', 'user', 'user_name'),
            array('/users/user-name', 'user', 'user-name'),

            // Images resource
            array('/users/christer/images', 'images', 'christer'),
            array('/users/christer/images/', 'images', 'christer'),
            array('/users/christer/images.json', 'images', 'christer', null, 'json'),
            array('/users/christer/images.xml', 'images', 'christer', null, 'xml'),
            array('/users/christer/images.html', 'images', 'christer', null, 'html'),
            array('/users/user_name/images', 'images', 'user_name'),
            array('/users/user-name/images', 'images', 'user-name'),

            // Image resource
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c', 'image', 'christer', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/', 'image', 'christer', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c.png', 'image', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'png'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c.jpg', 'image', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'jpg'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c.gif', 'image', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'gif'),
            array('/users/user_name/images/a9b80ed42957fd508c617549cad07d6c', 'image', 'user_name', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/user-name/images/a9b80ed42957fd508c617549cad07d6c', 'image', 'user-name', 'a9b80ed42957fd508c617549cad07d6c'),

            // Metadata resource
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta', 'metadata', 'christer', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta/', 'metadata', 'christer', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta.json', 'metadata', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'json'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta.xml', 'metadata', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'xml'),
            array('/users/christer/images/a9b80ed42957fd508c617549cad07d6c/meta.html', 'metadata', 'christer', 'a9b80ed42957fd508c617549cad07d6c', 'html'),
            array('/users/user_name/images/a9b80ed42957fd508c617549cad07d6c/meta', 'metadata', 'user_name', 'a9b80ed42957fd508c617549cad07d6c'),
            array('/users/user-name/images/a9b80ed42957fd508c617549cad07d6c/meta', 'metadata', 'user-name', 'a9b80ed42957fd508c617549cad07d6c'),
        );
    }

    /**
     * @dataProvider getValidRoutes
     * @covers Imbo\Router::route
     */
    public function testCanMatchValidRoutes($route, $resource, $publicKey = null, $imageIdentifier = null, $extension = null) {
        $this->request->expects($this->once())->method('setResource')->with($resource);
        $this->request->expects($this->once())->method('getPath')->will($this->returnValue($route));
        $this->request->expects($this->once())->method('getMethod')->will($this->returnValue('GET'));

        if ($publicKey) {
            $this->request->expects($this->once())->method('setPublicKey')->with($publicKey);
        }

        if ($imageIdentifier) {
            $this->request->expects($this->once())->method('setImageIdentifier')->with($imageIdentifier);
        }

        if ($extension) {
            $this->request->expects($this->once())->method('setExtension')->with($extension);
        }

        $this->router->route($this->event);
    }
}