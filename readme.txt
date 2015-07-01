=== Plugin Name ===
Contributors: Jan maat
Tags:  accommodations, appointment, availability, availability calendar, bed and breakfast, Booking, Booking calendar, booking form, booking system, bookings, calendar, contact form, event, event calendar, events, hotel, meeting, online booking calendar, online reservation, Reservation, reservation plugin, rooms, schedule, scheduling
Requires at least: 4.0
Tested up to: 4.2
Stable tag: trunk
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

A Calendar to show the day-to-day availability of a room/house with an integration with ContactForm 7 for on-line booking.


== Description ==
Availability Calendar & booking is a calendar to show the availability of whatever you might need it for (eg. holiday cottage, hotel rooms etc.).

The shortcode [availbooking name="Voorkamer"] inserts the calendar(screenshot-1) with the bookings information of the -Voorkamer- in the article. An article may contains different calendars each identified by a unique name.

The admin interface supports a database for the bookings and a database for the price information.

The bookings database contains all bookings for all bookable items (screenshot-2) With the menu -Add new booking- or with the -Add new booking- button a form is availabile to enter a new booking. (screenshot-3)

With an option the display of the calendar is swichted between one monht and a block of three months (see screenshot-6 and screenshot-10)

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

Calendar display: Show one month or a block of three months

Used in widget:
In an early stage of the page rendering the plugin checks the presents of the short code in one of more of the post on that page.
If present the required css and javascript files are added to the page.
As far as I know wp does not offer such an early hook for sidebar/widgets. The default text widget does not support shortcodes so a third party is required. Relying on these third party implementations is difficult therefore I added an option to the settings  -Used in widget- .
With this option set the required css and javascript files ar added to all pages. I think this is justifiable because widget are mostly shown on many pages.

Display Last Day as free: If set a new check in after a check out on the same day is allowed.

Show Weeknumbers: If set show weeknumbers in the calendar (screenshot 1).

Show Prices: If set show the price info in the calendar.



Minimum Nights: The minimum nigths allowed for the booking and is use in the contactform 7 bookings form.


Small Hotel mode: 
In the default mode there is 1 calendar per room identified by the name of the room as given in the -List of Rooms- parameter.

In the small Hotel moder there is 1 calendar per room type e.g standard room or deluxe room.
The -List of Rooms- parameter gives in this mode the room type with the number of rooms per type.

List of Rooms: List of bookable items separared by a comma.

Default mode:  room_name_1, room_name_2,room_name_3

Small Hotel mode: room_type_1:x,room_type_2:y,room_type_3:z
x,y and z are the number of rooms of that type.

Use fixed days for checkin and checkout. For the checkin and the checkout a fixed weekday can be selected. 
Exceptions can be set by combinations of two dates as 2015-07-25:2015-07-24,date-x:date-y. 
2015-07-24 replaces 2015-07-25, as for date-y and date-x.

Integration with ContactForm7.

The integrations with ContactForm7 not only sends the email with the booking information but also stores it directly into the WP Availability Calendar & Booking database.
It also adds the name of the calendar item (accommodation) to bookings form. (screenshot 7)

This is achieved by adding the shortcode [booking] to the form part of the ContactForm7 settings (screenshot 8) and the shortcode [booking]  into the message body (screenshot 9) .

The fields added to the database are:

Your Name - [text* your-name]


Your Email - [email* your-email]

Checkin Date - [date* start_date id:start_date date-format:dd-mm-yy first-day:1 min-date:0 change-month change-year]

Checkout Date - [date* end_date id:end_date date-format:dd-mm-yy first-day:1 min-date:0 change-month change-year]

Telephone Number - [text* your-phone]

Country - [select* your-country ....]

Correspondence language - [select* your-language "nl""en"]
 
These fields may be extended as possible in ContactForm7, e.g. a datepicker may be added.

There are three possible combinations of calendar and ContactForm7.

1. multiple calendars on one page with on that page the bookings form.
This is the default and needs no further actions as the above mentioned insert of the [booking] shortcodes.

2. one calendar with a for that calendar specific bookings form on the same page. So you need for each page a separate contactform.
In this case replace the [booking] in the form part of the contactform settings with 
[booking room_name]

3. There are multiple calendar pages (one for each room) with a generic bookings form page. In this case there is only one contactform needed as in situation 2 there is a contactform for each calendar page.
-The permalinks settings must be Post name.

-Set the  - Title of page with booking form -field in the settings (screenshot 6) to the name of the page with the generic bookings form.

-Insert  into the page with the calendar  a link to the  page with the generic form, add to the link part the name of the room. e.g. &lt;a href="http://localhost/wp/contact/voorkamer"&gt;Click here to book&lt;/a&gt;

In this example -contact- is the title of the page with the form and -voorkamer- the name of the room.

Default currency: Select the default currency format in the price info.

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
10. screenshot-10.png

== Changelog ==
Version 1.0.7
Warning: array_combine()  solved.

Version 1.0.6
Fixed checkin/checkout days with exceptions added.

Version 1.0.5
Datepicker issue solved
Link issue to central form solved

Version 1.0.4
Small Hotel mode added

Version 1.0.3
Added currency selection and support for use in a custom text widget

Version 1.0.2

Added default values settings for status, country and language to be used in a new booking.

Version 1.0.0

Generic booking form (ContactForm7) added
Readme.txt updated


Version 0.9.0

Display option with 3 month block added

Version 0.8.3

Option added to have a fixed value instead of drop down in the Contact Form 7

Version 0.8.2

Export status solved

Version 0.8.1

Navigation problem solved

Version 0.8

Include Export/import function.
Update Description op ContactForm7 integration.
Solved possible name conflicts in DB
