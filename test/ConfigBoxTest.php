<?php declare(strict_types=1);

namespace PhpPkg\ConfigTest;

use PhpPkg\Config\ConfigBox;
use PHPUnit\Framework\TestCase;
use function fclose;
use function fopen;
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
            __DIR__ . '/testdata/config.toml',
            __DIR__ . '/testdata/config.php',
            __DIR__ . '/testdata/config.json',
            __DIR__ . '/testdata/config.json5',
        ]);

        vdump($c->getData());
        $this->assertEquals(89, $c->getInt('age'));
        $this->assertEquals('inhere', $c->get('name'));

        $this->assertTrue($c->has('atIni'));
        $this->assertTrue($c->has('atPhp'));
        $this->assertTrue($c->has('atNeon'));
        $this->assertTrue($c->has('atYaml'));
        $this->assertTrue($c->has('atJson'));
        $this->assertTrue($c->has('atJson5'));
        $this->assertTrue($c->has('atToml'));

        // get by path
        $this->assertEquals(23, $c->getInt('arr0.1'));
        $this->assertEquals('val0', $c->getString('map0.key0'));
    }

    public function testNewFromStrings(): void
    {
        $c = ConfigBox::newFromStrings('ini', '
# comments
key0 = val0
        ');

        $c->loadFromStrings(ConfigBox::FORMAT_JSON5, "{
// comments
key1: 'val1'
        }");

        $c->loadFromStrings(ConfigBox::FORMAT_JSON, '{"key2": "val2"}');
        $c->loadFromStrings(ConfigBox::FORMAT_YAML, 'key3: val3');
        $c->loadFromStrings(ConfigBox::FORMAT_NEON, 'key4: val4');
        $c->set('arrKey', ['abc', 'def']);

        vdump($data = $c->toArray());
        $this->assertNotEmpty($data);
        $this->assertEquals('val0', $c->get('key0'));
        $this->assertEquals('val1', $c->get('key1'));
        $this->assertEquals('val2', $c->get('key2'));
        $this->assertEquals('val4', $c->get('key4'));

        $c->exportTo(__DIR__ . '/testdata/export.php');
        $c->exportTo(__DIR__ . '/testdata/export.json');
    }

    public function testLoadFromStream(): void
    {
        $s1 = fopen(__DIR__ . '/testdata/config.json', 'rb+');
        $c = ConfigBox::newFromStream(ConfigBox::FORMAT_JSON, $s1);
        fclose($s1);

        $this->assertNotEmpty($c->all());
        $this->assertEquals('val at json', $c->get('atJson'));

        $s2 = fopen(__DIR__ . '/testdata/config.yml', 'rb+');
        $c->loadFromStream(ConfigBox::FORMAT_YML, $s2);
        fclose($s2);
        $this->assertEquals('val at yaml', $c->get('atYaml'));
    }
}
