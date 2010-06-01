<?php
/**
 * @author Marcelo Guilherme Jacobus Jr (marcelo.jacobus@gmail.com)
 *
 * recebe o nome de uma classe no seguinte formato:
 * Gad_Componente_Afude
 *
 * e faz o require do seginte arquivo
 *
 * Mura/Componente/Afude.php
 *
 * @param string $class
 */
function __autoload($class)
{
    $file = str_replace('_','/',$class) . '.php';

    if (file_exists($file)) {
        require_once $file;
        return true;
    }
    return false;
}