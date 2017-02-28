# AntHQ/Ant - Magento Events

The module reads changes in the system via the Magento Event system, for the following events.

| Magento Event                      | Observer Class                                                   | Method                  |
|:-----------------------------------|:-----------------------------------------------------------------|:------------------------|
| catalog_product_save_commit_after  | [CatalogProductObserver](../Observer/CatalogProductObserver.php) | save                    |
| catalog_product_delete_after       | [CatalogProductObserver](../Observer/CatalogProductObserver.php) | delete                  |
| order_save_commit_after            | [OrderObserver](../Observer/OrderObserver.php)                   | save                    |
| customer_register_success          | [OrderObserver](../Observer/OrderObserver.php)                   | --                      |


## TODO
* product create, update, delete, import (get page per page), count (get page count, and total count)
* order update (just for voiding)
* webhook (product create, update, delete) (order create, update) - needs to tell us the payment status of orders so we can ignore them if the order hasnt been paid