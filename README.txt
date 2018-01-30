These instructions describe how to install the Rich Media Activity Module for Moodle 3.1+.  This module is developped and supported by Adrien Jamot.

With this plugin you can
	- Synchronize video content with pictures (jpg,png...)
	- Display the richmedia in an HTML5 player
	- Have access to the tracking of the students

Prerequisites:
============
You need a:

	1.  A server running Moodle 3.1+
	2.  A browser HTML5 compatible
   

Installation
============

These instructions assume your Moodle server is installed at /var/www/moodle.

1.  Copy richmedia.zip to /var/www/moodle/mod
2.  Enter the following commands

	cd /var/www/moodle/mod
    	sudo unzip richmedia.zip

    This will create the directory
 
        ./richmedia
        
3.  Login to your moodle site as administrator

	Moodle will detect the new module and prompt you to Upgrade.
	
4.  Click the 'Upgrade' button.  

	The activity module will install mod_richmedia.
	
5.  Click the 'Continue' button. 

	You'll be prompted to configure the activity module.
	
6.  Enter the default width and height of your Rich Media Flash player on the page
7.  Click the 'Save Changes' button.

At this point, you can enter any course, turn editing on, and add a Rich Media activity link to the class.

Add a theme manually
====================
1. Create a directory with the name of your theme in the theme directory
2. Copy you file in the new directory :
	- logo.png for the top left logo
	- background.png for the background of the rich media (980*600px)

Upgrade note
============
Version 2.4 :
- Bug fixed
- New HTML5 Player
- Theme customisation (css file)
- Color picker
- Use Moodle JQuery version

Regards,... Adrien Jamot
adrien [at] edunao [dt] com
