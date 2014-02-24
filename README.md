What is HackSpace:
=========================
It´s a Fork modified respecting licensing, regarding orientation and improvements in innovation concept of the app GLPI This app will be Oriented to: Management of the Smart Cities, Ideas for Social Innovation, entrepreneurial ideas, Coordination and Clusters Business, Living Labs and control in government sectors

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

