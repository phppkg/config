<?php
/**
 * Created by PhpStorm.
 * User: inhere
 * Date: 2016/2/24
 * Time: 15:04
 */

namespace PhpPkg\Config;

use ArrayAccess;
use Countable;
use InvalidArgumentException;
use IteratorAggregate;
use RangeException;
use Toolkit\FsUtil\FileSystem;
use Toolkit\Stdlib\Obj;
use Toolkit\Stdlib\Str;
use Traversable;
use function count;
use function explode;
use function in_array;
use function is_file;
use function preg_match;
use function str_replace;
use function strpos;
use function trim;
use function ucfirst;

/**
 * Class Language
 *
 * @package Toolkit\Utils
 */
class Language implements ArrayAccess, Countable, IteratorAggregate
{
    /**
     * current use language
     *
     * @var string
     */
    private string $lang = 'en';

    /**
     * fallback language
     *
     * @var string
     */
    private string $fallbackLang = 'en';

    /**
     * current use language
     *
     * @var string[]
     */
    private array $allowed = [];

    /**
     * @var Collection
     */
    private Collection $data;

    /**
     * @var Collection|null
     */
    private ?Collection $fallbackData;

    /**
     * The base path language directory.
     *
     * @var string
     */
    private string $basePath = '';

    /**
     * default file name.
     *
     * @var string
     */
    private string $defaultFile = 'default';

    /**
     * the language file type. more see Collection::FORMAT_*
     *
     * @var string
     */
    private string $format = ConfigBox::FORMAT_PHP;

    /**
     * file separator char. when want to get translation form other file.
     * e.g:
     *  $language->tl('app.createPage');
     *
     * @var string
     */
    private string $separator = '.';

    /**
     * e.g.
     * [
     *    'user'  => '{basePath}/zh-CN/user.yml'
     *    'admin' => '{basePath}/zh-CN/admin.yml'
     * ]
     *
     * @var array
     */
    private array $langFiles = [];

    /**
     * loaded language file list.
     *
     * @var array
     */
    private array $loadedFiles = [];

    /**
     * whether ignore not exists lang file when addLangFile()
     *
     * @var bool
     */
    private bool $ignoreError = false;

    public const DEFAULT_FILE_KEY = '__default';

    /**
     * @param array $settings
     *
     * @throws InvalidArgumentException
     * @throws RangeException
     */
    public function __construct(array $settings = [])
    {
        Obj::init($this, $settings);

        $this->data = new Collection();

        if ($this->defaultFile) {
            $file = $this->buildLangFilePath($this->defaultFile . '.' . $this->format);

            if (is_file($file)) {
                $this->data->load(ConfigUtil::readFromFile($file, $this->format));
            }
        }
    }

    /**
     * @see translate()
     */
    public function t(string $key, array $args = [], $lang = ''): string
    {
        return $this->translate($key, $args, $lang);
    }

    /**
     * @see translate()
     */
    public function tl(string $key, array $args = [], $lang = ''): string
    {
        return $this->translate($key, $args, $lang);
    }

    /**
     * @see translate()
     */
    public function trans(string $key, array $args = [], $lang = ''): string
    {
        return $this->translate($key, $args, $lang);
    }

    /**
     * how to use language translate ? please see '/doc/language.md'
     *
     * @param string $key
     * @param array $args
     * @param string $lang
     *
     * @return string
     */
    public function translate(string $key, array $args = [], string $lang = ''): string
    {
        $key = trim($key, ' ' . $this->separator);
        if ($key === '') { // '0' is equals False
            throw new InvalidArgumentException('Cannot translate the empty key');
        }

        [$lang, $key] = $this->parseKey($key, $lang);

        // translate form current language. if not found, translate form fallback language.
        if (($value = $this->findTranslationText($key)) === null) {
            $value = $this->transByFallbackLang($key);

            // no translate text
            if ($value === '') {
                if (!empty($args['__default'])) {
                    return $args['__default'];
                }

                return ucfirst(Str::toSnake(str_replace(['-', '_'], ' ', $key), ' '));
            }
        }

        return $args ? sprintf($value, ...$args) : $value;
    }

    /*********************************************************************************
     * handle current language translate
     *********************************************************************************/

    /**
     * @param string $key
     * @param mixed $value
     *
     * @return self
     */
    public function set(string $key, $value): self
    {
        $this->data->set($key, $value);
        return $this;
    }

    /**
     * @param string $key
     *
     * @return mixed
     */
    public function get(string $key): mixed
    {
        return $this->data->get($key);
    }

    /**
     * @param string $key
     *
     * @return bool
     */
    public function has(string $key): bool
    {
        return $this->data->has($key);
    }

    /**
     * @param string $key
     *
     * @return string|mixed
     */
    protected function findTranslationText(string $key): string
    {
        if ($val = $this->data->getString($key)) {
            return $val;
        }

        if (strpos($key, $this->separator)) {
            [$fileKey,] = explode($this->separator, $key);
        } else {
            $fileKey = $key;
        }

        // at first, load language data to collection.
        if ($file = $this->getLangFile($fileKey)) {
            $this->loadedFiles[] = $file;
            $this->data->set($fileKey, ConfigUtil::readFromFile($file, $this->format));

            return $this->data->getString($key);
        }

        return '';
    }

    /*********************************************************************************
     * fallback language translate handle
     *********************************************************************************/

    /**
     * @param string $key
     *
     * @return string
     */
    protected function transByFallbackLang(string $key): string
    {
        if ($this->lang === $this->fallbackLang || !$this->fallbackLang) {
            return '';
        }

        // init fallbackData
        if (!$this->fallbackData) {
            $this->fallbackData = new Collection();

            if ($this->defaultFile) {
                $file = $this->buildLangFilePath($this->defaultFile . '.' . $this->format, $this->fallbackLang);

                if (is_file($file)) {
                    $this->fallbackData->load(ConfigUtil::readFromFile($file, $this->format));
                }
            }
        }

        if ($val = $this->fallbackData->getString($key)) {
            return $val;
        }

        if (strpos($key, $this->separator)) {
            [$fileKey,] = explode($this->separator, $key);
        } else {
            $fileKey = $key;
        }

        // the first times fetch, instantiate lang data
        if ($file = $this->getLangFile($fileKey)) {
            $file = str_replace("/{$this->lang}/", "/{$this->fallbackLang}/", $file);

            if (is_file($file)) {
                $this->loadedFiles[] = $file;
                $this->fallbackData->set($fileKey, ConfigUtil::readFromFile($file, $this->format));

                return $this->fallbackData->getString($key);
            }
        }

        return '';
    }

    /*********************************************************************************
     * helper
     *********************************************************************************/

    /**
     * @param string $key
     * @param string $lang
     *
     * @return array
     */
    private function parseKey(string $key, string $lang = ''): array
    {
        if ($lang) {
            return [$lang, $key];
        }

        $langSep = ':';
        if (strpos($key, $langSep)) {
            $info = explode($langSep, $key, 2);
            if ($this->isLang($info[0])) {
                return $info;
            }
        }

        return [$this->lang, $key];
    }

    /**
     * @param string $filename
     * @param string $lang
     *
     * @return string
     */
    protected function buildLangFilePath(string $filename, string $lang = ''): string
    {
        $path = ($lang ?: $this->lang) . DIRECTORY_SEPARATOR . trim($filename);

        return $this->basePath . DIRECTORY_SEPARATOR . $path;
    }

    /*********************************************************************************
     * language file handle
     *********************************************************************************/

    /**
     * @param string $fileKey
     *
     * @return string|null
     */
    public function getLangFile(string $fileKey): ?string
    {
        return $this->langFiles[$fileKey] ?? null;
    }

    /**
     * @param string $fileKey
     *
     * @return bool
     */
    public function hasLangFile(string $fileKey): bool
    {
        return isset($this->langFiles[$fileKey]);
    }

    /**
     * @param string $file
     * @param string $fileKey
     *
     * @throws InvalidArgumentException
     */
    public function addLangFile(string $file, string $fileKey = null)
    {
        if (!FileSystem::isAbsPath($file)) {
            $file = $this->buildLangFilePath($file);
        }

        if (!is_file($file)) {
            if ($this->ignoreError) {
                return;
            }

            throw new InvalidArgumentException("The language file don't exists. FILE: $file");
        }

        $fileKey = $fileKey ?: basename($file, '.' . $this->format);

        if (!preg_match('/^[a-z][\w-]+$/i', $fileKey)) {
            throw new InvalidArgumentException("language file key [$fileKey] naming format error!!");
        }

        if ($this->hasLangFile($fileKey)) {
            if ($this->ignoreError) {
                return;
            }

            throw new InvalidArgumentException("language file key [$fileKey] have been exists, don't allow override!!");
        }

        $this->langFiles[$fileKey] = $file;
    }

    /**
     * @param string $fileKey
     *
     * @return bool
     */
    public function hasLangFileData(string $fileKey): bool
    {
        return isset($this->data[$fileKey]);
    }

    /*********************************************************************************
     * getter/setter
     *********************************************************************************/

    /**
     * @param string $lang
     *
     * @return bool
     */
    public function hasLang(string $lang): bool
    {
        return $this->isLang($lang);
    }

    /**
     * @param string $lang
     *
     * @return bool
     */
    public function isLang(string $lang): bool
    {
        return $lang && in_array($lang, $this->allowed, true);
    }

    /**
     * Allow quick access default file translate by `$lang->key`,
     * is equals to `$lang->tl('key')`.
     *
     * @param string $name
     *
     * @return mixed|string
     * @throws InvalidArgumentException
     */
    public function __get($name)
    {
        return $this->translate($name);
    }

    public function __isset($name)
    {
    }

    public function __set($name, $value)
    {
    }

    /**
     * Allow quick access default file translate by `$lang->key()`,
     * is equals to `$lang->tl('key')`.
     *
     * @param string $name
     * @param array $args
     *
     * @return mixed
     */
    public function __call(string $name, array $args): mixed
    {
        return $this->translate($name);
    }

    /**
     * @return string
     */
    public function getLang(): string
    {
        return $this->lang;
    }

    /**
     * @param string $lang
     */
    public function setLang(string $lang): void
    {
        $this->lang = trim($lang);
    }

    /**
     * @return string[]
     */
    public function getAllowed(): array
    {
        return $this->allowed;
    }

    /**
     * @param string[] $allowed
     */
    public function setAllowed(array $allowed): void
    {
        $this->allowed = $allowed;
    }

    /**
     * @return string
     */
    public function getBasePath(): string
    {
        return $this->basePath;
    }

    /**
     * @param string $path
     *
     * @throws InvalidArgumentException
     */
    public function setBasePath(string $path): void
    {
        if ($path && is_dir($path)) {
            $this->basePath = $path;
        } else {
            throw new InvalidArgumentException("The language files path: $path is not exists.");
        }
    }

    /**
     * @return Collection
     */
    public function getData(): Collection
    {
        return $this->data;
    }

    /**
     * @return array
     */
    public function getLangFiles(): array
    {
        return $this->langFiles;
    }

    /**
     * @param array $langFiles
     *
     * @throws InvalidArgumentException
     */
    public function setLangFiles(array $langFiles): void
    {
        foreach ($langFiles as $fileKey => $file) {
            $this->addLangFile($file, is_numeric($fileKey) ? '' : $fileKey);
        }
    }

    /**
     * @param bool $full
     *
     * @return string
     */
    public function getDefaultFile(bool $full = false): string
    {
        return $full ? $this->getLangFile(self::DEFAULT_FILE_KEY) : $this->defaultFile;
    }

    /**
     * @return string
     */
    public function getFallbackLang(): string
    {
        return $this->fallbackLang;
    }

    /**
     * @param string $fallbackLang
     */
    public function setFallbackLang(string $fallbackLang): void
    {
        $this->fallbackLang = $fallbackLang;
    }

    /**
     * @return Collection
     */
    public function getFallbackData(): Collection
    {
        return $this->fallbackData;
    }

    /**
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * @param string $format
     */
    public function setFormat(string $format): void
    {
        if (in_array($format, ConfigUtil::getFormats(), true)) {
            $this->format = $format;
        }
    }

    /**
     * @return string
     */
    public function getSeparator(): string
    {
        return $this->separator;
    }

    /**
     * @param string $separator
     */
    public function setSeparator(string $separator): void
    {
        $this->separator = $separator;
    }

    /**
     * @return array
     */
    public function getLoadedFiles(): array
    {
        return $this->loadedFiles;
    }

    /**
     * @return bool
     */
    public function isIgnoreError(): bool
    {
        return $this->ignoreError;
    }

    /**
     * @param bool $ignoreError
     */
    public function setIgnoreError($ignoreError): void
    {
        $this->ignoreError = (bool)$ignoreError;
    }

    /*********************************************************************************
     * interface implementing
     *********************************************************************************/

    /**
     * Retrieve an external iterator
     *
     * @link  http://php.net/manual/en/iteratoraggregate.getiterator.php
     * @return Traversable An instance of an object implementing <b>Iterator</b> or
     * <b>Traversable</b>
     * @since 5.0.0
     */
    public function getIterator(): Traversable
    {
        return $this->data->getIterator();
    }

    /**
     * Whether a offset exists
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetexists.php
     *
     * @param mixed $offset <p>
     *                      An offset to check for.
     *                      </p>
     *
     * @return boolean true on success or false on failure.
     * </p>
     * <p>
     * The return value will be casted to boolean if non-boolean was returned.
     * @since 5.0.0
     */
    public function offsetExists($offset): bool
    {
        return $this->has($offset);
    }

    /**
     * Offset to retrieve
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetget.php
     *
     * @param mixed $offset <p>
     *                      The offset to retrieve.
     *                      </p>
     *
     * @return mixed Can return all value types.
     * @since 5.0.0
     * @throws InvalidArgumentException
     */
    public function offsetGet($offset)
    {
        return $this->translate($offset);
    }

    /**
     * Offset to set
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetset.php
     *
     * @param mixed $offset <p>
     *                      The offset to assign the value to.
     *                      </p>
     * @param mixed $value <p>
     *                      The value to set.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetSet($offset, $value): void
    {
        $this->set($offset, $value);
    }

    /**
     * Offset to unset
     *
     * @link  http://php.net/manual/en/arrayaccess.offsetunset.php
     *
     * @param mixed $offset <p>
     *                      The offset to unset.
     *                      </p>
     *
     * @return void
     * @since 5.0.0
     */
    public function offsetUnset($offset): void
    {
        unset($this->data[$offset]);
    }

    /**
     * Count elements of an object
     *
     * @link  http://php.net/manual/en/countable.count.php
     * @return int The custom count as an integer.
     * </p>
     * <p>
     * The return value is cast to an integer.
     * @since 5.1.0
     */
    public function count(): int
    {
        return count($this->data);
    }
}
