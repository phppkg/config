<?php declare(strict_types=1);

namespace PhpPkg\Config;

use InvalidArgumentException;
use Toolkit\FsUtil\File;

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
     * @param string $filepath
     * @param string $format
     *
     * @return static
     */
    public static function newFromFile(string $filepath, string $format = ''): self
    {
        return (new self())->loadFromFile($filepath, $format);
    }

    /**
     * @param string $filepath
     * @param string $format
     *
     * @return $this
     */
    public function loadFromFile(string $filepath, string $format = ''): self
    {
        $format = $format ?: File::getExtension($filepath, true);
        switch ($format) {
            case self::FORMAT_INI:
                $this->loadIniFile($filepath);
                break;
            case self::FORMAT_PHP:
                $this->loadPhpFile($filepath);
                break;
            case self::FORMAT_NEON:
                $this->loadNeonFile($filepath);
                break;
            case self::FORMAT_JSON:
                $this->loadJsonFile($filepath);
                break;
            case self::FORMAT_JSON5:
                $this->loadJson5File($filepath);
                break;
            case self::FORMAT_YML;
            case self::FORMAT_YAML :
                $this->loadYamlFile($filepath);
                break;
            default:
                throw new InvalidArgumentException('unknown file format: ' . $format);
        }

        return $this;
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
}
