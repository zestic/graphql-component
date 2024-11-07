<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Composer\Autoload\ClassLoader;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\GraphQLQueryMessageInterface;

class AutoWireMessages
{
    private static array $files = [];

    public static function findHandlersForInterface(string $interface, array $directories = []): array
    {
        self::setUpFiles($directories);

        $operations = self::findOperationsAndMessagesForInterface($interface);
        self::addHandlersToOperations($operations);

        return $operations;
    }

    public static function getMutationHandlers(array $directories = []): array
    {
        return self::findHandlersForInterface(GraphQLMutationMessageInterface::class, $directories);
    }

    public static function getQueryHandlers(array $directories = []): array
    {
        return self::findHandlersForInterface(GraphQLQueryMessageInterface::class, $directories);
    }

    private static function classHandlesMessage(string $classname, string $message): bool
    {
        $method = new \ReflectionMethod($classname, '__invoke');
        $parameters = $method->getParameters();
        foreach ($parameters as $parameter) {
            $type = $parameter->getType()->getName();
            if ($type === $message) {
                return true;
            }
        }

        return false;
    }

    private static function classImplementsInterface(string $classname, string $interface): bool
    {
        return in_array($interface, class_implements($classname));
    }

    private static function findHandlersForMessage(string $message): array
    {
        $handlers = [];
        foreach (self::$files as $index => $file) {
            $content = file_get_contents($file);
            if (str_contains($content, $message)) {
                $classname = self::getFQCNFromFile($file);
                try {
                    if (self::classHandlesMessage($classname, $message)) {
                        $handlers[] = $classname;
                    }
                } catch (\Exception $e) {
                } finally {
                    // remove the file to save time looping
                    unset(self::$files[$index]);
                }
            }
        }

        return $handlers;
    }

    private static function addHandlersToOperations(array &$operations): void
    {
        foreach ($operations as $operationName => $operation) {
            $operations[$operationName]['handlers'] = self::findHandlersForMessage($operation['message']);
        }
    }

    private static function findOperationsAndMessagesForInterface(string $interface): array
    {
        $operations = [];
        foreach (self::$files as $index => $file) {
            $content = file_get_contents($file);
            if (str_contains($content, $interface)) {
                $classname = self::getFQCNFromFile($file);
                if (self::classImplementsInterface($classname, $interface)) {
                    $operation = self::getOperationFromClassName($classname);
                    $operations[$operation]['message'] = $classname;
                    unset(self::$files[$index]);
                }
            }
        }

        return $operations;
    }

    private static function getFQCNFromFile(string $filePath): string
    {
        $namespace = self::getNamespaceFromFile($filePath);
        $pathParts = pathinfo($filePath);

        return $namespace.'\\'.$pathParts['filename'];
    }

    private static function getNamespaceFromFile(string $filePath): string
    {
        $tokens = self::getTokensFromFile($filePath);
        $namespaceStart = false;
        foreach ($tokens as $token) {
            if (is_array($token) && $token[0] == T_NAMESPACE) {
                $namespaceStart = true;
                continue;
            }
            if ($namespaceStart && is_array($token) && $token[0] !== T_WHITESPACE) {
                return $token[1];
            }
        }

        return '';
    }

    private static function getTokensFromFile(string $filePath): array
    {
        $fp = fopen($filePath, 'r');
        $buffer = '';
        while (!feof($fp)) {
            $buffer .= fread($fp, 512);
        }
        fclose($fp);

        return token_get_all($buffer);
    }

    private static function loadFilePaths(): void
    {
        $loaders = ClassLoader::getRegisteredLoaders();
        $namespaces = [];
        foreach ($loaders as $loader) {
            $namespaces = array_merge($namespaces, $loader->getPrefixesPsr4());
        }

        foreach ($namespaces as $directories) {
            self::scanDirectories($directories);
        }
    }

    private static function scanDirectories(array $directories): void
    {
        $subDirectories = [];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                if ($dh = opendir($directory)) {
                    while (($file = readdir($dh)) !== false) {
                        $filePath = $directory . '/' . $file;
                        $info = pathinfo($filePath);
                        if (isset($info['extension']) && $info['extension'] === 'php') {
                            self::$files[] = realpath($filePath);
                        };
                        if ($info['basename'] === $info['filename']) {
                            $subDirectories[] = $filePath;
                        }
                    }
                    closedir($dh);
                }
            }
        }
        if (!empty($subDirectories)) {
            self::scanDirectories($subDirectories);
        }
    }

    private static function getOperationFromClassName(string $className): string
    {
        $parts = explode('\\', $className);
        $operationName = array_pop($parts);
        $position = strrpos($operationName, 'Message');
        if ($position!== false) {
            $operationName = substr($operationName, 0, $position);
        }

        return lcfirst($operationName);
    }

    private static function setUpFiles(array $directories = []): void
    {
        if (!empty($directories)) {
            self::scanDirectories($directories);
        }

        if (empty(self::$files)) {
            self::loadFilePaths();
        }
    }
}
