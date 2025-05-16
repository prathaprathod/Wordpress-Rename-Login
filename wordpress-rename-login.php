<?php
/*
 * Plugin Name: Wordpress Rename Login
 * Plugin URI:  https://wordpress.org/plugins/wordpress-rename-login/
 * Description: Wordpress Rename Login - You Can Change Your Login URL and restrict admin access by user roles.
 * Version:     1.2.0
 * Author:      Prathap Rathod
 * Text Domain: wordpress-rename-login
 * License:     GPL-3.0+
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class WP_Rename_Login {
	private $default_slug = 'my-login';
	private $wp_login_php = false;

	public function __construct() {
		add_action( 'plugins_loaded', array( $this, 'plugins_loaded' ) );
		add_action( 'wp_loaded', array( $this, 'wp_loaded' ) );
		add_filter( 'site_url', array( $this, 'site_url' ), 10, 4 );
		add_filter( 'network_site_url', array( $this, 'network_site_url' ), 10, 3 );
		add_filter( 'wp_redirect', array( $this, 'wp_redirect' ), 10, 2 );
		add_filter( 'update_welcome_email', array( $this, 'welcome_email' ) );

		// Admin settings
		add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
		add_action( 'admin_init', array( $this, 'register_settings' ) );

		// Hide admin bar and block admin access
		add_action( 'after_setup_theme', array( $this, 'maybe_hide_admin_bar' ) );
		add_action( 'admin_init', array( $this, 'maybe_block_admin_access' ) );
	}

	public function plugins_loaded() {
		global $pagenow;

		load_plugin_textdomain( 'wordpress-rename-login' );

		if ( ! is_multisite() && (
			strpos( $_SERVER['REQUEST_URI'], 'wp-signup' ) !== false ||
			strpos( $_SERVER['REQUEST_URI'], 'wp-activate' ) !== false
		)) {
			wp_die( __( 'This feature is not enabled.', 'wordpress-rename-login' ) );
		}

		$request = parse_url( $_SERVER['REQUEST_URI'] );

		if ( (
			strpos( $_SERVER['REQUEST_URI'], 'wp-login.php' ) !== false ||
			untrailingslashit( $request['path'] ) === site_url( 'wp-login', 'relative' )
		) && ! is_admin() ) {
			$this->wp_login_php = true;
			$_SERVER['REQUEST_URI'] = $this->user_trailingslashit( '/' . str_repeat( '-/', 10 ) );
			$pagenow = 'index.php';
		}
	}

	public function wp_loaded() {
		if ( $this->wp_login_php ) {
			$this->wp_template_loader();
		}
	}

	private function wp_template_loader() {
		require_once ABSPATH . 'wp-login.php';
		exit;
	}

	private function new_login_url( $scheme = null ) {
		return site_url( $this->new_login_slug(), $scheme );
	}

	private function new_login_slug() {
		return get_option( 'wp_rename_login_slug', $this->default_slug );
	}

	public function site_url( $url, $path, $scheme, $blog_id ) {
		if ( $path === 'wp-login.php' || $path === 'wp-login.php?action=register' ) {
			return $this->new_login_url( $scheme );
		}
		return $url;
	}

	public function network_site_url( $url, $path, $scheme ) {
		if ( $path === 'wp-login.php' || $path === 'wp-login.php?action=register' ) {
			return $this->new_login_url( $scheme );
		}
		return $url;
	}

	public function wp_redirect( $location, $status ) {
		if ( strpos( $location, 'wp-login.php' ) !== false ) {
			return $this->new_login_url();
		}
		return $location;
	}

	public function welcome_email( $welcome_email ) {
		return str_replace( 'wp-login.php', $this->new_login_slug(), $welcome_email );
	}

	private function user_trailingslashit( $string ) {
		return trailingslashit( $string );
	}

	public function add_settings_page() {
		add_options_page(
			'Rename Login',
			'Rename Login',
			'manage_options',
			'wp-rename-login',
			array( $this, 'settings_page_html' )
		);
	}

	public function register_settings() {
		register_setting( 'wp_rename_login_settings', 'wp_rename_login_slug', array(
			'type' => 'string',
			'sanitize_callback' => 'sanitize_title',
			'default' => $this->default_slug
		) );

		register_setting( 'wp_rename_login_settings', 'wp_rename_login_hide_admin_bar_roles', array(
			'type' => 'array',
			'sanitize_callback' => array( $this, 'sanitize_roles' ),
			'default' => array()
		) );

		register_setting( 'wp_rename_login_settings', 'wp_rename_login_block_admin_roles', array(
			'type' => 'array',
			'sanitize_callback' => array( $this, 'sanitize_roles' ),
			'default' => array()
		) );
	}

	public function sanitize_roles( $roles ) {
		return is_array( $roles ) ? array_map( 'sanitize_text_field', $roles ) : array();
	}

	public function maybe_hide_admin_bar() {
		if ( is_user_logged_in() ) {
			$current_user = wp_get_current_user();
			$roles_to_hide = get_option( 'wp_rename_login_hide_admin_bar_roles', array() );

			foreach ( $roles_to_hide as $role ) {
				if ( in_array( $role, (array) $current_user->roles, true ) ) {
					show_admin_bar( false );
					break;
				}
			}
		}
	}

	public function maybe_block_admin_access() {
		if ( is_user_logged_in() && is_admin() && ! defined( 'DOING_AJAX' ) ) {
			$current_user = wp_get_current_user();
			$roles_to_block = get_option( 'wp_rename_login_block_admin_roles', array() );

			foreach ( $roles_to_block as $role ) {
				if ( in_array( $role, (array) $current_user->roles, true ) ) {
					wp_redirect( home_url() );
					exit;
				}
			}
		}
	}

	public function settings_page_html() {
		$all_roles = wp_roles()->roles;
		?>
		<div class="wrap">
			<h1>Rename Login Settings</h1>
			<form method="post" action="options.php">
				<?php settings_fields( 'wp_rename_login_settings' ); ?>
				<?php do_settings_sections( 'wp_rename_login_settings' ); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">New Login Slug</th>
						<td>
							<input type="text" name="wp_rename_login_slug" value="<?php echo esc_attr( get_option( 'wp_rename_login_slug', $this->default_slug ) ); ?>" />
							<p class="description">Example: If you enter <strong>secure-login</strong>, your new login URL will be <code><?php echo home_url(); ?>/secure-login</code></p>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Hide Admin Bar For Roles</th>
						<td>
							<?php
							$hide_roles = get_option( 'wp_rename_login_hide_admin_bar_roles', array() );
							foreach ( $all_roles as $role_key => $role_data ) : ?>
								<label>
									<input type="checkbox" name="wp_rename_login_hide_admin_bar_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( in_array( $role_key, $hide_roles ) ); ?> />
									<?php echo esc_html( $role_data['name'] ); ?>
								</label><br>
							<?php endforeach; ?>
						</td>
					</tr>

					<tr valign="top">
						<th scope="row">Block wp-admin Access For Roles</th>
						<td>
							<?php
							$block_roles = get_option( 'wp_rename_login_block_admin_roles', array() );
							foreach ( $all_roles as $role_key => $role_data ) : ?>
								<label>
									<input type="checkbox" name="wp_rename_login_block_admin_roles[]" value="<?php echo esc_attr( $role_key ); ?>" <?php checked( in_array( $role_key, $block_roles ) ); ?> />
									<?php echo esc_html( $role_data['name'] ); ?>
								</label><br>
							<?php endforeach; ?>
							<p class="description">Selected roles will be redirected to the homepage when trying to access wp-admin.</p>
						</td>
					</tr>

				</table>
				<?php submit_button(); ?>
			</form>
		</div>
		<?php
	}
}

new WP_Rename_Login();
