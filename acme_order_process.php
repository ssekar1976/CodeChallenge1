<?php

Class ProcessOrder {
	const ORDER_MIN_QTY = 1;
	const ORDER_MAX_QTY = 5;
	
	/**
	 *  To validate the order with Min & Max (ie. 1 - 5)
	 *
	 */
	public function is_valid_order($order) {
		foreach($order->Lines as $product) {
			if ($product->Quantity < self::ORDER_MIN_QTY || $product->Quantity > self::ORDER_MAX_QTY) {
				return false;
			}
		}
		return true;
	}

	/**
	 *	To see all inventories are zero.
	 */
	public function is_inventory_zero($db_conn) {
		$inventory_zero = true;
		$sql_command = "SELECT product_code FROM products WHERE available_qty > 0 AND is_active = 1 LIMIT 1;";
		$result = mysqli_query($db_conn, $sql_command);
		if (mysqli_num_rows($result) > 0)
		{
			$inventory_zero = false;
		}
		return $inventory_zero;
	}
	
	/**
	 *	To get the inventory for available products to allocate it for the given order.
	 */
	public function get_inventory($db_conn, $order) {
		$inventory = array();		
		mysqli_autocommit($db_conn, false);		
		$sql_command = '';		
		foreach($order->Lines as $product) {
			$sql_command .= "SELECT product_code, available_qty FROM products WHERE product_code = '" . $product->Product . "' AND available_qty > 0 AND is_active = 1 FOR UPDATE;";
		}
		if (mysqli_multi_query($db_conn,$sql_command))
		{
		  do {
				if ($result = mysqli_store_result($db_conn)) {
					while ($result_arr = mysqli_fetch_array($result, MYSQLI_ASSOC)) {
						$inventory[$result_arr['product_code']] = $result_arr['available_qty'];
					}
					mysqli_free_result($result);
				}
			} while (mysqli_next_result($db_conn));
		}
		return $inventory;
	}

	/**
	 *	To update the inventory for the allocated order.
	 */
	public function update_inventory($db_conn, $order_info) {				
		foreach($order_info->OrderDetails as $product) {
			if ($product->AllotedQty > 0) {
				$sql_command = "UPDATE products SET available_qty = available_qty - " . $product->AllotedQty . ", ordered_qty = ordered_qty + " . $product->AllotedQty . " WHERE product_code = '" . $product->ProductCode . "';";
				$result = $db_conn->multi_query($sql_command);
				if (!$result) {
					throw new Exception ("Inventory Update Error: " . mysqli_errno($db_conn) . ' - ' . mysqli_error($db_conn));
				}				
			}
		}
	}
	
	/**
	 *	To allocate the inventory for the given order.
	 */
	public function allocate_inventory($inventory, $order) {
		$order_details_arr = array();
		$total_alloted_qty = 0;
		foreach($order->Lines as $line) {
			$ordered_qty = $line->Quantity;
			$alloted_qty = 0;
			$backordered = 0;
			$available_qty = 0;
			
			if (array_key_exists($line->Product, $inventory)) {
				$available_qty = $inventory[$line->Product];
			}
			
			if ($available_qty > 0 && $available_qty >= $ordered_qty) {
				$alloted_qty = $ordered_qty;
			} else if ($available_qty > 0 && $ordered_qty > $available_qty) {
				$alloted_qty = $available_qty;
				$backordered = $ordered_qty - $available_qty;
			} else {
				$backordered = $ordered_qty;
			}
			$total_alloted_qty += $alloted_qty;
			$order_details = new OrderDetails($line->Product, $ordered_qty, $alloted_qty, $backordered);
			array_push($order_details_arr, $order_details);
		}
		$processed_order = new OrderHeader($order->Header, $order_details_arr, $total_alloted_qty);
		return $processed_order;
	}
}

/**
*	Order Header & Order Details - Entities
*/
class OrderHeader {
	public $Header;
	public $OrderDetails;
	public $TotalAllotedQty = 0;
	function __construct($header_id, $order_details, $total_alloted_qty) {
		$this->Header = $header_id;
		$this->TotalAllotedQty = $total_alloted_qty;
		$this->OrderDetails = $order_details;
	}
}

class OrderDetails {
	public $ProductCode = '';
	public $OrderedQty = 0;
	public $AllotedQty = 0;
	public $Backordered = 0;
	function __construct($product_code, $ordered_qty, $alloted_qty, $backordered) {
		$this->ProductCode = $product_code;
		$this->OrderedQty = $ordered_qty;
		$this->AllotedQty = $alloted_qty;
		$this->Backordered = $backordered;
	}	
}

?>