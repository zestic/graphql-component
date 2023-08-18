<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Composer\Autoload\ClassLoader;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\GraphQLQueryMessageInterface;

class AutoWireMessages
{
    private static array $files;

    public static function findHandlersForInterface(string $interface): array
    {
        self::loadFilePaths();

        $messages = self::findMessagesForInterface($interface);

        return self::findHandlersForMessages($messages);
    }

    public static function getMutationHandlers(): array
    {
        return self::findHandlersForInterface(GraphQLMutationMessageInterface::class);
    }

    public static function getQueryHandlers(): array
    {
        return self::findHandlersForInterface(GraphQLQueryMessageInterface::class);
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

    private static function findHandlersForMessages(array $messages): array
    {
        $handlers = [];
        foreach ($messages as $message) {
            $handlers[$message] = self::findHandlersForMessage($message);
        }

        return $handlers;
    }

    private static function findMessagesForInterface(string $interface): array
    {
        $messages = [];
        foreach (self::$files as $index => $file) {
            $content = file_get_contents($file);
            if (str_contains($content, $interface)) {
                $classname = self::getFQCNFromFile($file);
                if (self::classImplementsInterface($classname, $interface)) {
                    $messages[] = $classname;
                    unset(self::$files[$index]);
                }
            }
        }

        return $messages;
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

        foreach ($namespaces as $namespace => $directories) {
            self::scanDirectories($namespace, $directories);
        }
    }

    private static function scanDirectories(string $namespace, array $directories): void
    {
        $subDirectories = [];
        foreach ($directories as $directory) {
            if (is_dir($directory)) {
                if ($dh = opendir($directory)) {
                    while (($file = readdir($dh)) !== false) {
                        $filePath = $directory . '/' . $file;
                        $info = pathinfo($filePath);
                        if ($info['extension'] === 'php') {
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
            self::scanDirectories($namespace, $subDirectories);
        }
    }
}
