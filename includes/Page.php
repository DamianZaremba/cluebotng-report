<?PHP

namespace ReportInterface;

class Page
{
    private static $pages = null;

    private function __construct()
    {
        //Nothing.
    }

    public static function sortPages($p1, $p2)
    {
        if ($p1['sort'] == $p2['sort']) {
            return 0;
        }
        return $p1['sort'] < $p2['sort'] ? -1 : 1;
    }

    public static function registerPage($name, $className, $sort = 0, $visible = true, $requireAdmin = false)
    {
        if (self::$pages === null) {
            self::$pages = array();
        }

        if (!$requireAdmin or (isset($_SESSION['sadmin']) and $_SESSION['sadmin'] === true)) {
            self::$pages[$name] = array('visible' => $visible, 'class' => $className, 'sort' => $sort);
        }
    }

    public static function findByName($name)
    {
        if (isset(self::$pages[$name])) {
            $className = self::$pages[$name]['class'];
        } else {
            $className = 'Page';
        }

        $page = new $className();
        return $page;
    }

    public function writeHeader()
    {
        echo 'Not Found';
    }

    public function writeNavigation()
    {
        echo '<ul>' . "\n";
        uasort(self::$pages, array('Page', 'sortPages'));
        foreach (self::$pages as $name => $data) {
            if ($data['visible']) {
                echo "\t" . '<li><a href="?page=' . urlencode($name) . '">' . htmlentities($name) . '</a></li>' . "\n";
            }
        }
        echo '</ul>' . "\n";
    }

    public function writeContent()
    {
        echo '404 - File not found.';
    }
}
