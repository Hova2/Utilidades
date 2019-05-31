<?php
 /*Este autoloader solamente funciona para las clases que esten ubicadas y creadas en un archivo, todas en un mismo directorio.
 El nombre de la clase y el nombre del archivo deben ser el mismo, con la diferencia de que el nombre del archivo 
 No hace falta que tengan namespaces*/
 
 spl_autoload_register(function ($class) {

    //Arreglo de directorios donde se encuentran las clases
    $arregloDirectorios = array(__DIR__ . '/Modelo/', __DIR__ . '/Vista/', __DIR__ . '/Controlador/');
    $existeArchivo = false;
    $archivo;
    
    //Recorre el arreglo de directorios buscando el archivo.
    foreach ($arregloDirectorios as $valor) {
        //Reconstruye el path donde se encuentra el archivo php que corresponde a la clase que se le pasa a la funcion.
        $archivo = $valor . $class . '.php';

        //Si el archivo existe cambia a true la variable existeArchivo;
        if (file_exists($archivo)) {
            $existeArchivo = true;
            break;
        }    
    }
    
    //Si la variable existeArchivo es true lo incluye. Si no retorna un null.
    if ($existeArchivo) {
        include_once $archivo;
    }else{
        return;
    }
});
?>