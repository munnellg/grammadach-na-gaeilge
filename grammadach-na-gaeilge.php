<?php
/**
 * Plugin Name: Grammadach na Gaeilge
 * Plugin URI: https://peculiarparity.com/grammadach-na-gaeilge
 * Description: A simple app for practicing Irish Grammar
 * Version: 0.1
 * Author: Gary Munnelly
 * Author URI: https://peculiarparity.com
 */

define("GRAMMADACH_NA_GAEILGE_VERSION", "0.1");

require_once __DIR__ . "/grammadach-na-gaeilge-rest.php";
require_once __DIR__ . '/grammadach-na-gaeilge-admin.php';

class GrammadachNaGaeilge {

	public static function init() {
		register_activation_hook(__FILE__, [ __CLASS__, "grammadach_na_gaeilge_install" ]);
		register_activation_hook(__FILE__, [ __CLASS__, "grammadach_na_gaeilge_install_data" ]);
		register_deactivation_hook( __FILE__, [ __CLASS__, 'grammadach_na_gaeilge_uninstall' ] );

		add_action('wp_footer', [ __CLASS__, 'add_scripts' ]);

		// REST API should always be available
		add_action("rest_api_init", function() {
			$gnag = new GrammadachNaGaeilgeRest;
			$gnag->register_routes();
		});
	}

	public static function add_scripts() {
		// only add javascript app to specified page
		if (is_page(get_option("grammadach_na_gaeilge_page_id"))) {
			wp_register_style("grammadach-na-gaeilge", plugin_dir_url( __FILE__ ) . "css/grammadach-na-gaeilge.css");
    		
    		wp_enqueue_style("grammadach-na-gaeilge");

    		wp_enqueue_script("jquery-effects-core");
    		
			wp_enqueue_script(
				"grammadach-na-gaeilge",
				plugin_dir_url( __FILE__ ) . "js/grammadach-na-gaeilge.js",
				array("jquery")
			);
		}
	}

	public static function grammadach_na_gaeilge_install() {
		global $wpdb;

		$table_name = $wpdb->prefix . "grammadach_na_gaeilge";

		$charset_collate = $wpdb->get_charset_collate();

		$sql = "CREATE TABLE $table_name (
			id mediumint(9) NOT NULL AUTO_INCREMENT,
			noun tinytext NOT NULL,
			gender varchar(16),
			declension int(1),
			is_proper int(1),
			is_immutable int(1),
			is_definite int(1),
			allow_articled_genitive int(1),
			PRIMARY KEY  (id)
		) $charset_collate;";

		require_once(ABSPATH . "wp-admin/includes/upgrade.php");

		dbDelta($sql);

		add_option("grammadach_na_gaeilge_version", GRAMMADACH_NA_GAEILGE_VERSION);
		add_option("grammadach_na_gaeilge_page_id", "");
	}

	public static function grammadach_na_gaeilge_uninstall() {
		global $wpdb;

		$table_name = $wpdb->prefix . "grammadach_na_gaeilge";

		$sql = "DROP TABLE $table_name;";

		delete_option('grammadach_na_gaeilge_version');
		delete_option('grammadach_na_gaeilge_page_id');
	}

	public static function grammadach_na_gaeilge_install_data() {
		global $wpdb;

		$table_name = $wpdb->prefix . "grammadach_na_gaeilge";
		
		$data_file = fopen(__DIR__ . "/grammadach-na-gaeilge-data.txt", "r") or die("Unable to open data file!");

		while (($line = fgets($data_file)) !== false) {
			$noun_properties = explode("\t", $line);

			$wpdb->insert(
				$table_name,
				array(
					"noun"                    => $noun_properties[0],
					"gender"                  => $noun_properties[1],
					"declension"              => $noun_properties[2],
					"is_proper"               => $noun_properties[3],
					"is_immutable"            => $noun_properties[4],
					"is_definite"             => $noun_properties[5],
					"allow_articled_genitive" => $noun_properties[6]
			));
	    }
		
		fclose($data_file);
	}
}

GrammadachNaGaeilge::init();
