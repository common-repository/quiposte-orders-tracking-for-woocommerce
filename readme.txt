=== quiposte-orders-tracking-for-woocommerce ===
Contributors: Quiposte
Tags:plugin
Requires at least: 4.7
Tested up to: 6.1.1
Stable tag: 1.2.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html
 
Allows tracking of the shipment of woocommerce orders

=== Description ===
In the order in a window you can insert the tracking number of the package
and the shipping company, it can be done for each item in the order or for all.

What does the plugin do?
The plugin adds a new metadata to the order items in a json format as follows:
{
    "corriere":"Courier Name",
    "url":"https://courier tracking url",
    "tracking_number":"Tracking code",
    "item_id":"current item id"
}
The visual part of the plugin searches the metadata for this information and adds
the possibility of being able to visualize the tracking number for each item, a url
link of the carrier and the possibility of modifying the data through a window.
It also shows in the list of orders a brief description of the delivery status of the order.

The plugin does not call any external service, thanks to the woocomerce api you
can access the status of the orders, and with this plugin it guarantees that you can
update the shipping status of the order.

How to use the plugin through the APIs
1-Obtain the order that I want to add the tracking data
2-Obtain within the item, the metadata with the name
_quiposte_order_trackin_data through, and adding a json in the format shown above,
you can access the information on the delivery status of your order


=== Supported Languages ===
* English
* Spanish
* Italian

== Screenshots ==

1. orderlistview.png  Shows in the list of orders a brief description of the shipping status of the order
2. itemview.png       Shows the shipping status under each item
3. edititem.png       Shows the window where the shipping status of the item is modified
4. boxview.png        Box that is added with access to a button to modify all the shipping statuses of the items
5. editallitems.png   Shows the window that allows you to modify all the items