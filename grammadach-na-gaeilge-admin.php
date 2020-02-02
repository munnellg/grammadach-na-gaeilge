<?php

class GrammadachNaGaeilgeAdmin {

	public static $admin_tags = [
		'input'  => [
			'type'     => [],
			'name'     => [],
			'id'       => [],
			'disabled' => [],
			'value'    => [],
			'checked'  => [],
		],
		'select' => [
			'name' => [],
			'id'   => [],
		],
		'option' => [
			'value'    => [],
			'selected' => [],
		],
	];

	public function __construct() {
		add_action('admin_menu', [ $this, 'admin_page_init' ]);
	}

	public function admin_page_init() {
		add_options_page('Grammadach na Gaeilge', 'Grammadach na Gaeilge', 'manage_options', 'grammadach-na-gaeilge', [ $this, 'plugin_options_menu' ]);
	}

	public function plugin_options_menu() {
		if (!current_user_can('manage_options')) {
			wp_die(__('You do not have sufficient permissions to access this page.')); // xss ok
		}

		$this->table_head();

		// save options if this is a valid post
		if ( isset( $_POST['grammadach_na_gaeilge_save_field'] ) && // input var okay
			wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['grammadach_na_gaeilge_save_field'] ) ), 'grammadach_na_gaeilge_save_action' ) // input var okay
		) {
			echo "<div class='updated settings-error' id='etting-error-settings_updated'><p><strong>Settings saved.</strong></p></div>\n";
			$this->admin_save();
		}

		$page_id = "value='" . esc_attr( get_option( 'grammadach_na_gaeilge_page_id', '' ) ) . "'";

		$this->admin_table_row(
			'Page Id',
			'Enter the Page Id on which Grammadach na Gaeilge should be displayed',
			"<input type='textbox' name='grammadach_na_gaeilge_page_id' id='grammadach_na_gaeilge_page_id' $page_id>",
			'grammadach_na_gaeilge_page_id'
		);

		$this->table_foot();
	}

	public function admin_save() {		
		if ( array_key_exists('grammadach_na_gaeilge_page_id', $_POST) && isset($_POST['grammadach_na_gaeilge_page_id'])) { // input var okay
			update_option('grammadach_na_gaeilge_page_id', wp_unslash( $_POST['grammadach_na_gaeilge_page_id'])); // input var okay
		}
	}

	public function table_head() {
		?>
		<div class='wrap' id='grammadach-na-gaeilge-options'>
			<h2>Grammadach na Gaeilge</h2>
			<form id='mathjaxlatex' name='mathjaxlatex' action='' method='POST'>
				<?php wp_nonce_field( 'grammadach_na_gaeilge_save_action', 'grammadach_na_gaeilge_save_field', true ); ?>
			<table class='form-table'>
			<caption class='screen-reader-text'>The following lists configuration options for the Grammadach na Gaeilge plugin.</caption>
		<?php
	}

	public function table_foot() {
		?>
		</table>

		<p class="submit"><input type="submit" class="button button-primary" value="Save Changes"/></p>
		</form>

		</div>
		<script type="text/javascript">
		jQuery(function($) {
			if (typeof($.fn.prop) !== 'function') {
				return; // ignore this for sites with jquery < 1.6
			}
			// enable or disable the cdn input field when checking/unchuecking the "use cdn" checkbox
			var cdn_check = $('#use_cdn'),
			cdn_location = $('#grammadach_na_gaeilge_page_id');

			cdn_check.change(function() {
				var checked = cdn_check.is(':checked');
				cdn_location.prop('disabled', checked);
			});
		});
		</script>
		<?php
	}

	public function admin_table_row($head, $comment, $input, $input_id) {
		?>
			<tr valign="top">
					<th scope="row">
						<label for="<?php echo esc_attr($input_id); ?>"><?php echo esc_html($head); ?></label>
					</th>
					<td>
						<?php echo wp_kses($input, self::$admin_tags); ?>
						<p class="description"><?php echo wp_kses_post($comment); ?></p>
					</td>
				</tr>
		<?php
	}
}

function grammadach_na_gaeilge_admin_init() {
	global $grammadach_na_gaeilge_admin;
	$grammadach_na_gaeilge_admin = new GrammadachNaGaeilgeAdmin();
}

if (is_admin()) {
	grammadach_na_gaeilge_admin_init();
}
