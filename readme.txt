=== Plugin Name ===
Contributors: Jan maat
Tags:  accommodations, appointment, availability, availability calendar, bed and breakfast, Booking, Booking calendar, booking form, booking system, bookings, calendar, contact form, event, event calendar, events, hotel, meeting, online booking calendar, online reservation, Reservation, reservation plugin, rooms, schedule, scheduling
Requires at least: 4.0
Tested up to: 4.1.1
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Calendar to show the day-to-day availability of a room/house with an integration with ContactForm 7 for on-line booking.


== Description ==
Availability Calendar & booking is a calendar to show the availability of whatever you might need it for (eg. holiday cottage, hotel rooms etc.).

The shortcode [availbooking name="Voorkamer"] inserts the calendar(screenshot-1) with the bookings information of the -Voorkamer- in the article. An article may contains different calendars each identified by a unique name.

The admin interface supports a database for the bookings and a database for the price information.

The bookings database contains all bookings for all bookable items (screenshot-2) With the menu -Add new booking- or with the -Add new booking- button a form is availabile to enter a new booking. (screenshot-3)

Name: select the name from the list of available bookable items (see settings)
Status:
- Requested (Booking filled by the Booking Form, displayed as free)
- Reserved (Booking accepted and displayed as busy but waiting for payment)
- Booked (Booking accepted and displayed as busy)
- Rejected (Displayed as free)
Check in
Check out
Email: The email address of the applicant.
Phone number: The phone number of the applicant.
Country: The country of the applicant.
Language: The preferred language of the applicant.

The price database contains the price definitions.(screenshot-4)
The example of screenshot 4 reads as, the price of the -Voorkamer- is euro 65,00 from 2015-01-05 till 2015-05-21 and is euro 75,00 from 2015-05-22 on.
With the menu -Add new price- or with the -Add new price- button a form is availabile to enter a new price. (screenshot 5)
Name: select the name from the list of available bookable items (see settings)
Date: Start date of the new price.
Price: The new price.

The menu Settings->WP Availability Calendar & Bookings Settings opens the settings form. (screenshot 6)
Display Last Day as free: If set a new check in after a check out on the same day is allowed.
Show Weeknumbers: If set show weeknumbers in the calendar (screenshot 1).
Show Prices: If set show the price info in the calendar.
Use Dollar sign: If set use the dollar sign and currency format in the price info.
Minimum Nights: The minimum nigths allowed for the booking and is use in the contactform 7 bookings form.
List of Rooms: List of bookable items separated by a comma.

Integration with the plugin ContactForm7 supports a booking form. (screenshot 7) 
This is achieved by adding the shortcode [booking] to the form part of the ContactForm7 settings (screenshot 8) and the shortcode [booking]  to the message body (screenshot 9) .
The short code [booking] in the form part of contactform7 insert the -SELECT ROOM- field. It also stores the booking info in the database at send.
In the message it includes the selection. 

Import/Export

With the menu -Import & Export- the bookings and price info can be exported and imported in a new installation. The export file is in xml format.

== Installation ==

1. Upload the entire jm_avail_booking folder to the /wp-content/plugins/directory.
2. Activate the plugin through the 'Plugins' menu in WordPress
3. Set the settings in Admin->settings->WP Availability & Bookings Settings

== Frequently Asked Questions ==



== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png
4. screenshot-4.png
5. screenshot-5.png
6. screenshot-6.png
7. screenshot-7.png
8. screenshot-8.png
9. screenshot-9.png

== Changelog ==
Version 0.8

Include Export/import function.
Update Description op ContactForm7 integration.
Solved possible name conflicts in DB