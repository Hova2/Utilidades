<?php
/**
 * An example of a project-specific implementation.
 *
 * After registering this autoload function with SPL, the following line
 * would cause the function to attempt to load the \Foo\Bar\Baz\Qux class
 * from /path/to/project/src/Baz/Qux.php:
 *
 *      new \Foo\Bar\Baz\Qux;
 *
 * @param string $class The fully-qualified class name.
 * @return void
 */
spl_autoload_register(function ($class) {

    // Prefijo del namespace.
    $prefix = 'Foo\\Bar\\';

    // Directorio base del prefijo del namespace.
    $base_dir = __DIR__ . '/src/';

    // Busca si la clase tiene el prefijo correcto.
    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        // No lo tiene, entonces sigue buscando con el siguiente autoloader registrado.
        return;
    }

    // Busca el nombre relativo de la clase.
    $relative_class = substr($class, $len);

    /* Concatena el directorio base con el nombre relativo de la clase
       (cambiando las \\ con la barra / para las rutas en el filesystem ) y le agrega
       el .php. */
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    // Si el archivo existe lo incluye.
    if (file_exists($file)) {
        require $file;
    }
});
?>
