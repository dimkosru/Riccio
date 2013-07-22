<?php

/**
 * PHP Template system
 * 
 * @author dimko
 */
class TemplateSystem {

    private static $mTemplate;

    /**
     * Для замены
     * @var array { "{$key}","value" } 
     */
    private static $mKeyValues;

    /**
     * Установка каталога шаблонов
     * @param mixed $template Каталог с шаблонами
     */
    public static function setTemplate($template = ".") {
        self::$mTemplate = $template;
    }

    /**
     * Назначение 
     * @param mixed $key Переменная 
     * @param mixed $value Значение
     */
    public static function assign($key, $value = "") {
        self::$mKeyValues["{\$$key}"] = $value;
    }

    public static function addList($key, $name, $value) {
        self::$mKeyValues["{\$$key}"] = "<br>" . $name . "<ul>";
        foreach ($value as $li)
            self::$mKeyValues["{\$$key}"] .="<li>" . $li . "</li>";
        self::$mKeyValues["{\$$key}"] .="</ul>";
    }

    public static function clearAssign($key) {
        unset(self::$mKeyValues["{\$$key}"]);
    }

    /**
     * Отображение шаблона
     * @param mixed $templateFile файл шаблона 
     */
    public static function showPage($templateFile) {
        foreach (self::$mKeyValues as $key => &$value) {
            $event = str_replace(array("{", "$", "}"), '', $key);
            EventSystem::fireEvent($event, array(&$value));
        }
        $templateFile = self::$mTemplate . "/" . $templateFile;
        $fPage = fopen($templateFile, "r");
        $tPage = fread($fPage, filesize($templateFile));
        echo str_replace(array_keys(self::$mKeyValues), self::$mKeyValues, $tPage);
        //echo strtr($tPage, $this->mValues);
    }

    /**
     * Отображение шаблона с вызывом события при встрече выражения
     * @param mixed $templateFile файл шаблона 
     */
    public static function showPage_new($templateFile) {
        $templateFile = self::$mTemplate . "/" . $templateFile;
        $fPage = fopen($templateFile, "r");
        $tPage = fread($fPage, filesize($templateFile));
        $found = array();
        preg_match_all('/\{\$(\w+)\}/', $tPage, $found, PREG_PATTERN_ORDER);
        $found[0] = array_values(array_unique($found[0]));
        $found[1] = array_values(array_unique($found[1]));
        foreach ($found[0] as $key) {
            if (isset(self::$mKeyValues{$key}))
                $found[2][] = self::$mKeyValues{$key};
            else
                $found[2][] = "";
        }
        foreach ($found[1] as $i => $event) {
            EventSystem::fireEvent($event, array(&$found[2][$i]));
        }
        echo str_replace($found[0], $found[2], $tPage);
    }

}

?>
