<?php declare(strict_types=1);

namespace PhpPkg\Config;

use InvalidArgumentException;
use Nette\Neon\Neon;
use PhpPkg\Ini\Ini;
use Symfony\Component\Yaml\Parser;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\OS;
use function in_array;
use function json5_decode;
use function json_decode;
use const JSON_BIGINT_AS_STRING;

/**
 * class ConfigReader
 *
 * @author inhere
 */
class ConfigUtil
{
    /**
     * @return array
     */
    public static function getFormats(): array
    {
        return [
            ConfigBox::FORMAT_INI,
            ConfigBox::FORMAT_PHP,
            ConfigBox::FORMAT_NEON,
            ConfigBox::FORMAT_JSON,
            ConfigBox::FORMAT_JSON5,
            ConfigBox::FORMAT_YML,
            ConfigBox::FORMAT_YAML,
        ];
    }

    /**
     * @param string $format
     *
     * @return bool
     */
    public static function isValidFormat(string $format): bool
    {
        return in_array($format, self::getFormats(), true);
    }

    /**
     * @param string $filepath
     * @param string $format
     *
     * @return array
     */
    public static function readFromFile(string $filepath, string $format = ''): array
    {
        $format = $format ?: File::getExtension($filepath, true);

        return match ($format) {
            ConfigBox::FORMAT_INI => self::readIniData($filepath),
            ConfigBox::FORMAT_PHP => self::readPhpData($filepath),
            ConfigBox::FORMAT_NEON => self::readNeonData($filepath),
            ConfigBox::FORMAT_JSON => self::readJsonData($filepath),
            ConfigBox::FORMAT_JSON5 => self::readJson5Data($filepath),
            ConfigBox::FORMAT_YML, ConfigBox::FORMAT_YAML => self::readYamlData($filepath),
            default => throw new InvalidArgumentException('unknown file format: ' . $format),
        };
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readIniData(string $filepath): array
    {
        return Ini::decodeFile($filepath);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readPhpData(string $filepath): array
    {
        return require $filepath;
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readNeonData(string $filepath): array
    {
        $neon = OS::readFile($filepath);

        return Neon::decode($neon);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readJsonData(string $filepath): array
    {
        $json = OS::readFile($filepath);
        $flag = JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR;
        return json_decode($json, true, 512, $flag);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readJson5Data(string $filepath): array
    {
        $json = OS::readFile($filepath);
        $flag = JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR;

        return json5_decode($json, true, 512, $flag);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readYamlData(string $filepath): array
    {
        $parser = new Parser();
        return $parser->parseFile($filepath);
    }
}
