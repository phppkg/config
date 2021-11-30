<?php declare(strict_types=1);

namespace PhpPkg\ConfigTest;

use PhpPkg\Config\ConfigBox;
use PHPUnit\Framework\TestCase;
use function vdump;

/**
 * class ConfigBoxTest
 *
 * @author inhere
 */
class ConfigBoxTest extends TestCase
{
    public function testNewFromFiles(): void
    {
        $c = ConfigBox::newFromFiles([
            __DIR__ . '/testdata/config.ini',
            __DIR__ . '/testdata/config.neon',
            __DIR__ . '/testdata/config.yml',
        ]);

        vdump($c);
        $this->assertEquals('inher', $c->get('name'));
    }
}