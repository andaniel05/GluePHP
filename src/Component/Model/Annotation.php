<?php

namespace Andaniel05\GluePHP\Component\Model;

/**
 * @author Andy Daniel Navarro Taño <andaniel05@gmail.com>
 */
class Annotation implements AnnotationInterface
{
    protected $name;
    protected $attributes;

    public function __construct(string $name, array $attributes)
    {
        $this->name = $name;
        $this->attributes = $attributes;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getAttributes(): array
    {
        return $this->attributes;
    }

    public function getAttribute(string $attribute)
    {
        return $this->attributes[$attribute] ?? null;
    }

    public static function parseString(string $subject): array
    {
        $result = [];

        $annPattern = '/@([a-zA-Z]\w*)( *\(.*\))?/';
        $annResults = [];
        preg_match_all($annPattern, $subject, $annResults, PREG_SET_ORDER);
        foreach ($annResults as $annResult) {
            $name = $annResult[1];
            $atts = [];

            if (isset($annResult[2])) {
                $attsPattern = '/([a-zA-Z]\w*)="([\w\.\,]*)"/';
                $attsResults = [];
                preg_match_all($attsPattern, $annResult[2], $attsResults, PREG_SET_ORDER);
                foreach ($attsResults as $attsResult) {
                    $atts[$attsResult[1]] = $attsResult[2];
                }
            }

            $result[] = new Annotation($name, $atts);
        }

        return $result;
    }
}
