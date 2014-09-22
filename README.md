What is HackSpace:
=========================
It´s a Fork modified respecting licensing, regarding orientation and improvements in innovation concept of the app GLPI This app will be Oriented to: Management of the Smart Cities, Ideas for Social Innovation, entrepreneurial ideas, Coordination and Clusters Business, Living Labs and control in Government sectors.
GLPI is a combined resource management, incident management, problem management, project management, Contracts Management and trouble ticket software geared specifically at the IT industry. Even you can track your inventory of IOT/IOE(by SNMP with pluggins of the OCS Inventory NG and Nagios) With implementation, customization and service options ranging from a fast-to-deploy “powered-by” application marketplace, to an integrated white-label marketplace, or a completely custom storefront, Has a potencial of the high integration with Perl, Python and others trough RPC.
You could implement your API's with GLPI, and do all the development work to get your own CRM, ERP, Trackin Dashboard, and others running quickly and painlessly.
This App will help you to your Smart Cities projects for make your city a ISO 37120:2014 Compliant
This app is compatible with: ITIL 2011, ISO/IEC 20000 and PRINCE2 and compliant with ITIL v3

What I need to make my HackSpace a ISO 37120:2014 compliant?
============================================================
Just Convert your entities in a any of the 17 Factors of the ISO 37120 and Enjoy!

What is GLPI:
=========================

GLPI is the Information Resource-Manager with an additional Administration- Interface. You can use it to build up a database with an inventory for your company (computer, software, printers...). It has enhanced functions to make the daily life for the administrators easier, like a job-tracking-system with mail-notification and methods to build a database with basic information about your network-topology.

The principal functionalities of the application are :

1) the precise inventory of all the technical resources. All their characteristics will be stored in a database.

2) management and the history of the maintenance actions and the bound procedures. This application is dynamic and is directly connected to the users who can post requests to the technicians. An interface thus authorizes the latter with if required preventing the service of maintenance and indexing a problem encountered with one of the technical resources to which they have access.

More info: http://glpi-project.org

Running on OpenShift
--------------------

Create an account at http://openshift.redhat.com/

Create a PHP application with MySQL

	rhc app create hackspace php-5.3 mysql-5.1

Make a note of the username, password, and host name as you will need to use these to complete the HackSpace installation on OpenShift

Add this upstream hackspace quickstart repo

	cd hackspace/php
	rm -rf *
	git remote add upstream -m master git://github.com/elitelinux/hack-space.git
	git pull -s recursive -X theirs upstream master

Then push the repo upstream to OpenShift

	git push

That's it, you can now checkout your application at:

	http://hackspace-$yournamespace.rhcloud.com
	
	
More info:
-----------

http://www.infotelcorp.com/services/technical-expertise/glpi/

GLPI provides the following features:

Inventory Management
--------------------

GLPI provides controlled inventory management to

    Administer all IT components.
    Ensure comprehensive management (resources, costs).
    Contains dedicated wiki knowledge base.
    Provide efficient support.
    Control the entire life cycle of hardware.
    Ensure reliable and coherent IT portfolio status.

Support Management
------------------

GLPI provides comprehensive management of service desk processes.

    Single point of entry for user support.
    Complies with ITIL v2 – v3 standards.
    Efficient processing by IT service, with history log.
    Incident / Request / Issue / Change Management
    Administrative closing of incidents and satisfaction surveys

Reliable and efficient tool
---------------------------

GLPI integrates entirely with the company’s IT systems

    Minimum technical requirements
    Immediate implementation
    Intuitive use
    Customisable interface
    Synchronisation with enterprise directories
    Communication with messaging system

Add-ons
-------

Through the use of add-ons, GLPI lets you add several functionalities.
Infotel has developed around thirty add-ons for GLPI.

    Personnel time and attendance management.
    Mapping tool.
    Project management (changes).
    Material guarantee amounts from manufacturing sites.
    IT Rack management.
    Electronic orders and billing management.

Install the App
-------
This app was only tested in Linux, please don't try this in Windows, because your installation will be fail.
1) Download the app
2) unzip the app
3) Run this in the console
    mv hackspace/ /var/www/hackspace
    chmod -R 777 /var/www/hackspace/files/
    chmod -R 777 /var/www/hackspace/config/
4) Go to http://localhost in your server.    
