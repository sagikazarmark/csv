<?php

/*
 * This file is part of the Indigo Csv package.
 *
 * (c) Indigo Development Team
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Indigo\Csv;

use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\OptionsResolver\Options;
use InvalidArgumentException;
use UnexpectedValueException;
use BadMethodCallException;

/**
 * Csv class
 *
 * Csv object which operates on a CsvFileObject
 *
 * @author Márk Sági-Kazár <mark.sagikazar@gmail.com>
 */
class Csv
{
    /**
     * File URI
     *
     * @var string
     */
    protected $file;

    /**
     * Csv file resource
     *
     * @var resource
     */
    protected $resource;

    /**
     * Options
     *
     * @var Options
     */
    protected $options = array();

    /**
     * File header
     *
     * @var array
     */
    protected $header = array();

    /**
     * Column count checked for row consistency
     *
     * @var integer
     */
    protected $columnCount;

    /**
     * Default options
     *
     * @var array
     */
    protected static $defaultOptions = array(
        'delimiter' => ',',
        'enclosure' => '"',
        'newline' => "\n",
        'escape' => '\\',
        'encoding' => 'UTF-8',
        'strict' => true,
    );

    /**
     * File open mode
     *
     * @var string
     */
    protected static $fileMode = 'r+';

    public function __construct($file, array $options = array())
    {
        if (is_string($file)) {
            $file = new CsvFileObject($file, static::$fileMode);
        }

        $this->setFile($file);
        $this->setOptions($options);
    }

    protected function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(static::$defaultOptions);

        $types = array_fill_keys(array_keys(static::$defaultOptions), 'string');
        $types['strict'] = 'bool';
        $resolver->setAllowedTypes($types);
    }

    public function setOptions(array $options)
    {
        static $resolver;

        if (is_null($resolver)) {
            $resolver = new OptionsResolver();
            $this->setDefaultOptions($resolver);
        } else {
            $resolver->setDefaults($this->options);
        }

        $this->options = $resolver->resolve($options);

        return $this;
    }

    public function getHeader()
    {
        return $this->header;
    }

    abstract public function setHeader($header);

    public function getFile()
    {
        return $this->file;
    }

    public function setFile(CsvFileObject $file)
    {
        // Not sure where the pointer is
        $file->rewind();

        $this->file = $file;

        return $this;
    }

    public function checkRowConsistency($line)
    {
        if ($this->options['strict'] !== true) {
            return true;
        }

        if (is_null($this->columnCount)) {
            if (!empty($this->header) and is_array($this->header)) {
                $this->columnCount = count($this->header);
            } else {
                $this->columnCount = count($line);
            }
        }

        return count($line) === $this->columnCount;
    }
}
