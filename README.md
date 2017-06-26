#############

Requirement:

To successfully test the code challenge you will need PHP, MySQL and a running RabbitMQ Server.

#############

## MySQL Database

1. mysql_db_changes_scripts.sql -- Use this script to setup product table and add test products in it.

## Code / Programs

1. config.php				-- Configure MySQL - Database Info and RabbitMQ - Server Info parameters.
2. order_sender_task.php 	-- Run this program to send the order (change the data as you need) to queue.
3. order_process_worker.php -- Run this program to receive the order and process it one by one. It can be run in multiple instances too. It will stop if there is no inventories.
4. acme_order_process.php	-- Order processing module contains the order validation and inventory allocation in it.

## Code Run & Test Results

Order Input: (Note: Input needs to be changed in the program for each run)

c:\shipwire\code_challenge>php order_sender_task.php

-----------------------------------------------------------------------------------------------------------------------------------------------------------
 [x] Sent {"Header": 1, "Lines": [{"Product": "A", "Quantity": "4"}, {"Product": "C", "Quantity": "0"}]}
 
 [x] Sent {"Header": 2, "Lines": [{"Product": "A", "Quantity": "2"}, {"Product": "C", "Quantity": "5"}]}
 
 [x] Sent {"Header": 3, "Lines": [{"Product": "B", "Quantity": "2"}, {"Product": "D", "Quantity": "5"}]}
 
 [x] Sent {"Header": 4, "Lines": [{"Product": "A", "Quantity": "5"}, {"Product": "D", "Quantity": "5"}]}
 
 [x] Sent {"Header": 5, "Lines": [{"Product": "E", "Quantity": "5"}, {"Product": "D", "Quantity": "5"}]}
 
-----------------------------------------------------------------------------------------------------------------------------------------------------------

Order Output:

c:\shipwire\code_challenge>php order_process_worker.php

-----------------------------------------------------------------------------------------------------------------------------------------------------------
  [*] Waiting for order to process [send order using task]. To exit press CTRL+C

 [x] Order Received : {"Header": 1, "Lines": [{"Product": "A", "Quantity": "4"}, {"Product": "C", "Quantity": "0"}]}
 [ ] Order Invalid : {"Header": 1, "Lines": [{"Product": "A", "Quantity": "4"}, {"Product": "C", "Quantity": "0"}]}

 [x] Order Received : {"Header": 2, "Lines": [{"Product": "A", "Quantity": "2"}, {"Product": "C", "Quantity": "5"}]}
 [x] Order Processed : {"Header":2,"OrderDetails":[{"ProductCode":"A","OrderedQty":"2","AllotedQty":"2","Backordered":0},{"ProductCode":"C","OrderedQty":"5","AllotedQty":"5","Backordered":0}],"TotalAllotedQty":7}

 [x] Order Received : {"Header": 3, "Lines": [{"Product": "B", "Quantity": "2"}, {"Product": "D", "Quantity": "5"}]}
 [x] Order Processed : {"Header":3,"OrderDetails":[{"ProductCode":"B","OrderedQty":"2","AllotedQty":0,"Backordered":"2"},{"ProductCode":"D","OrderedQty":"5","AllotedQty":0,"Backordered":"5"}],"TotalAllotedQty":0}

 [x] Order Received : {"Header": 4, "Lines": [{"Product": "A", "Quantity": "5"}, {"Product": "D", "Quantity": "5"}]}
 [x] Order Processed : {"Header":4,"OrderDetails":[{"ProductCode":"A","OrderedQty":"5","AllotedQty":"4","Backordered":1},{"ProductCode":"D","OrderedQty":"5","AllotedQty":0,"Backordered":"5"}],"TotalAllotedQty":4}

 [x] Order Received : {"Header": 5, "Lines": [{"Product": "E", "Quantity": "5"}, {"Product": "D", "Quantity": "5"}]}
 
 *** Zero Inventory, Order Process Stopped ***
 -----------------------------------------------------------------------------------------------------------------------------------------------------------
