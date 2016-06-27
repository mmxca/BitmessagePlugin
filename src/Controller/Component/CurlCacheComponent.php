<?php
namespace BitmessagePlugin\Controller\Component;

use Cake\Controller\Component;
use Cake\Controller\ComponentRegistry;

/**
 * CurlCache component based on the wrapper class for curl which was developed and implemented
 * by Donsheng Cai and used by class.bitmessage.php by Convertor
 *
 * Original class can be found here: 
 * @website http://conver.github.io/class.bitmessage.php/
 *
 * @copyright  Dongsheng Cai {@see http://dongsheng.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License
 */
class CurlCacheComponent extends Component {

    /**
     * Default configuration.
     *
     * @var array
     */
    protected $_defaultConfig = [];

    /**
     *  *  * @var string */
    public $dir = '';

    /**
     *
     * @param string @module which module is using curl_cache
     *
     */
    function __construct() {
        $this->dir = '/tmp/';
        if (!file_exists($this->dir)) {
            mkdir($this->dir, 0700, true);
        }
        $this->ttl = 1200;
    }

    /**
     * Get cached value
     *
     * @param mixed $param
     * @return bool|string
     */
    public function get($param) {
        $this->cleanup($this->ttl);
        $filename = 'u_' . md5(serialize($param));
        if (file_exists($this->dir . $filename)) {
            $lasttime = filemtime($this->dir . $filename);
            if (time() - $lasttime > $this->ttl) {
                return false;
            } else {
                $fp = fopen($this->dir . $filename, 'r');
                $size = filesize($this->dir . $filename);
                $content = fread($fp, $size);
                return unserialize($content);
            }
        }
        return false;
    }

    /**
     * Set cache value
     *
     * @param mixed $param
     * @param mixed $val
     */
    public function set($param, $val) {
        $filename = 'u_' . md5(serialize($param));
        $fp = fopen($this->dir . $filename, 'w');
        fwrite($fp, serialize($val));
        fclose($fp);
    }

    /**
     * Remove cache files
     *
     * @param int $expire The number os seconds before expiry
     */
    public function cleanup($expire) {
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if (!is_dir($file) && $file != '.' && $file != '..') {
                    $lasttime = @filemtime($this->dir . $file);
                    if (time() - $lasttime > $expire) {
                        @unlink($this->dir . $file);
                    }
                }
            }
        }
    }

    /**
     * delete current user's cache file
     *
     */
    public function refresh() {
        if ($dir = opendir($this->dir)) {
            while (false !== ($file = readdir($dir))) {
                if (!is_dir($file) && $file != '.' && $file != '..') {
                    if (strpos($file, 'u_') !== false) {
                        @unlink($this->dir . $file);
                    }
                }
            }
        }
    }

}
