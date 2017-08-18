<?php
namespace Pentagonal\Phalcon\Application\Globals\Library;

use Phalcon\Config;

/**
 * Class Theme
 * @package Pentagonal\Phalcon\Application\Globals\Library
 */
class Theme
{
    const THEME_VALID = true;
    const THEME_INVALID = false;

    const INVALID_REASON_INFO_INVALID     = 2;
    const INVALID_REASON_INFO_NOT_EXISTS  = 3;
    const INVALID_REASON_INCOMPLETE       = 4;
    const FILE_INFO = 'theme.ini';
    const BASE_PATH_TEMPLATE = 'templates';
    const BASE_PATH_PARTIAL  = 'partials';

    /**
     * @var string
     */
    protected $themeDir;

    /**
     * @var string
     */
    protected $activeTheme;

    /**
     * @var array
     */
    protected $listFiles = [];

    /**
     * @var Config|Config[]
     */
    protected $themeLists;

    /**
     * @var string[]
     */
    protected $validThemes = [];

    /**
     * @var array
     */
    protected $mustBeExists = [
        self::FILE_INFO => self::INVALID_REASON_INFO_NOT_EXISTS,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . '401.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . '404.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . '500.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . 'home.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . 'post.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . 'page.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_TEMPLATE . DIRECTORY_SEPARATOR . 'page.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_PARTIAL . DIRECTORY_SEPARATOR  . 'header.phtml' => self::INVALID_REASON_INCOMPLETE,
        self::BASE_PATH_PARTIAL . DIRECTORY_SEPARATOR . 'footer.phtml' => self::INVALID_REASON_INCOMPLETE,
    ];

    const THEME_BASE_NAME  = 'theme_base';
    const THEME_PATH_NAME  = 'theme_path';
    const THEME_VALIDATION_NAME = 'theme_validation';
    const THEME_INFO_NAME  = 'theme_info';
    const THEME_CORRUPT_NAME  = 'theme_corrupt';

    /**
     * Theme constructor.
     *
     * @param string $themeDir
     */
    public function __construct(string $themeDir)
    {
        $this->themeDir = realpath($themeDir) ?: $themeDir;
        $this->themeDir = preg_replace('/(\\\|\/)+/', DIRECTORY_SEPARATOR, $this->themeDir);
        $this->themeDir = rtrim($this->themeDir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
        if (!is_dir($themeDir)) {
            throw new \RuntimeException(
                sprintf(
                    'Invalid theme directory, directory %s does not exists.'
                ),
                E_WARNING
            );
        }

        $this->init();
    }

    /**
     * @return string
     */
    public function getThemesDir(): string
    {
        return $this->themeDir;
    }

    /**
     * @return null|string
     */
    public function getActiveThemeDir()
    {
        return rtrim(
            $this->getThemesDir() . $this->getActiveThemeBasePath(),
            DIRECTORY_SEPARATOR
        ) . DIRECTORY_SEPARATOR;
    }

    /**
     * @return null|string
     */
    public function getActiveThemeBasePath()
    {
        return $this->activeTheme;
    }

    /**
     * @return null|Config
     */
    public function getActiveTheme()
    {
        return $this->activeTheme
            ? $this->themeLists[$this->activeTheme]
            : null;
    }

    /**
     * @param string $name
     *
     * @return Theme
     */
    public function setActiveTheme(string $name) : Theme
    {
        if (!$this->isThemeIsValid($name)) {
            $exist = $this->isThemeExists($name);
            throw new \RuntimeException(
                sprintf(
                    $exist ? 'Theme %s is not valid' : 'Theme %s is not exists.',
                    $name
                )
            );
        }

        $this->activeTheme = $name;
        return $this;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isThemeExists(string $name) : bool
    {
        return isset($this->themeLists[$name]);
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function isThemeIsValid(string $name) : bool
    {
        return isset($this->validThemes[$name]);
    }

    /**
     * @return Config
     */
    public function getThemeList() : Config
    {
        $theme = clone $this->themeLists;
        return $theme;
    }

    protected function init()
    {
        $list = [];
        foreach (new \DirectoryIterator($this->getThemesDir()) as $iterator) {
            $name = $iterator->getFilename();
            if (in_array($name, ['.', '..'])) {
                continue;
            }
            if (! $iterator->isDir()) {
                if ($iterator->isFile()) {
                    $this->listFiles[] = $name;
                }
                continue;
            }

            $path = rtrim($iterator->getRealPath(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;
            $list[$name] = [
                self::THEME_VALIDATION_NAME => self::THEME_VALID,
                self::THEME_BASE_NAME => $name,
                self::THEME_PATH_NAME => $path,
                self::THEME_INFO_NAME => [],
                self::THEME_CORRUPT_NAME => []
            ];

            foreach ($this->mustBeExists as $key => $reason) {
                if (!file_exists($path .  DIRECTORY_SEPARATOR . $key)) {
                    $list[$name][self::THEME_VALIDATION_NAME] = $reason;
                    $list[$name][self::THEME_CORRUPT_NAME][] = $key;
                    continue;
                }

                if ($key == self::FILE_INFO) {
                    try {
                        $info = new Config\Adapter\Ini($path .  DIRECTORY_SEPARATOR . $key);
                    } catch (\Exception $e) {
                        $list[$name][self::THEME_VALIDATION_NAME] = self::INVALID_REASON_INFO_INVALID;
                        $list[$name][self::THEME_CORRUPT_NAME][] = $key;
                        continue;
                    }
                    $list[$name][self::THEME_INFO_NAME] = $info->toArray();
                }
            }

            if ($list[$name][self::THEME_VALIDATION_NAME] === self::THEME_VALID) {
                $this->validThemes[$name] = true;
                if (!isset($this->activeTheme)) {
                    $this->activeTheme = $name;
                }
            }
        }

        $this->themeLists = new Config($list);
    }
}
