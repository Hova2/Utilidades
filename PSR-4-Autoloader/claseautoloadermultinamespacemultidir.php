<?php
namespace Example;

/**
 * An example of a general-purpose implementation that includes the optional
 * functionality of allowing multiple base directories for a single namespace
 * prefix.
 *
 * Given a foo-bar package of classes in the file system at the following
 * paths ...
 *
 *     /path/to/packages/foo-bar/
 *         src/
 *             Baz.php             # Foo\Bar\Baz
 *             Qux/
 *                 Quux.php        # Foo\Bar\Qux\Quux
 *         tests/
 *             BazTest.php         # Foo\Bar\BazTest
 *             Qux/
 *                 QuuxTest.php    # Foo\Bar\Qux\QuuxTest
 *
 * ... add the path to the class files for the \Foo\Bar\ namespace prefix
 * as follows:
 *
 *      <?php
 *      // instantiate the loader
 *      $loader = new \Example\Psr4AutoloaderClass;
 *
 *      // register the autoloader
 *      $loader->register();
 *
 *      // register the base directories for the namespace prefix
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/src');
 *      $loader->addNamespace('Foo\Bar', '/path/to/packages/foo-bar/tests');
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\Quux class from /path/to/packages/foo-bar/src/Qux/Quux.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\Quux;
 *
 * The following line would cause the autoloader to attempt to load the
 * \Foo\Bar\Qux\QuuxTest class from /path/to/packages/foo-bar/tests/Qux/QuuxTest.php:
 *
 *      <?php
 *      new \Foo\Bar\Qux\QuuxTest;
 */
class Psr4AutoloaderClass
{
    /**
     * Un array asociativo que tiene como claves prefijos de los namespaces
     * y como valore de la clave un arreglo de directorios asociados a ese prefijo
     * 
     * @var array
     */
    protected $prefixes = array();

    /**
     * Regista el loader con el SPL autoloader stack.
     *
     * @return void
     */
    public function register()
    {
        spl_autoload_register(array($this, 'loadClass'));
    }

    /**
     * Agrega un directorio a un prefijo de namespace
     *
     * @param string $prefix El prefijo del namespace.
     * @param string $base_dir El directorio donde se guardan los archivos 
     * de las clases para ese namespace.
     * @param bool $prepend Si es true, se agrega en la primera posiscion del arreglo
     * en vez de al final. Esto hace que se busque primero y no al final
     *
     * @return void
     */
    public function addNamespace($prefix, $base_dir, $prepend = false)
    {
        // Normaliza el prefijo del namespace.
        $prefix = trim($prefix, '\\') . '\\';

        // Normaliza el directorio base agregando la / al final.
        $base_dir = rtrim($base_dir, DIRECTORY_SEPARATOR) . '/';

        // Si se esta agregando un prefijo que no existe, se crea el arreglo de directorios.
        if (isset($this->prefixes[$prefix]) === false) {
            $this->prefixes[$prefix] = array();
        }

        /* Agrega el directorio base al arreglo de directorios de ese prefijo. 
        Si prepend es true al principio, si no al final.*/
        if ($prepend) {
            array_unshift($this->prefixes[$prefix], $base_dir);
        } else {
            array_push($this->prefixes[$prefix], $base_dir);
        }
    }

    /**
     * Carga el archivo de la clase para el nombre de la clase dada
     *
     * @param string $class El nombre completo de la clase
     * @return mixto El archivo del mapper file, o boolean false si no encuentra el archivo.
     */
    public function loadClass($class)
    {
        // Prefijo del namespace.
        $prefix = $class;

        // Trabaja hacia atras en el prefijo, buscando el nombre de la clase y si existe en el directorio.
        while (false !== $pos = strrpos($prefix, '\\')) {

            // Se queda solamente con el nombre de la clase.
            $prefix = substr($class, 0, $pos + 1);

            // Resetea el nombre relativo de la clase.
            $relative_class = substr($class, $pos + 1);

            // Intenta cargar el maper file para el prefijo y el nombre relativo de la clase.
            $mapped_file = $this->loadMappedFile($prefix, $relative_class);
            if ($mapped_file) {
                return $mapped_file;
            }

            // Saca las \\ preparando el prefijo para la siguiente pasada de strrpos().
            $prefix = rtrim($prefix, '\\');
        }

        // No se encontro el archivo de mapper.
        return false;
    }

    /**
     * Carga el mapped file para el prefijo del namespace y el directorio relativo de la clase.
     *
     * @param string $prefix El prefijo del namespace.
     * @param string $relative_class El nombre relativo de la clase.
     * @return mixto Boolean false si no se pudo cargar el mapper file, o el nombre del archivo
     * del mapper file si se cargo correctamente.
     */
    protected function loadMappedFile($prefix, $relative_class)
    {
        // Pregunta si existe el arreglo de directorios del prefijo.
        if (isset($this->prefixes[$prefix]) === false) {
            return false;
        }

        // Busca el directorio base para el prefijo.
        foreach ($this->prefixes[$prefix] as $base_dir) {

            /* Concatena el directorio base con el nombre relativo de la clase
			   (cambiando las \\ con la barra / para las rutas en el filesystem ) y le agrega
			   el .php. */
            $file = $base_dir
                  . str_replace('\\', '/', $relative_class)
                  . '.php';

            // Si el archivo existe lo incluye.
            if ($this->requireFile($file)) {
                return $file;
            }
        }

        // No se encontro el archivo.
        return false;
    }

    /**
     * Si el archivo existe lo incluye.
     *
     * @param string $file El archivo a incluir..
     * @return bool True si existe, false si no.
     */
    protected function requireFile($file)
    {
        if (file_exists($file)) {
            require $file;
            return true;
        }
        return false;
    }
}
