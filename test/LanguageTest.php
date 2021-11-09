<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2018/10/15
 * Time: 下午3:56
 */

namespace PhpPkg\ConfigTest;

use PHPUnit\Framework\TestCase;
use PhpPkg\Config\Language;

/**
 * Class LanguageTest
 *
 * @package PhpPkg\ConfigTest
 */
class LanguageTest extends TestCase
{
    public function testTranslate(): void
    {
        $l = new Language([
            'lang'      => 'en',
            'allowed'   => ['en', 'zh-CN'],
            'basePath'  => __DIR__ . '/testdata/language',
            'langFiles' => [
                'response.php'
            ],
        ]);

        $arr = [
            0   => 'a',
            1   => 'b',
            'k' => 'c',
        ];

        $msg = $l->tl('response.key');
        $this->assertEquals('message', $msg);
        $this->assertTrue($l->has('response.key'));
        $this->assertEquals('successful', $l->trans('response.0'));
        $this->assertEquals('error', $l->trans('response.2'));
    }
}
