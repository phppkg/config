<?php declare(strict_types=1);

namespace PhpPkg\Config;

use InvalidArgumentException;
use Nette\Neon\Neon;
use PhpPkg\Ini\Ini;
use RuntimeException;
use Symfony\Component\Yaml\Parser;
use Symfony\Component\Yaml\Yaml;
use Toolkit\FsUtil\File;
use Toolkit\Stdlib\OS;
use Toolkit\Stdlib\Php;
use Yosymfony\Toml\Toml;
use function in_array;
use function json5_decode;
use function json_decode;
use function json_encode;
use function rtrim;
use const JSON_BIGINT_AS_STRING;
use const JSON_THROW_ON_ERROR;

/**
 * class ConfigReader
 *
 * @author inhere
 */
class ConfigUtil
{
    /**
     * @var int
     */
    public static int $jsonFlags = JSON_BIGINT_AS_STRING | JSON_THROW_ON_ERROR;

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
            ConfigBox::FORMAT_TOML,
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
     * @param string $format
     */
    public static function assertFormat(string $format): void
    {
        if (!self::isValidFormat($format)) {
            throw new InvalidArgumentException("invalid config format: $format");
        }
    }

    /**
     * @param string $format
     *
     * @return string
     */
    public static function resolveFormat(string $format): string
    {
        if ($format === ConfigBox::FORMAT_YML) {
            return ConfigBox::FORMAT_YAML;
        }
        return $format;
    }

    /**
     * @param string $format
     * @param resource $stream
     *
     * @return array
     */
    public static function readFromStream(string $format, $stream): array
    {
        $str = File::streamReadAll($stream);

        return self::readFromString($format, $str);
    }

    /**
     * @param string $format
     * @param string $str
     *
     * @return array
     */
    public static function readFromString(string $format, string $str): array
    {
        return match ($format) {
            ConfigBox::FORMAT_INI => Ini::decode($str),
            // ConfigBox::FORMAT_PHP => self::readPhpData($filepath),
            ConfigBox::FORMAT_NEON => Neon::decode($str),
            ConfigBox::FORMAT_JSON => json_decode($str, true, 512, self::$jsonFlags),
            ConfigBox::FORMAT_JSON5 => json5_decode($str, true, 512, self::$jsonFlags),
            ConfigBox::FORMAT_TOML => self::parseTomlString($str),
            ConfigBox::FORMAT_YML, ConfigBox::FORMAT_YAML => self::parseYamlString($str),
            default => throw new InvalidArgumentException('unknown config format: ' . $format),
        };
    }

    /**
     * @param array $data
     * @param string $format
     * @param int $flags
     *
     * @return string
     * @throws \JsonException
     */
    public static function encodeToString(array $data, string $format, int $flags = 0): string
    {
        switch ($format) {
            case ConfigBox::FORMAT_INI:
                return Ini::encode($data, $flags);
            case ConfigBox::FORMAT_TOML:
                throw new RuntimeException('not support encode data to ' . $format);
            case ConfigBox::FORMAT_PHP:
                $codes = rtrim(Php::exportVar($data));
                return "<?php\n// auto exported by phppkg/config\n\nreturn $codes;\n";
            case ConfigBox::FORMAT_NEON:
                return Neon::encode($data, true);
            case ConfigBox::FORMAT_YML:
            case ConfigBox::FORMAT_YAML:
                return Yaml::dump($data, 2, 4, $flags);
            case ConfigBox::FORMAT_JSON:
            case ConfigBox::FORMAT_JSON5:
            default:
                return json_encode($data, $flags|JSON_THROW_ON_ERROR);
        }
    }

    /**
     * read data from file
     *
     * @param string $filepath
     * @param string $format
     *
     * @return array
     */
    public static function readFromFile(string $filepath, string $format = ''): array
    {
        $format = $format ?: File::getExtension($filepath, true);

        if (ConfigBox::FORMAT_PHP === $format) {
            return self::readPhpData($filepath);
        }

        $str = File::readAll($filepath);
        return self::readFromString($format, $str);
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

        return json_decode($json, true, 512, self::$jsonFlags);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function readJson5Data(string $filepath): array
    {
        $json = OS::readFile($filepath);

        return json5_decode($json, true, 512, self::$jsonFlags);
    }

    /**
     * @param string $filepath
     * @param int $flags
     *
     * @return array
     */
    public static function readYamlData(string $filepath, int $flags = 0): array
    {
        return (new Parser())->parseFile($filepath, $flags);
    }

    /**
     * @param string $yaml
     * @param int $flags
     *
     * @return array
     */
    public static function parseYamlString(string $yaml, int $flags = 0): array
    {
        return (new Parser())->parse($yaml, $flags);
    }

    /**
     * @param string $filepath
     *
     * @return array
     */
    public static function parseTomlFile(string $filepath): array
    {
        return Toml::parseFile($filepath);
    }

    /**
     * @param string $toml
     *
     * @return array
     */
    public static function parseTomlString(string $toml): array
    {
        return Toml::parse($toml);
    }
}
