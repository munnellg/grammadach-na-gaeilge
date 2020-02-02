<?php
 
class GrammadachNaGaeilgeRest extends WP_REST_Controller {
 
	/**
	 * Register the routes for the objects of the controller.
	 */
	public function register_routes() {
		$version = "1";
		$namespace = "grammadach-na-gaeilge/v" . $version;
		$base = "noun";
		register_rest_route($namespace, "/" . $base, array(
			array(
				"methods"             => WP_REST_Server::READABLE,
				"callback"            => array($this, "get_noun_random"),
				"permission_callback" => array($this, "get_noun_permissions_check"),
				"args"                => array(),
			)
		));
	}
 
	/**
	 * Get a random noun
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|WP_REST_Response
	 */
	public function get_noun_random($request) {
		global $wpdb;

		//get parameters from request
		$params = $request->get_params();

		$result = $wpdb->get_results( "SELECT * FROM wp_grammadach_na_gaeilge ORDER BY RAND() LIMIT 1");

		$noun = $result[0]; 

		$data = $this->prepare_noun_for_response($noun, $request);
 
		//return a response or error based on some conditional
		if (1 == 1) {
			return new WP_REST_Response($data, 200);
		} else {
			return new WP_Error("code", __("message", "text-domain"));
		}
	}
 
	/**
	 * Check if a given request has access to get a specific noun
	 *
	 * @param WP_REST_Request $request Full data about the request.
	 * @return WP_Error|bool
	 */
	public function get_noun_permissions_check($request) {
		return true; // alter if we want security
	}
 
	/**
	 * Prepare the noun for the REST response
	 *
	 * @param mixed $noun WordPress representation of the noun.
	 * @param WP_REST_Request $request Request object.
	 * @return mixed
	 */
	public function prepare_noun_for_response($noun, $request) {
		return array(
			"noun" => $noun->noun,
			"gender" => $noun->gender,
			"declension" => $noun->declension,
			"is_proper" => $noun->is_proper,
			"is_immutable" => $noun->is_immutable,
			"is_definite" => $noun->is_definite,
			"allow_articled_genitive" => $noun->allow_articled_genitive
		);
	}
}