# AntHQ/Ant - Magento Integration

AntHQ/Ant is a Magento module designed to allow integration with [AntHQ](http://www.anthq.com). When installed as a module in your [Magento 1.x.x](https://magento.com/products/community-edition) installation, it synchronises your products, orders and other data with your AntHQ account. 

## Compatability
AntHQ/Ant is designed to work with Magento v1.9.x - please note it is not compatible with Magento 2.

## Version Updated

Version 0.1.0 - 4 March 2019

	Added Manual 'Sync With Ant' buttons added to the product and order UI forms.
		
		- Note: The orders can only sync if the products in the order are already in the Ant HQ system.

Version 0.0.4 - 4 March 2019

    Refactored installation process

        - Created user on installation
        - Added user to role
        - Ensured that oauth created was associated to that user
        - Deprecated roles attaching to each user in the system

Version 0.0.3
Version 0.0.2

	Adapt and edit some function to new hash products

		- Retrieve list products

		- Update,Create product simple and configruable

Version 0.0.1

	Release Ant Api.
	


## Installation
Please see the [Installation Guide](docs/installation.md).

## License
Please see the [LICENSE](LICENSE) file.