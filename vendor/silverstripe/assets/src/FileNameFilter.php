<?php

namespace SilverStripe\Assets;

use SilverStripe\Core\Config\Configurable;
use SilverStripe\Core\Injector\Injectable;
use SilverStripe\View\Parsers\Transliterator;

/**
 * Filter certain characters from file name, for nicer (more SEO-friendly) URLs
 * as well as better filesystem compatibility.
 *
 * Caution: Does not take care of full filename sanitization in regards to directory traversal etc.,
 * please use PHP's built-in basename() for this purpose.
 *
 * For file name filtering see {@link FileNameFilter}.
 *
 * The default sanitizer is quite conservative regarding non-ASCII characters,
 * in order to achieve maximum filesystem compatibility.
 * In case your filesystem supports a wider character set,
 * or is case sensitive, you might want to relax these rules
 * via overriding {@link FileNameFilter_DefaultFilter::$default_replacements}.
 *
 * To leave uploaded filenames as they are (being aware of filesystem restrictions),
 * add the following code to your YAML config:
 * <code>
 * FileNameFilter:
 *   default_use_transliterator: false
 *   default_replacements:
 * </code>
 *
 * See {@link URLSegmentFilter} for a more generic implementation.
 */
class FileNameFilter
{
    use Configurable;
    use Injectable;

    /**
     * @config
     * @var Boolean
     */
    private static $default_use_transliterator = true;

    /**
     * @config
     * @var array See {@link setReplacements()}.
     */
    private static $default_replacements = [
        '/\s/' => '-', // remove whitespace
        '/[^-_A-Za-z0-9+.]+/' => '', // remove non-ASCII chars, only allow alphanumeric plus dash, dot, and underscore
        '/_{2,}/' => '_', // remove duplicate underscores (since `__` is variant separator)
        '/-{2,}/' => '-', // remove duplicate dashes
        '/^[-_\.]+/' => '', // Remove all leading dots, dashes or underscores
    ];

    /**
     * @var array See {@link setReplacements()}
     */
    public $replacements = array();

    /**
     * Depending on the applied replacement rules, this method
     * might result in an empty string. In this case, {@link getDefaultName()}
     * will be used to return a randomly generated file name, while retaining its extension.
     *
     * @param string $name including extension (not path).
     * @return string A filtered filename
     */
    public function filter($name)
    {
        $ext = pathinfo($name, PATHINFO_EXTENSION);

        $transliterator = $this->getTransliterator();
        if ($transliterator) {
            $name = $transliterator->toASCII($name);
        }
        foreach ($this->getReplacements() as $regex => $replace) {
            $name = preg_replace($regex, $replace, $name);
        }

        // Safeguard against empty file names
        $nameWithoutExt = pathinfo($name, PATHINFO_FILENAME);
        if (empty($nameWithoutExt)) {
            $name = $this->getDefaultName();
            $name .= $ext ? '.' . $ext : '';
        }

        return $name;
    }

    /**
     * Take care not to add replacements which might invalidate the file structure,
     * e.g. removing dots will remove file extension information.
     *
     * @param array $replacements Map of find/replace used for preg_replace().
     */
    public function setReplacements($replacements)
    {
        $this->replacements = $replacements;
    }

    /**
     * @return array
     */
    public function getReplacements()
    {
        return $this->replacements ?: (array)$this->config()->get('default_replacements');
    }

    /**
     * Transliterator instance, or false to disable.
     * If null will use default.
     *
     * @var Transliterator|false
     */
    protected $transliterator;

    /**
     * @return Transliterator
     */
    public function getTransliterator()
    {
        if ($this->transliterator === null && $this->config()->default_use_transliterator) {
            $this->transliterator = Transliterator::create();
        }
        return $this->transliterator;
    }

    /**
     * @param Transliterator|false $transliterator
     */
    public function setTransliterator($transliterator)
    {
        $this->transliterator = $transliterator;
    }

    /**
     * @return string File name without extension
     */
    public function getDefaultName()
    {
        return (string)uniqid();
    }
}
