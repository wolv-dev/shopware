<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Unit\Components;

use PHPUnit\Framework\TestCase;

interface MyInterface
{
    public function myPublic($bar, $foo = 'bar', array $barBar = [], MyInterface $fooFoo = null);
}

interface MyReferenceInterface
{
    public function myPublic(&$bar, $foo);
}

class MyBasicTestClass implements MyInterface
{
    public function myPublic($bar, $foo = 'bar', array $barBar = [], MyInterface $fooFoo = null)
    {
        return $bar . $foo;
    }

    protected function myProtected($bar)
    {
    }
}

class MyReferenceTestClass implements MyReferenceInterface
{
    public function myPublic(&$bar, $foo)
    {
        return $bar . $foo;
    }
}

class TestProxyFactory extends \Enlight_Hook_ProxyFactory
{
    public function __construct(\Enlight_Hook_HookManager $hookManager, $proxyNamespace)
    {
        $this->hookManager = $hookManager;
        $this->proxyNamespace = $proxyNamespace;
    }
}

class EnlightHookProxyFactoryTest extends TestCase
{
    private $proxyFactory;

    public function setUp()
    {
        /** @var \Enlight_Hook_HookManager $SUT */
        $hookManager = $this->createConfiguredMock(\Enlight_Hook_HookManager::class, [
            'hasHooks' => true,
        ]);

        $this->proxyFactory = new TestProxyFactory($hookManager, 'ShopwareTests');
    }

    public function testGenerateBasicProxyClass()
    {
        $generatedClass = $this->invokeMethod($this->proxyFactory, 'generateProxyClass', [MyBasicTestClass::class]);
        $expectedClass = <<<'EOT'
<?php
class ShopwareTests_ShopwareTestsUnitComponentsMyBasicTestClassProxy extends \Shopware\Tests\Unit\Components\MyBasicTestClass implements \Enlight_Hook_Proxy
{

    private $_hookProxyExecutionContexts = null;

    /**
     * @inheritdoc
     */
    public static function getHookMethods()
    {
        return ['myPublic', 'myProtected'];
    }

    /**
     * @inheritdoc
     */
    public function pushHookExecutionContext($method, Enlight_Hook_HookExecutionContext $context)
    {
        $this->_hookProxyExecutionContexts[$method][] = $context;
    }

    /**
     * @inheritdoc
     */
    public function popHookExecutionContext($method)
    {
        if (isset($this->_hookProxyExecutionContexts[$method])) {
            array_pop($this->_hookProxyExecutionContexts[$method]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCurrentHookProxyExecutionContext($method)
    {
        if (!isset($this->_hookProxyExecutionContexts[$method]) || count($this->_hookProxyExecutionContexts[$method]) === 0) {
            return null;
        }

        $contextCount = count($this->_hookProxyExecutionContexts[$method]);
        $context = $this->_hookProxyExecutionContexts[$method][$contextCount - 1];

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function executeParent($method, array $args = array())
    {
        $context = $this->getCurrentHookProxyExecutionContext($method);
        if (!$context) {
            throw new Exception(
                sprintf('Cannot execute parent without hook execution context for method "%s"', $method)
            );
        }

        return $context->executeReplaceChain($args);
    }

    /**
     * @inheritdoc
     */
    public function myPublic($bar, $foo = 'bar', array $barBar = array(), \Shopware\Tests\Unit\Components\MyInterface $fooFoo = null)
    {
        $method = 'myPublic';
        $context = $this->getCurrentHookProxyExecutionContext($method);
        $hookManager = ($context) ? $context->getHookManager() : Shopware()->Hooks();

        return $hookManager->executeHooks(
            $this,
            $method,
            ['bar' => $bar, 'foo' => $foo, 'barBar' => $barBar, 'fooFoo' => $fooFoo]
        );
    }

    /**
     * @inheritdoc
     */
    protected function myProtected($bar)
    {
        $method = 'myProtected';
        $context = $this->getCurrentHookProxyExecutionContext($method);
        $hookManager = ($context) ? $context->getHookManager() : Shopware()->Hooks();

        return $hookManager->executeHooks(
            $this,
            $method,
            ['bar' => $bar]
        );
    }


}

EOT;
        $this->assertSame($expectedClass, $generatedClass);
    }

    public function testGenerateProxyClassWithReferenceParameter()
    {
        $generatedClass = $this->invokeMethod($this->proxyFactory, 'generateProxyClass', [MyReferenceTestClass::class]);
        $expectedClass = <<<'EOT'
<?php
class ShopwareTests_ShopwareTestsUnitComponentsMyReferenceTestClassProxy extends \Shopware\Tests\Unit\Components\MyReferenceTestClass implements \Enlight_Hook_Proxy
{

    private $_hookProxyExecutionContexts = null;

    /**
     * @inheritdoc
     */
    public static function getHookMethods()
    {
        return ['myPublic'];
    }

    /**
     * @inheritdoc
     */
    public function pushHookExecutionContext($method, Enlight_Hook_HookExecutionContext $context)
    {
        $this->_hookProxyExecutionContexts[$method][] = $context;
    }

    /**
     * @inheritdoc
     */
    public function popHookExecutionContext($method)
    {
        if (isset($this->_hookProxyExecutionContexts[$method])) {
            array_pop($this->_hookProxyExecutionContexts[$method]);
        }
    }

    /**
     * @inheritdoc
     */
    public function getCurrentHookProxyExecutionContext($method)
    {
        if (!isset($this->_hookProxyExecutionContexts[$method]) || count($this->_hookProxyExecutionContexts[$method]) === 0) {
            return null;
        }

        $contextCount = count($this->_hookProxyExecutionContexts[$method]);
        $context = $this->_hookProxyExecutionContexts[$method][$contextCount - 1];

        return $context;
    }

    /**
     * @inheritdoc
     */
    public function executeParent($method, array $args = array())
    {
        $context = $this->getCurrentHookProxyExecutionContext($method);
        if (!$context) {
            throw new Exception(
                sprintf('Cannot execute parent without hook execution context for method "%s"', $method)
            );
        }

        return $context->executeReplaceChain($args);
    }

    /**
     * @inheritdoc
     */
    public function myPublic(&$bar, $foo)
    {
        $method = 'myPublic';
        $context = $this->getCurrentHookProxyExecutionContext($method);
        $hookManager = ($context) ? $context->getHookManager() : Shopware()->Hooks();

        return $hookManager->executeHooks(
            $this,
            $method,
            ['bar' => &$bar, 'foo' => $foo]
        );
    }


}

EOT;
        $this->assertSame($expectedClass, $generatedClass);
    }

    private function invokeMethod($object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }
}
