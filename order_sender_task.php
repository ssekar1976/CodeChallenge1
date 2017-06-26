<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/vendor/autoload.php';
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PhpAmqpLib\Message\AMQPMessage;

$connection = new AMQPStreamConnection(rabbit_mq_instance, rabbit_mq_port, rabbit_mq_userid, rabbit_mq_password);
$channel = $connection->channel();

$channel->queue_declare('task_queue', false, true, false, false);

/**
*	Passing the sample order given below thru queue to process it by worker process. 
*/
$data = '{"Header": 1, "Lines": [{"Product": "A", "Quantity": "4"}, {"Product": "C", "Quantity": "5"}]}';


if(empty($data)) {
	die("Please pass Order in JSON");
}

$msg = new AMQPMessage($data, array('content_type' => 'application/json', 'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT));

$channel->basic_publish($msg, '', 'task_queue');

echo " [x] Sent ", $data, "\n";

$channel->close();
$connection->close();

?>