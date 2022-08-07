<?php declare(strict_types=1);

namespace PhpPkg\Config;

use JsonException;
use RuntimeException;
use Toolkit\FsUtil\File;
use const JSON_PRETTY_PRINT;

/**
 * class ConfigBox
 *
 * @author inhere
 */
class ConfigBox extends Collection
{
    public const FORMAT_INI   = 'ini';
    public const FORMAT_PHP   = 'php';
    public const FORMAT_YML   = 'yml';
    public const FORMAT_YAML  = 'yaml';
    public const FORMAT_TOML  = 'toml';
    public const FORMAT_NEON  = 'neon';
    public const FORMAT_JSON  = 'json';
    public const FORMAT_JSON5 = 'json5';

    /**
     * name of the config
     *
     * @var string
     */
    protected string $name = 'config';

    /**
     * Encode flags on export data to file
     *
     * @var array{string: int}
     */
    protected array $encodeFlags = [
        self::FORMAT_JSON  => JSON_PRETTY_PRINT,
        self::FORMAT_JSON5 => JSON_PRETTY_PRINT,
    ];

    /**
     * @param string $filepath
     * @param string $format
     *
     * @return static
     */
    public static function newFromFile(string $filepath, string $format = ''): self
    {
        return (new static())->loadFromFile($filepath, $format);
    }

    /**
     * @param string[] $filePaths
     * @param string $format
     *
     * @return static
     */
    public static function newFromFiles(array $filePaths, string $format = ''): self
    {
        return (new static())->loadFromFiles($filePaths, $format);
    }

    /**
     * @param string $format
     * @param resource $stream
     *
     * @return static
     */
    public static function newFromStream(string $format, $stream): self
    {
        return (new static())->loadFromStream($format, $stream);
    }

    /**
     * @param string $format
     * @param string $str
     *
     * @return static
     */
    public static function newFromString(string $format, string $str): self
    {
        return self::newFromStrings($format, $str);
    }

    /**
     * @param string $format
     * @param string ...$strings
     *
     * @return static
     */
    public static function newFromStrings(string $format, string ...$strings): self
    {
        return (new static())->loadFromStrings($format, ...$strings);
    }

    /**
     * @param string $format
     * @param resource $stream
     *
     * @return static
     */
    public function loadFromStream(string $format, $stream): self
    {
        $data = ConfigUtil::readFromStream($format, $stream);

        return $this->load($data);
    }

    /**
     * @param string $format
     * @param string ...$strings
     *
     * @return static
     */
    public function loadFromStrings(string $format, string ...$strings): self
    {
        foreach ($strings as $str) {
            $data = ConfigUtil::readFromString($format, $str);

            // load and merge to data.
            $this->load($data);
        }

        return $this;
    }

    /**
     * @param string[] $filePaths
     * @param string $format  If is empty, will parse from filepath.
     *
     * @return $this
     */
    public function loadFromFiles(array $filePaths, string $format = ''): self
    {
        foreach ($filePaths as $filePath) {
            $this->loadFromFile($filePath, $format);
        }

        return $this;
    }

    /**
     * @param string $filepath
     * @param string $format  If is empty, will parse from filepath.
     *
     * @return $this
     */
    public function loadFromFile(string $filepath, string $format = ''): self
    {
        $data = ConfigUtil::readFromFile($filepath, $format);

        return $this->load($data);
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadIniFile(string $filepath): self
    {
        return $this->load(ConfigUtil::readIniData($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadPhpFile(string $filepath): self
    {
        return $this->load(ConfigUtil::readPhpData($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadNeonFile(string $filepath): self
    {
        return $this->load(ConfigUtil::readNeonData($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadJsonFile(string $filepath): self
    {
        return $this->load(ConfigUtil::readJsonData($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadJson5File(string $filepath): self
    {
        return $this->load(ConfigUtil::readJson5Data($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadYamlFile(string $filepath): self
    {
        return $this->load(ConfigUtil::readYamlData($filepath));
    }

    /**
     * @param string $filepath
     *
     * @return $this
     */
    public function loadTomlFile(string $filepath): self
    {
        return $this->load(ConfigUtil::parseTomlFile($filepath));
    }

    /**
     * Export config data to file
     *
     * @param string $filepath
     * @param string $format If is empty, will parse from filepath.
     *
     * @return void
     */
    public function exportTo(string $filepath, string $format = ''): void
    {
        $format = $format ?: File::getExtension($filepath, true);
        $encFlag = $this->encodeFlags[$format] ?? 0;

        try {
            $string = ConfigUtil::encodeToString($this->data, $format, $encFlag);
        } catch (JsonException $e) {
            throw new RuntimeException('export data error: ' . $e->getMessage(), $e->getCode(), $e);
        }

        File::mkdirSave($filepath, $string);
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * @param string $format
     * @param int $encodeFlag
     */
    public function setEncodeFlag(string $format, int $encodeFlag): void
    {
        ConfigUtil::assertFormat($format);
        $this->encodeFlags[$format] = $encodeFlag;
    }

    /**
     * @param array $encodeFlags
     */
    public function setEncodeFlags(array $encodeFlags): void
    {
        $this->encodeFlags = $encodeFlags;
    }
}
