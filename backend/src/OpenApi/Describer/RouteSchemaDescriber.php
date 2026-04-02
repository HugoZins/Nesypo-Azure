<?php

namespace App\OpenApi\Describer;

use App\OpenApi\Attribute\OARouteSchema;
use Nelmio\ApiDocBundle\Describer\DescriberInterface;
use Nelmio\ApiDocBundle\OpenApiPhp\Util;
use Nelmio\ApiDocBundle\Util\SetsContextTrait;
use OpenApi\Analysers\AttributeAnnotationFactory;
use OpenApi\Annotations as OAA;
use OpenApi\Generator;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Symfony\Component\Routing\RouterInterface;

class RouteSchemaDescriber implements DescriberInterface
{
    use SetsContextTrait;

    public function __construct(
        private readonly RouterInterface $router,
    ) {}

    public function describe(OAA\OpenApi $api): void
    {
        foreach ($this->router->getRouteCollection()->all() as $route) {
            $defaults = $route->getDefaults();
            if (!isset($defaults['_controller'])) {
                continue;
            }

            [$controllerClass, $methodName] = $this->parseController($defaults['_controller']);
            if (!$controllerClass || !$methodName) {
                continue;
            }

            try {
                $reflMethod = new ReflectionMethod($controllerClass, $methodName);
            } catch (ReflectionException) {
                continue;
            }

            $schemaAttributes = $reflMethod->getAttributes(OARouteSchema::class);
            if (empty($schemaAttributes)) {
                continue;
            }

            $schemaClass = $schemaAttributes[0]->newInstance()->schemaClass;
            if (!class_exists($schemaClass) || !method_exists($schemaClass, '__invoke')) {
                continue;
            }

            $docMethod = new ReflectionMethod($schemaClass, '__invoke');
            $docClassReflector = $docMethod->getDeclaringClass();

            $path = Util::getPath($api, $route->getPath());
            $context = Util::createContext(['nested' => $path], $path->_context);
            $context->namespace = $docClassReflector->getNamespaceName();
            $context->class = $docClassReflector->getShortName();
            $context->method = $docMethod->name;
            $context->filename = $docMethod->getFileName();

            $this->setContext($context);

            $factory = new AttributeAnnotationFactory();
            $annotations = $factory->build($docMethod, $context);
            $this->setContext($context);

            $supportedMethods = array_map('strtolower', $route->getMethods())
                ?: ['get', 'post', 'put', 'patch', 'delete', 'options', 'head', 'trace'];

            foreach ($annotations as $annotation) {
                if ($annotation instanceof OAA\Operation) {
                    if (Generator::UNDEFINED !== $annotation->path && $path->path !== $annotation->path) {
                        continue;
                    }
                    $httpMethod = strtolower((new ReflectionClass($annotation))->getShortName());
                    if (!in_array($httpMethod, $supportedMethods, true)) {
                        continue;
                    }
                    $operation = Util::getOperation($path, $httpMethod);
                    $operation->mergeProperties($annotation);
                }
            }
        }

        $this->setContext(null);
    }

    private function parseController(string $controller): array
    {
        if (str_contains($controller, '::')) {
            return explode('::', $controller, 2);
        }
        return [null, null];
    }
}
