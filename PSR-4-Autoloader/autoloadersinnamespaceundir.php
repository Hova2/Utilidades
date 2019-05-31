<?php
 /*Este autoloader solamente funciona para las clases que esten ubicadas y creadas en un archivo, todas en un mismo directorio.
 El nombre de la clase y el nombre del archivo deben ser el mismo, con la diferencia de que el nombre del archivo 
 No hace falta que tengan namespaces*/
 
 spl_autoload_register(function ($class) {

    // Directorio base donde se van a guardar las clases.
    $base_dir = __DIR__ . '/Class/';
        
    //Reconstruye el path donde se encuentra el archivo php que corresponde a la clase que se le pasa a la funcion
    $file = $base_dir . $class . '.php';

    //Si el archivo existe lo incluye. Si no retorna un null.
    if (file_exists($file)) {
        include_once $file;
    }else{
        return;
    }
});
?>