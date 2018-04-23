<?php
declare(strict_types=1);

namespace Tests\Unit\Factory;

use Closure;
use Common\Jwt\JwtConfiguration;
use Tests\Fixture\TestContainer;
use UnitTester;
use Zestic\GraphQL\Factory\ExtractJwtIntoContextFactory;
use Zestic\GraphQL\Interactor\ExtractJwtIntoContext;

class ExtractJwtIntoContextFactoryCest
{
    public function testInvoke(UnitTester $I)
    {
        $container = new TestContainer();

        $container->set(JwtConfiguration::class, $this->getJwtConfiguration());

        $extractJwtIntoContext = (new ExtractJwtIntoContextFactory())->__invoke($container);
        $getOptions = function () {
            return $this->options;
        };
        $options = Closure::bind($getOptions, $extractJwtIntoContext, ExtractJwtIntoContext::class)->__invoke();

        // secret should be set from jwt config location, loaded then passed
        $I->assertSame($this->getExpectedPublicKey(), $options['secret']);
    }

    private function getExpectedPublicKey(): string
    {
       return <<<PUBLIC_KEY
-----BEGIN PUBLIC KEY-----
MIICIjANBgkqhkiG9w0BAQEFAAOCAg8AMIICCgKCAgEApJ1O3NsW3oWAYgMouZyE
yP8faYCm/P2l/hb1YaPEWiPCS6wI3E2gcDbSS/pQuBOZA8GX5xBYNG6G3s7KHwUi
2Z1IlvaH0Ac+pZIVZDhgyerIn7JZVlzcGgq0NFvljr0P9kDZkNW2LLhLYVISQAcv
Y04u1LlgjqPbWRzg5DXUwSdPwccnLGwsNLYJEhtmAUz+ggNpZGiiQkA8dO90Us+e
OWWAKQPItkAsxTfiIBi5D3L3L4EG4UbsKcn7LJCvnQ+O9vCUmZlOWiPAJ3cN7eX3
CqfZVZqaw+pq5UYAObBgJjRKi5z0xVS+JRCvpYPLz05ehK1ctklE1+32GGG8WdL2
QfP9N1eJHqJmVzymA+jqdmoKzmEVeHkn1BPms6XkUTLyzipxcdNbVcXYHDDHfKar
EgxATlitxSAk/UE+YqrN3WhIA7PCVi3SwohpSSJ8o1A+/zTe0eZ/gx4g1hf5LgTd
PQDsK2G5piwGy1Xy+lKUZIBMnTKw9CrwVnG7KimkwvPBCxnxblFq3Gx8R9/CDitV
ZkbOKxP7Q+18b+z7z/+EC4W7nPQa1wYR5dSPzyvPVAM23sNDInNoEVlHGa6jsnxe
VQUlL1F88OnApUKeJ+rOck2P7+X8v/keHzueR1GtGCnjukNjQW1AvmSQBQCWReei
+SVgCN1jO75vpPjVUs22ZbMCAwEAAQ==
-----END PUBLIC KEY-----

PUBLIC_KEY;
    }

    protected function getJwtConfiguration(): JwtConfiguration
    {
        $config = [
            'algorithm' => 'RS256',
            'publicKey' => $this->getExpectedPublicKey(),
            'tokenTtl' => 3600,
        ];

        return new JwtConfiguration($config);
    }
}
