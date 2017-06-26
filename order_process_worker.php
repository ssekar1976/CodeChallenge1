<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/acme_order_process.php';

require_once __DIR__ . '/vendor/autoload.php';

use PhpAmqpLib\Connection\AMQPStreamConnection;

$connection = new AMQPStreamConnection(rabbit_mq_instance, rabbit_mq_port, rabbit_mq_userid, rabbit_mq_password);
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

echo ' [*] Waiting for order to process [send order using task]. To exit press CTRL+C', "\n";
	  

$callback = function($msg){
	echo " [x] Order Received : ", $msg->body, "\n";	
	$order = json_decode($msg->body);	
	$processed_order = new ProcessOrder();
	if ($processed_order->is_valid_order($order)) {		
		$db_conn = get_connection();
		if (!$db_conn) {
			echo " [ ] Order Process Error : ", $msg->body, "DB Connection Error", "\n";
		}
		try {
			
			if ($processed_order->is_inventory_zero($db_conn)) {
				die(" *** Zero Inventory, Order Process Stopped ***");
			} else {
				$inventory = $processed_order->get_inventory($db_conn, $order);
				$order_info = $processed_order->allocate_inventory($inventory, $order);
				if ($order_info->TotalAllotedQty > 0) {
					$processed_order->update_inventory($db_conn, $order_info);
				}
				mysqli_commit($db_conn);
				echo " [x] Order Processed : ", json_encode($order_info), "\n";	
			}
		} catch (Exception $exp) {
			echo " [ ] Order Process Error : ", $msg->body, $exp->getMessage(), "\n";
			mysqli_rollback($db_conn);
			mysqli_autocommit($db_conn,true);			
		}
		mysqli_close($db_conn);
	} else {
		echo " [ ] Order Invalid : ", $msg->body, "\n";
	}

	echo "\n";
	
	$msg->delivery_info['channel']->basic_ack($msg->delivery_info['delivery_tag']);
};

$channel->basic_qos(null, 1, null);
$channel->basic_consume('task_queue', '', false, false, false, false, $callback);

while(count($channel->callbacks)) {
    $channel->wait();
}

$channel->close();
$connection->close();

/**
*	To get mysql connection.
*/
function  get_connection() {
	return $db_connection = mysqli_connect(mysql_instance, mysql_userid, mysql_password, mysql_database, mysql_port);
}

?>