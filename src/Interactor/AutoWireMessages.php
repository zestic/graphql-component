<?php
declare(strict_types=1);

namespace Zestic\GraphQL\Interactor;

use Composer\Autoload\ClassLoader;
use Symfony\Component\Finder\Finder;
use Zestic\GraphQL\GraphQLMutationMessageInterface;
use Zestic\GraphQL\GraphQLQueryMessageInterface;

class AutoWireMessages
{
    private static string $interface;
    private static string $contentFilter;

    public static function findHandlersForInterface(string $interface): array
    {
        self::$interface = $interface;
        self::$contentFilter = "/" . (new \ReflectionClass($interface))->getShortName() . "/";

        $loaders = ClassLoader::getRegisteredLoaders();
        $namespaces = [];
        foreach ($loaders as $loader) {
            $namespaces = array_merge($namespaces, $loader->getPrefixesPsr4());
        }
        $messages = self::findMessagesForInterface($namespaces);

        return self::findHandlersForMessages($messages, $namespaces);
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

    private static function findHandlersInDirectories(string $message, array $directories): array
    {
        $handlers = [];
        $finder = new Finder();
        $finder
            ->in($directories)
            ->name('*.php');
        foreach ($finder->files()->contains($message) as $fileInfo) {
            try {
                $classname = self::getFQCNFromFileInfo($fileInfo);
                if (self::classHandlesMessage($classname, $message)) {
                    $handlers[$message][] = $classname;
                }
            } catch (\Exception $e) {
                continue;
            }
        }

        return $handlers;
    }

    /**
     * @param string[] $messages
     * @param array $namespaces
     *
     * @return array
     */
    private static function findHandlersForMessages(array $messages, array $namespaces): array
    {
        $directories = [];
        foreach ($namespaces as $namespaceDirectories) {
            $directories = array_merge($directories, $namespaceDirectories);
        }
        $handlers = [];
        foreach ($messages as $message) {
            $handlers = array_merge($handlers, self::findHandlersInDirectories($message, $directories));
        }

        return $handlers;
    }

    private static function findMessagesForInterface(array $namespaces): array
    {
        $messages = [];
        foreach ($namespaces as $namespace => $directories) {
            $messages = array_merge($messages, self::findMessagesInNamespace($namespace, $directories));
        }

        return $messages;
    }

    private static function findMessagesInNamespace(string $namespace, array $directories): array
    {
        $messages = [];
        foreach ($directories as $directory) {
            $finder = new Finder();
            $finder
                ->in($directory)
                ->name('*.php')
                ->depth('== 0');
            foreach ($finder->files()->contains(self::$contentFilter) as $fileInfo) {
                try {
                    $classname = self::getFQCNFromFileInfo($fileInfo);
                    if (self::classImplementsInterface($classname, self::$interface)) {
                        $messages[] = $classname;
                    }
                } catch (\Exception $e) {
                    continue;
                }
            }
            $finder = new Finder();
            // find all files in the current directory
            $finder
                ->in($directory)
                ->depth('== 0');
            foreach ($finder->directories() as $subDirectory) {
                $subDirectoryMessages = self::findMessagesInNamespace(
                    "{$namespace}{$subDirectory->getRelativePathname()}\\",
                    [$subDirectory->getPathname()]
                );
                $messages = array_merge($messages, $subDirectoryMessages);
            }
        }

        return $messages;
    }

    private static function getClassnameFromFile(string $namespace, \SplFileInfo $fileInfo, string $directory): string
    {
        $classname = $fileInfo->getFilenameWithoutExtension();

        return "\\{$namespace}{$classname}";
    }

    private static function getFQCNFromFileInfo(\SplFileInfo $fileInfo): string
    {
        $namespace = self::getNamespaceFromFile($fileInfo);

        return $namespace.'\\'.$fileInfo->getBasename('.php');
    }

    private static function getNamespaceFromFile(\SplFileInfo $fileInfo): string
    {
        $tokens = self::getTokensFromFile($fileInfo);
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

    private static function getTokensFromFile(\SplFileInfo $fileInfo): array
    {
        $fp = fopen($fileInfo->getRealPath(), 'r');
        $buffer = '';
        while (!feof($fp)) {
            $buffer .= fread($fp, 512);
        }
        fclose($fp);

        return token_get_all($buffer);
    }
}
