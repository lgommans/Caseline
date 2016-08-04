# Caseline

Make a timeline for your forensics case

Interface overview with lots of events loaded:

![Caseline interface, loaded with events](http://lgms.nl/files/caseline-overview.png)

When zooming in on events, details quickly become clear:

![Some events, without interface](http://lgms.nl/files/caseline-events.png)

## About

Caseline will display events on a timeline. Navigating and zooming into
moments and events of interest is easy and quick.

Events are read in CSV format with the following fields:

- Datetime, e.g. December 12 2015 09:49:13
- Device, e.g. work pc
- Source, e.g. Firefox
- Summary, e.g. Searched resignation letter template
- Details, e.g. https://www.google.com/search?q=resignation+letter+template

You can choose which period of time to view, search/filter events (optionally
using a regex) and save interesting views.

## Getting started

You need a PHP server with php5-sqlite installed. In index.html, edit the case's
start and ending time, place the Caseline folder in the webserver and browse to
it in Firefox (other browsers are untested, though Chrome is probably not much
trouble).

Importing events can be done via the web interface (not recommended) or via the
command line: `./controlDB.php load my.csv`. Use `./controlDB.php` without
arguments for usage info.

You can zoom in on periods of time by scrolling, or using the up and down arrow
keys. Going forwards or backwards in time (viewing earlier or later events) can
be done with the left and right arrow keys, or using the + and - buttons on
screen.

If you see something interesting and you want to get back to it later, you can
save your current view.

If you see nothing and zooming out does not work anymore, use the 'Show all
time' button on top. This is a known bug (see issue #1).

## Display

In the top left corner you see the Save View button, which prompts you for a
name to save the current view under. To the right of that you can load saved
views or delete unnecessary views.

To the right of that you see a button to Import Events (and some other database
management stuff). This takes you to another page.

To the right of that you can see how many events there are in total, how many
matched your filter (if you set a filter), and how many are currently in your
view (if you zoomed in).

On the next line there is a - button which scrolls back in time if you press
it. The left arrow key does the same. The minus turns red when there are older
events outside of your view.

To the right of that is a field which displays the from-time of your current
view. You can edit this field.

To the right of that is a "Show all time" button which adjusts the from- and
until-time so all events are visible (after applying the filter).

Then you get the filter field with options to the right of that. You can click
the blue 'adv' for an explanation of the advanced filter feature. Deselect
regex to search without regexes.

Finally on the far right of the screen is the until field, which works the same
as the from field, and the + button which works the same as the minus on the
left did. It also turns red when there are events newer than your current view.

Below this line (the third line basically) you see a horizontal line, and below
that, a block pattern. Each block, white or light green, represents a day. If
you zoom in, you will see another block pattern below this one, where each
block represents an hour. This is to visualize how far you have zoomed in.

The rest of the screen is filled with events with alternating white and grey
background. Hovering over an event brings it to the foreground. Clicking it
displays details. Events always have the correct position on the timeline,
horizontally. Vertical position means nothing, that is only to make them easier
to read.

## Security

No security testing has been done. It is assumed you place this on your local
machine, an internal network with access control, or on a website secured with
some login mechanism (like .htpasswd).

Security fixes are welcome, but think for a minute about the threat model
before you try to fix anything. There is no separation of privileges so anyone
can (by design) overwrite the parsed.csv file, download the database, or even
truncate the database. Anything that causes RCE or LFI is certainly a valid
vulnerability though.

## CSV format

Datetime is in unix time format. Strings can be quoted. Example:

    1234567890,work pc,$!(*N%8\,"String, with commas.",

The special charactres in the 'source' field are all fine and will not
terminate the field. The backslash is no escape character. The "String, with
commas." will be imported without the surrounding quotes. The details field,
the last field on the line, is empty. Or if the summary would be empty, the
line would end in `...N%8\,,`

The web interface also has a feature to turn normal log files into a Caseline
csv file, and it has been tested... but people never used it (I ended up
importing all events myself) so I don't know how practical it is to use that.

## Known bugs / untested things

- The views thing is used only sporadically
- Importing logs or CSVs via the webinterface is a bit cumbersome
- Multi-year or year-boundary-spanning cases are *completely* untested and has
  not been kept in mind while writing the application.
- Multi-line values, quoted or not, are not supported. A small modification in
  controlDB.php should fix that.

Ready for production? Meh, it worked for me. If you spend 1 hour getting into
the code and 4 hours customizing your needs, it probably works for you in
production as well. (Assuming you have mostly the same goals and only need to
adjust/tweak some things.)

## Code

The code is structured as follows:

The user loads index.html, which is just HTML (with all buttons and divs) and
CSS. This loads:

- lib.js which are some generic functions.
- events.js which binds all buttons, the scroll wheel, arrow keys, etc.
- functions.js which contains functions to go forward in time, refresh the
  view, etc. These functions are absolutely application-specific whereas
  lib.js' functions are not necessarily.

After this it bootstraps the application which calls api.php to load all events
and all views. The index.html file also contains a few settings that you will
need to adjust (e.g. the case's from and until time which limits zooming and
stuff).

Api.php is a really dumb thing that actually just calls functions.php.
Functions.php also opens the database.

ControlDB.php can be used from the command line and is also called by
webmanipulator. It contains the csv parser.

Finally webmanipulator.php is the web interface for database manipulation. It
does some log parsing to create parsed.csv but contains little logic.
