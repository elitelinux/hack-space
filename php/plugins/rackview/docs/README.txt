RackView GLPI-Plugin
====================

Introduction
------------

Mount your GLPI-objects in virtual racks.

* Easy setup of racks
* Dynamically mount computers, network devices or other devices into a rack
* Mount one device into multiple racks (useful for KVM, cable management, etc.)
* Display a rack summary showing all racks in all locations
* Mount a maximum of three standing devices on rack floors by mounting them
  left, middle or right
* Mount front and back devices into one single row

Installation
------------

Extract the plugin archive and copy the files into your GLPI-ROOT/plugins-
directory. The plugin should then show up in the GLPI user interface.

Simply click "install" and "enable" afterwards to start.

Creating Racks
--------------

To create a rack, simply go to "Plugins/Rackview" and hit the "Add object"-
icon (usually a plus-sign). Enter the details of your rack:

* Name: A (short) name of the rack. This name will later be selectable when
  mounting an object
* Size: Size (in rack units) of the rack. Typically 42
* Description: A long description about the rack
* Location: Physical location of the rack (needed for the Summary view)

Mounting objects
----------------

After you have created a rack, go to a supported object type and open the
tab "RackView".

Currently these types are supported:

* Computers
* Network equipments
* "Other" Devices

First, specify the default size of the object in rack units on the right side
of the screen.

After that select a rack on the left side and enter the starting (lower) rack
unit where the object should be mounted.

The starting unit can also be clicked in the rack mini view.

You can additionally set the following options:

* Size: If you deselect "use standard" you can specify an object size (in rack
  units) for this specific mount. This is useful if you have some kind of
  meta objects like KVM-devices or cable management, for which you don't want
  to create multiple objects
* Depth: Specify the depth-size of the object. If the object is only mounted
  on the front of the rack, select "Front", if it is on the back select "back".
  If it has full lenght, select "full". This allows you to specify two
  objects on the same row
* Horizontal: Specify a horizontal position. This is useful if you have standing
  devices in your rack, which only take a third of the rack's width.
  Here you can specify, if the object is placed left, in the middle or right or
  (in case of a usual 19"-device) takes the complete ("Full") width of the rack
* Description: Additional notes about the mount

Unmounting objects
------------------

If you'd like to remove an object from a rack, simply click the "Unmount"
button on the specific mount.

NOTE: If you purge an object from GLPI it is also removed from the rack!

Rack summary
------------

When you click the summary-icon (usually an eye-icon) while in the
Plugin/RackView view, an overview of all locations and all racks within
the location is shown.

This gives you a quick overview of available rack space in your datacenter.