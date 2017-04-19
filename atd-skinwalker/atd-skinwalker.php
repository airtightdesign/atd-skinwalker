<?php
/**
 * Plugin Name: ATD Skinwalker
 * Plugin URI: http://bitbucket.org
 * Description: Dynamically alter and cache images requested from the uploads directory.
 * Version: 1.0.0
 * Author: William J Brown
 * Author URI: http://wjbrownllc.com
 * License: MIT
 */
 
class AtdSkinwalker
{
    
    private static $htaccess_src;

    private static $htaccess_debug_src;

    private static $htaccess_dst;

    private static $cache_dir;
    
	private static $sample_img_src;

	private static $sample_img_dst;

    private static $docs = 'https://bitbucket.org/airtightdesign/atd_skinwalker/overview';

	// Since we dynamically detect directories (and we are NOT a real part of the wordpress ecosystem)
	// these variables have to be computed at runtime.  The bottom of this file invokes the init() method.
    public static function init()
    {
        self::$htaccess_src = dirname(__FILE__) . '/htaccess_index';
        self::$htaccess_debug_src = dirname(__FILE__) . '/htaccess_debug';
        self::$htaccess_dst = dirname(dirname(dirname(__FILE__))) . '/uploads/.htaccess';
        self::$cache_dir = dirname(__FILE__) . '/cache';
		self::$sample_img_src = dirname(__FILE__) . '/assets/lena.jpg';
		self::$sample_img_dst = dirname(dirname(dirname(__FILE__))) . '/uploads/skinwalker.jpg';
    }
    
	// returns link to the bitbucket documentation / README
    private static function docs_link()
    {
        return sprintf(
            '<a href="%s" target="_blank">View installation instructions.</a>',
            self::$docs  
        );
    }

    // creates .htaccess file in the uploads directory
    public static function plugin_activated()
    {
        // attempt to create .htaccess file in uploads directory
        if (!self::create_htaccess()) {
            die("Unable to create .htaccess file. <br>" . self::docs_link());
        }

        // attempt to create cache directory
        if (!self::create_cache_dir()) {
            die("Unable to create cache directory");
        }

		// attempt to copy lena.jpg to uploads directory as skinwalker.jpg
		if (!self::create_sample_image()) {
			die("Unable to create sample image.");
		}

    }
    
    // deletes .htaccess from the uploads directory
    public static function plugin_deactivated()
    {
        // attempt to destroy .htaccess file from uploads directory
        if (!self::destroy_htaccess()) {
            die("Unable to remove .htaccess file");
        }

		// attempt to destroy the sample image file from uploads directory
        if (!self::destroy_sample_image()) {
            die("Unable to remove sample image file from uploads");
        }
    }
    
    // creates an .htaccess file in the uploads directory
    private static function create_htaccess($debug = false)
    {
        $src = $debug ? self::$htaccess_debug_src : self::$htaccess_src;
        $dst = self::$htaccess_dst;

        // only copy it if it doesn't already exist
        if (!file_exists($dst)) {
            @copy($src, $dst);
        }

        // if the file exists, we dangerously assume its correct
        return file_exists($dst);
    }
    
    // deletes .htaccess from the uploads directory
    private static function destroy_htaccess()
    {
        $dst = self::$htaccess_dst;
        self::unlink($dst);
        return !file_exists($dst);
    }

    // creates the directory where images will be cached
    private static function create_cache_dir()
    {
        $cache_dir = self::$cache_dir;
        
        // attempt to create directory
        if (!is_dir($cache_dir)) {
            mkdir($cache_dir);
        }
        
        // set permissions on the directory
        if (is_dir($cache_dir)) {
            @chmod($cache_dir, 0775);
        }
        
        return is_dir($cache_dir);
    }

	// copies a test image to the uploads directory
	private static function create_sample_image()
	{
		$src = self::$sample_img_src;
		$dst = self::$sample_img_dst;

		// only copy it if it doesn't already exist
        if (!file_exists($dst)) {
            @copy($src, $dst);
        }

        // if the file exists, we dangerously assume its correct
        return file_exists($dst);
	}

	// deletes the sample image from the uploads dir
	private static function destroy_sample_image()
	{
		$dst = self::$sample_img_dst;
        self::unlink($dst);
        return !file_exists($dst);
	}
    
	// clears the cache directory
    public static function clear_cache_dir()
    {
        self::unlink(self::$cache_dir . "/*");
    }
      
	// true IFF the htaccess file in the uploads directory is the same as the local debuggable htaccess version
	public static function debug_enabled()
	{
		return sha1_file(self::$htaccess_debug_src) == sha1_file(self::$htaccess_dst);
	}

	// add an admin menu item for the plugin
    public static function skinwalker_menu()
    {
       	add_options_page( 'ATD Skinwalker Settings', 'ATD Skinwalker', 'manage_options', 'atd-skinwalker-settings', array('AtdSkinwalker', 'skinwalker_settings') );
    }

	// settings page, allows for debug enable/disable & cache clearing operations
	// also shows sample image file transformations
    public static function skinwalker_settings()
    {
    	if ( !current_user_can( 'manage_options' ) )  {
    		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
    	}
    	
    	$cache_size = self::dirsize(self::$cache_dir);
    	$cache_file_count = self::count_files(self::$cache_dir);
    	?>
    	<div class="wrap">
    	    <form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                <input type="hidden" name="action" value="skinwalker_clear_cache">
                <p>
                    Does the .htaccess file exist in <?php echo self::$htaccess_dst; ?>?
                    <?php if (file_exists(self::$htaccess_dst)) : ?>
                        <span style="color:green; font-weight:bold;">YES</span>
                    <?php else: ?>
                        <span style="color:red; font-weight:bold;">NO</span>
                    <?php endif; ?>
                </p>
                <p>
                    Does the cache directory exist at <?php echo self::$cache_dir; ?> AND is it writable?
                    <?php if (file_exists(self::$cache_dir) && is_writable(self::$cache_dir)) : ?>
                        <span style="color:green; font-weight:bold;">YES</span>
                    <?php else: ?>
                        <span style="color:red; font-weight:bold;">NO</span>
                    <?php endif; ?>
                </p>
                <p>Total # cache files: <?php echo $cache_file_count; ?></p>
                <p>Total cache filesize: <?php echo ($cache_size / 1000000); ?> MB</p>
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Clear Cache') ?>" />
                </p>
			</form>

			<?php if (self::debug_enabled()) : ?>
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                <input type="hidden" name="action" value="skinwalker_disable_debug">
				<p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Disable Debug') ?>" />
                </p>
			</form>
			<?php else: ?>
			<form action="<?php echo esc_url( admin_url('admin-post.php') ); ?>" method="post">
                <input type="hidden" name="action" value="skinwalker_enable_debug">
				<p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Enable Debug') ?>" />
                </p>
            </form>
			<?php endif; ?>
			<img src="/wp-content/uploads/skinwalker.jpg">
			<img src="/wp-content/uploads/skinwalker.jpg?r=cover&w=200&h=200">
			<img src="/wp-content/uploads/skinwalker.jpg?w=300&r=widen">
			<img src="/wp-content/uploads/skinwalker.jpg?a=crop&w=200&h=200&x=15&y=50">
    	</div>
    	<?php
    }
    
	// callback for admin-posts.php, clears cache directory
    public static function skinwalker_clear_cache()
    {
        self::clear_cache_dir();
        status_header(200);
        wp_redirect(wp_get_referer());
        exit;
    }

	// callback for admin-posts.php, disables debug mode
	public static function skinwalker_disable_debug()
	{
		self::destroy_htaccess();
		self::create_htaccess();
        status_header(200);
        wp_redirect(wp_get_referer());
        exit;
	}

	// callback for admin-posts.php, enables debug mode
	public static function skinwalker_enable_debug()
	{
		self::destroy_htaccess();
		self::create_htaccess(true);
        status_header(200);
        wp_redirect(wp_get_referer());
        exit;
	}
    
	// recursively computes the filesize of a directory by summing the filesizes of its contents recursively
    public static function dirsize($path)
    {
        $bytestotal = 0;
    
        $path = realpath($path);
    
        if ($path !== false) {
            foreach(new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path, FilesystemIterator::SKIP_DOTS)) as $object){
                $bytestotal += $object->getSize();
            }
        }
        
        return $bytestotal;
    }
    
	// recursively returns the number of files contained in a directory recursively.
    public static function count_files($dir)
    {
        $size = 0;

        foreach(scandir($dir) as $file) {
            if (!in_array($file, ['.','..'])) {
                if (is_dir(rtrim($dir, '/') . '/' . $file)) {
                    $size += self::count_files(rtrim($dir, '/') . '/' . $file);
                } else {
                    $size++;
                }
            }
        }
        
        return $size;
    }

	// re-implementation of the php unlink function, but instead of taking a filename as
	// the input, it takes a pattern that will be passed to the glob() function
	// This implementation deletes recursively, so USE WITH CAUTION!
    public static function unlink($pattern = "*")
    {
        foreach (glob($pattern) as $file) {
            if (is_dir($file)) { 
                self::unlink($file . "/*");
                rmdir($file);
            } else {
                unlink($file);
            }
        }
    }
}

// this call initializes the path variables
AtdSkinwalker::init();

register_activation_hook( __FILE__, array('AtdSkinwalker', 'plugin_activated' ));
register_deactivation_hook( __FILE__, array('AtdSkinwalker', 'plugin_deactivated' ));

add_action( 'admin_menu',                          array('AtdSkinwalker', 'skinwalker_menu') );
add_action( 'admin_post_skinwalker_clear_cache',   array('AtdSkinwalker', 'skinwalker_clear_cache' ));
add_action( 'admin_post_skinwalker_disable_debug', array('AtdSkinwalker', 'skinwalker_disable_debug' ));
add_action( 'admin_post_skinwalker_enable_debug',  array('AtdSkinwalker', 'skinwalker_enable_debug' ));
