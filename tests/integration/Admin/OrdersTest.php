<?php

use SkyVerge\WooCommerce\Facebook\Admin;
use SkyVerge\WooCommerce\Facebook\Commerce\Orders;
use SkyVerge\WooCommerce\PluginFramework\v5_5_4\SV_WC_Plugin_Exception;

/**
 * Tests the Admin\Orders class.
 */
class OrdersTest extends \Codeception\TestCase\WPTestCase {


	/** @var \IntegrationTester */
	protected $tester;


	/**
	 * Runs before each test.
	 */
	protected function _before() {

		if ( ! class_exists( Admin\Orders::class ) ) {
			require_once 'includes/Admin/Orders.php';
		}

		if ( ! class_exists( Orders::class ) ) {
			require_once 'includes/Commerce/Orders.php';
		}
	}


	/**
	 * Runs after each test.
	 */
	protected function _after() {

	}


	/** Test methods **************************************************************************************************/


	// TODO: add test for enqueue_assets()

	// TODO: add test for add_notices()

	// TODO: add test for maybe_remove_order_metaboxes()

	// TODO: add test for render_refund_reason_field()


	/**
	 * @see Admin\Orders::handle_refund()
	 *
	 * @throws SV_WC_Plugin_Exception|WC_Data_Exception
	 */
	public function test_handle_refund() {

		// the API cannot be instantiated if an access token is not defined
		facebook_for_woocommerce()->get_connection_handler()->update_access_token( 'access_token' );

		$order = new \WC_Order();
		$order->save();

		$refund = new \WC_Order_Refund();
		$refund->set_parent_id( $order->get_id() );
		$refund->save();

		$this->get_orders_handler()->handle_refund( $refund->get_id() );

		$notes = array_map( static function( $note ) { return $note->content; }, wc_get_order_notes( [ 'order_id' => $order->get_id() ] ) );

		// asserts that handle_refund called add_order_refund
		$this->assertContains( 'Could not refund Instagram order: Remote ID for parent order not found.', $notes );
	}


	// TODO: add test for handle_bulk_update()


	/**
	 * @see Admin\Orders::is_order_editable()
	 *
	 * @param bool $maybe_editable
	 * @param string $created_via
	 * @param string $status
	 * @param bool $expected
	 *
	 * @dataProvider provider_is_order_editable
	 *
 	 * @throws WC_Data_Exception
	 */
	public function test_is_order_editable( $maybe_editable, $created_via, $status, $expected ) {

		$order = new \WC_Order();
		$order->set_created_via( $created_via );
		$order->set_status( $status );
		$order->save();

		$this->assertEquals( $expected, $this->get_orders_handler()->is_order_editable( $maybe_editable, $order ) );
	}


	/** @see test_is_order_editable */
	public function provider_is_order_editable() {

		return [
			[ false, 'checkout',  'pending',    false ],
			[ true,  'checkout',  'pending',    true ],
			[ true,  'instagram', 'pending',    false ],
			[ true,  'instagram', 'processing', true ],
			[ true,  'facebook',  'pending',    false ],
			[ true,  'facebook',  'processing', true ],
		];
	}


	/** Utility methods ***********************************************************************************************/


	/**
	 * Gets an orders handler instance.
	 *
	 * @since 2.1.0-dev.1
	 *
	 * @return Admin\Orders
	 */
	private function get_orders_handler() {

		return new Admin\Orders();
	}


}