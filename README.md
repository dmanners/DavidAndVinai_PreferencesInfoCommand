bin/magento command to display configured preferences for classes or interfaces
===============================================================================

Exploratory coding of plugins for the bin/magento cli tool.
Maybe this simple command module is useful as an example for someone.

Description
-----------
Written on a train. On the way home from #mm15nl. In about an hour. Pretty neat, hu?


Usage
-----
Run `/bin/magento help preferences:info`to find out.

Example:

```sh
% bin/magento preferences:info LoggerInterface
Psr\Log\LoggerInterface => Magento\Framework\Logger\Monolog
Magento\Framework\DB\LoggerInterface => Magento\Framework\DB\Logger\Null
```

Compatibility
-------------
- Magento 2 version 0.74.0-beta10

Installation Instructions
-------------------------
Install it. composer.json following, maybe.

Uninstallation
--------------
Why? Sin! Heresy!

Known Issues
------------
Currently the module only searches global and adminhtml scope preferences.  
Adding an option to specify the preference config area is on the todo list.

Developers
----------
David Manners & Vinai Kopp  
Twitter: [@VinaiKopp](https://twitter.com/VinaiKopp)  
Twitter: [@mannersd](https://twitter.com/mannersd)

Licence
-------
BSD 3-Clause.

Copyright
---------
(c) 2015 Vinai Kopp, David Manners
